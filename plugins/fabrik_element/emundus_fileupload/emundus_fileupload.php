<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Image;
use Fabrik\Helpers\Uploader;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.emundus_fileupload
 * @since       3.0
 */
class PlgFabrik_ElementEmundus_fileupload extends PlgFabrik_Element {

    /**
     * Storage method adaptor object (filesystem/amazon s3)
     * needs to be public as models have to see it
     *
     * @var object
     */
    public $storage = null;

    /**
     * @return bool
     */
    public function onAjax_upload() {

        jimport('joomla.filesystem.file');

        $jinput = $this->app->input;
        $current_user = JFactory::getSession()->get('emundusUser');

        if (JFactory::getUser()->guest) {
            echo json_encode(['status' => 'false']);
            return false;
        }

        $fnum = $jinput->post->get('fnum');
        $name = $jinput->post->get('elementId');

        $user = (int)substr($fnum, -7);
        $db = JFactory::getDBO();

        $attachId = $jinput->post->get('attachId');

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $can_submit_encrypted = ($jinput->post->get('encrypt') == 2)?$eMConfig->get('can_submit_encrypted', 1):$jinput->post->get('encrypt');

        $attachmentResult = $this->getAttachment($attachId);
        $label = $attachmentResult->lbl;

        $files = $jinput->files->get('file');

        $cid = $this->getCampaignId($fnum);
        $uploadResult = $this->getUploads($attachId, $user, $cid, $fnum);
        $nbAttachment = count($uploadResult);
        $lengthFile = count($files);
        $nbMaxFile = (int)$attachmentResult->nbmax;

        $acceptedExt = [];

        if (!file_exists(EMUNDUS_PATH_ABS.$user)) {
            // An error would occur when the index.html file was missing, the 'Unable to create user file' error appeared yet the folder was created.
            if (!file_exists(EMUNDUS_PATH_ABS.'index.html')) {
                touch(EMUNDUS_PATH_ABS.'index.html');
            }

            if (!mkdir(EMUNDUS_PATH_ABS.$user) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user.DS.'index.html')){
                $error = JUri::getInstance().' :: USER ID : '.$user.' -> Unable to create user file';
                JLog::add($error, JLog::ERROR, 'com_emundus');
                return false;
            }
        }
        chmod(EMUNDUS_PATH_ABS.$user, 0755);

        foreach ($files as $file) {

            $fileName = $this->getFileName($user, $attachId, $label, $file['name'], $fnum);

            $tmp_name = $file['tmp_name'];
            $fileSize = $file['size'];

            $target = $this->getPath($user,$fileName);

            $extension = explode('.', $fileName);
            $extensionAttachment = $attachmentResult->allowed_types;
            $typeExtension = $extension[1];

            $acceptedExt[] = stristr($extensionAttachment, $typeExtension);

            if (!in_array(false, $acceptedExt)) {
                $ext = true;

                if ($can_submit_encrypted == 0 &&  $typeExtension == 'pdf') {
                    if ($this->isEncrypted($tmp_name) == 1) {
                        $encrypt = false;
                    } else {
                        $encrypt = true;
                    }
                }

                // The maximum size is equal to the smallest of the two sizes, either the size configured in the plugin or in the server itself.
                $postSize = $jinput->post->getInt('size', 0);
                $iniSize = $this->file_upload_max_size();
                $sizeMax = ($postSize >= $iniSize) ? $iniSize : $postSize;

                if (!empty($fileName)) {
                    $insert[] = $user.' , '.$db->quote($fnum).' , '.$cid.' , '.$attachId.' , '.$db->quote($fileName).' , '.'0'.' , '.'1';
                }


                $fileLimitObtained = false;

                if (($lengthFile + $nbAttachment) > $nbMaxFile) {
                    $fileLimitObtained = true;
                } else {
                    if ($fileSize < $sizeMax) {
                        move_uploaded_file($tmp_name, $target);
                        $size = true;
                    } else {
                        $size = false;
                    }
                }

                $sizeMax = $this->formatBytes($sizeMax);

                $result[] = array('size' => $size, 'ext' => $ext, 'nbMax' => $fileLimitObtained, 'filename' => $fileName, 'target' => $target,'nbAttachment' => $nbAttachment, 'encrypt' => $encrypt, 'maxSize' => $sizeMax);
            } else {
                $size = true;
                $ext = false;
                $result[] = array('size' => $size, 'ext' => $ext,  'filename' => $fileName, 'target' => $target,'nbAttachment' => $nbAttachment);
            }

        }

