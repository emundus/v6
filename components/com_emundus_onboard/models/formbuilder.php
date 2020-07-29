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

    /**
     * Return special character to escape when insert into db
     *
     * @return string[]
     */
    function getSpecialCharacters() {
        return array('=','&',',','#','_','*',';','!','?',':','+','$','\'',' ','£',')','(','@','%');
    }

    function htmlspecial_array(&$variable) {
        foreach ($variable as &$value) {
            if (!is_array($value)) { $value = htmlspecialchars($value); }
            else { $this->htmlspecial_array($value); }
        }
    }

    function prepareElementParameters($plugin) {
        $params = array(
            'bootstrap_class' => 'input-xlarge',
            'show_in_rss_feed' => 0,
            'show_label_in_rss_feed' => 0,
            'use_as_rss_enclosure' => 0,
            'rollover' => '',
            'validations' => array(),
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

        if ($plugin == 'field') {
            $params['placeholder'] = '';
            $params['password'] = 0;
            $params['maxlength'] = 255;
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
            $params['text_input_mask'] = '';
            $params['text_input_mask_autoclear'] = 0;
        } elseif ($plugin == 'textarea') {
            $params['textarea_placeholder'] = '';
            $params['width'] = 60;
            $params['height'] = 6;
            $params['use_wysiwyg'] = 0;
            $params['maxlength'] = 255;
            $params['wysiwyg_extra_buttons'] = 1;
            $params['textarea_field_type'] = 'TEXT';
            $params['textarea-showmax'] = 0;
            $params['textarea_limit_type'] = 'char';
            $params['textarea-tagify'] = 0;
            $params['textarea_tagifyurl'] = '';
            $params['textarea-truncate-where'] = 0;
            $params['textarea-truncate-html'] = 0;
            $params['textarea-truncate'] = 0;
            $params['textarea-hover'] = 1;
            $params['textarea_hover_location'] = 'top';
        } elseif ($plugin === 'dropdown') {
            $params['multiple'] = 0;
            $params['dropdown_multisize'] = 3;
            $params['allow_frontend_addtodropdown'] = 0;
            $params['dd-allowadd-onlylabel'] = 0;
            $params['dd-savenewadditions'] = 0;
            $params['options_split_str'] = '';
            $params['dropdown_populate'] = '';
        } elseif ($plugin === 'checkbox') {
            $params['ck_options_per_row'] = 3;
            $params['allow_frontend_addtocheckbox'] = 0;
            $params['chk-allowadd-onlylabel'] = 0;
            $params['chk-savenewadditions'] = 0;
            $params['options_split_str'] = '';
            $params['dropdown_populate'] = '';
        } elseif ($plugin === 'radiobutton') {
            $params['options_per_row'] = 3;
            $params['btnGroup'] = 0;
            $params['rad-allowadd-onlylabel'] = 0;
            $params['rad-savenewadditions'] = 0;
            $params['options_split_str'] = '';
            $params['dropdown_populate'] = '';
        } elseif ($plugin === 'birthday') {
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
            unset($params['bootstrap_class']);
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

        $params['join_conn_id'] = 1;
        $params['database_join_where_sql'] = '';
        $params['database_join_where_access'] = 1;
        $params['database_join_where_when'] = 3;
        $params['databasejoin_where_ajax'] = 0;
        $params['database_join_filter_where_sql'] = '';
        $params['database_join_show_please_select'] = 1;
        $params['database_join_noselectionvalue'] = '';
        $params['database_join_noselectionlabel'] = '';
        $params['databasejoin_popupform'] = 41;
        $params['fabrikdatabasejoin_frontend_add'] = 0;
        $params['join_popupwidth'] = '';
        $params['databasejoin_readonly_link'] = 0;
        $params['fabrikdatabasejoin_frontend_select'] = 0;
        $params['dbjoin_options_per_row'] = 4;
        $params['dbjoin_multiselect_max'] = 0;
        $params['dbjoin_multilist_size'] = 6;
        $params['dbjoin_autocomplete_size'] = 20;
        $params['dbjoin_autocomplete_rows'] = 10;
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

    function removeEmptyLinesFr() {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);

        $Content_Folder_FR = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $Content_Folder_FR);
        file_put_contents($path_to_file_fr, $Content_Folder_FR);
    }

    function removeEmptyLinesEn() {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);

        $Content_Folder_EN = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $Content_Folder_EN);
        file_put_contents($path_to_file_en, $Content_Folder_EN);
    }

    /**
     * Get translation store in languages files instead of use JText to get the wanted language
     *
     * @param $text
     * @return string|string[]
     */
    function getTranslationFr($text) {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);

        $matches_fr = [];

        $textWithoutTags = str_replace('\'', '', strip_tags($text));
        $textTofind = $textWithoutTags . "=";
        $textTofind = "/^" . $textTofind . ".*/mi";

        // FR
        preg_match_all($textTofind, $Content_Folder_FR, $matches_fr, PREG_SET_ORDER, 0);
        return str_replace("\"",'',explode('=',$matches_fr[0][0])[1]);
    }

    /**
     * Get translation store in languages files instead of use JText to get the wanted language
     *
     * @param $text
     * @return string|string[]
     */
    function getTranslationEn($text) {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);

        $matches_en = [];

        $textWithoutTags = str_replace('\'', '', strip_tags($text));
        $textTofind = $textWithoutTags . "=";
        $textTofind = "/^" . $textTofind . ".*/mi";

        // EN
        preg_match_all($textTofind, $Content_Folder_EN, $matches_en, PREG_SET_ORDER, 0);
        return str_replace("\"",'',explode('=',$matches_en[0][0])[1]);
        //
    }

    function duplicateTranslation($text,$fr_content,$en_content,$fr_pathfile,$en_pathfile,$newtag) {
        $matches_fr = [];
        $matches_en = [];
        $textWithoutTags = str_replace('\'', '', strip_tags($text));
        $textTofind = $textWithoutTags . "=";
        $textTofind = "/^" . $textTofind . ".*/mi";
        // FR
        preg_match_all($textTofind, $fr_content, $matches_fr, PREG_SET_ORDER, 0);
        $ContentToAdd = str_replace($textWithoutTags, $newtag, $matches_fr[0][0]);
        file_put_contents($fr_pathfile, $ContentToAdd . PHP_EOL, FILE_APPEND | LOCK_EX);
        //

        // EN
        preg_match_all($textTofind, $en_content, $matches_en, PREG_SET_ORDER, 0);
        $ContentToAdd = str_replace($textWithoutTags, $newtag, $matches_en[0][0]);
        file_put_contents($en_pathfile, $ContentToAdd . PHP_EOL, FILE_APPEND | LOCK_EX);
        //
    }

    function updateTranslation($oldtext,$fr_content,$en_content,$fr_pathfile,$en_pathfile,$newtext) {
        $matches_fr = [];
        $matches_en = [];

        $textWithoutTags = str_replace('\'', '', strip_tags($oldtext));

        $replacetextfr = $textWithoutTags . '=' . '"' . str_replace("\"",'',$newtext['fr']) . '"';
        $replacetexten = $textWithoutTags . '=' . '"' . str_replace("\"",'',$newtext['en']) . '"';

        $textTofind = $textWithoutTags . "=";
        $textTofind = "/^" . $textTofind . ".*/mi";

        // FR
        preg_match_all($textTofind, $fr_content, $matches_fr, PREG_SET_ORDER, 0);
        $ContentToAdd = str_replace($matches_fr[0][0], $replacetextfr, $fr_content);
        file_put_contents($fr_pathfile, $ContentToAdd . PHP_EOL);
        //

        // EN
        preg_match_all($textTofind, $en_content, $matches_en, PREG_SET_ORDER, 0);
        $ContentToAdd = str_replace($matches_en[0][0], $replacetexten, $en_content);
        file_put_contents($en_pathfile, $ContentToAdd . PHP_EOL);
        //
    }

    function deleteTranslation($text) {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);

        $textWithoutTags = str_replace('\'', '', strip_tags($text));

        $textTofind = $textWithoutTags . "=";
        $textTofind = "/^". $textTofind .".*/mi";

        // FR
        preg_match_all($textTofind, $Content_Folder_FR, $matches_fr, PREG_SET_ORDER, 0);
        $newContent = str_replace($matches_fr[0][0],'',$Content_Folder_FR);
        file_put_contents($path_to_file_fr, $newContent . PHP_EOL);
        //

        // EN
        preg_match_all($textTofind, $Content_Folder_EN, $matches_en, PREG_SET_ORDER, 0);
        $newContent = str_replace($matches_en[0][0],'',$Content_Folder_EN);
        file_put_contents($path_to_file_en, $newContent . PHP_EOL);
        //
    }

    function addTransationFr($text) {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;

        $lines = file($path_to_file_fr);
        $last_line = $lines[count($lines)-1];

        if (strpos($last_line,'COM_USERS_RESET_REQUEST_LABEL') === 0) {
            file_put_contents($path_to_file_fr,"\r\n" . $text . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents($path_to_file_fr,$text . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        $this->removeEmptyLinesFr();
    }

    function addTransationEn($text) {
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;

        $lines = file($path_to_file_en);
        $last_line = $lines[count($lines)-1];

        if (strpos($last_line,'INSTITUTION_NAME') === 0) {
            file_put_contents($path_to_file_en,"\r\n" . $text . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents($path_to_file_en,$text . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        $this->removeEmptyLinesEn();
    }

    /**
     * Update translation of a menu label
     *
     * @param $labelTofind
     * @param $locallang
     * @param $NewSubLabel
     */
    function formsTrad($labelTofind, $NewSubLabel) {
        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);
        //

        $this->updateTranslation($labelTofind,$Content_Folder_FR,$Content_Folder_EN,$path_to_file_fr,$path_to_file_en,$NewSubLabel);
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
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,168,170];

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
        $db->setQuery($query);
        $profile = $db->loadObject();
        $menutype = $profile->menutype;
        $profileid= $profile->id;

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

        try {
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_forms'));

            $query->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid));
            $query->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $formid . '</p>'));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        // Add translation to translation files
        $this->addTransationFr('FORM_' . $profileid. '_' . $formid . '=' . "\"" . $label['fr'] . "\"");
        $this->addTransationFr('FORM_' . $profileid. '_INTRO_' . $formid . '=' . "\"" . $intro['fr'] . "\"");
        $this->addTransationEn('FORM_' . $profileid. '_' . $formid . '=' . "\"" . $label['en'] . "\"");
        $this->addTransationEn('FORM_' . $profileid. '_INTRO_' . $formid . '=' . "\"" . $intro['en'] . "\"");
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
        try {
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
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
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
            ->andWhere($db->quoteName('path') . ' != ' . $db->quote('envoi-du-dossier-' . $profile->id));

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


        $columns = array(
            'menutype',
            'title',
            'alias',
            'note',
            'path',
            'link',
            'type',
            'published',
            'parent_id',
            'level',
            'component_id',
            'checked_out',
            'checked_out_time',
            'browserNav',
            'access',
            'img',
            'template_style_id',
            'params',
            'lft',
            'rgt',
            'home',
            'language',
            'client_id',
        );

        $values = array(
            $menutype,
            'FORM_' . $prid . '_' . $formid,
            'form-' . $formid . '-' . str_replace($this->getSpecialCharacters(),'-',strtolower($label['fr'])),
            '',
            $menu_parent->path . '/' . str_replace($this->getSpecialCharacters(),'-',strtolower($label['fr'])),
            'index.php?option=com_fabrik&view=form&formid=' . $formid,
            'component',
            1,
            $menu_parent->id,
            2,
            10041,
            0,
            date('Y-m-d H:i:s'),
            0,
            1,
            '',
            22,
            '{"rowid":"","usekey":"","random":"0","fabriklayout":"","extra_query_string":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"applicant-form","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',
            array_values($lfts)[strval(sizeof($lfts)-1)] + 2,
            array_values($rgts)[strval(sizeof($rgts)-1)] + 2,
            0,
            '*',
            0
        );

        $query->clear()
            ->insert($db->quoteName('#__menu'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $db->quote($values)));

        try {
            $db->setQuery($query);
            $db->execute();
            $newmenuid = $db->insertid();

            // Insert translation into falang for modules
            $falang->insertFalang($label['fr'], $label['en'], $newmenuid, 'menu', 'title');
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
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
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
        //

        // Save as template
        if ($template == 'true') {
            $query->clear()
                ->insert($db->quoteName('#__emundus_template_form'))
                ->set($db->quoteName('form_id') . ' = ' . $db->quote($formid))
                ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $profileid. '_' . $formid))
                ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')));
            $db->setQuery($query);
            $db->execute();
        }
        //

        return array(
            'id' => $formid,
            'link' => 'index.php?option=com_fabrik&view=form&formid=' . $formid,
            'rgt' => array_values($rgts)[strval(sizeof($rgts)-1)] + 2,
        );
    }

    function deleteMenu($menu) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,103,104,168,170];

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_formgroup'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu));
        $db->setQuery($query);
        $groups = $db->loadObjectList();

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_lists'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu));
        $db->setQuery($query);
        $fabrik_list = $db->loadObject();
        $dbtable = $fabrik_list->db_table_name;
        $label = $fabrik_list->label;
        $intro = $fabrik_list->introduction;

        $query = "ALTER TABLE " . $dbtable . " DROP CONSTRAINT " . $dbtable . "_ibfk_1";
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $query = "ALTER TABLE " . $dbtable . " DROP CONSTRAINT " . $dbtable . "_ibfk_2";
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        foreach (array_values($groups) as $group) {
            $this->deleteGroup($group->group_id);
        }

        $this->deleteTranslation($label);
        $this->deleteTranslation($intro);

        $query = "DROP TABLE " . $dbtable;
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $query = $db->getQuery(true);

        $query->clear()
            ->delete($db->quoteName('#__fabrik_lists'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $menu));
        $db->setQuery($query);
        $jos_menu = $db->loadObject();

        $falang->deleteFalang($jos_menu->id,'menu','title');

        foreach ($modules as $module) {
            $query->clear()
                ->delete($db->quoteName('#__modules_menu'))
                ->where($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($jos_menu->id));
            try {
                $db->setQuery($query);
                $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            }
        }

        $query->clear()
            ->delete($db->quoteName('#__menu'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($jos_menu->id));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $query->clear()
            ->delete($db->quoteName('#__fabrik_forms'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($menu));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
    }

    function saveAsTemplate($menu,$template) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_template_form'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($menu['id']));
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
    }

    function createHiddenGroup($formid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $label = array(
            'fr' => 'Hidden group',
            'en' => 'Hidden group',
        );

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId(287);
        $groups	= $form->getGroups();

        $elementstoduplicate = [6473,6489,6490,6491];

        $hiddengroup = $this->createGroup($label, $formid, -1);

        foreach ($groups as $group) {
            $properties = $group->getGroupProperties($group->getFormModel());
            $elements = $group->getMyElements();

            if ($properties->id == 683) {
                foreach ($elements as $element) {
                    if (in_array($element->element->id,$elementstoduplicate)) {
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
        }
    }

    function createGroup($label, $fid, $repeat_group_show_first = 1) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

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

        $this->addTransationFr($tag . '=' . "\"" . $label['fr'] . "\"");
        $this->addTransationEn($tag . '=' . "\"" . $label['en'] . "\"");

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

        $order = array_values($orderings)[strval(sizeof($orderings)-1)] + 1;

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

        return array(
            'elements' => array(),
            'group_id' => $groupid,
            'group_tag' => $tag,
            'group_showLegend' => $this->getJTEXT("GROUP_" . $fid . "_" . $groupid),
            'label_fr' => $this->getTranslationFr("GROUP_" . $fid . "_" . $groupid),
            'label_en' => $this->getTranslationEn("GROUP_" . $fid . "_" . $groupid),
            'ordering' => $order,
            'formid' => $fid
        );
        //
    }

    function deleteGroup($group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_elements'))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($group));
        $db->setQuery($query);
        $elements = $db->loadObjectList();

        foreach (array_values($elements) as $element) {
            $this->deleteElement($element->id);
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($group));
        $db->setQuery($query);
        $fabrik_group = $db->loadObject();
        $label = $fabrik_group->label;

        $this->deleteTranslation($label);

        $query->clear()
            ->delete($db->quoteName('#__fabrik_formgroup'))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($group));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $query->clear()
            ->delete($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($group));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
    }

    /**
     * Create an element with default values
     *
     * @param $gid
     * @param $plugin
     * @param int $evaluation
     * @return mixed
     */
    function createSimpleElement($gid,$plugin,$evaluation = 0) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Default parameters
        $dbtype = 'VARCHAR(255)';
        $dbnull = 'NULL';
        //

        if ($plugin === 'birthday') {
            $dbtype = 'DATE';
        } elseif ($plugin === 'textarea') {
            $dbtype = 'TEXT';
        }

        // Prepare parameters
        $params = $this->prepareElementParameters($plugin);
        //

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

        $query->clear()
            ->insert($db->quoteName('#__fabrik_elements'))
            ->set($db->quoteName('name') . ' = ' . $db->quote('element'))
            ->set($db->quoteName('group_id') . ' = ' . $db->quote($gid))
            ->set($db->quoteName('plugin') . ' = ' . $db->quote($plugin))
            ->set($db->quoteName('label') . ' = ' . $db->quote(strtoupper('element_' . $gid)))
            ->set($db->quoteName('checked_out') . ' = 0')
            ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('created_by') . ' = 95')
            ->set($db->quoteName('created_by_alias') . ' = ' . $db->quote('coordinator'))
            ->set($db->quoteName('modified') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('modified_by') . ' = 95')
            ->set($db->quoteName('width') . ' = 0')
            ->set($db->quoteName('default') . ' = ' . $db->quote(''))
            ->set($db->quoteName('hidden') . ' = 0')
            ->set($db->quoteName('eval') . ' = 0')
            ->set($db->quoteName('ordering') . ' = ' . $db->quote(array_values($orderings)[strval(sizeof($orderings)-1)] + 1))
            ->set($db->quoteName('parent_id') . ' = 0')
            ->set($db->quoteName('published') . ' = 1')
            ->set($db->quoteName('access') . ' = 1')
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
        $db->setQuery($query);
        $db->execute();
        $elementId = $db->insertid();

        $this->addTransationFr('ELEMENT_' . $gid . '_' . $elementId . '=' . "\"" . 'Element sans titre' . "\"");
        $this->addTransationEn('ELEMENT_' . $gid . '_' . $elementId . '=' . "\"" . 'Unnamed item' . "\"");

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
            $name = 'criteria_' . $formid . '_' . $elementId;
        } else {
            $query = "ALTER TABLE " . $dbtable . " ADD e_" . $formid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
            $db->setQuery($query);
            $db->execute();
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
            $contentToAdd = strtoupper('sublabel_' . $gid . '_' . $elementId . '_0') . '=Option 1';
            $this->addTransationFr($contentToAdd);
            $this->addTransationEn($contentToAdd);

            $params['sub_options'] = array(
                'sub_values' => $sub_values,
                'sub_labels' =>  $sub_labels
            );

            $query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
        }
        //

        $query->set($db->quoteName('name') . ' = ' . $db->quote($name))
            ->where($db->quoteName('id') . '= ' . $db->quote($elementId));
        $db->setQuery($query);
        $db->execute();
        return $elementId;
    }

    /**
     * Update orders of a group's elements
     *
     * @param $elements
     * @param $group_id
     * @param $user
     * @return array|string
     */
    function updateOrder($elements, $group_id, $user) {
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
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        }

        return $results;
    }

    function ChangeRequire($element, $user) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();
        $eval = 0;

        if ($element['FRequire'] === 'true') {
            $element['params']['notempty-message'] = array("");
            $element['params']['notempty-validation_condition'] = array("");
            $element['params']['validations']= array("plugin"=>"notempty","plugin_published"=>"1","validate_in"=>"both","validation_on"=>"both","validate_hidden"=>"0","must_validate"=>"0","show_icon"=>"1");
            $eval = 1;
        } else {
            unset($element['params']['notempty-message']);
            unset($element['params']['notempty-validation_condition']);
            $element['params']['validations']= array();
        }

        foreach ($element['params'] as $key => $value) {
            if (!is_array($element['params'][$key])) {
                $element['params'][$key] = htmlspecialchars($element['params'][$key]);
            }
        }

        $fields = array(
            $db->quoteName('eval'). ' = '.  $db->quote($eval),
            $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            $db->quoteName('modified_by'). ' = '. $db->quote($user),
            $db->quoteName('modified'). ' = '. $db->quote($date),
        );
        $query->update($db->quoteName('#__fabrik_elements'))
            ->set($fields)
            ->where($db->quoteName('id'). '  ='. $element['id']);

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }

    }


    function UpdateParams($element, $user) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }
        $date = new Date();

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Update column type
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
        //

        // Filter by plugin
        if ($element['plugin'] === 'checkbox' || $element['plugin'] === 'radiobutton' || $element['plugin'] === 'dropdown') {
            $old_params = json_decode($db_element->params, true);

            if(isset($element['params']['join_db_name'])){
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

                foreach ($element['params']['sub_options']['sub_values'] as $index => $sub_value) {
                    if ($old_params['sub_options']) {
                        $new_label = array(
                            'fr' => $sub_value,
                            'en' => $sub_value,
                        );
                        if ($old_params['sub_options']['sub_labels'][$index]) {
                            $this->formsTrad($old_params['sub_options']['sub_labels'][$index], $new_label);
                            $sub_labels[] = $old_params['sub_options']['sub_labels'][$index];
                        } else {
                            $contentToAdd = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index . '=' . "\"" . $sub_value . "\"";
                            $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                            $this->addTransationFr($contentToAdd);
                            $this->addTransationEn($contentToAdd);
                            $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                        }
                        $sub_values[] = $element['params']['sub_options']['sub_values'][$index];
                    } else {
                        $contentToAdd = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index . '=' . "\"" . $sub_value . "\"";
                        $this->addTransationFr($contentToAdd);
                        $this->addTransationEn($contentToAdd);
                        $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                        $sub_values[] = $element['params']['sub_options']['sub_values'][$index];
                    }
                }

                $element['params']['sub_options'] = array(
                    'sub_values' => $sub_values,
                    'sub_labels' => $sub_labels
                );
            }

            $query = "ALTER TABLE " . $db_element->dbtable .
                " MODIFY COLUMN " . $db_element->name . " VARCHAR(255) NOT NULL";
            $db->setQuery($query);
            $db->execute();
        } elseif ($element['plugin'] === 'birthday') {
            $element['params']['birthday_yearstart'] = 1950;
            $dbtype = 'DATE';

            foreach ($element['params']['sub_options']['sub_labels'] as $index=>$sub_label) {
                $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
            }

            unset($element['params']['sub_options']);

            $query = "ALTER TABLE " . $db_element->dbtable .
                " MODIFY COLUMN " . $db_element->name . " " . $dbtype . " NOT NULL";
            $db->setQuery($query);
            $db->execute();
        } elseif ($element['plugin'] === 'field'){
            if ($element['params']['password'] != 6) {
                $dbtype = 'VARCHAR(' . $element['params']['maxlength'] . ')';
            } else {
                $dbtype = 'INT(' . $element['params']['maxlength'] . ')';
            }

            if ($element['params']['sub_options']) {
                foreach ($element['params']['sub_options']['sub_labels'] as $index=>$sub_label) {
                    $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                }
            }

            unset($element['params']['sub_options']);
            unset($element['params']['birthday_yearstart']);

            $query = "ALTER TABLE " . $db_element->dbtable .
                " MODIFY COLUMN " . $db_element->name . " " . $dbtype . " NOT NULL";
            $db->setQuery($query);
            $db->execute();
        } elseif ($element['plugin'] === 'textarea') {
            $element['params']['width'] = 60;
            $dbtype = 'TEXT';

            if ($element['params']['sub_options']) {
                foreach ($element['params']['sub_options']['sub_labels'] as $index=>$sub_label) {
                    $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                }
            }

            unset($element['params']['sub_options']);
            unset($element['params']['birthday_yearstart']);

            $query = "ALTER TABLE " . $db_element->dbtable .
                " MODIFY COLUMN " . $db_element->name . " " . $dbtype . " NOT NULL";
            $db->setQuery($query);
            $db->execute();
        }

        // Update the element
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('plugin'). ' = '.  $db->quote($element['plugin']),
            $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            $db->quoteName('modified_by'). ' = '. $db->quote($user),
            $db->quoteName('modified'). ' = '. $db->quote($date),
        );
        $query->update($db->quoteName('#__fabrik_elements'))
            ->set($fields)
            ->where($db->quoteName('id'). ' = '. $db->quote($element['id']));
        //
        try {
            $db->setQuery($query);
            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }

    function SubLabelsxValues($element, $locallang, $NewSubLabel, $user) {

        error_reporting(0);
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file .= $locallang . '.override.ini' ;
        $Content_Folder = file_get_contents($path_to_file);


        if (array_key_exists('sub_options.sub_labels', $element['params']) && count($NewSubLabel) < count($element['params']['sub_options']['sub_labels'])) {
            $dif = count($element['params']['sub_options']['sub_labels']) - count($NewSubLabel);
            for ($d = 0; $d < $dif; $d++) {
                array_pop($element['params']['sub_options']['sub_labels']);
                array_pop($element['params']['sub_options']['sub_values']);
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            foreach ($element['params'] as $key => $value) {
                $element['params'][$key] = htmlspecialchars($element['params'][$key]);
            }

            $fields = array(
                $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            );
            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($fields)
                ->where($db->quoteName('id'). '  ='. $element['id']);
            try {
                $db->setQuery($query);
                $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        }

        for ($i = 0; $i < count($NewSubLabel); $i++) {
            if (array_key_exists('sub_options.sub_labels', $element['params'])) {
                $labelTofind= $element['params']['sub_options']['sub_labels'][$i] . '=';
            } else {
                $labelTofind = "undefinied";
            }
            $trad = $NewSubLabel[$i];
            $re1 = '/["]+/';
            preg_match_all($re1, $trad, $matches1, PREG_SET_ORDER, 0);
            for ($tr = 0; $tr<count($matches1);$tr++) {
                $trad = str_replace($matches1[$tr], "''", $trad);
            }
            $re = '/[\x00-\x1F\x7F-\xFF\W+]/    ';
            preg_match_all($re, $NewSubLabel[$i], $matches, PREG_SET_ORDER, 0);
            for ($m = 0; $m < count($matches);$m++) {
                $NewSubLabel[$i] = str_replace($matches[$m], "", $NewSubLabel[$i]);
            }
            $NewSubLabel[$i] = strtoupper($NewSubLabel[$i]);

            if (strpos($Content_Folder,$labelTofind) === false || $labelTofind === "=") {


                $sublabel = 'SL_' . $NewSubLabel[$i] . $element['id'] .$i;
                $element['params']['sub_options']['sub_labels'][$i] = $sublabel;
                $element['params']['sub_options']['sub_values'][$i] = $sublabel;


                if (strpos($labelTofind,$sublabel) !== false) {

                    $labelToset= "\n".$sublabel. "=\"" . $trad . "\"";
                    file_put_contents($path_to_file, $labelToset , FILE_APPEND);
                } else {

                    $labelToset= "\n".$sublabel . "=\"" .$trad."\"" ;
                    file_put_contents($path_to_file, $labelToset , FILE_APPEND);

                    $db = $this->getDbo();
                    $query = $db->getQuery(true);

                    $this->htmlspecial_array($element['params']);


                    $fields = array(
                        $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
                    );
                    $query->update($db->quoteName('#__fabrik_elements'))
                        ->set($fields)
                        ->where($db->quoteName('id'). '  ='. $element['id']);
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch(Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                        return $e->getMessage();
                    }
                }
            } else {
                $labelToset= $labelTofind . "\"" .$trad."\"" ;
                $labelTofind = "/^".$labelTofind.".*/mi";
                preg_match_all($labelTofind, $Content_Folder, $matches, PREG_SET_ORDER, 0);
                $Content_Folder = str_replace($matches[0], $labelToset,$Content_Folder);
                file_put_contents($path_to_file, $Content_Folder);
            }

        }
        return $element['params'];

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

        ${"element".$element} = new stdClass();

        foreach ($elements as $group_elt) {
            if ($group_elt->element->id == $element) {
                $o_element = $group_elt->element;
                $el_params = json_decode($o_element->params);
                $content_element = $group_elt->preRender('0','1','bootstrap');

                $labelsAbove = $content_element->labels;

                ${"element".$o_element->id}->id = $o_element->id;
                ${"element".$o_element->id}->group_id = $gid;
                ${"element".$o_element->id}->hidden = $content_element->hidden;
                ${"element".$o_element->id}->labelsAbove=$labelsAbove;
                ${"element".$o_element->id}->plugin=$o_element->plugin;
                if (empty($el_params->validations)) {
                    $FRequire = false;
                } else {
                    $FRequire = true;
                }

                if ($el_params->sub_options) {
                    foreach ($el_params->sub_options->sub_labels as $key => $sub_label) {
                        $el_params->sub_options->sub_labels[$key] = $this->getTranslationFr($sub_label);
                    }
                }

                ${"element".$o_element->id}->FRequire=$FRequire;
                ${"element".$o_element->id}->params=$el_params;
                ${"element".$o_element->id}->label_tag='ELEMENT_' . $gid . '_' . $o_element->id;
                ${"element".$o_element->id}->label_fr = $this->getTranslationFr(${"element".$o_element->id}->label_tag);
                ${"element".$o_element->id}->label_en = $this->getTranslationEn(${"element".$o_element->id}->label_tag);
                ${"element".$o_element->id}->labelToFind=$group_elt->label;
                ${"element".$o_element->id}->publish=$group_elt->isPublished();


                if ($labelsAbove == 2) {
                    if ($el_params->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    if ($content_element->element) :
                        ${"element".$o_element->id}->element=$content_element->element;
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
                } else {
                    ${"element".$o_element->id}->label=$content_element->label;

                    if ($el_params->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    if ($content_element->element) :
                        ${"element".$o_element->id}->element=$content_element->element;
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

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__fabrik_elements'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($elt));
        $db->setQuery($query);
        $fabrik_element = $db->loadObject();
        $gid = $fabrik_element->group_id;
        $label = $fabrik_element->label;
        $name = $fabrik_element->name;
        $params = json_decode($fabrik_element->params, true);
        if ($params['sub_options']) {
            $sub_labels = json_decode($fabrik_element->params, true)['sub_options']['sub_labels'];
            foreach ($sub_labels as $sub_label) {
                $this->deleteTranslation($sub_label);
            }
        }

        $this->deleteTranslation($label);

        $query->clear()
            ->select([
                'fl.db_table_name AS dbtable',
                'fl.form_id AS formid',
            ])
            ->from($db->quoteName('#__fabrik_formgroup', 'fg'))
            ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
            ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);
        $dbtable = $db->loadObject()->dbtable;

        $query = "ALTER TABLE " . $dbtable . " DROP COLUMN " . $name;

        try {
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->clear()
                ->delete($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($elt));

            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
    }

    function reorderMenu($link, $rgt) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->update($db->quoteName('#__menu'))
            ->set('rgt = ' . $db->quote($rgt))
            ->set('lft = ' . $db->quote($rgt-1))
            ->where('link = ' . $db->quote($link));
        $db->setQuery($query);
        return $db->execute();
    }

    function getGroupOrdering($gid,$fid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('ordering')
            ->from($db->quoteName('#__fabrik_formgroup'))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
            ->andWhere($db->quoteName('form_id') . ' = ' . $db->quote($fid));

        $db->setQuery($query);
        return $db->loadResult();
    }

    function reorderGroup($gid, $fid, $order) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->update($db->quoteName('#__fabrik_formgroup'))
            ->set('ordering = ' . $db->quote($order))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
            ->andWhere($db->quoteName('form_id') . ' = ' . $db->quote($fid));

        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Get menus templates
     *
     * @return array|mixed|void
     */
    function getPagesModel() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_template_form'))
            ->order('form_id');

        try {
            $db->setQuery($query);
            $models = $db->loadObjectList();

            foreach ($models as $model) {
                $model->label = array(
                    'fr' => $this->getTranslationFr($model->label),
                    'en' => $this->getTranslationEn($model->label)
                );
                $model->intro = array(
                    'fr' => $this->getTranslationFr($model->intro),
                    'en' => $this->getTranslationEn($model->intro)
                );
            }

            return $models;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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
        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId(intval($formid));
        $groups	= $form->getGroups();
        //

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,168,170];
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

            // Update translation files
            $query->clear();
            $query->update($db->quoteName('#__fabrik_forms'));

            $this->addTransationFr('FORM_' . $prid. '_' . $newformid . '=' . "\"" . $label['fr'] . "\"");
            $this->addTransationEn('FORM_' . $prid. '_' . $newformid . '=' . "\"" . $label['en'] . "\"");
            $this->addTransationFr('FORM_' . $prid . '_INTRO_' . $newformid . '=' . "\"" . $intro['fr'] . "\"");
            $this->addTransationEn('FORM_' . $prid . '_INTRO_' . $newformid . '=' . "\"" . $intro['en'] . "\"");
            //

            $query->set('label = ' . $db->quote('FORM_' . $prid . '_' . $newformid));
            $query->set('intro = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $newformid . '</p>'));
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
            $table_query = "CREATE TABLE jos_emundus_" . $prid . "_" . $increment . " LIKE " . $list_model->db_table_name;
            $db->setQuery($table_query);
            $db->execute();
            //

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id' && $key != 'db_table_name' && $key != 'db_primary_key' && $key != 'access') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($newformid));
                } elseif ($key == 'db_table_name') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment));
                } elseif ($key == 'db_primary_key') {
                    $query->set($key . ' = ' . $db->quote('jos_emundus_' . $prid . '_' . $increment . '.id'));
                } elseif ($key == 'access') {
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

                // Update translation files
                $query->clear();
                $query->update($db->quoteName('#__fabrik_groups'));

                $this->duplicateTranslation($group_model->label, $Content_Folder_FR, $Content_Folder_EN, $path_to_file_fr, $path_to_file_en, 'GROUP_' . $newformid . '_' . $newgroupid);
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
                                $this->duplicateTranslation($sub_label, $Content_Folder_FR, $Content_Folder_EN, $path_to_file_fr, $path_to_file_en, 'SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index);
                                $sub_labels[] = 'SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index;
                            }
                            $el_params->sub_options->sub_labels = $sub_labels;
                        }
                        $query->clear();
                        $query->update($db->quoteName('#__fabrik_elements'));
                        $this->duplicateTranslation($element->element->label, $Content_Folder_FR, $Content_Folder_EN, $path_to_file_fr, $path_to_file_en, 'ELEMENT_' . $newgroupid . '_' . $newelementid);
                        //

                        $query->set('label = ' . $db->quote('ELEMENT_' . $newgroupid . '_' . $newelementid));
                        $query->set('published = 1');
                        $query->set('params = ' . $db->quote(json_encode($el_params)));
                        $query->where('id =' . $newelementid);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    }
                }
            }
            //

            // Duplicate the form-menu
            $query->clear()
                ->select('*')
                ->from('#__menu')
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($profile->menutype))
                ->andWhere($db->quoteName('path') . ' != ' . $db->quote('envoi-du-dossier-' . $profile->id))
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
                if ($key != 'id' && $key != 'menutype' && $key != 'alias' && $key != 'path' && $key != 'link' && $key != 'parent_id' && $key != 'lft' && $key != 'rgt') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'menutype') {
                    $query->set($key . ' = ' . $db->quote($profile->menutype));
                } elseif ($key == 'alias') {
                    $query->set($key . ' = ' . $db->quote('form-' . $newformid . '-' . $val));
                } elseif ($key == 'path') {
                    if($formid == 258){
                        $query->set($key . ' = ' . $db->quote('envoi-du-dossier-' . $profile->id));
                    } else {
                        if(strpos($val,'/')){
                            $newpath = explode('/', $val)[1];
                        } else {
                            $newpath = $val;
                        }
                        $query->set($key . ' = ' . $db->quote($menu_parent->path . '/' . $newpath));
                    }
                } elseif ($key == 'link') {
                    $query->set($key . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $newformid));
                } elseif ($key == 'parent_id') {
                    if($formid == 258){
                        $query->set($key . ' = ' . $db->quote(1));
                    } else {
                        $query->set($key . ' = ' . $db->quote($menu_parent->id));
                    }
                } elseif ($key == 'lft') {
                    if($formid == 258){
                        $query->set($key . ' = ' . $db->quote(103));
                    } else {
                        $query->set($key . ' = ' . $db->quote(array_values($lfts)[strval(sizeof($lfts) - 1)] + 2));
                    }
                } elseif ($key == 'rgt') {
                    if($formid == 258){
                        $query->set($key . ' = ' . $db->quote(104));
                    } else {
                        $query->set($key . ' = ' . $db->quote(array_values($rgts)[strval(sizeof($rgts) - 1)] + 2));
                    }
                }
            }
            $db->setQuery($query);
            $db->execute();
            $newmenuid = $db->insertid();

            // Add translation for modules
            $falang->insertFalang($label['fr'],$label['en'],$newmenuid,'menu','title');
            //

            // Affect modules to this menu
            foreach ($modules as $module) {
                $query->clear()
                    ->insert($db->quoteBinary('#__modules_menu'))
                    ->set($db->quote('moduleid') . ' = ' . $db->quote($module))
                    ->set($db->quote('menuid') . ' = ' . $db->quote($newmenuid));
                $db->setQuery($query);
                $db->execute();
            }
            //
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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

        $query->select('sg.id')
            ->from($db->quoteName('#__emundus_setup_campaigns','c'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc'). ' ON '. $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg'). ' ON '. $db->quoteName('gc.parent_id') . ' = ' . $db->quoteName('sg.id'))
            ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid))
            ->andWhere($db->quoteName('sg.description') . ' LIKE ' . $db->quote('constraint_group'));
        $db->setQuery($query);
        return $db->loadResult();
    }

    function checkVisibility($group,$cid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(gf.id)')
            ->from($db->quoteName('#__emundus_setup_campaigns','c'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc'). ' ON '. $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg'). ' ON '. $db->quoteName('gc.parent_id') . ' = ' . $db->quoteName('sg.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link', 'gf'). ' ON '. $db->quoteName('gf.parent_id') . ' = ' . $db->quoteName('sg.id'))
            ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid))
            ->andWhere($db->quoteName('sg.description') . ' LIKE ' . $db->quote('constraint_group'))
            ->andWhere($db->quoteName('gf.fabrik_group_link') . ' = ' . $db->quote($group));
        $db->setQuery($query);
        return $db->loadResult();
    }

    function publishUnpublishElement($element) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
    }

    function enableRepeatGroup($gid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);
        $group = $db->loadObject();

        $group_params = json_decode($group->params);
        $group_params->repeat_group_button = 1;

        $query->clear()
            ->update($db->quoteName('#__fabrik_groups'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($group_params)))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);

        try {
            return $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function disableRepeatGroup($gid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);
        $group = $db->loadObject();

        $group_params = json_decode($group->params);
        $group_params->repeat_group_button = 0;

        $query->clear()
            ->update($db->quoteName('#__fabrik_groups'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($group_params)))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);

        try {
            return $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

}
