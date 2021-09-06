<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

jimport('joomla.filesystem.file');
JLoader::register('JuupdaterHelper', JPATH_SITE . '/plugins/installer/juupdater/helper.php');

/**
 * Class DropfilesControllerJutranslation
 */
class DropfilesControllerJutranslation extends JControllerLegacy
{

    /**
     * Extension component name
     *
     * @var string
     */
    private $extension = 'com_dropfiles';

    /**
     * Extension slug name
     *
     * @var string
     */
    private $extension_slug = 'dropfiles';

    /**
     * Extension name
     *
     * @var string
     */
    private $extension_name = 'Dropfiles';

    /**
     * Save a post translation for a given language
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function saveStrings()
    {
        //Include JUTranslation helper
        $path_jutranslation = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'jutranslation.php';
        include_once($path_jutranslation);

        $extension_version = '';
        $revision = JFactory::getApplication()->input->getInt('revision', 1);

        //Check if user has permissions
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.admin')) {
            echo json_encode(array('status' => 'error', 'message' => 'unhautorized'));
            die();
        }

        //Security check
        JSession::checkToken() || die('Invalid Token');

        //Get and check language
        $language = JFactory::getApplication()->input->getCmd('language');
        if (!$language) {
            echo json_encode(array('status' => 'error', 'message' => 'language empty'));
            die();
        }
        if (!preg_match('/^[a-z]{2,3}(?:-[a-zA-Z]{4})?(?:-[A-Z]{2,3})?$/', $language)) {
            echo json_encode(array('status' => 'error', 'message' => 'invalid language code'));
            die();
        }

        //Get the file to write to
        $destination = JFactory::getApplication()->input->getCmd('destination');
        $path_admin_extension = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_admin_extension .= $this->extension . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        $path_admin_extension .= $language . DIRECTORY_SEPARATOR . $language . '.' . $this->extension;
        $file = $path_admin_extension;
        switch ($destination) {
            case 'override':
                $file .= '.override.ini';
                break;
            case 'edition':
                //Disallow editing main en-GB file
                if ($language === 'en-GB') {
                    $status = array('status' => 'error', 'message' => 'editing main reference file not allowed');
                    echo json_encode($status);
                    die();
                }

                $file .= '.ini';

                //Get informations about previous installed file
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    $informations = Jutranslation::extractInformations($content);
                } else {
                    $informations = array();
                }

                //Get the file version
                if (isset($informations['version']) && $informations['version']) {
                    $extension_version = $informations['version'];
                } else {
                    //Use the current extension version
                    $extension_version = Jutranslation::getComponentVersion();
                }

                //Get the file revision
                if (isset($informations['revision'])) {
                    $revision = $informations['revision'];
                } else {
                    //Use the current extension version
                    $revision = JFactory::getApplication()->input->getInt('revision', 1);
                }
                break;
            default: //Case new language version installation from Joomunited
                //Get the version
                $extension_version = JFactory::getApplication()->input->getCmd('extension_version');
                if (!$extension_version) {
                    echo json_encode(array('status' => 'error', 'message' => 'version empty'));
                    die();
                }

                //Get revision
                $revision = JFactory::getApplication()->input->getInt('revision', 1);

                $file .= '.ini';
                break;
        }

        //Check revision number
        if ($destination !== 'override' &&
            !preg_match('/^([0-9]+)\.([0-9]+)(\.([0-9]+))?(\.([0-9]+))?$/', $extension_version)) {
            echo json_encode(array('status' => 'error', 'message' => 'invalid version number'));
            die();
        }

        $modified = JFactory::getApplication()->input->getBool('modified', 0);

        //Get strings
        $strings = JFactory::getApplication()->input->get('strings', false, 'raw');

        //Check if strings is a valid array
        $strings = json_decode($strings);
        if ($strings === false || !is_object($strings) || !count((array)$strings)) {
            $strings = new \stdClass();
        }

        //Generate the file header
        if ($destination === 'override') {
            $content = ';' . $this->extension_name . ' language override file' . PHP_EOL;
        } else {
            $content = ';' . $this->extension_name . ' language file' . PHP_EOL;
            $content .= ';Please do not edit directly this file, use the dedicated tool in the component options page';
            $content .= PHP_EOL;
            $content .= ';version="' . $extension_version . '"' . PHP_EOL;
            $content .= ';revision="' . $revision . '"' . PHP_EOL;
            $content .= ';modified="' . (int)$modified . '"' . PHP_EOL;
        }

        foreach ($strings as $code => $string) {
            //Only save reference language empty strings
            if ($string !== '' || $language === 'en-GB') {
                $content .= $code . '="' . str_replace('"', '"QQ"', $string) . '"' . PHP_EOL;
            }
        }

        //Write the language file
        if (JFile::write($file, $content)) {
            echo json_encode(array('status' => 'success', 'message' => 'file created'));
            die();
        }

        echo json_encode(array('status' => 'error', 'message' => 'writing file failed'));
        die();
    }


    /**
     * Return the existing strings for a given language
     * containing reference string in en-GB and overrides strings
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getTranslation()
    {
        //Check if user has permissions
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.admin')) {
            echo json_encode(array('status' => 'error', 'message' => 'unhautorized'));
            die();
        }

        //Get and check language
        $language = JFactory::getApplication()->input->getCmd('language');
        if (!$language) {
            echo json_encode(array('status' => 'error', 'message' => 'language empty'));
            die();
        }
        if (!preg_match('/^[a-z]{2,3}(?:-[a-zA-Z]{4})?(?:-[A-Z]{2,3})?$/', $language)) {
            echo json_encode(array('status' => 'error', 'message' => 'invalid language code'));
            die();
        }

        //Include JUTranslation helper
        $path_jutranslation = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'jutranslation.php';
        include_once($path_jutranslation);

        //Get the language file for reference language en-GB
        $path_language_init = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_language_init .= $this->extension . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'en-GB';
        $path_language_init .= DIRECTORY_SEPARATOR . 'en-GB.' . $this->extension . '.ini';
        $reference_content = file_get_contents($path_language_init);
        if ($reference_content === false) {
            echo json_encode(array('status' => 'error', 'message' => 'language file en-GB not found'));
            die();
        }
        //Retrieve reference the strings
        $reference_strings = Jutranslation::extractLanguageStrings($reference_content);

        //Get the default language file for this language
        $base_file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->extension;
        $base_file .= DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR;
        $base_file .= $language . '.' . $this->extension . '.ini';
        if (file_exists($base_file)) {
            $base_content = file_get_contents($base_file);
            //Retrieve default the strings
            $language_strings = Jutranslation::extractLanguageStrings($base_content);
        } else {
            $language_strings = array();
        }


        //Get the override file content if exists
        $override_file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $override_file .= $this->extension . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language;
        $override_file .= DIRECTORY_SEPARATOR . $language . '.' . $this->extension . '.override.ini';
        if (file_exists($override_file)) {
            $override_content = file_get_contents($override_file);
            //Retrieve the strings
            $override_strings = Jutranslation::extractLanguageStrings($override_content);
        } else {
            $override_strings = array();
        }

        //Generate the final variable cotaining all strings
        $final_result = array();
        $variable_array = array('reference_strings' => $reference_strings,
            'language_strings' => $language_strings,
            'override_strings' => $override_strings
        );
        foreach ($variable_array as $variable => $strings) {
            foreach ($strings as $constant => $value) {
                if (empty($final_result[$constant])) {
                    $obj = new stdClass();
                    $obj->key = $constant;
                    $obj->reference = '';
                    $obj->language = '';
                    $obj->override = '';
                    $final_result[$constant] = $obj;
                }
                switch ($variable) {
                    case 'reference_strings':
                        $final_result[$constant]->reference = $value;
                        break;
                    case 'language_strings':
                        $final_result[$constant]->language = $value;
                        break;
                    case 'override_strings':
                        $final_result[$constant]->override = $value;
                        break;
                }
            }
        }
        $status = array('status' => 'success', 'datas' => array('language' => $language, 'strings' => $final_result));
        echo json_encode($status);
        die();
    }

    /**
     * Show submit form to share translation
     *
     * @return void
     * @throws /Exception Throw when application can not start
     * @since  version
     */
    public function showViewForm()
    {
        //Check if user has permissions
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.admin')) {
            echo json_encode(array('status' => 'error', 'message' => 'unhautorized'));
            die();
        }

