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
        $structureFromStandard = $this->getTableStructure('local', 'jos_menu');

        $difference = $this->getTableStructureDifference($structureFromDump, $structureFromStandard);

        $filePath = '/var/www/html/cli/diff_out_put.txt';
        try {
            // Write the SHOW CREATE TABLE statement to the file
            file_put_contents($filePath, $difference);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }

    }


    function getTableStructure($data_source, $table = 'jos_menu')
    {
        $structure = "";
        switch ($data_source) {
            case 'dump':
                $structure = $this->getTableStructureFromDump($table);
                break;
            default:
                $structure = $this->getTableStructureFromStandard($table);
                break;
        }

        return $structure;

    }

    function getTableStructureFromDump($table)
    {
        $dumpSourceFile = JPATH_BASE . "/cli/kit_last.sql";
        $dumpSourceContent = file_get_contents($dumpSourceFile);

        $matchesSource = [];
        preg_match('/CREATE TABLE `' . $table . '` (.+?)(?=CREATE|$)/s', $dumpSourceContent, $matchesSource);
        return $matchesSource[0] ?? "";

    }

    function getTableStructureFromStandard($table)
    {
        $dumpSourceFile = JPATH_BASE . "/cli/vanilla.sql";
        $dumpSourceContent = file_get_contents($dumpSourceFile);

        $matchesSource = [];
        preg_match('/CREATE TABLE `' . $table . '` (.+?)(?=CREATE|$)/s', $dumpSourceContent, $matchesSource);
        return $matchesSource[0] ?? "";
    }

    function getTableStructureDifference($structureFromDump, $structureFromStandard)
    {
        // Create temporary files with the structures
        $dumpFile = tempnam(sys_get_temp_dir(), 'structure_dump_');
        $standardFile = tempnam(sys_get_temp_dir(), 'structure_standard_');

        file_put_contents($standardFile, $structureFromStandard);
        file_put_contents($dumpFile, $structureFromDump);

        // Use the diff command to compare the two structures
        $diffCommand = "diff -u $standardFile $dumpFile";
        $diffOutput = shell_exec($diffCommand);

        // Split the diff output into sections starting with @@
        $sections = preg_split('/^@@.*@@$/m', $diffOutput, -1, PREG_SPLIT_NO_EMPTY);

        // Start building the HTML
        $html = '<pre><code>';

        foreach ($sections as $section) {
            // Split each section into lines
            $lines = explode("\n", $section);

            foreach ($lines as $line) {
                // Apply different styles based on the type of line
                if (strpos($line, '-') === 0) {
                    // Lines starting with '-' (removed) will be styled in red
                    $html .= '<span style="color:red;">' . htmlspecialchars($line) . '</span>' . "\n";
                } elseif (strpos($line, '+') === 0) {
                    // Lines starting with '+' (added) will be styled in yellow
                    $html .= '<span style="color:green;">' . htmlspecialchars($line) . '</span>' . "\n";
                } else {
                    // Lines that are unchanged
                    $html .= htmlspecialchars($line) . "\n";
                }
            }

            // Add a separator between sections
            $html .= '<hr>';
        }

        // Closing the HTML tags
        $html .= '</code></pre>';
        // Clean up temporary files
        unlink($dumpFile);
        unlink($standardFile);

        return $html;
        //return $diffOutput;
    }


}

JApplicationCli::getInstance('PlatformStandardScan')->execute();
