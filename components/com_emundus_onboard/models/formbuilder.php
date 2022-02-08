<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

class EmundusonboardModelformbuilder extends JModelList {
    var $model_language = null;
    var $model_language_overrides = null;
    var $model_menus = null;
    var $m_translations = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'translations.php');
        $this->m_translations = new EmundusModelTranslations;

        JModelLegacy::addIncludePath(JPATH_SITE . '/administrator/components/com_languages/models');
        JModelLegacy::addIncludePath(JPATH_SITE . '/administrator/components/com_menus/models');
        $this->model_language = JModelLegacy::getInstance('Override', 'LanguagesModel');
        $this->model_language_overrides = JModelLegacy::getInstance('Overrides', 'LanguagesModel');
        $this->model_menus = JModelLegacy::getInstance('Item', 'MenusModel');
    }

    public function replaceAccents($value){
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '!'=>'', '?'=>'', '*'=>'', '%'=>'y', '^'=>'', '€'=>'', '+'=>'', '='=>'',
            ';'=>'', ','=>'', '&'=>'', '@'=>'', '#'=>'', '`'=>'', '¨'=>'', '§'=>'', '"'=>'', '\''=>'', '\\'=>'', '/'=>'', '('=>'', ')'=>'', '['=>'', ']'=>'', ' '=>'_');
        return strtr($value, $unwanted_array);
    }

    /** TRANSLATION SYSTEM */
    public function translate($key,$values,$reference_table = '',$id = '',$reference_field = ''){
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $this->m_translations->insertTranslation($key,$values[$language->sef],$language->lang_code,'','override',$reference_table,$id,$reference_field);
        }
        return $key;
    }

    public function updateTranslation($key,$values,$reference_table = '',$reference_id = 0){
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $this->m_translations->updateTranslation($key,$values[$language->sef],$language->lang_code,'override',$reference_table,$reference_id);
        }
        return $key;
    }

    function deleteTranslation($text) {
        $this->m_translations->deleteTranslation($text);
    }

    /**
     * Copy languages file to administration to get elements translations in backoffice
     * @param $langtag
     * @return bool
     */
    function copyFileToAdministration($langtag) {
        $origin_file = basename(__FILE__) . '/../language/overrides/' . $langtag . '.override.ini' ;
        $newfile = basename(__FILE__) . '/../administrator/language/overrides/' . $langtag . '.override.ini';

        if(file_exists($newfile)) {
            unlink($newfile);
        }

        if(!copy($origin_file,$newfile)){
            return false;
        }

        return true;
    }

    /**
     * Ge translation of an element in all languages
     * @param $text
     * @param $content
     * @return false|string|string[]
     */
    function getTranslation($text,$code_lang){
        $matches = [];

        $fileName = constant('JPATH_BASE') . '/language/overrides/' . $code_lang . '.override.ini';
        $strings  = JLanguageHelper::parseIniFile($fileName);

        if(!empty($text)) {
            if(isset($strings[$text])){
                return $strings[$text];
            } else {
                return $text;
            }
        } else {
            return '';
        }
        //
    }

    /**
     * Get translation of an array
     *
     * @param $toJTEXT
     * @return array
     */
    function getJTEXTA($toJTEXT) {
        if ($toJTEXT != null) {
            for ($i = 0; $i < count($toJTEXT); $i++) {
                $toJTEXT[$i] = JText::_($toJTEXT[$i]);
            }
            return $toJTEXT;
        } else {
            return [];
        }
    }

    /**
     * Get translation of a text
     *
     * @param $toJTEXT
     * @return mixed
     */
    function getJTEXT($toJTEXT) {
        $toJTEXT =  JText::_($toJTEXT);
        return JText::_($toJTEXT);
    }

    /**
     * Update translation
     *
     * @param $labelTofind
     * @param $locallang
     * @param $NewSubLabel
     */
    function formsTrad($labelTofind, $NewSubLabel, $element = null, $group = null, $page = null) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            if($element != null){
                $new_key = $this->updateTranslation($labelTofind,$NewSubLabel,'fabrik_elements',$element);
                $query->update($db->quoteName('#__fabrik_elements'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($new_key))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($element));
                $db->setQuery($query);
                $db->execute();
            } elseif ($group != null){
                $new_key = $this->updateTranslation($labelTofind,$NewSubLabel,'fabrik_groups',$group);
                $query->update($db->quoteName('#__fabrik_groups'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($new_key))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($group));
                $db->setQuery($query);
                $db->execute();
            } elseif ($page != null){
                $new_key = $this->updateTranslation($labelTofind,$NewSubLabel,'fabrik_forms',$page);
                $query->update($db->quoteName('#__fabrik_forms'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($new_key))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($page));
                $db->setQuery($query);
                $db->execute();
            }
            return $new_key;
        }  catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when update the translation of ' . $labelTofind . ' : ' .$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
    /** END TRANSLATION SYSTEM */

    function getSpecialCharacters() {
        return array('=','&',',','#','_','*',';','!','?',':','+','$','\'',' ','£',')','(','@','%');
    }

    function htmlspecial_array(&$variable) {
        foreach ($variable as &$value) {
            if (!is_array($value)) {
                $value = htmlspecialchars($value);
            } else {
                $this->htmlspecial_array($value);
            }
        }
    }

    function insertMenu($menu,$label){
        // TODO Use Joomla API to create menus
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $app = JFactory::getApplication();

        $model = new MenusModelItem();

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,103,104,168,170];

        try {
            // INSERT MENU
            $query->clear()
                ->insert($db->quoteName('#__menu'));
            $query->set($db->quoteName('menutype') . ' = ' . $db->quote($menu['menutype']))
                ->set($db->quoteName('title') . ' = ' . $db->quote('FORM_' . $menu['profile_id'] . '_' . $menu['form_id']))
                ->set($db->quoteName('alias') . ' = ' . $db->quote('form-' . $menu['form_id'] . '-' . preg_replace('/\s+/', '-', $this->replaceAccents(strtolower($label['fr'])))))
                ->set($db->quoteName('path') . ' = ' . $db->quote($menu['path']))
                ->set($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $menu['form_id']))
                ->set($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->set($db->quoteName('published') . ' = ' . $db->quote(1))
                ->set($db->quoteName('parent_id') . ' = ' . $db->quote($menu['parent_id']))
                ->set($db->quoteName('level') . ' = ' . $db->quote($menu['level']))
                ->set($db->quoteName('component_id') . ' = ' . $db->quote(10041))
                ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('access') . ' = ' . $db->quote(1))
                ->set($db->quoteName('img') . ' = ' . $db->quote(''))
                ->set($db->quoteName('template_style_id') . ' = ' . $db->quote(22))
                ->set($db->quoteName('params') . ' = ' . $db->quote('{"rowid":"","usekey":"","random":"0","fabriklayout":"","extra_query_string":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"0","page_heading":"","pageclass_sfx":"applicant-form","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}'))
                ->set($db->quoteName('lft') . ' = ' . $db->quote($menu['lft']))
                ->set($db->quoteName('rgt') . ' = ' . $db->quote($menu['rgt']))
                ->set($db->quoteName('language') . ' = ' . $db->quote('*'));
            $db->setQuery($query);
            $db->execute();
            $newmenuid = $db->insertid();

            // Insert translation into falang for modules
            $falang->insertFalang($label, $newmenuid, 'menu', 'title');
            //

            // Affect modules to this menu
            foreach ($modules as $module) {
                $query->clear()
                    ->insert($db->quoteName('#__modules_menu'))
                    ->set($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                    ->set($db->quoteName('menuid') . ' = ' . $db->quote($newmenuid));
                $db->setQuery($query);
                $db->execute();
            }
            //
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when create a menu : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array();
        }

    }

    function prepareSubmittionPlugin($params) {
        $params['applicationsent_status'] = "0";
        $params['emundusconfirmpost_status'] = "1";
        $params['admission'] = "0";
        $params['ajax_validations'] = "0";
        $params['only_process_curl'] = array(
            2 => "onBeforeLoad"
        );
        $params['form_php_file'] = array(
            2 => "emundus-isApplicationCompleted.php"
        );
        $params['form_php_require_once'] = array(
            2 => "0"
        );
        $params['thanks_message'] = array(
            3 => "Félicitations, votre dossier a bien été envoyé."
        );
        $params['save_insession'] = array(
            3 => "0"
        );
        $params['redirect_conditon'] = array(
            3 => ""
        );
        $params['redirect_content_reset_form'] = array(
            3 => "1"
        );
        $params['redirect_content_how'] = array(
            3 => "popup"
        );
        $params['redirect_content_popup_width'] = array(
            3 => ""
        );
        $params['redirect_content_popup_height'] = array(
            3 => ""
        );
        $params['redirect_content_popup_x_offset'] = array(
            3 => ""
        );
        $params['redirect_content_popup_y_offset'] = array(
            3 => ""
        );
        $params['redirect_content_popup_title'] = array(
            3 => ""
        );
        $params['plugins'] = array("emundusisapplicationsent", "emundusconfirmpost", "php", "redirect");
        $params['plugin_description'] = array("isSent", "Confirm", "isComplete", "redirect");
        $params['plugin_state'] = array("1", "1", "1", "1");
        $params['plugin_locations'] = array("both", "both", "both", "both");
        $params['plugin_events'] = array("both", "both", "both", "both");

        return $params;
    }

    function prepareFormPlugin($params) {
        $params['emundusredirect_field_status'] = "-1";
        $params['copy_form'] = "0";
        $params['notify_complete_file'] = "0";
        $params['applicationsent_status'] = "0";
        $params['admission'] = "0";
        $params['only_process_curl'] = array(
            2 => "getEndContent"
        );
        $params['form_php_file'] = array(
            2 => "-1"
        );
        $params['form_php_require_once'] = array(
            2 => "0"
        );
        $params['plugins'] = array("emundusredirect", "emundusisapplicationsent", "php");

        return $params;
    }

    function prepareElementParameters($plugin,$attachementId) {
        $params = array(
            'bootstrap_class' => 'input-xlarge',
            'show_in_rss_feed' => 0,
            'show_label_in_rss_feed' => 0,
            'use_as_rss_enclosure' => 0,
            'rollover' => '',
            'tipseval' => 0,
            'tiplocation' => 'top-left',
            'labelindetails' => 0,
            'labelinlist' => '0',
            'comment' => '',
            'edit_access' => 1,
            'edit_access_user' => '',
            'view_access' => 1,
            'view_access_user' => '',
            'list_view_access' => 1,
            'encrypt' => 0,
            'store_in_db' => 1,
            'default_on_copy' => 0,
            'can_order' => 0,
        );

        if($plugin != 'display'){
            $params['validations'] = array(
                'plugin' => array(
                    "notempty",
                ),
                'plugin_published' => array(
                    "1",
                ),
                'validate_in' => array(
                    "both",
                ),
                'validation_on' => array(
                    "both",
                ),
                'validate_hidden' => array(
                    "0",
                ),
                'must_validate' => array(
                    "0",
                ),
                'show_icon' => array(
                    "1",
                ),
            );

            //if plugin == field
            if($plugin == 'field'){
                $params['text_input_format'] = array();
            }
            $params['notempty-message'] = array();
            $params['notempty-validation_condition'] = array();
        }
        if ($plugin == 'nom' || $plugin == 'prenom' || $plugin == 'email') {
            return $this->updateElementParams('field', null, $params, $attachementId);

        } else {
            return $this->updateElementParams($plugin, null, $params, $attachementId);

        }
    }

    function updateElementParams($plugin, $oldplugin, $params,$attachementId = null){
        try {
            // Reset params
            if ($oldplugin != null) {
                switch ($oldplugin) {
                    case 'field':
                        if ($params['password'] == 3) {
                            $key = array_search("isemail", $params['validations']['plugin']);
                            if ($key != false && $key != null) {
                                unset($params['validations']['plugin'][$key]);
                                unset($params['validations']['plugin_published'][$key]);
                                unset($params['validations']['validate_in'][$key]);
                                unset($params['validations']['validation_on'][$key]);
                                unset($params['validations']['validate_hidden'][$key]);
                                unset($params['validations']['must_validate'][$key]);
                                unset($params['validations']['show_icon'][$key]);
                                unset($params['isemail-message']);
                                unset($params['isemail-validation_condition']);
                                unset($params['isemail-allow_empty']);
                                unset($params['isemail-check_mx']);
                            }
                        }
                        unset($params['placeholder']);
                        unset($params['password']);
                        unset($params['maxlength']);
                        unset($params['disable']);
                        unset($params['readonly']);
                        unset($params['autocomplete']);
                        unset($params['speech']);
                        unset($params['advanced_behavior']);
                        unset($params['text_format']);
                        unset($params['integer_length']);
                        unset($params['decimal_length']);
                        unset($params['field_use_number_format']);
                        unset($params['field_thousand_sep']);
                        unset($params['field_decimal_sep']);
                        unset($params['text_format_string']);
                        unset($params['field_format_string_blank']);
                        unset($params['text_input_mask']);
                        unset($params['text_input_mask_autoclear']);
                        break;
                    case 'textarea':
                        unset($params['textarea_placeholder']);
                        unset($params['width']);
                        unset($params['height']);
                        unset($params['use_wysiwyg']);
                        unset($params['maxlength']);
                        unset($params['wysiwyg_extra_buttons']);
                        unset($params['textarea_field_type']);
                        unset($params['textarea-showmax']);
                        unset($params['textarea_limit_type']);
                        unset($params['textarea-tagify']);
                        unset($params['textarea_tagifyurl']);
                        unset($params['textarea-truncate-where']);
                        unset($params['textarea-truncate-html']);
                        unset($params['textarea-truncate']);
                        unset($params['textarea-hover']);
                        unset($params['textarea_hover_location']);
                        unset($params['textarea-maxlength']);
                        break;
                    case 'dropdown':
                        unset($params['multiple']);
                        unset($params['dropdown_multisize']);
                        unset($params['allow_frontend_addtodropdown']);
                        unset($params['dd-allowadd-onlylabel']);
                        unset($params['dd-savenewadditions']);
                        unset($params['options_split_str']);
                        unset($params['dropdown_populate']);
                        if (isset($params['join_db_name'])) {
                            unset($params['join_conn_id']);
                            unset($params['join_val_column_concat']);
                            unset($params['database_join_where_sql']);
                            unset($params['database_join_where_access']);
                            unset($params['database_join_where_when']);
                            unset($params['databasejoin_where_ajax']);
                            unset($params['database_join_filter_where_sql']);
                            unset($params['database_join_show_please_select']);
                            unset($params['database_join_noselectionvalue']);
                            unset($params['database_join_noselectionlabel']);
                            unset($params['databasejoin_popupform']);
                            unset($params['fabrikdatabasejoin_frontend_add']);
                            unset($params['join_popupwidth']);
                            unset($params['databasejoin_readonly_link']);
                            unset($params['fabrikdatabasejoin_frontend_select']);
                            unset($params['dbjoin_options_per_row']);
                            unset($params['dbjoin_multiselect_max']);
                            unset($params['dbjoin_multilist_size']);
                            unset($params['dbjoin_autocomplete_size']);
                            unset($params['dbjoin_autocomplete_rows']);
                            unset($params['dabase_join_label_eval']);
                            unset($params['join_desc_column']);
                            unset($params['dbjoin_autocomplete_how']);
                        }
                        break;
                    case 'checkbox':
                        unset($params['ck_options_per_row']);
                        unset($params['allow_frontend_addtocheckbox']);
                        unset($params['chk-allowadd-onlylabel']);
                        unset($params['chk-savenewadditions']);
                        unset($params['options_split_str']);
                        unset($params['dropdown_populate']);
                        break;
                    case 'radiobutton':
                        unset($params['options_per_row']);
                        unset($params['btnGroup']);
                        unset($params['rad-allowadd-onlylabel']);
                        unset($params['rad-savenewadditions']);
                        unset($params['options_split_str']);
                        unset($params['dropdown_populate']);
                        break;
                    case 'birthday':
                        unset($params['birthday_daylabel']);
                        unset($params['birthday_monthlabel']);
                        unset($params['birthday_yearlabel']);
                        unset($params['birthday_yearopt']);
                        unset($params['birthday_yearstart']);
                        unset($params['birthday_forward']);
                        unset($params['details_date_format']);
                        unset($params['details_dateandage']);
                        unset($params['list_date_format']);
                        unset($params['list_age_format']);
                        unset($params['empty_is_null']);
                        break;
                    case 'date':
                        unset($params['date_showtime']);
                        unset($params['date_time_format']);
                        unset($params['bootstrap_time_class']);
                        unset($params['placeholder']);
                        unset($params['date_store_as_local']);
                        unset($params['date_table_format']);
                        unset($params['date_form_format']);
                        unset($params['date_defaulttotoday']);
                        unset($params['date_alwaystoday']);
                        unset($params['date_firstday']);
                        unset($params['date_allow_typing_in_field']);
                        unset($params['date_csv_offset_tz']);
                        unset($params['date_advanced']);
                        unset($params['date_allow_func']);
                        unset($params['date_allow_php_func']);
                        unset($params['date_observe']);
                        $params['bootstrap_class'] = 'input-xlarge';
                        break;
                    case 'display':
                        unset($params['display_showlabel']);
                        $params['store_in_db'] = 1;
                        break;
                    default:
                        break;
                }
            }
            //

            // Prepare new params
            switch ($plugin) {
                case 'field':
                    // User params
                    if (!isset($params['placeholder'])) {
                        $params['placeholder'] = '';
                    }
                    if (!isset($params['password'])) {
                        $params['password'] = 0;
                    }
                    if (!isset($params['maxlength'])) {
                        $params['maxlength'] = 255;
                    }
                    if (!isset($params['text_input_mask'])) {
                        $params['text_input_mask'] = '';
                    }
                    //

                    // Default params
                    $params['disable'] = 0;
                    $params['readonly'] = 0;
                    $params['autocomplete'] = 0;
                    $params['speech'] = 0;
                    $params['advanced_behavior'] = 0;
                    $params['text_format'] = 'text';
                    $params['integer_length'] = 11;
                    $params['decimal_length'] = 2;
                    $params['field_use_number_format'] = 0;
                    $params['field_thousand_sep'] = ',';
                    $params['field_decimal_sep'] = '.';
                    $params['text_format_string'] = '';
                    $params['field_format_string_blank'] = 1;
                    $params['text_input_mask_autoclear'] = 0;
                    //
                    break;
                case 'textarea':
                    // User params
                    if (!isset($params['textarea_placeholder'])) {
                        $params['textarea_placeholder'] = '';
                    }
                    if (!isset($params['height'])) {
                        $params['height'] = 6;
                    }
                    if (!isset($params['use_wysiwyg'])) {
                        $params['use_wysiwyg'] = 0;
                    }
                    if (!isset($params['maxlength'])) {
                        $params['maxlength'] = 255;
                    }
                    if (!isset($params['textarea-showmax'])) {
                        $params['textarea-showmax'] = 0;
                    }
                    //

                    // Default params
                    $params['width'] = 60;
                    $params['wysiwyg_extra_buttons'] = 1;
                    $params['textarea_field_type'] = 'TEXT';
                    $params['textarea_limit_type'] = 'char';
                    $params['textarea-tagify'] = 0;
                    $params['textarea_tagifyurl'] = '';
                    $params['textarea-truncate-where'] = 0;
                    $params['textarea-truncate-html'] = 0;
                    $params['textarea-truncate'] = 0;
                    $params['textarea-hover'] = 1;
                    $params['textarea_hover_location'] = 'top';
                    //
                    break;
                case 'dropdown':
                    $params['multiple'] = 0;
                    $params['dropdown_multisize'] = 3;
                    $params['allow_frontend_addtodropdown'] = 0;
                    $params['dd-allowadd-onlylabel'] = 0;
                    $params['dd-savenewadditions'] = 0;
                    $params['options_split_str'] = '';
                    $params['dropdown_populate'] = '';
                    break;
                case 'checkbox':
                    $params['ck_options_per_row'] = 1;
                    $params['allow_frontend_addtocheckbox'] = 0;
                    $params['chk-allowadd-onlylabel'] = 0;
                    $params['chk-savenewadditions'] = 0;
                    $params['options_split_str'] = '';
                    $params['dropdown_populate'] = '';
                    break;
                case 'radiobutton':
                    $params['options_per_row'] = 1;
                    $params['btnGroup'] = 0;
                    $params['rad-allowadd-onlylabel'] = 0;
                    $params['rad-savenewadditions'] = 0;
                    $params['options_split_str'] = '';
                    $params['dropdown_populate'] = '';
                    break;
                case 'birthday':
                    $params['birthday_daylabel'] = '';
                    $params['birthday_monthlabel'] = '';
                    $params['birthday_yearlabel'] = '';
                    $params['birthday_yearopt'] = '';
                    $params['birthday_yearstart'] = 1950;
                    $params['birthday_forward'] = 0;
                    $params['details_date_format'] = 'd.m.Y';
                    $params['details_dateandage'] = 0;
                    $params['list_date_format'] = 'd.m.Y';
                    $params['list_age_format'] = 'no';
                    $params['empty_is_null'] = 1;
                    break;
                case 'years':
                    $params['birthday_yearopt'] = 'number';
                    $params['birthday_forward'] = '0';
                    $params['birthday_yearstart'] = '100';
                    break;
                case 'date':
                    $params['date_showtime'] = 0;
                    $params['date_time_format'] = 'H:i';
                    $params['bootstrap_time_class'] = 'input-medium';
                    $params['placeholder'] = 'dd/mm/yyyy';
                    $params['date_store_as_local'] = 1;
                    $params['date_table_format'] = 'd\/m\/Y';
                    $params['date_form_format'] = 'd/m/Y';
                    $params['date_defaulttotoday'] = 0;
                    $params['date_alwaystoday'] = 0;
                    $params['date_firstday'] = 0;
                    $params['date_allow_typing_in_field'] = 1;
                    $params['date_csv_offset_tz'] = 0;
                    $params['date_advanced'] = 0;
                    $params['date_allow_func'] = '';
                    $params['date_allow_php_func'] = '';
                    $params['date_observe'] = '';
                    $params['bootstrap_class'] = 'input-medium';
                    break;
                case 'display':
                    $params['display_showlabel'] = 1;
                    //$params['store_in_db'] = 0;
                    break;
                case 'emundus_fileupload':
                    $params['size'] = 10485760;
                    $params['attachmentId'] = $attachementId;
                    $params['can_submit_encrypted'] = 2;
                    break;
                case 'yesno':
                    $params["yesno_default"]="0";
                    $params["yesno_icon_yes"]="";
                    $params["yesno_icon_no"]="";
                    $params["options_per_row"]="4";
                    $params["toggle_others"]="0";
                    $params["toggle_where"]="";
                    $params["show_in_rss_feed"]="0";
                    $params["show_label_in_rss_feed"]="0";
                    $params["use_as_rss_enclosure"]="0";
                    $params["rollover"]="";
                    $params["tipseval"]="0";
                    $params["tiplocation"]="top-left";
                    $params["labelindetails"]="0";
                    $params["labelinlist"]="0";
                    $params["comment"]="";
                    $params["edit_access"]="1";
                    $params["edit_access_user"]="";
                    $params["view_access"]="1";
                    $params["view_access_user"]="";
                    $params["list_view_access"]="1";
                    $params["encrypt"]="0";
                    $params["store_in_db"]="1";
                    $params["default_on_copy"]="0";
                    $params["can_order"]="0";
                    $params["alt_list_heading"]="";
                    $params["custom_link"]="";
                    $params["custom_link_target"]="";
                    $params["custom_link_indetails"]="1";
                    $params["use_as_row_class"]="0";
                    $params["include_in_list_query"]="1";
                    $params["always_render"]="0";
                    $params["icon_folder"]="0";
                    $params["icon_hovertext"]="1";
                    $params["icon_file"]="";
                    $params["icon_subdir"]="";
                    $params["filter_length"]="20";
                    $params["filter_access"]="1";
                    $params["full_words_only"]="0";
                    $params["filter_required"]="0";
                    $params["filter_build_method"]="0";
                    $params["filter_groupby"]="text";
                    $params["inc_in_adv_search"]="1";
                    $params["filter_class"]="input-medium";
                    $params["filter_responsive_class"]="";
                    $params["tablecss_header_class"]="";
                    $params["tablecss_header"]="";
                    $params["tablecss_cell_class"]="";
                    $params["tablecss_cell"]="";
                    $params["sum_on"]="0";
                    $params["sum_label"]="Sum";
                    $params["sum_access"]="1";
                    $params["sum_split"]="";
                    $params["avg_on"]="0";
                    $params["avg_label"]="Average";
                    $params["avg_access"]="1";
                    $params["avg_round"]="0";
                    $params["avg_split"]="";
                    $params["median_on"]="0";
                    $params["median_label"]="Median";
                    $params["median_access"]="1";
                    $params["median_split"]="";
                    $params["count_on"]="0";
                    $params["count_label"]="Count";
                    $params["count_condition"]="";
                    $params["count_access"]="1";
                    $params["count_split"]="";
                    $params["custom_calc_on"]="0";
                    $params["custom_calc_label"]="Custom";
                    $params["custom_calc_query"]="";
                    $params["custom_calc_access"]="1";
                    $params["custom_calc_split"]="";
                    $params["custom_calc_php"]="";
                    $params["validations"]=[];

                default:
                    break;
            }
            //
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error update params of the element '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return $params;
    }

    function addDatabaseJoinParameters($params){
        unset($params['allow_frontend_addtodropdown']);
        unset($params['dd-allowadd-onlylabel']);
        unset($params['dd-savenewadditions']);
        unset($params['dropdown_multisize']);
        unset($params['dropdown_populate']);
        unset($params['multiple']);
        unset($params['sub_options']);

        $params['join_conn_id'] = '1';
        if($params['database_join_where_sql']==''){
            $params['database_join_where_sql'] = 'order by ' . $params['join_key_column'];
        }

        $params['database_join_where_access'] = '1';
        $params['database_join_where_when'] = '3';
        $params['databasejoin_where_ajax'] = '0';
        $params['database_join_filter_where_sql'] = '';
        $params['database_join_show_please_select'] = '1';
        $params['database_join_noselectionvalue'] = '';
        $params['database_join_noselectionlabel'] = '';
        $params['databasejoin_popupform'] = '41';
        $params['fabrikdatabasejoin_frontend_add'] = '0';
        $params['join_popupwidth'] = '';
        $params['databasejoin_readonly_link'] = '0';
        $params['fabrikdatabasejoin_frontend_select'] = '0';
        $params['dbjoin_options_per_row'] = '3';
        $params['dbjoin_multiselect_max'] = '0';
        $params['dbjoin_multilist_size'] = '6';
        $params['dbjoin_autocomplete_size'] = '20';
        $params['dbjoin_autocomplete_rows'] = '10';
        $params['dabase_join_label_eval'] = '';
        $params['join_desc_column'] = '';
        $params['dbjoin_autocomplete_how'] = 'contains';

        return $params;
    }

    function deleteDatabaseJoinParams($params){
        unset($params['join_conn_id']);
        unset($params['join_val_column_concat']);
        unset($params['database_join_where_sql']);
        unset($params['database_join_where_access']);
        unset($params['database_join_where_when']);
        unset($params['databasejoin_where_ajax']);
        unset($params['database_join_filter_where_sql']);
        unset($params['database_join_show_please_select']);
        unset($params['database_join_noselectionvalue']);
        unset($params['database_join_noselectionlabel']);
        unset($params['databasejoin_popupform']);
        unset($params['fabrikdatabasejoin_frontend_add']);
        unset($params['join_popupwidth']);
        unset($params['databasejoin_readonly_link']);
        unset($params['fabrikdatabasejoin_frontend_select']);
        unset($params['dbjoin_options_per_row']);
        unset($params['dbjoin_multiselect_max']);
        unset($params['dbjoin_multilist_size']);
        unset($params['dbjoin_autocomplete_size']);
        unset($params['dbjoin_autocomplete_rows']);
        unset($params['dabase_join_label_eval']);
        unset($params['join_desc_column']);
        unset($params['dbjoin_autocomplete_how']);

        return $params;
    }

    function updateElementWithoutTranslation($eid,$label) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error update label of the element ' . $eid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateGroupWithoutTranslation($gid,$label) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('name') . ' = ' . $db->quote($label))
                ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error update label of the group ' . $gid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updatePageWithoutTranslation($pid,$label) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__fabrik_lists'))
                ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error update label of the page ' . $pid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updatePageIntroWithoutTranslation($pid,$intro) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('intro') . ' = ' . $db->quote($intro))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__fabrik_lists'))
                ->set($db->quoteName('introduction') . ' = ' . $db->quote($intro))
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error update label of the page intro ' . $pid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createHeadingMenu($menutype,$title,$prid) {
        // TODO Use Joomla API to create menu
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->insert($db->quoteName('#__menu'));
            $query->set($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->set($db->quoteName('title') . ' = ' . $db->quote($title))
                ->set($db->quoteName('alias') . ' = ' . $db->quote(str_replace($this->getSpecialCharacters(),'-',strtolower($title)) . '-' . $prid))
                ->set($db->quoteName('path') . ' = ' . $db->quote($menutype))
                ->set($db->quoteName('link') . ' = ' . $db->quote(''))
                ->set($db->quoteName('type') . ' = ' . $db->quote('heading'))
                ->set($db->quoteName('published') . ' = ' . $db->quote(1))
                ->set($db->quoteName('level') . ' = ' . $db->quote(1))
                ->set($db->quoteName('access') . ' = ' . $db->quote(1))
                ->set($db->quoteName('template_style_id') . ' = ' . $db->quote(22))
                ->set($db->quoteName('params') . ' = ' . $db->quote('{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1}'))
                ->set($db->quoteName('rgt') . ' = ' . $db->quote(1))
                ->set($db->quoteName('language') . ' = ' . $db->quote('*'));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when create the heading menu of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Create a new page associate to the profile
     *
     * @param $label
     * @param $intro
     * @param $prid
     * @return array
     */
    function createMenu($label, $intro, $prid, $template) {
        // TODO Use Joomla API to create a menu
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $app = JFactory::getApplication();

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
        try {
            $db->setQuery($query);
            $profile = $db->loadObject();
            $menutype = $profile->menutype;

            // INSERT FABRIK_FORMS
            $query->clear()
                ->select('*')
                ->from('#__fabrik_forms')
                ->where($db->quoteName('id') . ' = 287');
            $db->setQuery($query);
            $form_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_forms'));
            foreach ($form_model as $key => $val) {
                if ($key != 'id') {
                    $query->set($key . ' = ' . $db->quote($val));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            // Set emundus plugin in params
            $query->clear();
            $query->select('params')
                ->from($db->quoteName('#__fabrik_forms'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(), true);
            $params = $this->prepareFormPlugin($params);
            //
            $query->update($db->quoteName('#__fabrik_forms'));

            $query->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid));
            $query->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $formid . '</p>'));
            $query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $db->execute();

            // Add translation to translation files
            $this->translate('FORM_' . $prid . '_' . $formid,$label,'fabrik_forms',$formid,'label');
            $this->translate('FORM_' . $prid . '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            // CREATE TABLE
            $query->clear()
                ->select('COUNT(*)')
                ->from($db->quoteName('information_schema.tables'))
                ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('%jos_emundus_' . $prid . '%'));
            $db->setQuery($query);
            $result = $db->loadResult();

            if ($result < 10) {
                $increment = '0' . strval($result);
            } elseif ($result > 10) {
                $increment = strval($result);
            }

            $query = "CREATE TABLE IF NOT EXISTS jos_emundus_" . $prid . "_" . $increment . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            time_date datetime NULL DEFAULT current_timestamp(),
            fnum varchar(28) CHARSET UTF8 NOT NULL,
            user int(11) NULL,
            PRIMARY KEY (id),
            UNIQUE KEY fnum (fnum)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
            $db->setQuery($query);
            $db->execute();

            // Add constraints
            $query = "ALTER TABLE jos_emundus_" . $prid . "_" . $increment . "
            ADD CONSTRAINT jos_emundus_" . $prid . "_" . $increment . "_ibfk_1
            FOREIGN KEY (user) REFERENCES jos_emundus_users (user_id) ON DELETE CASCADE ON UPDATE CASCADE;";
            $db->setQuery($query);
            $db->execute();

            $query = "ALTER TABLE jos_emundus_" . $prid . "_" . $increment . "
            ADD CONSTRAINT jos_emundus_" . $prid . "_" . $increment . "_ibfk_2
            FOREIGN KEY (fnum) REFERENCES jos_emundus_campaign_candidature (fnum) ON DELETE CASCADE ON UPDATE CASCADE;";
            $db->setQuery($query);
            $db->execute();

            $query = "CREATE INDEX user
            ON jos_emundus_" . $prid . "_" . $increment . " (user);";
            $db->setQuery($query);
            $db->execute();
            //

            // INSERT FABRIK LIST
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('#__fabrik_lists')
                ->where($db->quoteName('id') . ' = 297');
            $db->setQuery($query);
            $list_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id' && $key != 'db_table_name' && $key != 'db_primary_key') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($formid));
                } elseif ($key == 'db_table_name') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment));
                } elseif ($key == 'db_primary_key') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment . '.id'));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $listid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'));

            $query->set('label = ' . $db->quote('FORM_' . $prid . '_' . $formid));
            $query->set('access = ' . $db->quote($prid));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($listid));
            $db->setQuery($query);
            $db->execute();
            //

            // INSERT MENU
            // Get the header menu
            $query
                ->clear()
                ->select('*')
                ->from('#__menu')
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
            $db->setQuery($query);
            $menu_parent = $db->loadObject();
            //

            $query
                ->clear()
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->andWhere($db->quoteName('parent_id') . ' = ' . $db->quote($menu_parent->id))
                ->order('rgt');
            $db->setQuery($query);
            $results = $db->loadObjectList();
            $rgts = [];
            $lfts = [];
            foreach (array_values($results) as $result) {
                if (!in_array($result->rgt, $rgts)) {
                    $rgts[] = intval($result->rgt);
                }
                if (!in_array($result->lft, $lfts)) {
                    $lfts[] = intval($result->lft);
                }
            }

            $menu = array(
                'menutype' => $profile->menutype,
                'profile_id' => $prid,
                'form_id' => $formid,
                'path' => $menu_parent->path . '/' . preg_replace('/\s+/', '-', $this->replaceAccents(strtolower($label['fr']))) . '-form-' . $formid,
                'parent_id' => $menu_parent->id,
                'level' => 2,
                'lft' => array_values($lfts)[strval(sizeof($lfts) - 1)] + 2,
                'rgt' => array_values($rgts)[strval(sizeof($rgts) - 1)] + 2
            );
            $this->insertMenu($menu,$label);
            //

            // JOIN LIST AND PROFILE_ID
            $columns = array(
                'form_id',
                'profile_id',
                'created'
            );

            $values = array(
                $listid,
                $prid,
                date('Y-m-d H:i:s')
            );

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_formlist'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->quote($values)));
            $db->setQuery($query);
            $db->execute();
            //

            // Create hidden group
            $this->createHiddenGroup($formid);
            $group_label = array(
                'fr' => 'Nouveau groupe',
                'en' => 'New group'
            );
            $this->createGroup($group_label,$formid);
            //

            // Save as template
            if ($template == 'true') {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_template_form'))
                    ->set($db->quoteName('form_id') . ' = ' . $db->quote($formid))
                    ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid))
                    ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')));
                $db->setQuery($query);
                $db->execute();
            }
            //

            return array(
                'id' => $formid,
                'label' => $label[$actualLanguage],
                'link' => 'index.php?option=com_fabrik&view=form&formid=' . $formid,
                'rgt' => array_values($rgts)[strval(sizeof($rgts) - 1)] + 2,
            );
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when create a new page in form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array();
        }
    }

    function createSubmittionPage($label, $intro, $prid) {
        // TODO Use Joomla API to create a menu
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
        try {
            $db->setQuery($query);
            $profile = $db->loadObject();

            // INSERT FABRIK_FORMS
            $query->clear()
                ->select('*')
                ->from('#__fabrik_forms')
                ->where($db->quoteName('id') . ' = 258');
            $db->setQuery($query);
            $form_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_forms'));
            foreach ($form_model as $key => $val) {
                if ($key != 'id') {
                    $query->set($key . ' = ' . $db->quote($val));
                }
            }

            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            // Set emundus plugin in params
            $query->clear();
            $query->select('params')
                ->from($db->quoteName('#__fabrik_forms'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(), true);
            $params = $this->prepareSubmittionPlugin($params);
            //
            $query->update($db->quoteName('#__fabrik_forms'));

            $query->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid));
            $query->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $formid . '</p>'));
            $query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $db->execute();

            // Add translation to translation files
            $this->translate('FORM_' . $prid . '_' . $formid,$label,'fabrik_forms',$formid,'label');
            $this->translate('FORM_' . $prid . '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            // INSERT FABRIK LIST
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('#__fabrik_lists')
                ->where($db->quoteName('id') . ' = 267');
            $db->setQuery($query);
            $list_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id' && $key != 'db_table_name' && $key != 'db_primary_key') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($formid));
                } elseif ($key == 'db_table_name') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_declaration'));
                } elseif ($key == 'db_primary_key') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_declaration.id'));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $listid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'));

            $query->set('label = ' . $db->quote('FORM_' . $prid . '_' . $formid));
            $query->set('access = ' . $db->quote($prid));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($listid));
            $db->setQuery($query);
            $db->execute();
            //

            $menu = array(
                'menutype' => $profile->menutype,
                'profile_id' => $prid,
                'form_id' => $formid,
                'path' => preg_replace('/\s+/', '-', $this->replaceAccents(strtolower($label['fr']))) . '-form-' . $formid,
                'parent_id' => 1,
                'level' => 1,
                'lft' => 110,
                'rgt' => 111
            );

            $this->insertMenu($menu,$label);

            // Create hidden group
            $this->createHiddenGroup($formid);
            $group_label = array(
                'fr' => "Confirmation d'envoi de dossier",
                'en' => 'Submitting application'
            );
            $group = $this->createGroup($group_label,$formid);

            $query = $db->getQuery(true);
            $query->select('fe.id as eid, fg.group_id as gid')
                ->from($db->quoteName('#__fabrik_elements','fe'))
                ->leftJoin($db->quoteName('#__fabrik_formgroup', 'fg') . ' ON ' . $db->quoteName('fg.group_id') . ' = ' . $db->quoteName('fe.group_id'))
                ->where($db->quoteName('fg.form_id') . ' = ' . $db->quote(258))
                ->andWhere($db->quoteName('fe.hidden') . ' = ' . $db->quote(0));
            $db->setQuery($query);
            $result = $db->loadObject();
            $eid = $result->eid;
            $oldgroup = $result->gid;
            $this->duplicateElement($eid,$group['group_id'],$oldgroup,$formid);
            //

            return array(
                'id' => $formid,
                'link' => 'index.php?option=com_fabrik&view=form&formid=' . $formid,
                'rgt' => 111,
            );
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when create the submittion page of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array();
        }
    }

    function deleteMenu($menu) {
        // TODO Use Joomla API to create a menu
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $menu));
            $db->setQuery($query);
            $jos_menu = $db->loadObject();

            $query->clear()
                ->update($db->quoteName('#__menu'))
                ->set($db->quoteName('published') . ' = -2')
                ->where($db->quoteName('id') . ' = ' . $db->quote($jos_menu->id));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at move to trash the menu with the fabrik_form ' . $menu . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function saveAsTemplate($menu,$template) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_template_form'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu['id']));
        try {
            $db->setQuery($query);
            $existing_template = $db->loadObject();

            if ($template != 'false') {
                if ($existing_template == null) {
                    $query->clear()
                        ->insert('#__emundus_template_form')
                        ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                        ->set($db->quoteName('form_id') . ' = ' . $db->quote($menu['id']))
                        ->set($db->quoteName('label') . ' = ' . $db->quote($menu['show_title']['titleraw']))
                        ->set($db->quoteName('intro') . ' = ' . $db->quote($menu['intro_raw']));
                    $db->setQuery($query);
                    $db->execute();
                }
            } else {
                if ($existing_template != null) {
                    $query->clear()
                        ->delete('#__emundus_template_form')
                        ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu['id']));
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when save a page as a model : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createHiddenGroup($formid,$eval = 0) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $label = array(
            'fr' => 'Hidden group',
            'en' => 'Hidden group',
        );

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        if($eval) {
            $form->setId(270);
            $elementstoduplicate = [6040, 6041, 6042, 6044, 6045];
        } else {
            $form->setId(287);
            $elementstoduplicate = [6473, 6489, 6490, 6491];
        }
        $groups	= $form->getGroups();

        try {
            $hiddengroup = $this->createGroup($label, $formid, -1);

            foreach ($groups as $group) {
                $elements = $group->getMyElements();

                foreach ($elements as $element) {
                    if (in_array($element->element->id, $elementstoduplicate)) {
                        $newelement = $element->copyRow($element->element->id, $element->element->name, $hiddengroup['group_id']);

                        // Update to publish element
                        $query->clear();
                        $query->update($db->quoteName('#__fabrik_elements'));
                        //

                        $query->set('published = 1');
                        $query->where('id =' . $newelement->id);
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }
        } catch(Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when create the hidden group of the fabrik_form ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }

    function createGroup($label, $fid, $repeat_group_show_first = 1) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();

        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        try {
            // INSERT FABRIK_GROUP
            // Insert columns.
            $columns = array(
                'name',
                'css',
                'label',
                'published',
                'created',
                'created_by',
                'created_by_alias',
                'modified',
                'modified_by',
                'checked_out',
                'checked_out_time',
                'is_join',
                'private',
                'params');

            // Insert values.
            $values = array(
                'GROUP_' . $fid,
                '',
                'GROUP_' . $fid,
                1,
                date('Y-m-d H:i:s'),
                62,
                'coordinateur',
                date('Y-m-d H:i:s'),
                0,
                0,
                date('Y-m-d H:i:s'),
                0,
                0,
                "{\"split_page\":\"0\",\"list_view_and_query\":\"1\",\"access\":\"1\",\"intro\":\"\",\"outro\":\"\",\"repeat_group_button\":\"0\",\"repeat_template\":\"repeatgroup\",\"repeat_max\":\"\",\"repeat_min\":\"\",\"repeat_num_element\":\"\",\"repeat_error_message\":\"\",\"repeat_no_data_message\":\"\",\"repeat_intro\":\"\",\"repeat_add_access\":\"1\",\"repeat_delete_access\":\"1\",\"repeat_delete_access_user\":\"\",\"repeat_copy_element_values\":\"0\",\"group_columns\":\"1\",\"group_column_widths\":\"\",\"repeat_group_show_first\":\"" . $repeat_group_show_first . "\",\"random\":\"0\",\"labels_above\":\"-1\",\"labels_above_details\":\"-1\"}"
            );

            $query->clear()
                ->insert($db->quoteName('#__fabrik_groups'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->Quote($values)));

            $db->setQuery($query);
            $db->execute();
            $groupid = $db->insertid();

            $tag = 'GROUP_' . $fid . '_' . $groupid;

            $this->translate($tag,$label,'fabrik_groups',$groupid,'label');

            $query->clear()
                ->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('name') . ' = ' . $db->quote('GROUP_' . $fid . '_' . $groupid))
                ->set($db->quoteName('label') . ' = ' . $db->quote('GROUP_' . $fid . '_' . $groupid))
                ->where($db->quoteName('id') . ' = ' . $db->quote($groupid));
            $db->setQuery($query);
            $db->execute();
            //

            // INSERT FORMGROUP
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__fabrik_formgroup'))
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($fid))
                ->order('ordering');

            $db->setQuery($query);
            $results = $db->loadObjectList();
            $orderings = [];
            foreach (array_values($results) as $result) {
                if (!in_array($result->ordering, $orderings)) {
                    $orderings[] = intval($result->ordering);
                }
            }

            $columns = array(
                'form_id',
                'group_id',
                'ordering',
            );

            $order = array_values($orderings)[strval(sizeof($orderings) - 1)] + 1;

            $values = array(
                $fid,
                $groupid,
                $order,
            );

            $query->clear()
                ->insert($db->quoteName('#__fabrik_formgroup'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->Quote($values)));

            $db->setQuery($query);
            $db->execute();

            $label_fr = $this->getTranslation($tag, 'fr-FR');
            $label_en = $this->getTranslation($tag, 'en-GB');

            return array(
                'elements' => array(),
                'group_id' => $groupid,
                'group_tag' => $tag,
                'group_showLegend' => $this->getJTEXT("GROUP_" . $fid . "_" . $groupid),
                'label' => array(
                    'fr' => $label_fr,
                    'en' => $label_en,
                ),
                'ordering' => $order,
                'formid' => $fid
            );
            //
        } catch(Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at creating a group for fabrik_form ' . $fid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function deleteGroup($group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        try {
            $query->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('published') . ' = ' . 0)
                ->where($db->quoteName('id') . ' = ' . $db->quote($group));
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error when move to trash the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Create an element with default values
     *
     * @param $gid
     * @param $plugin
     * @param $attachementId
     * @param int $evaluation
     * @return mixed
     */
    function createSimpleElement($gid,$plugin,$attachementId = null,$evaluation = 0) {
        $user = JFactory::getUser();
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        JModelLegacy::addIncludePath(JPATH_SITE . '/administrator/components/com_languages/models');
        $language = JModelLegacy::getInstance('Override', 'LanguagesModel');

        // Default parameters
        $dbtype = 'TEXT';
        $dbnull = 'NULL';
        $default = '';
        //

        if ($plugin === 'birthday') {
            $dbtype = 'DATE';
        } elseif ($plugin === 'textarea') {
            $dbtype = 'TEXT';
        } elseif ($plugin === 'display') {
            $default = 'Ajoutez du texte personnalisé pour vos candidats';
            $dbtype = 'TEXT';
        } /*elseif ($plugin === 'fileupload'){
            $dbtype='FILEUPLOAD';
        }*/

        // Prepare parameters

        $plugin == 'email' ? $params = $this->prepareElementParameters('field', $attachementId) : $params = $this->prepareElementParameters($plugin, $attachementId);
        //

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_elements'))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
            ->order('ordering');
        try {
            $db->setQuery($query);
            $results = $db->loadObjectList();
            $orderings = [];
            foreach (array_values($results) as $result) {
                if (!in_array($result->ordering, $orderings)) {
                    $orderings[] = intval($result->ordering);
                }
            }

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__fabrik_groups'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $fabrik_group = $db->loadObject();

            $group_params = json_decode($fabrik_group->params);

            $query->clear()
                ->insert($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('name') . ' = ' . $db->quote('element'))
                ->set($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                ->set($db->quoteName('plugin') . ' = ' . $db->quote($plugin == 'nom' || $plugin == 'prenom' || $plugin == 'email' ? 'field' : $plugin))
                ->set($db->quoteName('label') . ' = ' . $db->quote(strtoupper('element_' . $gid)))
                ->set($db->quoteName('checked_out') . ' = 0')
                ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('created_by') . ' = ' . $user->id)
                ->set($db->quoteName('created_by_alias') . ' = ' . $db->quote('coordinator'))
                ->set($db->quoteName('modified') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('modified_by') . ' = ' . $user->id)
                ->set($db->quoteName('width') . ' = 0')
                ->set($db->quoteName('default') . ' = ' . $db->quote($default))
                ->set($db->quoteName('hidden') . ' = 0')
                ->set($db->quoteName('eval') . ' = 1')
                ->set($db->quoteName('ordering') . ' = ' . $db->quote(array_values($orderings)[strval(sizeof($orderings) - 1)] + 1))
                ->set($db->quoteName('parent_id') . ' = 0')
                ->set($db->quoteName('published') . ' = 1')
                ->set($db->quoteName('access') . ' = 1')
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
            $db->setQuery($query);
            $db->execute();
            $elementId = $db->insertid();
            if ($plugin == 'nom' || $plugin == 'prenom') {


                if ($plugin == 'nom') {
                    $label = array(
                        'fr' => 'Nom',
                        'en' => 'Name',
                    );
                }
                if ($plugin == 'prenom') {
                    $label = array(
                        'fr' => 'Prénom',
                        'en' => 'First name',
                    );
                }

            } else {

                if ($plugin == 'email') {
                    $label = array(
                        'fr' => 'Email',
                        'en' => 'Email',
                    );
                    $plugin = 'field';
                } else {
                    $label = array(
                        'fr' => 'Element sans titre',
                        'en' => 'Unnamed item',
                    );
                }


            }


            $this->translate('ELEMENT_' . $gid . '_' . $elementId, $label,'fabrik_elements',$elementId,'label');

            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('label') . ' = ' . $db->quote(strtoupper('element_' . $gid . '_' . $elementId)))
                ->where($db->quoteName('id') . '= ' . $db->quote($elementId));
            $db->setQuery($query);
            $db->execute();

            // Add element to table
            $query
                ->clear()
                ->select([
                    'fl.db_table_name AS dbtable',
                    'fl.form_id AS formid',
                ])
                ->from($db->quoteName('#__fabrik_formgroup', 'fg'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
                ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $dbtable = $db->loadObject()->dbtable;
            $formid = $db->loadObject()->formid;

            if ($evaluation) {
                $query = "ALTER TABLE jos_emundus_evaluations" . " ADD criteria_" . $formid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                $db->setQuery($query);
                $db->execute();

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('params')
                    ->from($db->quoteName('#__fabrik_groups'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
                $db->setQuery($query);
                $group_cible_params = json_decode(($db->loadObject())->params);

                if ($group_cible_params->repeat_group_button == 1) {


                    //le groupe cible est un groupe répétable
                    //alors on crée la colone correspondante à l'element dans la table repetable;
                    $query->clear();
                    $query->select('table_join')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                        ->and($db->quoteName('table_join_key') . '=' . $db->quote('parent_id'));
                    $db->setQuery($query);
                    $table_join_name = $db->loadObject();
                    // on crée maintenant la colonne donc;
                    $query = "ALTER TABLE " . $table_join_name->table_join . " ADD criteria_" . $formid . "_" . $elementId . " " . $dbtype . " " . $dbnull;

                    $db->setQuery($query);
                    try {
                        $db->execute();
                    } catch (Exception $e) {

                        JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot not create new colum in the repeat table case: new element form group to an target group witc at group   because column already exist ' . $gid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

                    }


                }


                $name = 'criteria_' . $formid . '_' . $elementId;
            } else {
                $query = "ALTER TABLE " . $dbtable . " ADD e_" . $formid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                $db->setQuery($query);
                $db->execute();
                if($group_params->repeat_group_button == 1 || $fabrik_group->is_join == 1){
                    $repeat_table_name = $dbtable . "_" . $gid . "_repeat";
                    $query = "ALTER TABLE " . $repeat_table_name . " ADD e_" . $formid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                    $db->setQuery($query);
                    $db->execute();
                }
                $name = 'e_' . $formid . '_' . $elementId;
            }
            //

            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__fabrik_elements'));

            // Init a default subvalue
            if ($plugin === 'checkbox' || $plugin === 'radiobutton' || $plugin === 'dropdown') {
                $sub_values = [];
                $sub_labels = [];

                $sub_labels[] = strtoupper('sublabel_' . $gid . '_' . $elementId . '_0');
                $sub_values[] = 'Option 1';
                $labels = array(
                    'fr' => 'Option 1',
                    'en' => 'Option 1'
                );

                $this->translate(strtoupper('sublabel_' . $gid . '_' . $elementId . '_0'),$labels,'fabrik_elements',$elementId,'sub_labels');

                $params['sub_options'] = array(
                    'sub_values' => $sub_values,
                    'sub_labels' => $sub_labels
                );

                $query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
            }
            //

            $query->set($db->quoteName('name') . ' = ' . $db->quote($name))
                ->where($db->quoteName('id') . '= ' . $db->quote($elementId));
            $db->setQuery($query);
            $db->execute();


            if ($plugin == 'nom' || $plugin == 'prenom') {
                $query->clear()
                    ->select('count(id)')
                    ->from($db->quoteName('#__fabrik_jsactions'))
                    ->where($db->quoteName('element_id') . ' = ' . $db->quote($elementId));
                $db->setQuery($query);

                $assignations = $db->loadResult();

                if (empty($assignations)) {

                    $query->clear()
                        ->insert($db->quoteName('#__fabrik_jsactions'));
                    $query->set($db->quoteName('element_id') . ' = ' . $db->quote($elementId))
                        ->set($db->quoteName('action') . ' = ' . $db->quote('keyup'))
                        ->set($db->quoteName('code') . ' = ' . $db->quote($plugin == "nom" ? "this.set(this.get('value').toUpperCase());" : "
                            const mySentence = this.get(&#039;value&#039;);
                            const words = mySentence.split(&quot; &quot;);

                            for (let i = 0; i &lt; words.length; i++) {
                            words[i] = words[i][0].toUpperCase() + words[i].substr(1);
                               }

;
this.set(words.join(&quot; &quot;));


                        "))
                        ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode(

                                array("js_e_event" => "",
                                    "js_e_trigger" => "",
                                    "js_e_condition" => "",
                                    "js_e_value" => "",
                                    "js_published" => "1")
                            )));


                    $db->execute();

                }
            }


            return $elementId;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Problem when create a simple element in the group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateGroupElementsOrder($elements, $group_id)
    {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $date = new Date();
        $results= [];

        for ($i = 0; $i < count($elements); $i++) {

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('ordering'). ' = '.  $db->quote(htmlspecialchars($elements[$i]['order'])),
                $db->quoteName('modified_by'). ' = '. $db->quote($user),
                $db->quoteName('modified'). ' = '. $db->quote($date),
                $db->quoteName('group_id'). ' = '. $db->quote($group_id),
            );

            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($fields)
                ->where($db->quoteName('id'). ' = '. $db->quote(htmlspecialchars($elements[$i]['id'])));
            try {
                $db->setQuery($query);
                $results[] = $db->execute();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot reorder elements : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        }

    }

    /**
     * Update orders of a group's elements
     *
     * @param $elements
     * @param $group_id
     * @param $user
     * @return array|string
     */
    function updateOrder($elements, $group_id, $user, $moved_el = null)
    {
        if ($moved_el != null) {

            if ($moved_el['group_id'] == $group_id) {

                return $this->updateGroupElementsOrder($elements, $group_id);
            } else {

                //groupe cible different du groupe de provenance
                // on vérifie si le groupe cible est un groupe repeat

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('params')
                    ->from($db->quoteName('#__fabrik_groups'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($group_id));
                $db->setQuery($query);
                $group_cible_params = json_decode(($db->loadObject())->params);

                if ($group_cible_params->repeat_group_button == 1) {

                    //le groupe cible est un groupe répétable
                    //alors on crée la colone correspondante à l'element dans la table repetable;
                    $query->clear();
                    $query->select('table_join')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('group_id') . ' = ' . $db->quote($group_id))
                        ->andWhere($db->quoteName('table_join_key') . '=' . $db->quote('parent_id'));
                    $db->setQuery($query);
                    $table_join_name = $db->loadObject();


                    // on recupere la form_id
                    $query->clear()
                        ->select('fl.form_id as formid')
                        ->from($db->quoteName('#__fabrik_formgroup', 'fg'))
                        ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
                        ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($group_id));
                    $db->setQuery($query);
                    $object = $db->loadObject();

                    $form_id = $object->formid;

                    if ($moved_el['plugin'] === 'birthday') {
                        $dbtype = 'DATE';
                    } elseif ($moved_el['plugin'] === 'textarea') {
                        $dbtype = 'TEXT';
                    } else {
                        $dbtype = 'TEXT';
    }

                    // on crée maintenant la colonne donc;

                    $query = "ALTER TABLE " . $table_join_name->table_join . " ADD " . $moved_el['name'] . " " . $dbtype . " NULL";

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (Exception $e) {

                        JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot not create new colum in the repeat table case: moving element form group to an target group witch is repeat group because column already exist ' . $group_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

                    }


                }


                // Maintenant j'update enfin les ordres
                return $this->updateGroupElementsOrder($elements, $group_id);

            }
        } else {
            return $this->updateGroupElementsOrder($elements, $group_id);
        }

    }

    function ChangeRequire($element, $user)
    {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();
        $eval = 0;

        $query->select([
            'el.name AS name',
            'fl.db_table_name AS dbtable',
            'el.params AS params'
        ])
            ->from($db->quoteName('#__fabrik_elements','el'))
            ->leftJoin($db->quoteName('#__fabrik_formgroup','fg') . ' ON ' . $db->quoteName('fg.group_id') . ' = ' . $db->quoteName('el.group_id'))
            ->leftJoin($db->quoteName('#__fabrik_lists','fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
            ->where($db->quoteName('el.id') . ' = ' . $db->quote($element['id']));
        $db->setQuery($query);
        $db_element = $db->loadObject();
        $old_params = json_decode($db_element->params, true);

        if ($element['FRequire'] === 'true') {
            $old_params['validations']['plugin'][] = "notempty";
            $old_params['validations']['plugin_published'][] = "1";
            $old_params['validations']['validate_in'][] = "both";
            $old_params['validations']['validation_on'][] = "both";
            $old_params['validations']['validate_hidden'][] = "0";
            $old_params['validations']['must_validate'][] = "0";
            $old_params['validations']['show_icon'][] = "1";
            $old_params['notempty-message'] = array("");
            $old_params['notempty-validation_condition'] = array("");
            $eval = 1;
        } else {
            $key = array_search("notempty",$old_params['validations']['plugin']);
            unset($old_params['validations']['plugin'][$key]);
            unset($old_params['validations']['plugin_published'][$key]);
            unset($old_params['validations']['validate_in'][$key]);
            unset($old_params['validations']['validation_on'][$key]);
            unset($old_params['validations']['validate_hidden'][$key]);
            unset($old_params['validations']['must_validate'][$key]);
            unset($old_params['validations']['show_icon'][$key]);
            unset($old_params['notempty-message']);
            unset($old_params['notempty-validation_condition']);
            $old_params['validations']['plugin'] = array_values($old_params['validations']['plugin']);
            $old_params['validations']['plugin_published'] = array_values($old_params['validations']['plugin_published']);
            $old_params['validations']['validate_in'] = array_values($old_params['validations']['validate_in']);
            $old_params['validations']['validation_on'] = array_values($old_params['validations']['validation_on']);
            $old_params['validations']['validate_hidden'] = array_values($old_params['validations']['validate_hidden']);
            $old_params['validations']['must_validate'] = array_values($old_params['validations']['must_validate']);
            $old_params['validations']['show_icon'] = array_values($old_params['validations']['show_icon']);
        }

        $fields = array(
            $db->quoteName('eval'). ' = '.  $db->quote($eval),
            $db->quoteName('params'). ' = '.  $db->quote(json_encode($old_params)),
            $db->quoteName('modified_by'). ' = '. $db->quote($user),
            $db->quoteName('modified'). ' = '. $db->quote($date),
        );
        $query->clear()
            ->update($db->quoteName('#__fabrik_elements'))
            ->set($fields)
            ->where($db->quoteName('id'). '  ='. $element['id']);

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Problem when change require of the element ' . $element['id'] . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    function UpdateParams($element, $user) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }
        $date = new Date();

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Get old element
        $query->select([
            'el.name AS name',
            'el.plugin AS plugin',
            'el.default as default_text',
            'fl.db_table_name AS dbtable',
            'el.params AS params'
        ])
            ->from($db->quoteName('#__fabrik_elements','el'))
            ->leftJoin($db->quoteName('#__fabrik_formgroup','fg') . ' ON ' . $db->quoteName('fg.group_id') . ' = ' . $db->quoteName('el.group_id'))
            ->leftJoin($db->quoteName('#__fabrik_lists','fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
            ->where($db->quoteName('el.id') . ' = ' . $db->quote($element['id']));

        try {
            $db->setQuery($query);
            $db_element = $db->loadObject();
            //

            // Default parameters
            $dbnull = 'NULL';
            //

            switch ($element['plugin']) {
                case 'birthday':
                    $dbtype = 'DATE';
                    break;
                case 'textarea':
                    $dbtype = 'TEXT';
                    break;
                case 'date':
                    $dbtype = 'DATETIME';
                    break;
                case 'years':
                    $dbtype = 'VARCHAR(4)';
                    break;
                case 'databasejoin':
                    $dbtype = 'INT';
                    break;
                case 'display':
                    $dbtype = 'TEXT';
                    break;
                default:
                    $dbtype = 'TEXT';
            }

            if($db_element->plugin == 'display' && $element['plugin'] != 'display'){
                $element['default'] = '';
            }

            // If we change the plugin we set new params for our element
            if($db_element->plugin != $element['plugin']){
                $element['params'] = $this->updateElementParams($element['plugin'],$db_element->plugin,$element['params']);
            }

            $key = array_search("notempty", $element['params']['validations']['plugin']);
            if($element['FRequire'] != "true") {
                if($key !== false && $key !== null) {
                    unset($element['params']['validations']['plugin'][$key]);
                    unset($element['params']['validations']['plugin_published'][$key]);
                    unset($element['params']['validations']['validate_in'][$key]);
                    unset($element['params']['validations']['validation_on'][$key]);
                    unset($element['params']['validations']['validate_hidden'][$key]);
                    unset($element['params']['validations']['must_validate'][$key]);
                    unset($element['params']['validations']['show_icon'][$key]);
                }
            } else {
                if($key === false || $key === null) {
                    $element['params']['validations']['plugin'][] = "notempty";
                    $element['params']['validations']['plugin_published'][] = "1";
                    $element['params']['validations']['validate_in'][] = "both";
                    $element['params']['validations']['validation_on'][] = "both";
                    $element['params']['validations']['validate_hidden'][] = "0";
                    $element['params']['validations']['must_validate'][] = "0";
                    $element['params']['validations']['show_icon'][] = "1";
                }
            }


            // Filter by plugin
            if ($element['plugin'] === 'checkbox' || $element['plugin'] === 'radiobutton' || $element['plugin'] === 'dropdown' || $element['plugin'] === 'databasejoin') {
                $old_params = json_decode($db_element->params, true);

                if (isset($element['params']['join_db_name'])) {
                    if ($old_params['sub_options']) {
                        foreach ($old_params['sub_options']['sub_values'] as $index => $sub_value) {
                            $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                        }
                    }

                    $element['params'] = $this->addDatabaseJoinParameters($element['params']);

                    $element['plugin'] = 'databasejoin';
                } else {
                    $element['params'] = $this->deleteDatabaseJoinParams($element['params']);
                    $sub_values = [];
                    $sub_labels = [];
                    $sub_initial_selection = [];

                    if($element['params']['default_value'] == 'true') {
                        if (!array_search('PLEASE_SELECT', $old_params['sub_options']['sub_labels'])) {
                            $sub_labels[] = 'PLEASE_SELECT';
                            $sub_values[] = '';
                            $sub_initial_selection[] = '';
                        } else {
                            $sub_initial_selection[0] = '';
                        }
                    }

                    foreach ($element['params']['sub_options']['sub_values'] as $index => $sub_value) {
                        if ($old_params['sub_options']) {
                            $new_label = array(
                                'fr' => $sub_value,
                                'en' => $sub_value,
                            );
                            if ($old_params['sub_options']['sub_labels'][$index]) {
                                if($old_params['sub_options']['sub_labels'][$index] != 'PLEASE_SELECT'){
                                    $this->formsTrad($old_params['sub_options']['sub_labels'][$index], $new_label);
                                    $sub_labels[] = $old_params['sub_options']['sub_labels'][$index];
                                    $sub_values[] = $element['params']['sub_options']['sub_values'][$index];
                                }
                            } else {
                                $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                                $labels = array(
                                    'fr' => $sub_value,
                                    'en' => $sub_value,
                                );
                                $this->translate('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index,$labels,'fabrik_elements',$element['id'],'sub_labels');
                                $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                                $sub_values[] = $element['params']['sub_options']['sub_values'][$index];
                            }
                        } else {
                            $labels = array(
                                'fr' => $sub_value,
                                'en' => $sub_value,
                            );
                            $this->translate('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index,$labels,'fabrik_elements',$element['id'],'sub_labels');

                            $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                            $sub_values[] = $element['params']['sub_options']['sub_values'][$index];
                        }
                    }

                    $element['params']['sub_options'] = array(
                        'sub_values' => $sub_values,
                        'sub_labels' => $sub_labels,
                        'sub_initial_selection' => $sub_initial_selection,
                    );
                }
            } else {
                foreach ($element['params']['sub_options']['sub_labels'] as $index => $sub_label) {
                    $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                }
                if(isset($element['params']['sub_options'])) {
                    unset($element['params']['sub_options']);
                }
            }

            if ($element['plugin'] === 'field') {
                $key = array_search("isemail", $element['params']['validations']['plugin']);

                if ($element['params']['password'] != 6) {
                    $dbtype = 'TEXT';
                } else {
                    $dbtype = 'INT(' . $element['params']['maxlength'] . ')';
                }

                if ($element['params']['password'] == 3) {
                    if($key === false || $key === null) {
                        $element['params']['isemail-message'] = array("");
                        $element['params']['isemail-validation_condition'] = array("");
                        $element['params']['isemail-allow_empty'] = array("1");
                        $element['params']['isemail-check_mx'] = array("0");
                        $element['params']['validations']['plugin'][] = "isemail";
                        $element['params']['validations']['plugin_published'][] = "1";
                        $element['params']['validations']['validate_in'][] = "both";
                        $element['params']['validations']['validation_on'][] = "both";
                        $element['params']['validations']['validate_hidden'][] = "0";
                        $element['params']['validations']['must_validate'][] = "0";
                        $element['params']['validations']['show_icon'][] = "0";
                    }
                } else {
                    //$element['params']['validations']['plugin'] = array_merge(array_diff($element['params']['validations']['plugin'], array("isemail")));
                    $key = array_search("isemail", $element['params']['validations']['plugin']);
                    if($key !== false && $key !== null) {
                        unset($element['params']['validations']['plugin'][$key]);
                        unset($element['params']['validations']['plugin_published'][$key]);
                        unset($element['params']['validations']['validate_in'][$key]);
                        unset($element['params']['validations']['validation_on'][$key]);
                        unset($element['params']['validations']['validate_hidden'][$key]);
                        unset($element['params']['validations']['must_validate'][$key]);
                        unset($element['params']['validations']['show_icon'][$key]);
                        unset($element['params']['isemail-message']);
                        unset($element['params']['isemail-validation_condition']);
                        unset($element['params']['isemail-allow_empty']);
                        unset($element['params']['isemail-check_mx']);
                    }
                }
            }

            $query = "ALTER TABLE " . $db_element->dbtable .
                " MODIFY COLUMN `" . $db_element->name . "` " . $dbtype . " " . $dbnull;
            $db->setQuery($query);
            $db->execute();

            // Update the element
            $query = $db->getQuery(true);

            $fields = array(
                $db->quoteName('plugin') . ' = ' . $db->quote($element['plugin']),
                $db->quoteName('default') . ' = ' . $db->quote($element['default']),
                $db->quoteName('params') . ' = ' . $db->quote(json_encode($element['params'])),
                $db->quoteName('modified_by') . ' = ' . $db->quote($user),
                $db->quoteName('modified') . ' = ' . $db->quote($date),
            );
            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $db->quote($element['id']));
            //
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at updating the element ' . $element['id'] . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function duplicateElement($eid,$group,$old_group,$form_id){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
        $groupModel->setId(intval($old_group));
        $elements = $groupModel->getMyElements();
        //

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($group));
        $db->setQuery($query);
        $new_group = $db->loadObject();

        $new_group_params = json_decode($new_group->params);

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        try {
            foreach ($elements as $element) {
                if($element->element->id == $eid) {
                    $dbtype = 'TEXT';

                    $newelement = $element->copyRow($element->element->id, 'Copy of %s', intval($group),'e_' . $form_id . '_tmp');
                    $newelementid = $newelement->id;

                    $el_params = json_decode($element->element->params);

                    // Update translation files
                    if (($element->element->plugin === 'checkbox' || $element->element->plugin === 'radiobutton' || $element->element->plugin === 'dropdown') && $el_params->sub_options) {
                        $sub_labels = [];
                        foreach ($el_params->sub_options->sub_labels as $index => $sub_label) {
                            $labels_to_duplicate = array(
                                'fr' => $this->getTranslation($sub_label, 'fr-FR'),
                                'en' => $this->getTranslation($sub_label, 'en-GB')
                            );
                            if($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
                                $labels_to_duplicate = array(
                                    'fr' => $sub_label,
                                    'en' => $sub_label
                                );
                            }
                            $this->translate('SUBLABEL_' . $group . '_' . $newelementid . '_' . $index,$labels_to_duplicate,'fabrik_elements',$newelementid,'sub_labels');
                            $sub_labels[] = 'SUBLABEL_' . $group . '_' . $newelementid . '_' . $index;
                        }
                        $el_params->sub_options->sub_labels = $sub_labels;
                    }
                    $query->clear();
                    $query->update($db->quoteName('#__fabrik_elements'));

                    $labels_to_duplicate = array(
                        'fr' => $this->getTranslation($element->element->label, 'fr-FR'),
                        'en' => $this->getTranslation($element->element->label, 'en-GB')
                    );
                    if($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
                        $labels_to_duplicate = array(
                            'fr' => $element->element->label,
                            'en' => $element->element->label
                        );
                    }
                    $this->translate('ELEMENT_' . $group . '_' . $newelementid,$labels_to_duplicate,'fabrik_elements',$newelementid,'label');
                    //

                    $query->set('label = ' . $db->quote('ELEMENT_' . $group . '_' . $newelementid));
                    $query->set('name = ' . $db->quote('e_' . $form_id . '_' . $newelementid));
                    $query->set('published = 1');
                    $query->set('params = ' . $db->quote(json_encode($el_params)));
                    $query->where('id =' . $newelementid);
                    $db->setQuery($query);
                    $db->execute();

                    $query
                        ->clear()
                        ->select([
                            'fl.db_table_name AS dbtable',
                        ])
                        ->from($db->quoteName('#__fabrik_lists', 'fl'))
                        ->where($db->quoteName('fl.form_id') . ' = ' . $db->quote($form_id));
                    $db->setQuery($query);
                    $dbtable = $db->loadObject()->dbtable;

                    if ($element->element->plugin === 'birthday') {
                        $dbtype = 'DATE';
                    } elseif ($element->element->plugin === 'textarea') {
                        $dbtype = 'TEXT';
                    }

                    $query = "ALTER TABLE " . $dbtable . " ADD e_" . $form_id . "_" . $newelementid . " " . $dbtype . " NULL";
                    $db->setQuery($query);
                    $db->execute();

                    if($new_group_params->repeat_group_button == 1){
                        $repeat_table_name = $dbtable . "_" . $group . "_repeat";
                        $query = "ALTER TABLE " . $repeat_table_name . " ADD e_" . $form_id . "_" . $newelementid . " " . $dbtype . " NULL";
                        $db->setQuery($query);
                        $db->execute();
                    }

                    return  $newelementid;
                }
            }
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot duplicate the element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Return an element with fabrik parameters
     *
     * @param $element
     * @param $gid
     * @return mixed
     */
    function getElement($element,$gid) {
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $group = JModelLegacy::getInstance('Group', 'FabrikFEModel');
        $group->setId(intval($gid));
        $elements = $group->getMyElements();

        // Prepare languages
        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        ${"element".$element} = new stdClass();

        foreach ($elements as $group_elt) {
            if ($group_elt->element->id == $element) {
                $o_element = $group_elt->element;
                $el_params = json_decode($o_element->params);
                $content_element = $group_elt->preRender('0','1','bootstrap');

                $labelsAbove = $content_element->labels;

                ${"element".$o_element->id}->id = $o_element->id;
                ${"element".$o_element->id}->name = $o_element->name;
                ${"element".$o_element->id}->group_id = $gid;

                ${"element".$o_element->id}->hidden = $content_element->hidden;
                ${"element".$o_element->id}->default = $o_element->default;
                ${"element".$o_element->id}->labelsAbove=$labelsAbove;
                ${"element".$o_element->id}->plugin=$o_element->plugin;
                if (empty($el_params->validations)) {
                    $FRequire = false;
                } else {
                    if(isset($el_params->validations->plugin)){
                        if(empty($el_params->validations->plugin) || !in_array('notempty',$el_params->validations->plugin)){
                            $FRequire = false;
                        } else {
                            $FRequire = true;
                        }
                    }
                }

                if ($el_params->sub_options) {
                    foreach ($el_params->sub_options->sub_labels as $key => $sub_label) {
                        $el_params->sub_options->sub_labels[$key] = $this->getTranslation($sub_label,'fr-FR');
                    }
                }

                ${"element".$o_element->id}->FRequire=$FRequire;
                ${"element".$o_element->id}->params=$el_params;
                ${"element".$o_element->id}->label_tag = $o_element->label;
                ${"element" . $o_element->id}->label = new stdClass;
                ${"element".$o_element->id}->label->fr = $this->getTranslation(${"element".$o_element->id}->label_tag,'fr-FR');
                ${"element".$o_element->id}->label->en = $this->getTranslation(${"element".$o_element->id}->label_tag,'en-GB');
                if(${"element" . $o_element->id}->label->fr === false){
                    ${"element" . $o_element->id}->label->fr = $o_element->label;
                }
                if(${"element" . $o_element->id}->label->en === false){
                    ${"element" . $o_element->id}->label->en = $o_element->label;
                }
                ${"element".$o_element->id}->labelToFind=$group_elt->label;
                ${"element".$o_element->id}->publish=$group_elt->isPublished();


                if ($labelsAbove == 2) {
                    if ($el_params->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    ///// ici
                    if ($content_element->element) :
                        if($o_element->plugin == 'date') {
                            ${"element" . $o_element->id}->element = '<input data-v-8d3bb2fa="" class="form-control" type="date">';
                        }
                        else {
                            ${"element" . $o_element->id}->element = $content_element->element;
                        }
                    endif;
                    //// ici
                    if ($content_element->error) :
                        ${"element".$o_element->id}->error=$content_element->error;
                        ${"element".$o_element->id}->errorClass=$el_params->class;
                    endif;
                    if ($el_params->tipLocation == 'side') :
                        ${"element".$o_element->id}->tipSide=$content_element->tipSide;
                    endif;
                    if ($el_params->tipLocation == 'below') :
                        ${"element".$o_element->id}->tipBelow=$content_element->tipBelow;
                    endif;
                } else {
                    ${"element" . $o_element->id}->label_value = $content_element->label;

                    if ($el_params->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    if ($content_element->element) :
                        if($o_element->plugin == 'date') {
                            ${"element" . $o_element->id}->element = '<input data-v-8d3bb2fa="" class="form-control" type="date">';
                        }
                        else {
                            ${"element" . $o_element->id}->element = $content_element->element;
                        }
                    endif;
                    if ($content_element->error) :
                        ${"element".$o_element->id}->error=$content_element->error;
                        ${"element".$o_element->id}->errorClass=$el_params->class;
                    endif;
                    if ($el_params->tipLocation == 'side') :
                        ${"element".$o_element->id}->tipSide=$content_element->tipSide;
                    endif;
                    if ($el_params->tipLocation == 'below') :
                        ${"element".$o_element->id}->tipBelow=$content_element->tipBelow;
                    endif;
                }
            }
        }

        return ${"element".$element};
    }

    function deleteElement($elt) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update('#__fabrik_elements')
                ->set($db->quoteName('published') . ' = -2')
                ->where($db->quoteName('id') . ' = ' . $db->quote($elt));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot move the element to trash ' . $elt . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }

    function reorderMenu($link, $rgt) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->update($db->quoteName('#__menu'))
                ->set('rgt = ' . $db->quote($rgt))
                ->set('lft = ' . $db->quote($rgt - 1))
                ->where('link = ' . $db->quote($link));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at reorder the menu with link ' . $link . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getGroupOrdering($gid,$fid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('ordering')
                ->from($db->quoteName('#__fabrik_formgroup'))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                ->andWhere($db->quoteName('form_id') . ' = ' . $db->quote($fid));

            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot get ordering of group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function reorderGroup($gid, $fid, $order) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->update($db->quoteName('#__fabrik_formgroup'))
                ->set('ordering = ' . $db->quote($order))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                ->andWhere($db->quoteName('form_id') . ' = ' . $db->quote($fid));

            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot reorder group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get menus templates
     *
     * @return array|mixed|void
     */
    function getPagesModel() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        //Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();

        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        $query->select('*')
            ->from($db->quoteName('#__emundus_template_form'))
            ->order('form_id');

        try {
            $db->setQuery($query);
            $models = $db->loadObjectList();

            foreach ($models as $model) {
                $model->label = array(
                    'fr' => $this->getTranslation($model->label,'fr-FR'),
                    'en' => $this->getTranslation($model->label,'en-GB')
                );
                $model->intro = array(
                    'fr' => $this->getTranslation($model->intro,'fr-FR'),
                    'en' => $this->getTranslation($model->intro,'en-GB')
                );
            }

            return $models;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at getting pages models : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    /**
     * Create a menu from a choosen template
     *
     * @param $formid
     * @param $prid
     * @return array
     */
    function createMenuFromTemplate($label, $intro, $formid, $prid) {
        // TODO Use Joomla API to create a menu
        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId(intval($formid));
        $groups	= $form->getGroups();
        //

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,103,104,168,170];
        //

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Get the profile
        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
        $db->setQuery($query);
        $profile = $db->loadObject();
        //

        // Get the header menu
        $query->clear()
            ->select('*')
            ->from('#__menu')
            ->where($db->quoteName('menutype') . ' = ' . $db->quote($profile->menutype))
            ->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
        $db->setQuery($query);
        $menu_parent = $db->loadObject();
        //

        // Duplicate the form
        $query->clear()
            ->select('*')
            ->from('#__fabrik_forms')
            ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
        $db->setQuery($query);
        $form_model = $db->loadObject();

        $query->clear();
        $query->insert($db->quoteName('#__fabrik_forms'));
        foreach ($form_model as $key => $val) {
            if ($key != 'id') {
                $query->set($key . ' = ' . $db->quote($val));
            }
        }
        try {
            $db->setQuery($query);
            $db->execute();
            $newformid = $db->insertid();

            // Set emundus plugin in params
            /*if($formid == 258) {
                $query->clear();
                $query->select('params')
                    ->from($db->quoteName('#__fabrik_forms'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
                $db->setQuery($query);
                $params = json_decode($db->loadResult(), true);
                $params = $this->prepareSubmittionPlugin($params);

            } else {
                $query->clear();
                $query->select('params')
                    ->from($db->quoteName('#__fabrik_forms'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
                $db->setQuery($query);
                $params = json_decode($db->loadResult(), true);
                $params = $this->prepareFormPlugin($params);
            }*/
            //

            // Update translation files
            $query->clear();
            $query->update($db->quoteName('#__fabrik_forms'));

            $this->translate('FORM_' . $prid. '_' . $newformid,$label,'fabrik_forms',$newformid,'label');
            $this->translate('FORM_' . $prid . '_INTRO_' . $newformid,$intro,'fabrik_forms',$newformid,'intro');
            //

            $query->set('label = ' . $db->quote('FORM_' . $prid . '_' . $newformid));
            $query->set('intro = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $newformid . '</p>'));
            //$query->set('params = ' . $db->quote(json_encode($params)));
            $query->where('id =' . $newformid);
            $db->setQuery($query);
            $db->execute();
            //

            // Duplicate fabrik list
            $query->clear()
                ->select('*')
                ->from('#__fabrik_lists')
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $list_model = $db->loadObject();

            $db_table_name = $list_model->db_table_name;

            /*if($list_model->db_table_name != 'jos_emundus_declaration') {
                // Create table
                $query->clear()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('information_schema.tables'))
                    ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('%jos_emundus_' . $prid . '%'));
                $db->setQuery($query);
                $result = $db->loadResult();

                if ($result < 10) {
                    $increment = '0' . strval($result);
                } elseif ($result > 10) {
                    $increment = strval($result);
                }
                $db_table_name = 'jos_emundus_' . $prid . '_' . $increment;
                $table_query = "CREATE TABLE " . $db_table_name . " LIKE " . $list_model->db_table_name;
                $db->setQuery($table_query);
                $db->execute();
                //
            } else {
                $db_table_name = 'jos_emundus_declaration';
            }*/

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id' && $key != 'access') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($newformid));
                } /*elseif ($key == 'db_table_name') {
                    if($val != 'jos_emundus_declaration') {
                        $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment));
                    } else {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                }
                elseif ($key == 'db_primary_key') {
                    if($list_model->db_table_name != 'jos_emundus_declaration') {
                        $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment . '.id'));
                    } else {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                } */
                elseif ($key == 'access') {
                    $query->set($key . ' = ' . $db->quote($prid));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $newlistid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'));
            $query->set('label = ' . $db->quote('FORM_' . $prid . '_' . $newformid));
            $query->set('introduction = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $newformid . '</p>'));
            $query->where('id =' . $db->quote($newlistid));
            $db->setQuery($query);
            $db->execute();
            //

            // JOIN LIST AND PROFILE_ID
            $columns = array(
                'form_id',
                'profile_id',
                'created'
            );

            $values = array(
                $newlistid,
                $prid,
                date('Y-m-d H:i:s')
            );

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_formlist'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->quote($values)));
            $db->setQuery($query);
            $db->execute();
            //
            //

            // Duplicate group
            $ordering = 0;
            foreach ($groups as $group) {
                $ordering++;
                $properties = $group->getGroupProperties($group->getFormModel());
                $elements = $group->getMyElements();

                $query->clear()
                    ->select('*')
                    ->from('#__fabrik_groups')
                    ->where($db->quoteName('id') . ' = ' . $db->quote($properties->id));
                $db->setQuery($query);
                $group_model = $db->loadObject();

                $query->clear();
                $query->insert($db->quoteName('#__fabrik_groups'));
                foreach ($group_model as $key => $val) {
                    if ($key != 'id') {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                }
                $db->setQuery($query);
                $db->execute();
                $newgroupid = $db->insertid();

                if($group_model->is_join == 1){
                    $query->clear()
                        ->select('table_join')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('group_id') . ' = ' . $db->quote($properties->id))
                        ->andWhere($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
                    $db->setQuery($query);
                    $repeat_table_to_copy = $db->loadResult();

                    /*$newtablename = 'jos_emundus_' . $prid . '_' . $increment . '_' . $newgroupid . '_repeat';
                    $table_query = "CREATE TABLE " . $newtablename . " LIKE " . $repeat_table_to_copy;
                    $db->setQuery($table_query);
                    $db->execute();*/

                    $joins_params = '{"type":"group","pk":"`' . $repeat_table_to_copy . '`.`id`"}';

                    $query->clear()
                        ->insert($db->quoteName('#__fabrik_joins'));
                    $query->set($db->quoteName('list_id') . ' = ' . $db->quote($newlistid))
                        ->set($db->quoteName('element_id') . ' = ' . $db->quote(0))
                        ->set($db->quoteName('join_from_table') . ' = ' . $db->quote($db_table_name))
                        ->set($db->quoteName('table_join') . ' = ' . $db->quote($repeat_table_to_copy))
                        ->set($db->quoteName('table_key') . ' = ' . $db->quote('id'))
                        ->set($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'))
                        ->set($db->quoteName('join_type') . ' = ' . $db->quote('left'))
                        ->set($db->quoteName('group_id') . ' = ' . $db->quote($newgroupid))
                        ->set($db->quoteName('params') . ' = ' . $db->quote($joins_params));
                    $db->setQuery($query);
                    $db->execute();
                }

                // Update translation files
                $query->clear();
                $query->update($db->quoteName('#__fabrik_groups'));

                if($formid == 258) {
                    $labels = array(
                        'fr' => "Confirmation d'envoi de dossier",
                        'en' => 'Confirmation of file sending',
                    );
                    $this->translate('GROUP_' . $newformid . '_' . $newgroupid,$labels,'fabrik_groups',$newgroupid,'label');
                } else {
                    $labels_to_duplicate = array();
                    foreach ($languages as $language) {
                        $labels_to_duplicate[$language->sef] = $this->getTranslation($group_model->label,$language->lang_code);
                        if($label[$language->sef] == ''){
                            $label[$language->sef] = $group_model->label;
                        }
                    }
                    $this->translate('GROUP_' . $newformid . '_' . $newgroupid, $labels_to_duplicate,'fabrik_groups',$newgroupid,'label');
                }
                //

                $query->set('label = ' . $db->quote('GROUP_' . $newformid . '_' . $newgroupid));
                $query->set('name = ' . $db->quote('GROUP_' . $newformid . '_' . $newgroupid));
                $query->where('id =' . $newgroupid);
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->insert($db->quoteName('#__fabrik_formgroup'))
                    ->set('form_id = ' . $db->quote($newformid))
                    ->set('group_id = ' . $db->quote($newgroupid))
                    ->set('ordering = ' . $db->quote($ordering));
                $db->setQuery($query);
                $db->execute();

                foreach ($elements as $element) {
                    try {
                        $newelement = $element->copyRow($element->element->id, 'Copy of %s', $newgroupid);
                        $newelementid = $newelement->id;

                        $el_params = json_decode($element->element->params);

                        // Update translation files
                        if(($element->element->plugin === 'checkbox' || $element->element->plugin === 'radiobutton' || $element->element->plugin === 'dropdown') && $el_params->sub_options){
                            $sub_labels = [];
                            foreach ($el_params->sub_options->sub_labels as $index => $sub_label) {
                                $labels_to_duplicate = array();
                                foreach ($languages as $language) {
                                    $labels_to_duplicate[$language->sef] = $this->getTranslation($sub_label,$language->lang_code);
                                    if($label[$language->sef] == ''){
                                        $label[$language->sef] = $sub_label;
                                    }
                                }
                                $this->translate('SUBLABEL_' . $newgroupid. '_' . $newelementid . '_' . $index,$labels_to_duplicate,'fabrik_elements',$newelementid,'sub_labels');
                                $sub_labels[] = 'SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index;
                            }
                            $el_params->sub_options->sub_labels = $sub_labels;
                        }
                        $query->clear();
                        $query->update($db->quoteName('#__fabrik_elements'));

                        $labels_to_duplicate = array();
                        foreach ($languages as $language) {
                            $labels_to_duplicate[$language->sef] = $this->getTranslation($element->element->label,$language->lang_code);
                            if($label[$language->sef] == ''){
                                $label[$language->sef] = $element->element->label;
                            }
                        }
                        $this->translate('ELEMENT_' . $newgroupid. '_' . $newelementid,$labels_to_duplicate,'fabrik_elements',$newelementid,'label');
                        //

                        $query->set('label = ' . $db->quote('ELEMENT_' . $newgroupid . '_' . $newelementid));
                        $query->set('published = 1');
                        $query->set('params = ' . $db->quote(json_encode($el_params)));
                        $query->where('id =' . $newelementid);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add('component/com_emundus_onboard/models/formbuilder | Error at create a page from the model ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                    }
                }
            }
            //

            // Duplicate the form-menu
            $query
                ->clear()
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($profile->menutype))
                ->andWhere($db->quoteName('path') . ' LIKE ' . $db->quote($profile->menutype . '%'))
                ->order('rgt');
            $db->setQuery($query);
            $menus = $db->loadObjectList();
            $rgts = [];
            $lfts = [];
            foreach (array_values($menus) as $menu) {
                if (!in_array($menu->rgt, $rgts)) {
                    $rgts[] = intval($menu->rgt);
                }
                if (!in_array($menu->lft, $lfts)) {
                    $lfts[] = intval($menu->lft);
                }
            }

            $query->clear()
                ->select(['id AS id', 'link AS link'])
                ->from($db->quoteName('#__menu'));

            $db->setQuery($query);
            $model_menus = $db->loadObjectList();

            $menu_id = 0;

            foreach ($model_menus as $model_menu) {
                if ($formid == explode('=', $model_menu->link)[3]) {
                    $menu_id = $model_menu->id;
                    break;
                }
            }

            $query->clear()
                ->select('*')
                ->from('#__menu')
                ->where($db->quoteName('id') . ' = ' . $menu_id);

            $db->setQuery($query);
            $menu_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__menu'));
            foreach ($menu_model as $key => $val) {
                if ($key != 'id' && $key != 'menutype' && $key != 'alias' && $key != 'path' && $key != 'link' && $key != 'parent_id' && $key != 'lft' && $key != 'rgt'  && $key != 'title') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'menutype') {
                    $query->set($key . ' = ' . $db->quote($profile->menutype));
                } elseif ($key == 'title') {
                    $query->set($key . ' = ' . $db->quote('FORM_' . $profile->id . '_' . $newformid));
                } elseif ($key == 'alias') {
                    $query->set($key . ' = ' . $db->quote('form-' . $newformid . '-' . str_replace($this->getSpecialCharacters(),'-',strtolower($label['fr']))));
                } elseif ($key == 'path') {
                    if(strpos($val,'/') !== false){
                        $query->set($key . ' = ' . $db->quote($menu_parent->path . '/' . str_replace($this->getSpecialCharacters(), '-', strtolower($label['fr'])) . '-' . $newformid));
                    } else {
                        $query->set($key . ' = ' . $db->quote($val . '-' . $profile->id));
                    }
                } elseif ($key == 'link') {
                    $query->set($key . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $newformid));
                } elseif ($key == 'parent_id') {
                    if($list_model->db_table_name != 'jos_emundus_declaration') {
                        $query->set($key . ' = ' . $db->quote($menu_parent->id));
                    } else {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                } elseif ($key == 'lft') {
                    if($list_model->db_table_name != 'jos_emundus_declaration') {
                        if (strpos($menu_model->path, '/') !== false) {
                            $query->set($key . ' = ' . $db->quote(array_values($lfts)[strval(sizeof($lfts) - 1)] + 2));
                        }
                    } else {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                } elseif ($key == 'rgt') {
                    if($list_model->db_table_name != 'jos_emundus_declaration') {
                        if (strpos($menu_model->path, '/') !== false) {
                            $query->set($key . ' = ' . $db->quote(array_values($rgts)[strval(sizeof($rgts) - 1)] + 2));
                        }
                    } else {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                }
            }
            $db->setQuery($query);
            $db->execute();
            $newmenuid = $db->insertid();

            // Add translation for menu
            $falang->insertFalang($label,$newmenuid,'menu','title');
            //

            // Affect modules to this menu
            foreach ($modules as $module) {
                $query->clear()
                    ->insert($db->quoteName('#__modules_menu'))
                    ->set($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                    ->set($db->quoteName('menuid') . ' = ' . $db->quote($newmenuid));
                $db->setQuery($query);
                $db->execute();
            }
            //
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at create a page from the model ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
        //

        return array(
            'id' => $newformid,
            'link' => 'index.php?option=com_fabrik&view=form&formid=' . $newformid,
            'rgt' => array_values($rgts)[strval(sizeof($rgts)-1)] + 2,
        );
    }

    function checkConstraintGroup($cid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('sg.id')
                ->from($db->quoteName('#__emundus_setup_campaigns', 'c'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc') . ' ON ' . $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $db->quoteName('gc.parent_id') . ' = ' . $db->quoteName('sg.id'))
                ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid))
                ->andWhere($db->quoteName('sg.description') . ' LIKE ' . $db->quote('constraint_group'));
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at check constraints groups of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function checkVisibility($group,$cid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('COUNT(gf.id)')
                ->from($db->quoteName('#__emundus_setup_campaigns', 'c'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc') . ' ON ' . $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $db->quoteName('gc.parent_id') . ' = ' . $db->quoteName('sg.id'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link', 'gf') . ' ON ' . $db->quoteName('gf.parent_id') . ' = ' . $db->quoteName('sg.id'))
                ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid))
                ->andWhere($db->quoteName('sg.description') . ' LIKE ' . $db->quote('constraint_group'))
                ->andWhere($db->quoteName('gf.fabrik_group_link') . ' = ' . $db->quote($group));
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at check visibility of the group ' . $group . ' in campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function publishUnpublishElement($element) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('published')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($element));
            $db->setQuery($query);
            $old_publish = $db->loadResult();

            $publish = 1;
            if ($old_publish == 1) {
                $publish = 0;
            }

            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('published') . ' = ' . $db->quote($publish))
                ->where($db->quoteName('id') . ' = ' . $db->quote($element));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at publish/unpublish element ' . $element . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getDatabasesJoin() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_datas_library'));
        $db->setQuery($query);
        try {
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at getting databases references : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
    function getDatabasesJoinOrdonancementColumns($database_name) {

        $db = $this->getDbo();
        $query = "SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$database_name'";

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at getting databases references columns : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function enableRepeatGroup($gid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser()->id;

        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
        $groupModel->setId(intval($gid));
        $elements = $groupModel->getMyElements();
        //

        try {
            $query->select('*')
                ->from($db->quoteName('#__fabrik_groups'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $group = $db->loadObject();

            $query->clear()
                ->select('fl.db_table_name as dbtable, fl.form_id as formid, fl.id as listid')
                ->from($db->quoteName('#__fabrik_formgroup', 'fg'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
                ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $object = $db->loadObject();
            $db_table = $object->dbtable;
            $form_id = $object->formid;
            $list_id = $object->listid;

            $group_params = json_decode($group->params);
            $group_params->repeat_group_button = 1;

            $query->clear()
                ->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('is_join') . ' = ' . $db->quote(1))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($group_params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $db->execute();

            // Create the new table
            $newtablename = $db_table . "_" . $gid . "_repeat";
            $joins_params = '{"type":"group","pk":"`' . $newtablename . '`.`id`"}';

            $query = "CREATE TABLE IF NOT EXISTS " . $newtablename . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
            $db->setQuery($query);
            $db->execute();
            //

            // Create parent_id element
            $query = $db->getQuery(true);
            $params = $this->prepareElementParameters('field',0);
            $params['validations'] = array();

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                ->order('ordering');
            $db->setQuery($query);
            $results = $db->loadObjectList();
            $orderings = [];
            foreach (array_values($results) as $result) {
                if (!in_array($result->ordering, $orderings)) {
                    $orderings[] = intval($result->ordering);
                }
            }

            // Check if the ID and parent_id already exists in the group
            $ignore_elms = [];
            foreach ($elements as $element => $value) {
                if ($value->element->name == 'parent_id' || $value->element->name == 'id') {
                    $ignore_elms[] = $value->element->name;
                }
            }
            // Insert parent_id in elements

            if (!in_array('parent_id', $ignore_elms)) {
                $query
                    ->clear()
                    ->insert($db->quoteName('#__fabrik_elements'))
                    ->set($db->quoteName('name') . ' = ' . $db->quote('parent_id'))
                    ->set($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                    ->set($db->quoteName('plugin') . ' = ' . $db->quote('field'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote('parent_id'))
                    ->set($db->quoteName('checked_out') . ' = 0')
                    ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('created_by') . ' = ' . $db->quote($user))
                    ->set($db->quoteName('created_by_alias') . ' = ' . $db->quote('coordinator'))
                    ->set($db->quoteName('modified') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
                    ->set($db->quoteName('width') . ' = 0')
                    ->set($db->quoteName('default') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('hidden') . ' = 1')
                    ->set($db->quoteName('eval') . ' = 0')
                    ->set($db->quoteName('ordering') . ' = ' . $db->quote(array_values($orderings)[strval(sizeof($orderings) - 1)] + 1))
                    ->set($db->quoteName('parent_id') . ' = 0')
                    ->set($db->quoteName('published') . ' = 1')
                    ->set($db->quoteName('access') . ' = 1')
                    ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
                $db->setQuery($query);
                $db->execute();
            }

            if (!in_array('id', $ignore_elms)) {
                // Insert id in elements
                $query
                    ->clear()
                    ->insert($db->quoteName('#__fabrik_elements'))
                    ->set($db->quoteName('name') . ' = ' . $db->quote('id'))
                    ->set($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                    ->set($db->quoteName('plugin') . ' = ' . $db->quote('internalid'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote('id'))
                    ->set($db->quoteName('checked_out') . ' = 0')
                    ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('created_by') . ' = ' . $db->quote($user))
                    ->set($db->quoteName('created_by_alias') . ' = ' . $db->quote('coordinator'))
                    ->set($db->quoteName('modified') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
                    ->set($db->quoteName('width') . ' = 0')
                    ->set($db->quoteName('default') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('hidden') . ' = 1')
                    ->set($db->quoteName('eval') . ' = 0')
                    ->set($db->quoteName('ordering') . ' = ' . $db->quote(array_values($orderings)[strval(sizeof($orderings) - 1)] + 1))
                    ->set($db->quoteName('parent_id') . ' = 0')
                    ->set($db->quoteName('published') . ' = 1')
                    ->set($db->quoteName('access') . ' = 1')
                    ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
                $db->setQuery($query);
                $db->execute();
            }

            try {

                $query = "ALTER TABLE " . $newtablename . " ADD COLUMN parent_id int(11) NULL AFTER id";
                $db->setQuery($query);
                $db->execute();

                $query = "CREATE INDEX fb_parent_fk_parent_id_INDEX ON " . $newtablename . " (parent_id);";
                $db->setQuery($query);
                $db->execute();

            } catch(Exception $e) {
                // This means that the parent_id already exists in the table.
            }


            //verify if left join dosn't already exist;
            $query = $db->getQuery(true);
            $query->select('id')
                ->from($db->quoteName('#__fabrik_joins'))
                ->where($db->quoteName('table_join') . ' = ' . $db->quote($newtablename))
                ->and($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
            $db->setQuery($query);
            $left_join_exist = $db->loadObject();

            if ($left_join_exist == NULL) {
                $query->clear();
                $query->insert($db->quoteName('#__fabrik_joins'));
                $query->set($db->quoteName('list_id') . ' = ' . $db->quote($list_id))
                    ->set($db->quoteName('element_id') . ' = ' . $db->quote(0))
                    ->set($db->quoteName('join_from_table') . ' = ' . $db->quote($db_table))
                    ->set($db->quoteName('table_join') . ' = ' . $db->quote($newtablename))
                    ->set($db->quoteName('table_key') . ' = ' . $db->quote('id'))
                    ->set($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'))
                    ->set($db->quoteName('join_type') . ' = ' . $db->quote('left'))
                    ->set($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                    ->set($db->quoteName('params') . ' = ' . $db->quote($joins_params));
                $db->setQuery($query);
                $db->execute();
                //
            }


            // Insert leftjoin in fabrik


            // Insert element present in the group
            foreach ($elements as $element) {
                if ($element->element->plugin === 'birthday') {
                    $dbtype = 'DATE';
                } elseif ($element->element->plugin === 'textarea') {
                    $dbtype = 'TEXT';
                } else {
                    $dbtype = 'TEXT';
                }

                $query = "ALTER TABLE " . $newtablename . " ADD e_" . $form_id . "_" . $element->element->id . " " . $dbtype . " NULL";
                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    continue;
                }
            }
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot enable repeat group ' . $gid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function disableRepeatGroup($gid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__fabrik_groups'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $group = $db->loadObject();

            // Disable group repeat
            $query->clear()
                ->select('fl.db_table_name as dbtable')
                ->from($db->quoteName('#__fabrik_formgroup','fg'))
                ->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('fg.form_id'))
                ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $group_params = json_decode($group->params);
            $group_params->repeat_group_button = 0;

            $query->clear()
                ->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($group_params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot disable repeat group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function displayHideGroup($gid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__fabrik_groups'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $group_params = json_decode($db->loadResult());
            if((int)$group_params->repeat_group_show_first == -1){
                $group_params->repeat_group_show_first = 1;
            } else {
                $group_params->repeat_group_show_first = -1;
            }

            $query->clear()
                ->update($db->quoteName('#__fabrik_groups'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($group_params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $db->execute();
            return $group_params->repeat_group_show_first;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot disable repeat group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateMenuLabel($label,$pid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $link = 'index.php?option=com_fabrik&view=form&formid=' . $pid;

        $query->select('id')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote($link));
        $db->setQuery($query);

        try {
            $menuid = $db->loadObject();

            return $falang->updateFalang($label,$menuid->id,'menu','title');
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot update the menu label of the fabrik_form ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getFormTesting($prid,$uid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,label')
                ->from($db->quoteName('#__emundus_setup_campaigns'))
                ->where($db->quoteName('profile_id') . ' = ' . $db->quote($prid));
            $db->setQuery($query);
            $campaigns = $db->loadObjectList();
            if(sizeof($campaigns) > 0){
                foreach ($campaigns as $campaign) {
                    $query->clear()
                        ->select('id,fnum')
                        ->from($db->quoteName('#__emundus_campaign_candidature'))
                        ->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign->id))
                        ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                        ->andWhere($db->quoteName('published') . ' != ' . $db->quote(-1));
                    $db->setQuery($query);
                    $campaign->files = $db->loadObjectList();
                }
            }
            return $campaigns;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at getting files and campaigns of the form ' . $prid . ' and of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createTestingFile($cid,$uid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        include_once(JPATH_SITE.'/components/com_emundus/helpers/files.php');

        $fnum = @EmundusHelperFiles::createFnum($cid, $uid);

        try {
            $query->insert($db->quoteName('#__emundus_campaign_candidature'));
            $query->set($db->quoteName('applicant_id') . ' = ' . $db->quote($uid))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($cid))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $db->execute();

            return $fnum;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Error at creating a testing file in the campaign ' . $cid . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function deleteFormTesting($fnum,$uid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        try {
            $query->delete()
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($uid));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot delete testing file ' . $fnum . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function retriveElementFormAssociatedDoc($gid,$docid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {

            $query->select('*')

                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($docid));

            $db->setQuery($query);

            return $db->loadObject();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/formbuilder | Cannot get ordering of group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

}
