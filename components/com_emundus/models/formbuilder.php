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

class EmundusModelFormbuilder extends JModelList {
    var $model_language = null;
    var $model_language_overrides = null;
    var $model_menus = null;
    var $m_translations = null;
    var $h_fabrik = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'translations.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'fabrik.php');
        $this->m_translations = new EmundusModelTranslations;
        $this->h_fabrik = new EmundusHelperFabrik;

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

    public function updateTranslation($key, $values, $reference_table = '', $reference_id = 0){
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $this->m_translations->updateTranslation($key, $values[$language->sef], $language->lang_code,'override', $reference_table, $reference_id);
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

        $fileName = constant('JPATH_SITE') . '/language/overrides/' . $code_lang . '.override.ini';
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
                $new_key = $this->updateTranslation($labelTofind, $NewSubLabel,'fabrik_elements', $element);
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
            } else {
                $new_key = $this->updateTranslation($labelTofind, $NewSubLabel);
            }
            return $new_key;
        }  catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when update the translation of ' . $labelTofind . ' : ' .$e->getMessage(), JLog::ERROR, 'com_emundus');
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
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');
        $falang = new EmundusModelFalang;
        $modules = [93,102,103,104,168,170];

        try {
            $params = $this->h_fabrik->prepareFabrikMenuParams();

            $query->clear()
                ->insert($db->quoteName('#__menu'));
            $query->set($db->quoteName('menutype') . ' = ' . $db->quote($menu['menutype']))
                ->set($db->quoteName('title') . ' = ' . $db->quote('FORM_' . $menu['profile_id'] . '_' . $menu['form_id']))
                ->set($db->quoteName('alias') . ' = ' . $db->quote(preg_replace('/\s+/', '-', strtolower($this->replaceAccents($label['fr']))) . '-form-' . $menu['form_id']))
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
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
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

            return $newmenuid;
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/formbuilder | Error when create a menu : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }

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
            JLog::add('component/com_emundus/models/formbuilder | Error update label of the element ' . $eid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error update label of the group ' . $gid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error update label of the page ' . $pid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error update label of the page intro ' . $pid . ' without translation : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createApplicantHeadingMenu($menutype,$title,$prid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->insert($db->quoteName('#__menu'));
            $query->set($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->set($db->quoteName('title') . ' = ' . $db->quote($title))
                ->set($db->quoteName('alias') . ' = ' . $db->quote(str_replace($this->getSpecialCharacters(),'-',strtolower($this->replaceAccents($title))) . '-' . $prid))
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
            JLog::add('component/com_emundus/models/formbuilder | Error when create the heading menu of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createApplicantMenu($label, $intro, $prid, $template) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if(!is_array($label)) {
            $label = json_decode($label, true);
        }
        if(!is_array($intro)) {
            $intro = json_decode($intro, true);
        }

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        try {
            $formid = $this->createFabrikForm($prid,$label,$intro);
            $list = $this->createFabrikList($prid,$formid);
            $this->joinFabrikListToProfile($list['id'],$prid);

            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
            $db->setQuery($query);
            $profile = $db->loadObject();
            $menutype = $profile->menutype;

            // INSERT MENU
            $query
                ->clear()
                ->select('*')
                ->from('#__menu')
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
            $db->setQuery($query);
            $menu_parent = $db->loadObject();

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
                'path' => $menu_parent->path . '/' . preg_replace('/\s+/', '-', strtolower($this->replaceAccents($label['fr']))) . '-form-' . $formid,
                'parent_id' => $menu_parent->id,
                'level' => 2,
                'lft' => array_values($lfts)[strval(sizeof($lfts) - 1)] + 2,
                'rgt' => array_values($rgts)[strval(sizeof($rgts) - 1)] + 2
            );
            $this->insertMenu($menu,$label);
            //

            // Create hidden group
            $label = array(
                'fr' => 'Hidden group',
                'en' => 'Hidden group',
            );
            $group = $this->createGroup($label, $formid, -1);
            $this->createElement('id',$group['group_id'],'internalid','id','',1,0,0);
            $this->createElement('time_date',$group['group_id'],'date','time date','',1, 0);
            $this->createElement('user',$group['group_id'],'user','user','',1, 0);
            $default_fnum = '$fnum = JFactory::getSession()->get(\'emundusUser\')->fnum;if (!isset($fnum)) {return JFactory::getApplication()->input->get->get(\'rowid\');}return $fnum;';
            $this->createElement('fnum',$group['group_id'],'field','fnum',$default_fnum,1,0,1,1,0,44);
            //

            // Create the first group
            $group_label = array(
                'fr' => 'Nouvelle section',
                'en' => 'New section'
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
                'db_table_name' => $list['db_table_name'],
                'label' => $label[$actualLanguage],
                'link' => 'index.php?option=com_fabrik&view=form&formid=' . $formid,
                'rgt' => array_values($rgts)[strval(sizeof($rgts) - 1)] + 2,
            );
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when create a new page in form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array();
        }
    }

    function createFabrikForm($prid,$label,$intro){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $params = $this->h_fabrik->prepareFormParams();

            $data = array(
                'label' => 'FORM_' . $prid,
                'record_in_database' => 1,
                'error' => 'FORM_ERROR',
                'intro' => '<p>' . 'FORM_' . $prid . '_INTRO</p>',
                'created' => date('Y-m-d h:i:s'),
                'created_by' => JFactory::getUser()->id,
                'created_by_alias' => JFactory::getUser()->username,
                'modified' => date('Y-m-d h:i:s'),
                'modified_by' => JFactory::getUser()->id,
                'checked_out' => JFactory::getUser()->id,
                'checked_out_time' => date('Y-m-d h:i:s'),
                'publish_up' => date('Y-m-d h:i:s'),
                'reset_button_label' => 'RESET',
                'submit_button_label' => 'SAVE_CONTINUE',
                'form_template' => 'bootstrap',
                'view_only_template' => 'bootstrap',
                'published' => 1,
                'params' => json_encode($params),
            );

            $query->insert($db->quoteName('#__fabrik_forms'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',',$db->quote(array_values($data))));
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            $query->clear()
                ->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid))
                ->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $formid . '</p>'));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $db->execute();

            // Add translation to translation files
            $this->translate('FORM_' . $prid . '_' . $formid,$label,'fabrik_forms',$formid,'label');
            $this->translate('FORM_' . $prid . '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            return $formid;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when create a form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    function createFabrikList($prid,$formid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            // Create core table
            $query->select('COUNT(*)')
                ->from($db->quoteName('information_schema.tables'))
                ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('%jos_emundus_' . $prid . '%'));
            $db->setQuery($query);
            $result = $db->loadResult();

            if ($result < 10) {
                $increment = '0' . strval($result);
            } elseif ($result > 10) {
                $increment = strval($result);
            } else {
                $increment = '01';
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
            //

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
            $params = $this->h_fabrik->prepareListParams();

            $data = array(
                'label' => 'FORM_' . $prid,
                'introduction' => '',
                'form_id' => $formid,
                'db_table_name' => 'jos_emundus_' . $prid . '_' . $increment,
                'db_primary_key' => 'jos_emundus_' . $prid . '_' . $increment . '.id',
                'auto_inc' => 1,
                'connection_id' => 1,
                'created' => date('Y-m-d h:i:s'),
                'created_by' => JFactory::getUser()->id,
                'created_by_alias' => JFactory::getUser()->username,
                'modified' => date('Y-m-d h:i:s'),
                'modified_by' => JFactory::getUser()->id,
                'checked_out' => JFactory::getUser()->id,
                'checked_out_time' => date('Y-m-d h:i:s'),
                'published' => 1,
                'publish_up' => date('Y-m-d h:i:s'),
                'access' => 7,
                'hits' => 0,
                'rows_per_page' => 10,
                'template' => 'bootstrap',
                'order_by' => '[""]',
                'order_dir' => '["ASC"]',
                'filter_action' => 'onchange',
                'group_by' => '',
                'params' => json_encode($params),
            );

            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__fabrik_lists'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',',$db->quote(array_values($data))));
            $db->setQuery($query);
            $db->execute();
            $listid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'))
                ->set('label = ' . $db->quote('FORM_' . $prid . '_' . $formid))
                ->set('access = ' . $db->quote($prid));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($listid));
            $db->setQuery($query);
            $db->execute();
            //

            return array(
                'id' => $listid,
                'db_table_name' => 'jos_emundus_' . $prid . '_' . $increment,
            );
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when create a list ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array();
        }
    }

    function joinFabrikListToProfile($listid,$prid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
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

            $query->insert($db->quoteName('#__emundus_setup_formlist'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->quote($values)));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when join list ' . $listid . ' to profile ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createSubmittionPage($label, $intro, $prid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($prid));
            $db->setQuery($query);
            $profile = $db->loadObject();

            $params = $this->h_fabrik->prepareFormParams();
            $params = $this->h_fabrik->prepareSubmittionPlugin($params);

            $data = array(
                'label' => 'FORM_' . $prid,
                'record_in_database' => 1,
                'error' => 'FORM_ERROR',
                'intro' => '<p>' . 'FORM_' . $prid . '_INTRO</p>',
                'created' => date('Y-m-d h:i:s'),
                'created_by' => JFactory::getUser()->id,
                'created_by_alias' => JFactory::getUser()->username,
                'modified' => date('Y-m-d h:i:s'),
                'modified_by' => JFactory::getUser()->id,
                'checked_out' => JFactory::getUser()->id,
                'checked_out_time' => date('Y-m-d h:i:s'),
                'publish_up' => date('Y-m-d h:i:s'),
                'reset_button_label' => 'RESET',
                'submit_button_label' => 'SUBMIT',
                'form_template' => 'bootstrap',
                'view_only_template' => 'bootstrap',
                'published' => 1,
                'params' => json_encode($params),
            );

            $query->insert($db->quoteName('#__fabrik_forms'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',',$db->quote(array_values($data))));
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            $query->clear()
                ->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $prid . '_' . $formid . '_SUBMITTING_APPLICATION'))
                ->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $prid . '_INTRO_' . $formid . '</p>'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $db->execute();

            // Add translation to translation files
            $this->translate('FORM_' . $prid . '_' . $formid . '_SUBMITTING_APPLICATION',$label,'fabrik_forms',$formid,'label');
            $this->translate('FORM_' . $prid . '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            // INSERT FABRIK LIST
            $params = $this->h_fabrik->prepareListParams();

            $data = array(
                'label' => 'FORM_' . $prid . '_' . $formid . '_SUBMITTING_APPLICATION',
                'introduction' => '',
                'form_id' => $formid,
                'db_table_name' => 'jos_emundus_declaration',
                'db_primary_key' => 'jos_emundus_declaration.id',
                'auto_inc' => 1,
                'connection_id' => 1,
                'created' => date('Y-m-d h:i:s'),
                'created_by' => JFactory::getUser()->id,
                'created_by_alias' => JFactory::getUser()->username,
                'modified' => date('Y-m-d h:i:s'),
                'modified_by' => JFactory::getUser()->id,
                'checked_out' => JFactory::getUser()->id,
                'checked_out_time' => date('Y-m-d h:i:s'),
                'published' => 1,
                'publish_up' => date('Y-m-d h:i:s'),
                'access' => 7,
                'hits' => 0,
                'rows_per_page' => 10,
                'template' => 'bootstrap',
                'order_by' => '[""]',
                'order_dir' => '["ASC"]',
                'filter_action' => 'onchange',
                'group_by' => '',
                'params' => json_encode($params),
            );


            $query->clear()
                ->insert($db->quoteName('#__fabrik_lists'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',',$db->quote(array_values($data))));
            $db->setQuery($query);
            $db->execute();
            $listid = $db->insertid();
            //

            $menu = array(
                'menutype' => $profile->menutype,
                'profile_id' => $prid,
                'form_id' => $formid,
                'path' => preg_replace('/\s+/', '-', strtolower($this->replaceAccents($label['fr']))) . '-form-' . $formid,
                'parent_id' => 1,
                'level' => 1,
                'lft' => 110,
                'rgt' => 111
            );
            $this->insertMenu($menu,$label);

            // Create hidden group
            $label = array(
                'fr' => 'Hidden group',
                'en' => 'Hidden group',
            );
            $hidden_group = $this->createGroup($label, $formid, -1);
            $this->createElement('id',$hidden_group['group_id'],'internalid','id','',1,0,0);
            $this->createElement('time_date',$hidden_group['group_id'],'date','SENT_ON','',1, 0);
            $this->createElement('user',$hidden_group['group_id'],'user','user','',1, 0);
            $default_fnum = '$fnum = JFactory::getSession()->get(\'emundusUser\')->fnum;if (!isset($fnum)) {return JFactory::getApplication()->input->get->get(\'rowid\');}return $fnum;';
            $this->createElement('fnum',$hidden_group['group_id'],'field','fnum',$default_fnum,1,0,1,1,0,44);
            //

            $group_label = array(
                'fr' => "Confirmation d'envoi de dossier",
                'en' => 'Submitting application'
            );
            $group = $this->createGroup($group_label,$formid);

            $eid = $this->createElement('declare',$group['group_id'],'checkbox','Confirmation','',0,0,0);
            $this->h_fabrik->addOption($eid,'CONFIRM_POST',1);
            $this->h_fabrik->addNotEmptyValidation($eid);
            //

            return array(
                'id' => $formid,
                'link' => 'index.php?option=com_fabrik&view=form&formid=' . $formid,
                'rgt' => 111,
            );
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/formbuilder | Error when create the submittion page of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at move to trash the menu with the fabrik_form ' . $menu . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error when save a page as a model : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /*function createHiddenGroup($formid,$eval = 0) {
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
            JLog::add('component/com_emundus/models/formbuilder | Error when create the hidden group of the fabrik_form ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }*/

    function createGroup($label, $fid, $repeat_group_show_first = 1) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if(!is_array($label)) {
            $label = json_decode($label, true);
        }

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
            $params = $this->h_fabrik->prepareGroupParams();
            $params = $this->h_fabrik->updateParam($params,'repeat_group_show_first',$repeat_group_show_first);

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
                JFactory::getUser()->id,
                JFactory::getUser()->username,
                date('Y-m-d H:i:s'),
                JFactory::getUser()->id,
                0,
                date('Y-m-d H:i:s'),
                0,
                0,
                json_encode($params)
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
                ->set($db->quoteName('name') . ' = ' . $db->quote($label['fr']))
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
            JLog::add('component/com_emundus/models/formbuilder | Error at creating a group for fabrik_form ' . $fid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error when move to trash the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createSectionSimpleElements($gid, $plugins)
    {
        $created_elements = [];
        $user = JFactory::getUser()->id;

        foreach ($plugins as $plugin) {


            switch ($plugin) {


                case 'birthday':

                    $label = array(
                        'fr' => 'Date de naissance',
                        'en' => 'Birthday',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, $plugin, null, 0, $label);
                    break;
                case 'date_debut':
                    $label = array(
                        'fr' => 'Date de début du contrat',
                        'en' => 'Contract start date',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, 'birthday', null, 0, $label);
                    break;
                case 'date_fin':
                    $label = array(
                        'fr' => 'Date de fin du contrat',
                        'en' => 'Contract end date',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, 'birthday', null, 0, $label);
                    break;

                case 'telephone':

                    $label = array(
                        'fr' => 'Téléphone',
                        'en' => 'Phone',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;
                case 'fonction':

                    $label = array(
                        'fr' => 'Fonction',
                        'en' => 'Function',
                    );


                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;

                case 'employeur':
                    $label = array(
                        'fr' => 'Employeur',
                        'en' => 'Employer',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;

                case 'ville_employeur':

                    $label = array(
                        'fr' => "Ville de l'employeur",
                        'en' => 'Employer city',
                    );


                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;
                case 'missions':

                    $label = array(
                        'fr' => 'Missions réalisées',
                        'en' => 'Missions',
                    );


                    $created_elements[] = $this->createSimpleElement($gid, 'textarea', null, 0, $label);
                    break;
                case 'adresse':
                    $label = array(
                        'fr' => 'Adresse',
                        'en' => 'Address',
                    );


                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;
                case 'code postal':
                    $label = array(
                        'fr' => 'Code postal',
                        'en' => 'postal code',
                    );
                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;
                case 'ville':
                    $label = array(
                        'fr' => 'Ville',
                        'en' => 'City',
                    );
                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;
                case 'adresseComplementaire':
                    $label = array(
                        'fr' => 'Adresse complémentaire',
                        'en' => 'Additional addresd',
                    );


                    $created_elements[] = $this->createSimpleElement($gid, 'field', null, 0, $label);
                    break;

                case 'email':

                    $label = array(
                        'fr' => 'Email',
                        'en' => 'Email',
                    );

                    $created_elements[] = $this->createSimpleElement($gid, $plugin, null, 0, $label);

                    break;
                case 'nationalite':

                    $label = array(
                        'fr' => 'Nationalité',
                        'en' => 'Nationality',
                    );

                    $el_id = $this->createSimpleElement($gid, 'databasejoin', null, 0, $label);

                    $created_elements[] = $el_id;
                    $element = json_decode(json_encode($this->getElement($el_id, $gid)), true);

                    $element['params']["join_db_name"] = "data_nationality";
                    $element['params']["join_key_column"] = "id";
                    $element['params']["join_val_column"] = "label_fr";
                    $element['params']["database_join_where_sql"] = "order by id";

                    $this->UpdateParams($element, $user);
                    break;
                case 'pays':
                    $label = array(
                        'fr' => 'Pays',
                        'en' => 'Country',
                    );

                    $el_id = $this->createSimpleElement($gid, 'databasejoin', null, 0, $label);

                    $created_elements[] = $el_id;
                    $element = json_decode(json_encode($this->getElement($el_id, $gid)), true);

                    $element['params']["join_db_name"] = "data_country";
                    $element['params']["join_key_column"] = "id";
                    $element['params']["join_val_column"] = "label_fr";
                    $element['params']["database_join_where_sql"] = "order by id";

                    $this->UpdateParams($element, $user);
                    break;

                default:

                    $created_elements[] = $this->createSimpleElement($gid, $plugin);
                    break;


            }

        }


        return ["data" => $created_elements];

    }

    function createElement($name,$group_id,$plugin,$label,$default = '',$hidden = 0,$create_column = 1,$show_in_list_summary = 1,$published = 1,$parent_id = 0,$width = 20) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            //Create element in fabrik_elements
            $params = $this->h_fabrik->prepareElementParameters($plugin,false);

            $data = array(
                'name' => $name,
                'group_id' => $group_id,
                'plugin' => $plugin,
                'label' => $label,
                'checked_out_time' => date('Y-m-d H:i:s'),
                'created' => date('Y-m-d H:i:s'),
                'created_by' => JFactory::getUser()->id,
                'created_by_alias' => JFactory::getUser()->username,
                'modified' => date('Y-m-d H:i:s'),
                'modified_by' => JFactory::getUser()->id,
                'width' => $width,
                'default' => $default,
                'hidden' => $hidden,
                'eval' => $default === '' ? 0 : 1,
                'ordering' => 1,
                'show_in_list_summary' => $show_in_list_summary,
                'filter_type' => '',
                'filter_exact_match' => 0,
                'published' => $published,
                'access' => 1,
                'parent_id' => $parent_id,
                'params' => json_encode($params),
            );

            $query->insert($db->quoteName('#__fabrik_elements'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',',$db->quote(array_values($data))));
            $db->setQuery($query);
            $db->execute();
            $eid = $db->insertid();
            //

            $this->h_fabrik->checkFabrikJoins($eid,$name,$plugin,$group_id);

            // Create columns in database
            if($create_column) {
                $db_type = $this->h_fabrik->getDBType($plugin);

                $query->clear()
                    ->select('*')
                    ->from($db->quoteName('#__fabrik_groups'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($group_id));
                $db->setQuery($query);
                $fabrik_group = $db->loadObject();
                $group_params = json_decode($fabrik_group->params);

                $query->clear()
                    ->select([
                        'fl.db_table_name AS dbtable',
                        'fl.form_id AS formid',
                    ])
                    ->from($db->quoteName('#__fabrik_formgroup', 'fg'))
                    ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('fg.form_id'))
                    ->where($db->quoteName('fg.group_id') . ' = ' . $db->quote($group_id));
                $db->setQuery($query);
                $result = $db->loadObject();

                $query = "ALTER TABLE " . $result->dbtable . " ADD " . $name . " " . $db_type . " NULL";
                $db->setQuery($query);
                $db->execute();
                if ($group_params->repeat_group_button == 1 || $fabrik_group->is_join == 1) {
                    $repeat_table_name = $result->dbtable . "_" . $group_id . "_repeat";
                    $query = "ALTER TABLE " . $repeat_table_name . " ADD " . $name . " " . $db_type . " NULL";
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            return $eid;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error when create an element in group ' . $group_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    function createSimpleElement($gid, $plugin, $attachementId = null, $evaluation = 0, $labels = null) {
        $user = JFactory::getUser();
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $dbtype = $this->h_fabrik->getDBType($plugin);
            $dbnull = 'NULL';
            $default = '';

            if ($plugin === 'display') {
                $default = 'Ajoutez du texte personnalisé pour vos candidats';
            }

            // Prepare parameters
            $params = $this->h_fabrik->prepareElementParameters($plugin);
            //

            // Prepare ordering
            $query->clear()
                ->select('ordering')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                ->order('ordering');

            $db->setQuery($query);
            $results = $db->loadColumn();
            $orderings = [];
            foreach ($results as $result) {
                if (!in_array($result, $orderings)) {
                    $orderings[] = intval($result);
                }
            }
            //

            // Create our element
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
                ->set($db->quoteName('created_by_alias') . ' = ' . $db->quote($user->username))
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
            //

            $query->clear()
                ->select(['fg.is_join,fg.params,fl.db_table_name AS dbtable'])
                ->from($db->quoteName('#__fabrik_formgroup', 'ffg'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('ffg.form_id'))
                ->leftJoin($db->quoteName('#__fabrik_groups', 'fg') . ' ON ' . $db->quoteName('fg.id') . ' = ' . $db->quoteName('ffg.group_id'))
                ->where($db->quoteName('fg.id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $formlist = $db->loadObject();
            $group_params = json_decode($formlist->params);

            // Prepare label
            if($labels != null) {
                $label = $labels;
            } else {
                $label = $this->h_fabrik->initLabel($plugin);
            }
            $this->translate('ELEMENT_' . $gid . '_' . $elementId, $label,'fabrik_elements',$elementId,'label');
            if($evaluation){
                $name = 'criteria_' . $gid . '_' . $elementId;
            } else {
                $name = 'e_' . $gid . '_' . $elementId;
            }
            //

            // Init a default subvalue for checkboxes
            if ($plugin === 'checkbox' || $plugin === 'radiobutton' || $plugin === 'dropdown') {
                $sub_values = [];
                $sub_labels = [];

                $sub_labels[] = strtoupper('sublabel_' . $gid . '_' . $elementId . '_0');
                $sub_values[] = 1;
                $labels = array(
                    'fr' => 'Option 1',
                    'en' => 'Option 1'
                );
                $this->translate(strtoupper('sublabel_' . $gid . '_' . $elementId . '_0'),$labels,'fabrik_elements',$elementId,'sub_labels');

                $params['sub_options'] = array(
                    'sub_values' => $sub_values,
                    'sub_labels' => $sub_labels
                );
            }
            //

            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('label') . ' = ' . $db->quote(strtoupper('element_' . $gid . '_' . $elementId)))
                ->set($db->quoteName('name') . ' = ' . $db->quote($name))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('id') . '= ' . $db->quote($elementId));
            $db->setQuery($query);
            $db->execute();

            // Add element to table
            if ($evaluation) {
                $query = "ALTER TABLE jos_emundus_evaluations" . " ADD criteria_" . $gid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                $db->setQuery($query);
                $db->execute();

                if ($group_params->repeat_group_button == 1 || $formlist->is_join == 1) {
                    $query = $db->getQuery(true);
                    $query->select('table_join')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                        ->and($db->quoteName('table_join_key') . '=' . $db->quote('parent_id'));
                    $db->setQuery($query);
                    $table_join_name = $db->loadObject();

                    $query = "ALTER TABLE " . $table_join_name->table_join . " ADD criteria_" . $gid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                    $db->setQuery($query);
                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add('component/com_emundus/models/formbuilder | Cannot not create new colum in the repeat table case: new element form group to an target group witc at group   because column already exist ' . $gid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
                    }
                }

            } else {
                $query = "ALTER TABLE " . $formlist->dbtable . " ADD e_" . $gid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                $db->setQuery($query);
                $db->execute();

                if ($group_params->repeat_group_button == 1 || $formlist->is_join == 1) {
                    $repeat_table_name = $formlist->dbtable . "_" . $gid . "_repeat";
                    $query = "ALTER TABLE " . $repeat_table_name . " ADD e_" . $gid . "_" . $elementId . " " . $dbtype . " " . $dbnull;
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            //

            $this->h_fabrik->addJsAction($elementId,$plugin);

            return $elementId;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Problem when create a simple element in the group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateGroupElementsOrder($elements, $group_id) {
        $db = $this->getDbo();

        $elements_ids = array();
        $case = array();
        for ($i = 0; $i < count($elements); $i++) {
            $case[] = 'when id = '.$elements[$i]['id'].' then '.$elements[$i]['order'];
            $elements_ids[] = $elements[$i]['id'];
        }

        $query = "UPDATE jos_fabrik_elements SET ordering = (case ".join(' ',$case). " end), modified = ".$db->quote(date('Y-m-d H:i:s')). ", modified_by = ".$db->quote(JFactory::getUser()->id). " WHERE id in (".join(',',$elements_ids).");";
        $db->setQuery($query);
        return $db->execute();
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

                        JLog::add('component/com_emundus/models/formbuilder | Cannot not create new colum in the repeat table case: moving element form group to an target group witch is repeat group because column already exist ' . $group_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

                    }


                }


                // Maintenant j'update enfin les ordres
                return $this->updateGroupElementsOrder($elements, $group_id);

            }
        } else {
            return $this->updateGroupElementsOrder($elements, $group_id);
        }

    }

    function updateElementOrder($group_id, $element_id, $new_index)
    {
        // get elements from group_id
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
        $groupModel->setId($group_id);
        $elements = $groupModel->getMyElements();
        $elements_order = array();

        foreach ($elements as $key => $element) {
            if ($element->element->id == $element_id) {
                $elements_order[] = [
                    'id' => $element->element->id,
                    'order' => intval($new_index),
                ];
            } else {
                $elements_order[] = [
                    'id' => $element->element->id,
                    'order' => $element->element->ordering,
                ];
            }
        }

        // sort elements by order
        usort($elements_order, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $after_element_id = false;
        foreach ($elements_order as $key => $element) {
            if ($element_id == $element['id']) {
                $after_element_id = true;
            }

            if ($after_element_id && $element['order'] == $elements_order[$key -1]['order']) {
                $elements_order[$key]['order'] = $elements_order[$key -1]['order'] + 1;
            }
        }

        return $this->updateGroupElementsOrder($elements_order, $group_id);
    }

    function ChangeRequire($element, $user) {
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
            JLog::add('component/com_emundus/models/formbuilder | Problem when change require of the element ' . $element['id'] . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
                    $query->clear()
                        ->select('*')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('element_id') . ' = ' . $element['id']);
                    $db->setQuery($query);
                    $fabrik_join = $db->loadObject();

                    if(!empty($fabrik_join)){
                        $join_params = json_decode($fabrik_join->params);
                        $join_params->{'join-label'} = $element['params']['join_val_column'];
                        $join_params->pk = $db->quoteName($element['params']['join_db_name']) . '.' . $db->quoteName($element['params']['join_key_column']);

                        $fields = array(
                            $db->quoteName('table_join_key') . ' = ' . $db->quote($element['params']['join_key_column']),
                            $db->quoteName('table_join') . ' = ' . $db->quote($element['params']['join_db_name']),
                            $db->quoteName('params') . ' = ' . $db->quote(json_encode($join_params)),
                        );
                        $query->clear()
                            ->update($db->quoteName('#__fabrik_joins'))
                            ->set($fields)
                            ->where($db->quoteName('id') . ' = ' . $db->quote($fabrik_join->id));
                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    $sub_values = [];
                    $sub_labels = [];
                    $sub_initial_selection = [];

                    if($element['params']['default_value'] == 1) {
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
                                'fr' => $element['params']['sub_options']['sub_labels'][$index],
                                'en' => $element['params']['sub_options']['sub_labels'][$index],
                            );
                            if ($old_params['sub_options']['sub_labels'][$index]) {
                                if($old_params['sub_options']['sub_labels'][$index] != 'PLEASE_SELECT'){
                                    $this->updateTranslation($old_params['sub_options']['sub_labels'][$index], $new_label);
                                    $sub_labels[] = $old_params['sub_options']['sub_labels'][$index];
                                    $sub_values[] = $sub_value;
                                }
                            } else {
                                $this->deleteTranslation('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index);
                                $labels = array(
                                    'fr' => $element['params']['sub_options']['sub_labels'][$index],
                                    'en' => $element['params']['sub_options']['sub_labels'][$index],
                                );
                                $this->translate('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index,$labels,'fabrik_elements',$element['id'],'sub_labels');
                                $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                                $sub_values[] = $index + 1;
                            }
                        } else {
                            $labels = array(
                                'fr' => $element['params']['sub_options']['sub_labels'][$index],
                                'en' => $element['params']['sub_options']['sub_labels'][$index],
                            );
                            $this->translate('SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index,$labels,'fabrik_elements',$element['id'],'sub_labels');

                            $sub_labels[] = 'SUBLABEL_' . $element['group_id'] . '_' . $element['id'] . '_' . $index;
                            $sub_values[] = $index + 1;
                        }
                    }

                    $element['params']['sub_options'] = array(
                        'sub_values' => $sub_values,
                        'sub_labels' => $sub_labels,
                        'sub_initial_selection' => $sub_initial_selection,
                    );
                }
            }

            if ($element['plugin'] === 'field') {
                $key = array_search("isemail", $element['params']['validations']['plugin']);

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

            // Update the element
            $fields = array(
                $db->quoteName('plugin') . ' = ' . $db->quote($element['plugin']),
                $db->quoteName('default') . ' = ' . $db->quote($element['default']),
                $db->quoteName('params') . ' = ' . $db->quote(json_encode($element['params'])),
                $db->quoteName('modified_by') . ' = ' . $db->quote($user),
                $db->quoteName('modified') . ' = ' . $db->quote($date),
            );
            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $db->quote($element['id']));
            //
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            if (gettype($query) == 'string') {
                JLog::add('component/com_emundus/models/formbuilder | Error at updating the element ' . $element['id'] . ' : ' . preg_replace("/[\r\n]/"," ",$query .' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            } else {
                JLog::add('component/com_emundus/models/formbuilder | Error at updating the element ' . $element['id'] . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }

            return false;
        }
    }

    function updateGroupParams($group_id, $params)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get old params
        $query->select('params')
            ->from('#__fabrik_groups')
            ->where('id = ' . $db->quote($group_id));
        $db->setQuery($query);

        try {
            $group_params = json_decode($db->loadResult(), true);
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error at getting group params : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }

        if($group_params['repeat_group_button'] == 1 && $params['repeat_group_button'] == 0){
            $this->disableRepeatGroup($group_id);
        }
        if($group_params['repeat_group_button'] == 0 && $params['repeat_group_button'] == 1){
            $this->enableRepeatGroup($group_id);
        }

        if (!empty($group_params)) {
            foreach($params as $param => $value) {
                $group_params[$param] = $value;
            }
        }

        $query->clear()
            ->update('#__fabrik_groups')
            ->set('params = ' . $db->quote(json_encode($group_params)))
            ->where('id = ' . $db->quote($group_id));

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error at updating group params : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot duplicate the element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot move the element to trash ' . $elt . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }

    function reorderMenu($menus,$profile) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $rgt = 2;
            foreach ($menus as $key => $menu) {
                $rgt = $menu->rgt + $key + 3;
                $lft = $menu->rgt + $key + 2;

                $query->clear()
                    ->update($db->quoteName('#__menu'))
                    ->set('rgt = ' . $db->quote($rgt))
                    ->set('lft = ' . $db->quote($lft))
                    ->where('link = ' . $db->quote($menu->link));
                $db->setQuery($query);
                $db->execute();
            }

            $query->clear()
                ->update($db->quoteName('#__menu'))
                ->set('lft = ' . $db->quote(1))
                ->set('rgt = ' . $db->quote($rgt - 1))
                ->where('menutype = ' . $db->quote('menu-profile'.$profile))
                ->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/formbuilder | Error at reorder the menu with link ' . $link . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot get ordering of group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot reorder group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at getting pages models : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');
        $falang = new EmundusModelFalang;

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
                        JLog::add('component/com_emundus/models/formbuilder | Error at create a page from the model ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
                ->andWhere($db->quoteName('published') . ' = 1')
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
            JLog::add('component/com_emundus/models/formbuilder | Error at create a page from the model ' . $formid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at check constraints groups of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at check visibility of the group ' . $group . ' in campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at publish/unpublish element ' . $element . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function hiddenUnhiddenElement($element) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('hidden')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($element));
            $db->setQuery($query);
            $old_hidden = $db->loadResult();

            $hidden = 1;
            if ($old_hidden == 1) {
                $hidden = 0;
            }

            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('hidden') . ' = ' . $db->quote($hidden))
                ->where($db->quoteName('id') . ' = ' . $db->quote($element));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/formbuilder | Error at publish/unpublish element ' . $element . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at getting databases references : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
    function getDatabaseJoinOrderColumns($database_name) {

        $db = $this->getDbo();
        $query = "SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$database_name'";

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error at getting databases references columns : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
    function getAllDatabases() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('table_name as database_name,table_name as label')
            ->from($db->quoteName('information_schema.TABLES'))
            ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('jos_%'));
        $db->setQuery($query);
        try {
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error at getting databases references : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function enableRepeatGroup($gid){
        $saved = false;
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser()->id;

        $group = $this->getFabrikGroup($gid);
        if (!empty($group)) {
            // Prepare Fabrik API
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
            $groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
            $groupModel->setId(intval($gid));
            $elements = $groupModel->getMyElements();
            $listModel = $groupModel->getListModel();
            $list = $listModel->getTable();
            $db_table = $list->db_table_name;
            $list_id = $list->id;
            $form_id = $list->form_id;

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

            // Create parent_id element
            $query = $db->getQuery(true);
            $params = $this->h_fabrik->prepareElementParameters('field',false);

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
            } else {
                $query->clear()
                    ->update('#__fabrik_elements')
                    ->set('published = 1')
                    ->where('group_id = ' . $db->quote($gid))
                    ->andWhere('name = ' . $db->quote('parent_id'));
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

            //verify if left join doesn't already exist;
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
            }

            // Insert element present in the group
            foreach ($elements as $element) {
                if ($element->element->plugin === 'birthday') {
                    $dbtype = 'DATE';
                } elseif ($element->element->plugin === 'textarea') {
                    $dbtype = 'TEXT';
                } else {
                    $dbtype = 'TEXT';
                }


                if (!empty($element->element->name)) {
                    $query = "ALTER TABLE " . $newtablename . " ADD " . $element->element->name . " " . $dbtype . " NULL";
                } else {
                    $query = "ALTER TABLE " . $newtablename . " ADD e_" . $form_id . "_" . $element->element->id . " " . $dbtype . " NULL";
                }

                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    continue;
                }
            }

            $saved = true;
        }

        return $saved;
    }

    private function getFabrikGroup($gid) {
        $group = null;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);
        try {
            $group = $db->loadObject();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Cannot get group ' . $gid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        return $group;
    }

    function disableRepeatGroup($gid){
        $saved=false;

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('jfg.*, jff.form_id AS form_id')
            ->from('#__fabrik_groups AS jfg')
            ->leftJoin('#__fabrik_formgroup AS jff ON jff.group_id = jfg.id')
            ->where('jfg.id = ' . $db->quote($gid));
        $db->setQuery($query);

        try {
            $group = $db->loadAssoc();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Error at enabling repeat group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        if (!empty($group)) {
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
            require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/models/group.php');
            $groupModel = new FabrikAdminModelGroup;
            $params = json_decode($group['params'], true);
            $params['repeat_group_button'] = 0;

            $data = array(
                'id' => $gid,
                'label' => $group['label'],
                'form' => $group['form_id'],
                'name' => $group['name'],
                'published' => $group['published'],
                'is_join' => $group['is_join'],
                'params' => $params,
                'tags' => $group['tags']
            );

            $saved = $groupModel->save($data);

            if ($saved) {
                $query->clear()
                    ->update('#__fabrik_groups')
                    ->set('is_join = 0')
                    ->where('id = ' . $db->quote($gid));

                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->update('#__fabrik_elements')
                    ->set('published = 0')
                    ->where('group_id = ' . $db->quote($gid))
                    ->andWhere('name = ' . $db->quote('parent_id'));

                $db->setQuery($query);
                $db->execute();
            }
        }

        return $saved;
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot disable repeat group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateMenuLabel($label,$pid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $falang = new EmundusModelFalang;

        $link = 'index.php?option=com_fabrik&view=form&formid=' . $pid;

        $query->select('id')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote($link));
        $db->setQuery($query);

        try {
            $menuid = $db->loadObject();

            return $falang->updateFalang($label,$menuid->id,'menu','title');
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Cannot update the menu label of the fabrik_form ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at getting files and campaigns of the form ' . $prid . ' and of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Error at creating a testing file in the campaign ' . $cid . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot delete testing file ' . $fnum . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/formbuilder | Cannot get ordering of group ' . $gid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateDefaultValue($eid,$value){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('default') . ' = ' . $db->quote($value))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Cannot update default value of element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getSection($section){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,label,params')
                ->from($db->quoteName('#__fabrik_groups'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($section));
            $db->setQuery($query);
            $group = $db->loadObject();

            $group->label = JText::_($group->label);
            $group->params = json_decode($group->params);

            return $group;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/formbuilder | Cannot get group ' . $section . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

}