        //No extra decoration
        JFactory::getApplication()->input->set('tmpl', 'component');

        //Get and check language
        $language = JFactory::getApplication()->input->getCmd('language');
        if (!$language) {
            throw new Exception('language empty');
        }
        if (!preg_match('/^[a-z]{2,3}(?:-[a-zA-Z]{4})?(?:-[A-Z]{2,3})?$/', $language)) {
            throw new Exception('invalid language code');
        }

        $file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->extension;
        $file .= DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR;
        $file .= $language . '.' . $this->extension . '.ini';
        if (!file_exists($file)) {
            throw new Exception('language file doesn\'t exist');
        }

        //Include JUTranslation helper
        $path_jutranslation = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'jutranslation.php';
        include_once($path_jutranslation);

        $file_content = file_get_contents($file);

        //Get informations
        $informations = Jutranslation::extractInformations($file_content);

        //Check if the file has been modified by the user
        if (isset($informations['modified']) && $informations['modified'] !== '1') {
            throw new Exception('language file not modified');
        }

        $strings = Jutranslation::extractLanguageStrings($file_content);
        if (!count($strings)) {
            throw new Exception('no string found');
        }
        $strings = json_encode($strings);

        $version = Jutranslation::getComponentVersion();
        $user_token = Jutranslation::juGetToken();

