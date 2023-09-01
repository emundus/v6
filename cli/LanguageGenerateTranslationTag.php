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

define(DS, DIRECTORY_SEPARATOR);

// dependance


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

        $args = (array)$GLOBALS['argv']; // like C or C++

        $this->generateTranslationTag((int)$args[1]);
    }

    private function generateTranslationTag($profile_id) {

        require_once JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'form.php';
        $languages = $this->getPlatformLanguages();

        $fileEndname = '.override.ini';
        $file = parse_ini_file(JPATH_SITE.DS. 'language'.DS.'overrides'.DS.$languages[0].$fileEndname);

        var_dump($file);

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

    private function getFormsByProfileId($profile_id) {

        if (empty($profile_id)) {
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');

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
            JLog::add('component/com_emundus/models/form | Error at getting form pages by profile_id ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}

JApplicationCli::getInstance('LanguageGenerateTranslationTag')->execute();
