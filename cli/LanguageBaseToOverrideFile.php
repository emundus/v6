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
class LanguageBaseToOverrideFile extends JApplicationCli {


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

        try {
            $files = [];
            foreach ($this->getPlatformLanguages() as $language) {
                $override_file = JPATH_BASE . '/language/overrides/' . $language . '.override.ini';
                if (file_exists($override_file)) {
                    $files[] = $override_file;
                } else {
	                $fp = fopen($override_file, "w");
	                fclose($fp);
	                $files[] = $override_file;
                }
            }

            foreach ($files as $file) {
                echo $file . "\n";

                $file_explode = explode('/', $file);
                $file_name = end($file_explode);

                $query->clear()
                    ->select('id,tag,override,location,original_md5,override_md5')
                    ->from($db->quoteName('#__emundus_setup_languages'))
                    ->where($db->quoteName('location') . ' LIKE ' . $db->quote($file_name));
                $db->setQuery($query);
                $modified_overrides = $db->loadObjectList();

                $parsed_file = JLanguageHelper::parseIniFile($file);
                if(empty($parsed_file)) {
                    foreach ($modified_overrides as $modified_override) {
                        $parsed_file[$modified_override->tag] = $modified_override->override;
                    }
                    JLanguageHelper::saveToIniFile($file, $parsed_file);
                } else {
                    foreach ($modified_overrides as $modified_override) {
                        if(empty($parsed_file[$modified_override->tag])) {
                            $parsed_file[$modified_override->tag] = $modified_override->override;
                        }
                    }
                    JLanguageHelper::saveToIniFile($file, $parsed_file);
                }
            }
        } catch(Exception $e){
            echo '<pre>'; var_dump($e->getMessage()); echo '</pre>'; die;
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

JApplicationCli::getInstance('LanguageBaseToOverrideFile')->execute();
