<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron job to import language Tags to jo_emundus_setup_languages table
 */

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

define('DS', DIRECTORY_SEPARATOR);

/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class LanguageGenerateTranslationTag extends JApplicationCli {


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute() {

        $args = (array)$GLOBALS['argv'];

        if (empty($args[1])) {
            $this->out('Please provide a profile id');
            return;
        } else {
            $profile_id = (int)$args[1];

            if (!$this->checkProfileIdExists($profile_id)) {
                $this->out('Given profile id does not exist');
            } else {
                $this->generateTranslationTag($profile_id);
            }
        }
    }

    private function checkProfileIdExists($profile_id)
    {
        $exists = false;

        if (!empty($profile_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('jos_emundus_setup_profiles'))
                ->where($db->quoteName('id') . ' = ' . $profile_id);

            try {
                $db->setQuery($query);
                $result = $db->loadResult();

                $exists = !empty($result);
            } catch (Exception $e) {
                $this->out($e->getMessage());
            }
        }

        return $exists;
    }

    private function generateTranslationTag($profile_id)
    {

        require_once JPATH_SITE . '/components/com_emundus/models/form.php';
        $languages = $this->getPlatformLanguages();

        foreach($languages as $language)
        {
            $filename = JPATH_SITE . '/language/overrides/' . $language. '.override.ini';
            $file_content = parse_ini_file($filename);

            if ($file_content)
            {
                $this->out('Generating translation tag for language: ' . $language);
                $this->generateTagFormsHandler($filename, $file_content, $profile_id);
                $this->generateTagGroupsHandler($filename, $file_content, $profile_id); // not working
                $this->generateTagElementsHandler($filename, $file_content, $profile_id); // not working
            }
        }
    }

    private function generateTagFormsHandler($filename, $file_content, $profile_id)
    {
        $forms = $this->getFormsByProfileId($profile_id);

        if (!empty($forms)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            foreach ($forms as $form)
            {
                $query->clear()
                    ->select($db->quoteName('label'))
                    ->from($db->quoteName('jos_fabrik_forms'))
                    ->where($db->quoteName('id') . ' = ' . $form->id);

                $db->setQuery($query);

                try {
                    $value = $db->loadResult();

                    $this->insertTagValue($value, $filename, $file_content, $form->id, $profile_id, 'jos_fabrik_forms', 'label', 'FORM');
                } catch (Exception $e) {
                    $value = null;
                }
            }
        } else {
            $this->out('- No forms found for profile id: ' . $profile_id);
        }
    }

    private function generateTagGroupsHandler($fileName, $file_content, $profile_id)
    {
        $forms = $this->getFormsByProfileId($profile_id);

        if (!empty($forms)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            foreach($forms as $form) {
                $groups = $this->getGroupsFromFormId($form->id);

                if (!empty($groups)) {
                    foreach ($groups as $group) {
                        $query->clear()
                            ->select($db->quoteName('label'))
                            ->from($db->quoteName('jos_fabrik_groups'))
                            ->where($db->quoteName('id') . ' ='. $group->id);

                        try {
                            $db->setQuery($query);
                            $value = $db->loadResult();

                            $this->insertTagValue($value, $fileName, $file_content, $group->id, $form->id, 'jos_fabrik_groups', 'label', 'GROUP');
                        } catch (Exception $e) {
                            $value = null;
                        }
                    }
                }
            }
        } else {
            $this->out('- No groups found for profile id: ' . $profile_id);
        }
    }

    private function generateTagElementsHandler($filename, $file_content, $profile_id)
    {
        $forms = $this->getFormsByProfileId($profile_id);

        if (!empty($forms)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            foreach($forms as $form)
            {
                $groups = $this->getGroupsFromFormId($form->id);

                foreach($groups as $group) {
                    $elements = $this->getElementsFromGroupId($group->id);

                    foreach ($elements as $element)
                    {
                        $query->clear()
                            ->select($db->quoteName('label'))
                            ->from($db->quoteName('jos_fabrik_elements'))
                            ->where($db->quoteName('id') . ' ='. $element->id);

                        try {
                            $db->setQuery($query);
                            $value = $db->loadResult();

                            $this->insertTagValue($value, $filename, $file_content, $element->id, $group->id, 'jos_fabrik_elements', 'label', 'ELEMENT');
                        } catch (Exception $e) {
                            $value = null;
                        }
                    }
                }
            }
        } else {
            $this->out('- No elements found for profile id: ' . $profile_id);
        }
    }

    private function insertTagValue($value, $filename, $file_content, $id, $ref_id, $table, $column, $tag)
    {
        if ($value) {
            if (is_null($file_content[$value])) {
                $new_tag = $this->generateTag($id, $ref_id, $table, $column, $tag);

                if ($new_tag) {
                    $this->writeTagInFileLanguage($filename, $new_tag, $value);
                }
            }
        } else {
            $new_tag = $this->generateTag($id, $ref_id, $table, $column, $tag);
            $this->writeTagInFileLanguage($filename, $new_tag, 'Unnamed item');
        }
    }

    /**
     * @param $id int du formulaire / group / element
     * @param $ref_id int de sa référence (profile utilisateur / formulaire / group)
     * @param $table string la table associée
     * @param $column string
     * @param $tag
     * @return false|string le nouveau nom de la balise = $tag_$ref_id_$id. ex = FORM_95_183
     */
    private function generateTag($id, $ref_id, $table, $column, $tag)
    {
        $inserted_tag = '';

        if (!empty($id) && !empty($ref_id) && !empty($table) && !empty($column) && !empty($tag)) {
            $new_tag = $tag . '_' . $ref_id . '_' . $id;

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->update($table)
                ->set($db->quoteName($column) .' = '. $db->quote($new_tag))
                ->where('id = '. $id);

            try {
                $db->setQuery($query);
                $inserted = $db->execute();

                if ($inserted) {
                    $inserted_tag = $new_tag;
                }
            } catch (Exception $e) {
                JLog::add('cli/LanguageGenerateTranslationTag | Error at updating column '.$column. ' in table '.$table. ' with id ' . $ref_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $inserted_tag;
    }

    private function writeTagInFileLanguage($filename, $tag, $value)
    {
        $file = fopen($filename, 'a') or die('Unable to open file!');

        $text = $tag."=\"".$value."\"";

        fwrite($file, $text."\n");
        fclose($file);

        $this->out('Tag '.$tag.' added in file with value '.$value);
    }

    private function getColumnValueFromId($id, $info, $table)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName($info))
            ->from($db->quoteName($table))
            ->where($db->quoteName('id') . ' ='.$id);

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            return false;
        }
    }

    private function getPlatformLanguages() : array {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1 ');

        $db->setQuery($query);

        try {
            return $db->loadColumn();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getFormsByProfileId($profile_id)
    {

        if (empty($profile_id)) {
            return false;
        }

        require_once (JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
        $formbuilder = new EmundusModelFormbuilder;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['menu.link' , 'menu.rgt'])
            ->from ($db->quoteName('#__menu', 'menu'))
            ->leftJoin($db->quoteName('#__menu_types', 'mt').' ON '.$db->quoteName('mt.menutype').' = '.$db->quoteName('menu.menutype'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp').' ON '.$db->quoteName('sp.menutype').' = '.$db->quoteName('mt.menutype'))
            ->where($db->quoteName('sp.id') . ' = '.$profile_id)
            ->where($db->quoteName('menu.parent_id') . ' != 1')
            ->where($db->quoteName('menu.published') . ' = 1')
            ->where($db->quoteName('menu.link') . ' LIKE ' . $db->quote('%option=com_fabrik%'))
            ->group('menu.rgt')
            ->order('menu.rgt ASC');


        try {
            $db->setQuery($query);
            $forms = $db->loadObjectList();

            foreach ($forms as $form) {
                $link = explode('=', $form->link);
                $form->id = $link[sizeof($link) - 1];

                $query->clear()
                    ->select('label')
                    ->from($db->quoteName('#__fabrik_forms'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($form->id));
                $db->setQuery($query);
                $form->label = $formbuilder->getJTEXT($db->loadResult());
                print_r($forms->label);
            }

            return $forms;
        } catch(Exception $e) {
            JLog::add('cli/LanguageGenerateTranslationTag | Error at getting form pages by profile_id ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    private function getGroupsFromFormId($form_id)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('groups.id, groups.name, groups.label')
            ->from($db->quoteName('#__fabrik_groups', 'groups'))
            ->leftJoin($db->quoteName('#__fabrik_formgroup', 'formgroup').' ON '.$db->quoteName('formgroup.group_id').' = '.$db->quoteName('groups.id'))
            ->where($db->quoteName('formgroup.form_id'). ' = '. $form_id);

        $db->setQuery($query);

        try
        {
            return $db->loadObjectList();
        }
        catch (Exception $e)
        {
            JLog::add('cli/LanguageGenerateTranslationTag | Error at getting groups pages by form_id ' . $form_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    private function getElementsFromGroupId($group_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id, label')
            ->from($db->quoteName('#__fabrik_elements', 'elms'))
            ->where($db->quoteName('elms.group_id'). ' = '. $group_id);

        $db->setQuery($query);

        try
        {
            return $db->loadObjectList();
        }
        catch (Exception $e)
        {
            JLog::add('cli/LanguageGenerateTranslationTag | Error at getting elements pages by group_id ' . $group_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}

JApplicationCli::getInstance('LanguageGenerateTranslationTag')->execute();
