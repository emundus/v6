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
class UpdateFont extends JApplicationCli {


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute() {
        $font = $this->input->getString('f', 'family=Maven+Pro:500,700,900,400&subset=latin,vietnamese,latin-ext');

        $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(JPATH_BASE . '/templates/g5_helium/custom/config/default/styles.yaml'));

        $yaml['font']['family-default'] = $font;
        $yaml['font']['family-title'] = $font;

        $new_yaml = \Symfony\Component\Yaml\Yaml::dump($yaml, 5);

        file_put_contents(JPATH_BASE . '/templates/g5_helium/custom/config/default/styles.yaml', $new_yaml);
    }
}

JApplicationCli::getInstance('UpdateFont')->execute();
