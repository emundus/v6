<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron to scan platform and detect no standars elements
 */

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
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
class PlatformStandardScan extends JApplicationCli
{


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute()
    {

        $structureFromDump = $this->getTableStructure('dump', 'jos_menu');
        $structureFromLocal = $this->getTableStructure('local', 'jos_menu');

        $difference = $this->getTableStructureDifference($structureFromDump, $structureFromLocal);
        echo "<pre>----- HEY defiiferences------</pre>";
        echo "<pre>$difference</pre>";
    }


    function getTableStructure($data_source, $table = 'jos_menu')
    {
        $structure = "";
        switch ($data_source) {
            case 'dump':
                $structure = $this->getTableStructureFromDump($table);
                break;
            default:
                $structure = $this->getTableStructureFromLocal($table);
                break;
        }

        return $structure;

    }

    function getTableStructureFromDump($table)
    {
        $dumpSourceFile = JPATH_BASE . "/cli/standard.sql";
        $dumpSourceContent = file_get_contents($dumpSourceFile);

        $matchesSource = [];
        preg_match('/CREATE TABLE `' . $table . '` (.+?)(?=CREATE|$)/s', $dumpSourceContent, $matchesSource);
        return $matchesSource[0] ?? "";

    }

    function getTableStructureFromLocal($table)
    {
        $dumpSourceFile = JPATH_BASE . "/cli/kit_last.sql";
        $dumpSourceContent = file_get_contents($dumpSourceFile);

        $matchesSource = [];
        preg_match('/CREATE TABLE `' . $table . '` (.+?)(?=CREATE|$)/s', $dumpSourceContent, $matchesSource);
        return $matchesSource[0] ?? "";
    }

    function getTableStructureDifference($structure1, $structure2): false|string|null
    {
        // Create temporary files with the structures
        $file1 = tempnam(sys_get_temp_dir(), 'structure_');
        $file2 = tempnam(sys_get_temp_dir(), 'structure_');

        file_put_contents($file1, $structure1);
        file_put_contents($file2, $structure2);

        // Use the diff command to compare the two structures
        $diffCommand = "diff -u $file1 $file2";
        $diffOutput = shell_exec($diffCommand);

        // Clean up temporary files
        unlink($file1);
        unlink($file2);

        return $diffOutput;
    }


}

JApplicationCli::getInstance('PlatformStandardScan')->execute();
