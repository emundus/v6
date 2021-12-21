<?php


use Joomla\Utilities\ArrayHelper;

const _JEXEC = 1;
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);
define('JPATH_BASE', dirname(__DIR__));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_CONFIGURATION . '/configuration.php';
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/');
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_joomlaupdate/models/default.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/update.php';

class MakeUpdateServer extends JApplicationCli
{

    public function removeSql(){
        $source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
        $dest = JPATH_ADMINISTRATOR . '/components/com_emundus/sql/updates/mysql';
        mkdir($dest, 0777, true);
        $files = scandir($source);
        foreach ($files as $file) {
            if (strpos($file, 'em') !== false) {
                if (!rename($source . '/' . $file, $dest . '/' . $file)) {
                    echo $file . " can't be moved!";
                } else {
                    echo $file . " has been moved!";
                }
            }
        }
    }

    public function updateServerXml() {
        # Modify emundus.xml
        $file = JPATH_ADMINISTRATOR . '/components/com_emundus/emundus.xml';

        $updateserver = "\t<updateservers>\n\t\t<server type='collection' name='eMundus61'>http://localhost:8888/emundus-updates/list.xml</server>\n\t</updateservers>\n";
        $update = "\n\t<update>\n\t\t<schemas>\n\t\t\t<schemapath type='mysql'>sql/updates/mysql</schemapath>\n\t\t</schemas>\n\t</update>\n\n";
        $sql = "\t\t<folder>sql</folder>";
        $sep = "</extension>";

        $contents = explode("</extension>", file_get_contents($file), 2);
        file_put_contents($file, $contents[0] . $updateserver . $update . $sep);

        $contents = explode('folder="admin">', file_get_contents($file), 2);
        $sep = 'folder="admin">';
        file_put_contents($file, $contents[0] . $sep . "\n\t" . $sql . $contents[1]);


        // Add xml files for update server
        # list.xml
        $filepath = JPATH_BASE . '/emundus-updates';
        mkdir($filepath);
        mkdir($filepath . "/com_emundus");
        $list = fopen($filepath . "/list.xml", "w") or die("Unable to open file!");
        $txt = "<extensionset name='eMundus Update Collection' description='eMundus Update as Collection'>\n\t<extension name='eMundus Component' element='com_emundus' type='component' client='administrator' version='8.0.0' targetplatformversion='3.[23456789]' detailsurl='http://localhost:8888/emundus-updates/com_emundus/updates.xml'/>\n</extensionset>";
        fwrite($list, $txt);
        fclose($list);

        # updates.xml
        $filepath = JPATH_BASE . '/emundus-updates/com_emundus';
        $update = fopen($filepath . "/updates.xml", "w") or die("Unable to open file!");
        $txt = "<updates>\n\t<update>\n\t\t<name>eMundus component</name>\n\t\t<description>eMundus component</description>\n\t\t<element>com_emundus</element>\n\t\t<type>component</type>\n\t\t<version>8.0.0</version>\n\t\t<infourl>http://localhost:8888/emundus-updates/com_emundus/emundus-8.0.0.html</infourl>\n\t\t<downloads>\n\t\t\t<downloadurl type='full' format='zip'>http://localhost:8888/emundus-updates/com_emundus/emundus-8.0.0.zip</downloadurl>\n\t\t</downloads>\n\t\t<tags>\n\t\t\t<tag>stable</tag>\n\t\t</tags>\n\t\t<targetplatform name='joomla' version='3.[23456789]' />\n\t\t<php_minimum>5.3</php_minimum>\n\t\t<supported_databases mysql='5.6.19'></supported_databases>\n\t</update>\n</updates>";
        fwrite($update, $txt);
        fclose($update);
    }

    public function packageComponent()
    {
        // Set path for component's folders
        $admin_path = JPATH_ADMINISTRATOR . "/components/com_emundus/";
        $site_path = JPATH_BASE . "/components/com_emundus";
        $fr_path = $admin_path . '/language/fr-FR';
        $en_path = $admin_path . '/language/en-GB';
        $media_path = JPATH_BASE . "/media/com_emundus";
        $xml_path = $admin_path . 'emundus.xml';
        // Set destination path
        $dest = JPATH_ROOT . '/tmp';
        // Copy files in tmp folder
        $this->custom_copy($admin_path, $dest . '/admin');
        $this->custom_copy($site_path, $dest . '/site');
        $this->custom_copy($fr_path, $dest . '/language');
        $this->custom_copy($en_path, $dest . '/language');
        $this->custom_copy($media_path, $dest . '/media/com_emundus');
        if (!copy($xml_path, $dest . '/emundus.xml')) {
            echo 'failed copy';
        } else {
            echo 'copy ok';
        }
    }

    public function zipComponent($zipname, $zipdir) {

        // Create new zip class
        // Get real path for our folder
        $rootPath = realpath($zipdir);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zipdir . $zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($zipdir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    public function custom_copy($src, $dst)
    {

        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        @mkdir($dst, 0777, true);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {

            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {

                    // Recursively calling custom copy function
                    // for sub directory
                    $this->custom_copy($src . '/' . $file, $dst . '/' . $file);

                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    public function deleteTmp() {
        # Delete all files in tmp folder
        $path = JPATH_ROOT . '/tmp/';
        if (file_exists($path)) {
            $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        }
    }
    public function doExecute()
    {
        //$this->removeSql();
        //$this->updateServerXml();
        $this->packageComponent();
        $zipname = "emundus-8.0.0.zip";
        $zipdir = JPATH_ROOT . '/tmp/';
        $zipdir2 = JPATH_ROOT . '/emundus-updates/com_emundus/';
        $this->zipComponent($zipname, $zipdir);
        $this->zipComponent($zipname, $zipdir2);

        $this->deleteTmp();

    }

}

JApplicationCli::getInstance('MakeUpdateServer')->execute();