        //Remove the scripts automatically add by component and keep only jquery
        JFactory::getDocument()->_scripts = array();
        JHtml::_('script', 'jui/jquery.min.js', false, true, false, false);

        //Content of the form to post to Joomunited
        $html = '';
        //Submit automatically the form on page loading
        $html .= '<script type="text/javascript">jQuery(document).ready(function($){$("form").submit();});</script>';
        //todo: replace by joomunited address
        $action_form = 'https://www.joomunited.com/index.php?option=com_jutranslation&task=contribution.share';
        $html .= '<form method="POST" action="' . $action_form . '">';
        $html .= '<input type="hidden" name="extension" value="' . $this->extension_slug . '" />';
        $html .= '<input type="hidden" name="extension_language" value="' . $language . '" />';
        $html .= '<input type="hidden" name="extension_version" value="' . $version . '" />';
        $html .= '<input type="hidden" name="user_token" value="' . $user_token . '" />';
        $html .= '<input type="hidden" name="site_url" value="' . JUri::base() . '" />';
        $html .= '<input type="hidden" name="extension_updated" value="1" />';
        $html .= '<textarea style="display: none" name="strings">' . $strings . '</textarea>';
        $html .= '</form>';
        //Add waiting image
        $html .= '<div style="text-align:center">';
        $src_loadder = JUri::root() . '/administrator/components/' . $this->extension;
        $src_loadder .= '/assets/images/preview_loader.gif';
        $html .= '<img src="' . $src_loadder . '"></div>';
        echo $html;
    }

    /**
     * Save Ju Token
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function saveJuToken()
    {
        //Check if user has permissions
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.admin')) {
            echo json_encode(array('status' => 'error', 'message' => 'unhautorized'));
            die();
        }

        //Security check
        JSession::checkToken() || die('Invalid Token');

        //Add jutoken into client site
        JuupdaterHelper::juAddToken();
    }
}
