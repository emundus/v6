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
/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class LanguageFileToBase extends JApplicationCli {


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('DISTINCT(element), CONCAT(type, "s") AS type')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' LIKE ' . $db->quote('%emundus%'));

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            echo "Error getting extensions";
        }

        // Components, modules, extensions files
        $files = [];
        foreach ($this->getPlatformLanguages() as $language) {
            foreach ($extensions as $extension) {
                $file = JPATH_BASE . '/' . $extension->type . '/' . $extension->element . '/language/' . $language . '/' . $language.'.'.$extension->element. '.ini';
                if (file_exists($file)) {
                    $files[] = $file;
                }
            }
            // Overrides
            $override_file = JPATH_BASE . '/language/overrides/' . $language.'.override.ini';
            if (file_exists($override_file)) {
                $files[] = $override_file;
            }
            //
        }
        //

        $db_columns = [
            $db->quoteName('tag'),
            $db->quoteName('lang_code'),
            $db->quoteName('override'),
            $db->quoteName('original_text'),
            $db->quoteName('original_md5'),
            $db->quoteName('override_md5'),
            $db->quoteName('location'),
            $db->quoteName('type'),
            $db->quoteName('created_by')];
        $db_values = [];

        foreach ($files as $file) {
            echo $file . "\n";

            $parsed_file = JLanguageHelper::parseIniFile($file);

            $file = explode('/', $file);
            $file_name = end($file);
            $language = strtok($file_name, '.');

            foreach ($parsed_file as $key => $val) {
                $query->clear()
                    ->select('count(id)')
                    ->from($db->quoteName('jos_emundus_setup_languages'))
                    ->where($db->quoteName('tag') . ' = ' . $db->quote($key))
                    ->andWhere($db->quoteName('lang_code') . ' = ' . $db->quote($language))
                    ->andWhere($db->quoteName('location') . ' = ' . $db->quote($file_name));
                $db->setQuery($query);

                if($db->loadResult() == 0) {
                    if(strpos($file_name,'override') !== false) {
                        $row = [$db->quote($key), $db->quote($language), $db->quote($val), $db->quote($val), $db->quote(md5($val)), $db->quote(md5($val)), $db->quote($file_name),$db->quote('override'), 62];
                    } else {
                        $row = [$db->quote($key), $db->quote($language), $db->quote($val), $db->quote($val), $db->quote(md5($val)), $db->quote(md5($val)), $db->quote($file_name),$db->quote(null), 62];
                    }
                    $db_values[] = implode(',', $row);
                }
            }
        }

        if(!empty($db_values)) {
            $query
                ->clear()
                ->insert($db->quoteName('jos_emundus_setup_languages'))
                ->columns($db_columns)
                ->values($db_values);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $exception) {
                echo "<pre>";
                var_dump('error inserting data : ' . $exception->getMessage() . ' ' . $query->__toString());
                echo "</pre>";
                die();
            }
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
}

JApplicationCli::getInstance('LanguageFileToBase')->execute();
