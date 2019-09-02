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
     * Is the element an upload element
     *
     * @var bool
     */
    //protected $is_upload = true;

    /**
     * Storage method adaptor object (filesystem/amazon s3)
     * needs to be public as models have to see it
     *
     * @var object
     */
    public $storage = null;


    /**
     * @return bool
     * @throws Exception
     */
    public function onBeforeStore() {

        jimport('joomla.filesystem.file');

        $current_user = JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;
        $db = JFactory::getDBO();

        if (JFactory::getUser()->guest) {

            $status='false';
            $result = array('status' => $status);
            echo json_encode($result);
            return false;
        }

        $attachId = $this->getAttachId();
        $name = $this->getFullName();
        $cid = $this->getCampaignId($current_user->fnum);

        $attachmentResult = $this->getAttachment($attachId);
        $uploadResult = $this->getUploads($attachId, $current_user->id, $cid);

        $nbMax = (int)$attachmentResult->nbmax;
        $insert = [];

        for ($i = 0; $i < $attachmentResult->nbmax; $i++) {
            $fileName = $jinput->post->get($name.'_filename'.$i);
            if (!empty($fileName)) {
                $insert[] = $current_user->id.' , '.$db->quote($current_user->fnum).' , '.$cid.' , '.$attachId.' , '.$db->quote($fileName).' , '.'0'.' , '.'1';
            }
        }

        if (empty($uploadResult) && $attachmentResult->nbmax >= 1) {
            $this->insertFile($insert);
        }

        if (!empty($uploadResult)) {
            if ($nbMax == 1) {
                $fileNameUpdate = $jinput->post->get($name.'_filename0');
                if (!empty($fileNameUpdate)) {
                    $this->updateFile($current_user->fnum, $cid, $attachId, $fileNameUpdate);
                }

            }
            if ($nbMax > 1 && count($uploadResult) < $nbMax) {
                $this->insertFile($insert);
            }
        }

        return true;
    }


    /**
     * @return bool
     */
    public function onAjax_upload() {

        jimport('joomla.filesystem.file');

        $jinput = $this->app->input;
        $current_user = JFactory::getSession()->get('emundusUser');

        if (JFactory::getUser()->guest) {
            
            $status='false';
            $result = array('status' => $status);
            echo json_encode($result);
            return false;
        }
        $attachId = $jinput->post->get('attachId');
        $can_submit_encrypted = $jinput->post->get('encrypt');

        $attachmentResult = $this->getAttachment($attachId);
        $label = $attachmentResult->lbl;

        $files = $jinput->files->get('file');
        $cid = $this->getCampaignId($current_user->fnum);

        $uploadResult = $this->getUploads($attachId, $current_user->id, $cid);
        $nbAttachment = count($uploadResult);
        $lengthFile = count($files);
        $nbMaxFile = (int)$attachmentResult->nbmax;

        $acceptedExt = [];

        if (!file_exists(EMUNDUS_PATH_ABS.$current_user->id)) {
            // An error would occur when the index.html file was missing, the 'Unable to create user file' error appeared yet the folder was created.
            if (!file_exists(EMUNDUS_PATH_ABS.'index.html'))
                touch(EMUNDUS_PATH_ABS.'index.html');

            if (!mkdir(EMUNDUS_PATH_ABS.$current_user->id) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$current_user->id.DS.'index.html')){
                $error = JUri::getInstance().' :: USER ID : '.$current_user->id.' -> Unable to create user file';
                JLog::add($error, JLog::ERROR, 'com_emundus');


                return false;
            }
        }
        chmod(EMUNDUS_PATH_ABS.$current_user->id, 0755);

        foreach ($files as $file) {

            $fileName = $this->getFileName($current_user, $attachId, $label, $file['name']);
            $tmp_name = $file['tmp_name'];
            $fileSize = $file['size'];
            $target = $this->getPath($current_user->id,$fileName);
            $size = $jinput->post->get('size');

            $extension = explode('.', $fileName);
            $extensionAttachment = $attachmentResult->allowed_types;
            $typeExtension = $extension[1];

            if (empty($size)) {
                $sizeMax = ini_get("upload_max_filesize");
            } else {
                $sizeMax = $jinput->post->getInt('size');
            }

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

                if ($lengthFile <= $nbMaxFile) {
                    if ($nbAttachment < $nbMaxFile) {
                        $nbMax = true;
                        if ($fileSize < $sizeMax) {
                            move_uploaded_file($tmp_name, $target);
                            $size = true;
                        } else {
                            $size = false;
                        }
                    } else {
                        $nbMax = false;
                    }
                }

                if ($lengthFile > $nbMaxFile) {
                    $nbMax = false;
                }
                $result[] = array('size' => $size, 'ext' => $ext, 'nbMax' => $nbMax, 'filename' => $fileName, 'target' => $target,'nbAttachment' => $nbAttachment, 'encrypt' => $encrypt, 'maxSize' => $sizeMax);
            } else {
                $ext = false;
                $result[] = array('size' => $size, 'ext' => $ext,  'filename' => $fileName, 'target' => $target,'nbAttachment' => $nbAttachment);
            }


        }

        echo json_encode($result);
        return true;
    }


    /**
     * @return bool
     */
    public function onAjax_attachment() {

        $jinput = $this->app->input;

        $current_user = JFactory::getSession()->get('emundusUser');
        if (JFactory::getUser()->guest) {

            $status='false';
            $result = array('status' => $status);
            echo json_encode($result);
            return false;
        }

        $attachId = $jinput->post->get('attachId');
        $cid = $this->getCampaignId($current_user->fnum);
        $uploadResult = $this->getUploads($attachId, $current_user->id, $cid);

        foreach ($uploadResult as $upload) {
            if (!empty($upload->filename)) {
                $fileName = $upload->filename;
            }

            $target = '/images'.DS.'emundus'.DS.'files'.DS.$current_user->id.DS.$fileName;
            $result[] = array('filename' => $fileName, 'target' => $target);
        }

        echo json_encode($result);
        return true;

    }


    /**
     * @return bool
     */
    public function onAjax_delete() {

        $jinput = $this->app->input;
        $current_user = JFactory::getSession()->get('emundusUser');

        $fileName = $jinput->post->get('filename');
        $attachId = $jinput->post->get('attachId');

        if (!EmundusHelperAccess::asApplicantAccessLevel($current_user->id) || !EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id)) {
            return false;
        }

        $cid = $this->getCampaignId($current_user->fnum);
        $uploadResult = $this->getUploads($attachId, $current_user->id, $cid);
        $target = $this->getPath($current_user->id, $fileName);

        if (file_exists($target) && !empty($uploadResult)) {
            unlink($target);
            $this->deleteFile($fileName, $current_user->fnum, $cid, $attachId);
            $status = true;
        }
        if (!file_exists($target) && !empty($uploadResult)) {
            $this->deleteFile($fileName, $current_user->fnum, $cid, $attachId);
            $status = true;
        }
        if (!file_exists($target) && empty($uploadResult)) {
            $status = false;
        }

        $result = array('status' => $status);
        echo json_encode($result);
        return true;
    }

    public function dataConsideredEmptyForValidation($data, $repeatCounter) {
        $current_user = JFactory::getSession()->get('emundusUser');

        $attachId = $this->getAttachId();
        $cid = $this->getCampaignId($current_user->fnum);
        $uploadResult = $this->getUploads($attachId,$current_user->id,$cid);

        if(empty($uploadResult) && $data == ""){
            return true;
        }
        else{
            return false;
        }
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
        $query->select($db->quoteName(array('esa.lbl', 'esa.allowed_types', 'esa.nbmax')))
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
    public function getUploads($attachId, $uid, $cid) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('id'),$db->quoteName('filename')))
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('attachment_id') . ' = ' . $attachId . ' AND ' . $db->quoteName('user_id') . ' = ' . $uid . " AND " . $db->quoteName('campaign_id') . " = " . $cid);
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
    public function getFileName($user, $attachId, $label, $file) {
        $fileName = strtolower(preg_replace(array('([\40])', '([^a-zA-Z0-9-])', '(-{2,})'), array('_', '', '_'), preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', htmlentities(strtoupper($user->lastname) . '_' . ucfirst($user->firstname), ENT_NOQUOTES, 'UTF-8'))));
        $fileName .= '_' . $attachId . $label . '-' . rand() . '.' . pathinfo($file, PATHINFO_EXTENSION);
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
        $params = $this->getParams();

        foreach ($data as &$d) {
            $d = $this->format($d);

            $this->_guessLinkType($d, $thisRow);

            if ($params->get('render_as_qrcode', '0') === '1' && !empty($d)) {
                $d = $this->qrCodeLink($thisRow);
            }
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
        $params = $this->getParams();
        $format = $params->get('text_format_string');
        $formatBlank = $params->get('field_format_string_blank', true);

        if ($doNumberFormat) {
            $d = $this->numberFormat($d);
        }

        if ($format != '' && ($formatBlank || $d != '')) {
            $d = sprintf($format, $d);
        }

        if ($params->get('password') == '1') {
            $d = str_pad('', JString::strlen($d), '*');
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
            if ($params->get('render_as_qrcode', '0') === '1') {
                if (!empty($value)) {
                    $value = $this->qrCodeLink($data);
                }
            } else {
                $this->_guessLinkType($value, $data);
                $value = $this->format($value, false);
                $value = $this->getReadOnlyOutput($value, $value);
            }

            return ($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;

        } elseif ($params->get('autocomplete', '0') === '3') {
            $bits['class'] .= ' fabrikGeocomplete';
            $bits['autocomplete'] = 'off';
        }

        if (version_compare(phpversion(), '5.2.3', '<')) {
            $bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
        } else {
            $bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
        }

        $bits['class'] .= ' ' . $params->get('text_format');
        $bits['attachmentId'] = $params->get('attachmentId');
        $bits['size'] = $params->get('size');
        $bits['encrypted'] = $params->get('can_submit_encrypted');

        if ($params->get('speech', 0)) {
            $bits['x-webkit-speech'] = 'x-webkit-speech';
        }

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
     * Format guess link type
     *
     * @param string  &$value Original field value
     * @param array $data Record data
     *
     * @return  void
     */
    protected function _guessLinkType(&$value, $data) {
        $params = $this->getParams();

        if ($params->get('guess_linktype') == '1') {
            $w = new FabrikWorker;
            $opts = $this->linkOpts();
            $title = $params->get('link_title', '');
            $attrs = $params->get('link_attributes', '');

            if (!empty($attrs)) {
                $attrs = $w->parseMessageForPlaceHolder($attrs);
                $attrs = explode(' ', $attrs);

                foreach ($attrs as $attr) {
                    list($k, $v) = explode('=', $attr);
                    $opts[$k] = trim($v, '"');
                }
            } else {
                $attrs = array();
            }

            if ((new MediaHelper)->isImage($value)) {
                $alt = empty($title) ? '' : 'alt="' . strip_tags($w->parseMessageForPlaceHolder($title, $data)) . '"';
                $value = '<img src="' . $value . '" ' . $alt . ' ' . implode(' ', $attrs) . ' />';
            } else {
                if (!FabrikWorker::isEmail($value) && !JString::stristr($value, 'http') && JString::stristr($value, 'www.')) {
                    $value = 'http://' . $value;
                }

                if ($title !== '') {
                    $opts['title'] = strip_tags($w->parseMessageForPlaceHolder($title, $data));
                }

                $label = FArrayHelper::getValue($opts, 'title', '') !== '' ? $opts['title'] : $value;
                $value = FabrikHelperHTML::a($value, $label, $opts);
            }
        }
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
        $params = $this->getParams();
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);

        $inputMask = trim($params->get('text_input_mask', ''));

        if (!empty($inputMask)) {
            $opts->use_input_mask = true;
            $opts->input_mask = $inputMask;
            $opts->input_mask_definitions = $params->get('text_input_mask_definitions', '{}');
            $opts->input_mask_autoclear = $params->get('text_input_mask_autoclear', '0') === '1';
        } else {
            $opts->use_input_mask = false;
            $opts->input_mask = '';
        }

        $opts->geocomplete = $params->get('autocomplete', '0') === '3';

        $config = JComponentHelper::getParams('com_fabrik');
        $apiKey = trim($config->get('google_api_key', ''));
        $opts->mapKey = empty($apiKey) ? false : $apiKey;

        if ($this->getParams()->get('autocomplete', '0') == '2') {
            $autoOpts = array();
            $autoOpts['max'] = $this->getParams()->get('autocomplete_rows', '10');
            $autoOpts['storeMatchedResultsOnly'] = false;
            FabrikHelperHTML::autoComplete($id, $this->getElement()->id, $this->getFormModel()->getId(), 'field', $autoOpts);
        }

        JText::script('PLG_ELEMENT_FIELD_SUCCESS');
        JText::script('PLG_ELEMENT_FIELD_EXTENSION');
        JText::script('PLG_ELEMENT_FIELD_ENCRYPT');
        JText::script('PLG_ELEMENT_FIELD_ERROR');
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
        $params = $this->getParams();
        $inputMask = trim($params->get('text_input_mask', ''));
        $geoComplete = $params->get('autocomplete', '0') === '3';

        $s = new stdClass;

        // Even though fab/element is now an AMD defined module we should still keep it in here
        // otherwise (not sure of the reason) jQuery.mask is not defined in field.js

        // Seems OK now - reverting to empty array
        $s->deps = array();

        if (!empty($inputMask)) {
            $folder = 'components/com_fabrik/libs/masked_input/';
            $s->deps[] = $folder . 'jquery.maskedinput';
        }

        if ($geoComplete) {
            $folder = 'components/com_fabrik/libs/googlemaps/geocomplete/';
            $s->deps[] = $folder . 'jquery.geocomplete';
        }

        if (array_key_exists($key, $shim)) {
            $shim[$key]->deps = array_merge($shim[$key]->deps, $s->deps);
        } else {
            $shim[$key] = $s;
        }

        parent::formJavascriptClass($srcs, $script, $shim);
        return false;
    }


    /**
     * Get database field description
     *
     * @return  string  db field type
     */
    public function getFieldDescription() {
        $p = $this->getParams();

        if ($this->encryptMe()) {
            return 'BLOB';
        }

        switch ($p->get('text_format')) {
            default:
            case 'text':
                $objType = "VARCHAR(" . $p->get('maxlength', 255) . ")";
                break;
            case 'integer':
                $objType = "INT(" . $p->get('integer_length', 11) . ")";
                break;
            case 'decimal':
                $total = (int)$p->get('integer_length', 11) + (int)$p->get('decimal_length', 2);
                $objType = "DECIMAL(" . $total . "," . $p->get('decimal_length', 2) . ")";
                break;
        }

        return $objType;
    }


    /**
     * Get Joomfish options
     *
     * @return  array    key=>value options
     * @deprecated - not supporting joomfish
     *
     */
    public function getJoomfishOptions() {
        $params = $this->getParams();
        $return = array();
        $size = (int)$this->getElement()->width;
        $maxLength = (int)$params->get('maxlength');

        if ($size !== 0) {
            $return['length'] = $size;
        }

        if ($maxLength === 0) {
            $maxLength = $size;
        }

        if ($params->get('textarea-showmax') && $maxLength !== 0) {
            $return['maxlength'] = $maxLength;
        }

        return $return;
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


    /**
     * Get the element's cell class
     *
     * @return  string    css classes
     * @since 3.0.4
     *
     */
    public function getCellClass() {
        $params = $this->getParams();
        $classes = parent::getCellClass();
        $format = $params->get('text_format');

        if ($format == 'decimal' || $format == 'integer') {
            $classes .= ' ' . $format;
        }

        return $classes;
    }


    /**
     * Output a QR Code image
     *
     * @since 3.1
     */
    public function onAjax_renderQRCode() {
        $input = $this->app->input;
        $this->setId($input->getInt('element_id'));
        $this->loadMeForAjax();
        $this->getElement();
        $url = 'index.php';
        $this->lang->load('com_fabrik.plg.element.field', JPATH_ADMINISTRATOR);

        if (!$this->getListModel()->canView() || !$this->canView()) {
            $this->app->enqueueMessage(FText::_('JERROR_ALERTNOAUTHOR'));
            $this->app->redirect($url);
            exit;
        }

        $rowId = $input->get('rowid', '', 'string');

        if (empty($rowId)) {
            $this->app->redirect($url);
            exit;
        }

        $listModel = $this->getListModel();
        $row = $listModel->getRow($rowId, false);

        if (empty($row)) {
            $this->app->redirect($url);
            exit;
        }

        $elName = $this->getFullName(true, false);
        $value = $row->$elName;

        if (!empty($value)) {
            require JPATH_SITE . '/components/com_fabrik/libs/phpqrcode/phpqrcode.php';

            ob_start();
            QRCode::png($value);
            $img = ob_get_contents();
            ob_end_clean();
        }

        if (empty($img)) {
            $img = file_get_contents(JPATH_SITE . '/media/system/images/notice-note.png');
        }

        // Some time in the past
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . strlen($img));

        // Serve up the file
        echo $img;

        // And we're done.
        exit();
    }


    /**
     * Get a link to this element which will call onAjax_renderQRCode().
     *
     * @param array|object $thisRow Row data
     *
     * @return   string  QR code link
     * @since 3.1
     *
     */
    protected function qrCodeLink($thisRow) {
        if (is_object($thisRow)) {
            $thisRow = ArrayHelper::fromObject($thisRow);
        }

        $formModel = $this->getFormModel();
        $formId = $formModel->getId();
        $rowId = $formModel->getRowId();

        if (empty($rowId)) {

            $rowId = FArrayHelper::getValue($thisRow, '__pk_val', '');

            if (empty($rowId)) {
                $rowId = $formModel->getListModel()->lastInsertId;
                if (empty($rowId)) {
                    return '';
                }
            }
        }

        $elementId = $this->getId();
        $src = COM_FABRIK_LIVESITE
            . 'index.php?option=com_' . $this->package . '&amp;task=plugin.pluginAjax&amp;plugin=field&amp;method=ajax_renderQRCode&amp;'
            . 'format=raw&amp;element_id=' . $elementId . '&amp;formid=' . $formId . '&amp;rowid=' . $rowId . '&amp;repeatcount=0';

        $layout = $this->getLayout('qr');
        $displayData = new stdClass;
        $displayData->src = $src;
        $displayData->data = $thisRow;

        return $layout->render($displayData);
    }


    /**
     * Turn form value into email formatted value
     *
     * @param mixed $value Element value
     * @param array $data Form data
     * @param int $repeatCounter Group repeat counter
     *
     * @return  string  email formatted value
     */
    protected function getIndEmailValue($value, $data = array(), $repeatCounter = 0) {
        $params = $this->getParams();

        if ($params->get('render_as_qrcode', '0') === '1') {
            return html_entity_decode($this->qrCodeLink($data));
        } else {
            $value = $this->format($value);
            return parent::getIndEmailValue($value, $data, $repeatCounter);
        }
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
    public function insertFile($values)
    {
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
            JFactory::getApplication()->enqueueMessage('Probrème survenu à la mise à jour des fichiers', 'message');
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
            JFactory::getApplication()->enqueueMessage('Probrème survenu à la suppression des fichiers', 'message');
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