        if (empty($uploadResult) && $attachmentResult->nbmax >= 1 && $fileLimitObtained === false) {
            $this->insertFile($insert);
        }

        if (!empty($uploadResult)) {

            if ($fileLimitObtained != false) {
                $fileNameUpdate = $jinput->post->get($name.'_filename0');
                if (!empty($fileNameUpdate)) {
                    $this->updateFile($fnum, $cid, $attachId, $fileNameUpdate);
                }
            }
            else {
                $this->insertFile($insert);
            }
        }

        // track the LOGS (ATTACHMENT_CREATE)
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $user = JFactory::getSession()->get('emundusUser'); # logged user #

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $mFile = new EmundusModelFiles();
        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

        EmundusModelLogs::log($user->id, $applicant_id, $fnum, 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE');

        echo json_encode($result);
        return true;
    }

    // Returns a file size limit in bytes based on the PHP upload_max_filesize
    // and post_max_size
    private function file_upload_max_size() {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = $this->parse_size(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    private function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = array('KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }


    /**
     * @return bool
     */
    public function onAjax_attachment() {

        $jinput = $this->app->input;

        $current_user = JFactory::getSession()->get('emundusUser');
        if (JFactory::getUser()->guest) {
            echo json_encode(['status' => 'false']);
            return false;
        }

        $fnum = $jinput->post->get('fnum');
        $user = (int)substr($fnum, -7);

        $attachId = $jinput->post->get('attachId');
        $cid = $this->getCampaignId($fnum);
        $uploadResult = $this->getUploads($attachId, $user, $cid, $fnum);

        $attachmentResult = $this->getAttachment($attachId);
        $nbMaxFile = (int)$attachmentResult->nbmax;
        $result = array('limitObtained' => $nbMaxFile<=sizeof($uploadResult));

        foreach ($uploadResult as $upload) {
            if (!empty($upload->filename)) {
                $fileName = $upload->filename;
            }

            $target = '/images'.DS.'emundus'.DS.'files'.DS.$user.DS.$fileName;
            $result['files'][] = array('filename' => $fileName, 'target' => $target);

        }

        echo json_encode($result);
        return true;

    }


    /**
     * @return bool
     * @throws Exception
     */
    public function onAjax_delete() {

        $jinput = $this->app->input;
        $current_user = JFactory::getSession()->get('emundusUser');

        $fileName = $jinput->post->get('filename');
        $attachId = $jinput->post->get('attachId');

        if (!EmundusHelperAccess::isApplicant($current_user->id)) {
            return false;
        }

        $fnum = $jinput->post->get('fnum');
        $user = (int)substr($fnum, -7);

        $cid = $this->getCampaignId($fnum);
        $uploadResult = $this->getUploads($attachId, $user, $cid, $fnum);
        $target = $this->getPath($user, $fileName);

        if (file_exists($target) && !empty($uploadResult)) {
            unlink($target);
            $this->deleteFile($fileName, $fnum, $cid, $attachId);
            $status = true;
        }
        if (!file_exists($target) && !empty($uploadResult)) {
            $this->deleteFile($fileName, $fnum, $cid, $attachId);
            $status = true;
        }
        if (!file_exists($target) && empty($uploadResult)) {
            $status = false;
        }

        // track the LOGS (ATTACHMENT_DELETE)
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $user = JFactory::getSession()->get('emundusUser'); # logged user #

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $mFile = new EmundusModelFiles();
        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

        EmundusModelLogs::log($user->id, $applicant_id, $fnum, 4, 'd', 'COM_EMUNDUS_ACCESS_ATTACHMENT_DELETE');

        echo json_encode(['status' => $status]);
        return true;
    }

    public function dataConsideredEmptyForValidation($data, $repeatCounter) {
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->post->get($this->getTableName().'___fnum');
        $user = (int)substr($fnum, -7);

        $attachId = $this->getAttachId();
        $cid = $this->getCampaignId($fnum);
        $uploadResult = $this->getUploads($attachId,$user,$cid, $fnum);

        return (empty($uploadResult) && $data == "");
    }


    /**
     * @return String
     * @throws Exception
     */
    public function getFormId() {
        $jinput = JFactory::getApplication()->input;
        return $jinput->get('formid');
    }


    /**
     * @return String
     * @throws Exception
     */
    public function getItemId() {
        $jinput = JFactory::getApplication()->input;
        return $jinput->get('Itemid', null);
    }


    /**
     * @return mixed
     */
    public function getAttachId() {
        $params = $this->getParams();
        return $params->get('attachmentId');
    }


    /**
     * @param $fnum
     *
     * @return Int
     */
    public function getCampaignId($fnum) {

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('campaign_id'))
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . " LIKE " . $db->quote($fnum));
        $db->setQuery($query);

        return $db->loadResult();
    }


    /**
     * @param $attachId
     *
     * @return mixed
     */
    public function getAttachment($attachId) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('esa.lbl', 'esa.value', 'esa.allowed_types', 'esa.nbmax')))
            ->from($db->quoteName('#__emundus_setup_attachments', 'esa'))
            ->where($db->quoteName('id') . ' = ' . $attachId);
        $db->setQuery($query);

        return $db->loadObject();
    }


    /**
     * @param $attachId
     * @param $uid
     * @param $cid
     *
     * @return mixed
     */
    public function getUploads($attachId, $uid, $cid, $fnum) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('id'),$db->quoteName('filename')))
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('attachment_id') . ' = ' . $attachId . ' AND ' . $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
        $db->setQuery($query);

        return $db->loadObjectList();
    }


    /**
     * @param $uid
     * @param $fileName
     *
     * @return string
     */
    public function getPath($uid, $fileName) {
        return EMUNDUS_PATH_ABS.$uid.DS.$fileName;
    }


    /**
     * @param $user
     * @param $attachId
     * @param $label
     * @param $file
     *
     * @return mixed
     */
    public function getFileName($user, $attachId, $label, $file, $fnum) {
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $m_profile = new EmundusModelProfile();
        $m_cheklist = new EmundusModelChecklist();
        $m_files = new EmundusModelFiles();

        $profile = $m_profile->getProfileByApplicant($user);
        $fnumInfos = $m_files->getFnumInfos($fnum);
        $fileName = $m_cheklist->setAttachmentName($file, $label, $fnumInfos);

        /*$fileName = strtolower(preg_replace(array('([\40])', '([^a-zA-Z0-9-])', '(-{2,})'), array('_', '', '_'), preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', htmlentities(strtoupper($profile['lastname']) . '_' . ucfirst($profile['firstname']), ENT_NOQUOTES, 'UTF-8'))));
        $fileName .= $label . '-' . rand() . '.' . pathinfo($file, PATHINFO_EXTENSION);*/
        return JFile::makeSafe($fileName);
    }


    /**
     * @param             $data
     * @param   stdClass  $thisRow
     * @param   array     $opts
     *
     * @return string
     */
    public function ListData($data, stdClass &$thisRow, $opts = array()) {
        $profiler = JProfiler::getInstance('Application');
        JDEBUG ? $profiler->mark("renderListData: {$this->element->plugin}: start: {$this->element->name}") : null;

        $data = FabrikWorker::JSONtoData($data, true);

        foreach ($data as &$d) {
            $d = $this->format($d);
        }

        return parent::renderListData($data, $thisRow, $opts);
    }


    /**
     * Format the string for use in list view, email data
     *
     * @param mixed $d data
     * @param bool $doNumberFormat run numberFormat()
     *
     * @return string
     */
    protected function format(&$d, $doNumberFormat = true) {

        if ($doNumberFormat) {
            $d = $this->numberFormat($d);
        }

        return $d;
    }


    /**
     * Prepares the element data for CSV export
     *
     * @param string $data Element data
     * @param object  &$thisRow All the data in the lists current row
     *
     * @return  string    Formatted CSV export value
     */
    public function renderListData_csv($data, &$thisRow) {
        return $this->format($data);
    }


    /**
     * Draws the html form element
     *
     * @param array $data To pre-populate element with
     * @param int $repeatCounter Repeat group counter
     *
     * @return  string    elements html
     */
    public function render($data, $repeatCounter = 0) {

        JHTML::stylesheet('plugins/fabrik_element/emundus_fileupload/css/emundus_fileupload.css');
        JHTML::script('plugins/fabrik_element/emundus_fileupload/emundus_fileupload.js');

        $params = $this->getParams();
        $element = $this->getElement();
        $bits = $this->inputProperties($repeatCounter);

        if (is_array($this->getFormModel()->data)) {
            $data = $this->getFormModel()->data;
        }

        $value = $this->getValue($data, $repeatCounter);
        if (!$this->getFormModel()->failedValidation()) {
            $value = $this->numberFormat($value);
        }

        if (!$this->isEditable()) {

            $value = $this->format($value, false);
            $value = $this->getReadOnlyOutput($value, $value);

            return ($element->hidden == '1') ? "<!-- ".$value." -->" : $value;

        }

        if (version_compare(phpversion(), '5.2.3', '<')) {
            $bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
        } else {
            $bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
        }

        $bits['class'] .= ' ' . $params->get('text_format');
        $bits['attachmentId'] = $params->get('attachmentId');
        $bits['size'] = $params->get('size');

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $bits['encrypted'] = ($params->get('encrypt') == 2)?$eMConfig->get('can_submit_encrypted', 1):$params->get('encrypt');

        $layout = $this->getLayout('form');
        $layoutData = new stdClass;
        $layoutData->attributes = $bits;
        $layoutData->sizeClass = $params->get('bootstrap_class', '');

        return $layout->render($layoutData);
    }


    /**
     * Determines the value for the element in the form view
     *
     * @param array $data Form data
     * @param int $repeatCounter When repeating joined groups we need to know what part of the array to access
     * @param array $opts Options, 'raw' = 1/0 use raw value
     *
     * @return  string    value
     */
    public function getValue($data, $repeatCounter = 0, $opts = array()) {
        $value = parent::getValue($data, $repeatCounter, $opts);

        if (is_array($value)) {
            return array_pop($value);
        }

        return $value;
    }


    /**
     * Get the link options
     *
     * @return  array
     */
    protected function linkOpts() {
        $fbConfig = JComponentHelper::getParams('com_fabrik');
        $params = $this->getParams();
        $target = $params->get('link_target_options', 'default');
        $opts = array();
        $opts['rel'] = $params->get('rel', '');

        switch ($target) {
            default:
                $opts['target'] = $target;
                break;
            case 'default':
                break;
            case 'lightbox':
                FabrikHelperHTML::slimbox();
                $opts['rel'] = 'lightbox[]';

                if ($fbConfig->get('use_mediabox', false)) {
                    $opts['target'] = 'mediabox';
                }

                break;
        }

        return $opts;
    }

    /**
     * Returns javascript which creates an instance of the class defined in formJavascriptClass()
     *
     * @param int $repeatCounter Repeat group counter
     *
     * @return  array
     */
    public function elementJavascript($repeatCounter) {
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);

        JText::script('PLG_ELEMENT_FIELD_SUCCESS');
        JText::script('PLG_ELEMENT_FIELD_EXTENSION');
        JText::script('PLG_ELEMENT_FIELD_ENCRYPT');
        JText::script('PLG_ELEMENT_FIELD_ERROR');
        JText::script('PLG_ELEMENT_FIELD_ERROR_TEXT');
        JText::script('PLG_ELEMENT_FIELD_SIZE');
        JText::script('PLG_ELEMENT_FIELD_LIMIT');
        JText::script('PLG_ELEMENT_FIELD_SURE');
        JText::script('PLG_ELEMENT_FIELD_SURE_TEXT');
        JText::script('PLG_ELEMENT_FIELD_CONFIRM');
        JText::script('PLG_ELEMENT_FIELD_CANCEL');
        JText::script('PLG_ELEMENT_FIELD_DELETE');
        JText::script('PLG_ELEMENT_FIELD_DELETE_TEXT');
        JText::script('PLG_ELEMENT_FIELD_ACCESS');
        JText::script('PLG_ELEMENT_FIELD_UPLOAD');
        JText::script('PLG_ELEMENT_FILEUPLOAD_UPLOADED_FILES');

        return array('FbField', $id, $opts);
    }

    /**
     * Get the class to manage the form element
     * to ensure that the file is loaded only once
     *
     * @param array   &$srcs Scripts previously loaded
     * @param string $script Script to load once class has loaded
     * @param array   &$shim Dependant class names to load before loading the class - put in requirejs.config shim
     *
     * @return void|boolean
     */
    public function formJavascriptClass(&$srcs, $script = '', &$shim = array()) {
        $key = FabrikHelperHTML::isDebug() ? 'element/field/field' : 'element/field/field-min';

        $s = new stdClass;

        // Even though fab/element is now an AMD defined module we should still keep it in here
        // otherwise (not sure of the reason) jQuery.mask is not defined in field.js

        // Seems OK now - reverting to empty array
        $s->deps = array();

        if (array_key_exists($key, $shim)) {
            $shim[$key]->deps = array_merge($shim[$key]->deps, $s->deps);
        } else {
            $shim[$key] = $s;
        }

        parent::formJavascriptClass($srcs, $script, $shim);
        return false;
    }


    /**
     * Can the element plugin encrypt data
     *
     * @return  bool
     */
    public function canEncrypt() {
        return true;
    }


    /**
     * Manipulates posted form data for insertion into database
     *
     * @param mixed $val This elements posted form data
     * @param array $data Posted form data
     *
     * @return  mixed
     */
    public function storeDatabaseFormat($val, $data) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $val[$k] = $this->_indStoreDatabaseFormat($v);
            }

            $val = implode(GROUPSPLITTER, $val);
        } else {
            $val = $this->_indStoreDatabaseFormat($val);
        }

        return $val;
    }


    /**
     * Manipulates individual values posted form data for insertion into database
     *
     * @param string $val This elements posted form data
     *
     * @return  string
     */
    protected function _indStoreDatabaseFormat($val) {
        return $this->unNumberFormat($val);
    }


    public function upload($tmpFile, $filepath) {
        $this->uploadedFilePath = $filepath;

        $params = $this->getParams();
        $allowUnsafe = $params->get('allow_unsafe', '0') === '1';

        if (JFile::upload($tmpFile, $filepath, false, $allowUnsafe)) {
            return $this->createIndexFile(dirname($filepath));
        }

        return false;
    }


    /**
     * @param $values
     *
     * @throws Exception
     */
    public function insertFile($values) {
        if (!empty($values)) {


            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $columns = array('user_id', 'fnum', 'campaign_id', 'attachment_id', 'filename', 'can_be_deleted', 'can_be_viewed');

            $query->insert($db->quoteName('#__emundus_uploads'))
                ->columns($db->quoteName($columns))
                ->values($values);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage('Probrème survenu au téléchargement des fichiers', 'message');
            }
        }
    }


    /**
     * @param $fnum
     * @param $cid
     * @param $attachId
     * @param $fileName
     *
     * @throws Exception
     */
    public function updateFile($fnum, $cid, $attachId, $fileName) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__emundus_uploads'))
            ->set($db->quoteName('filename') . " = " . $db->quote($fileName))
            ->where($db->quoteName('campaign_id') . ' = ' . $cid . " AND " . $db->quoteName('attachment_id') . " = " . $attachId . " AND " . $db->quoteName('fnum') . " LIKE " . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage('Problème survenu à la mise à jour des fichiers', 'message');
        }

    }


    /**
     * @param $fileName
     * @param $fnum
     * @param $cid
     * @param $attachId
     *
     * @throws Exception
     */
    public function deleteFile($fileName,$fnum, $cid, $attachId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('filename'). " LIKE " . $db->quote($fileName) . ' AND ' .$db->quoteName('campaign_id') . ' = ' . $cid . " AND " . $db->quoteName('attachment_id') . " = " . $attachId . " AND " . $db->quoteName('fnum') . " LIKE " . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage('Problème survenu à la suppression des fichiers', 'message');
        }

    }


    /**
     * @param $file
     *
     * @return bool|false|int
     */
    public function isEncrypted($file) {
        $f = fopen($file, 'rb');
        if (!$f) {
            return false;
        }

        //Read the last 320KB
        fseek($f, -323840, SEEK_END);
        $s = fread($f, 323840);

        //Extract Info object number
        return preg_match('/Encrypt ([0-9]+) /', $s);
    }
}
