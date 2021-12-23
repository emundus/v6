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

    /**
     * Replace sql files in component directory
     * @param $component
     * @return void
     */
    public function removeSql($component){
        $source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
        $dest = JPATH_ADMINISTRATOR . '/components/'. $component .'/sql/updates/mysql';
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

    /**
     * Make update server for the component
     * @param $component
     * @param $sep
     * @param $sep_admin
     * @param $updateserver
     * @param $update
     * @param $folder_admin
     * @return void
     */
    public function updateServerXml($component, $sep, $sep_admin, $updateserver, $update, $folder_admin)
    {
        $name = preg_split("/[_]+/", $component, 2);

        # Modify emundus.xml
        $file = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . $name[1] . '.xml';

        $contents = explode($sep, file_get_contents($file), 2);
        if ($component == 'com_emundus'){
        file_put_contents($file, $contents[0] . $updateserver . $update . $sep);
        $contents = explode($sep_admin, file_get_contents($file), 2);
        $sep = 'folder="admin">';
        file_put_contents($file, $contents[0] . $sep . "\n\t" . $folder_admin . $contents[1]);
        } elseif ($component == 'com_emundus_messenger'){
            $file = JPATH_SITE . '/components/' . $component . '/' . $name[1] . '.xml';
        } else {
            file_put_contents($file, $contents[0] . $updateserver . $sep);
        }
    }

    /**
     * Copy all files (in tmp folder & on update server) needed for install and update
     * @return void
     */
    public function packageComponent($component)
    {
        // Set path for component's folders
        $name = preg_split("/[_]+/", $component, 2);
        $admin_path = JPATH_ADMINISTRATOR . "/components/". $component ."/";
        $site_path = JPATH_BASE . "/components/". $component ."/";
//        $fr_path = $admin_path . 'language/fr-FR';
//        $en_path = $admin_path . 'language/en-GB';
        $media_path = JPATH_BASE . "/media/" . $component;
        $xml_path = $admin_path . $name[1] .'.xml';
        // Set destination path
        $dest = JPATH_ROOT . '/tmp/' . $component;
        mkdir($dest);
        // Copy files in tmp folder
        $this->custom_copy($admin_path, $dest . '/admin');
        $this->custom_copy($site_path, $dest . '/site');
//        $this->custom_copy($fr_path, $dest . '/language');
//        $this->custom_copy($en_path, $dest . '/language');
        $this->custom_copy($media_path, $dest . '/media/' . $component);
        if (!copy($xml_path, $dest . '/'. $name[1] . '.xml')) {
            $xml_path = $site_path . $name[1] . '.xml';
            if (!copy($xml_path, $dest . '/' . $name[1] . '.xml')) {
                echo 'Xml copy failed';
            }
        }
    }

    /**
     * Create archive file for install and update the component
     * @param $zipname
     * @param $zipdir
     * @param $component
     * @return void
     */
    public function zipComponent($zipname, $zipdir, $component) {

        // Create new zip class
        // Get real path for our folder
        $tmp =  JPATH_ROOT . '/tmp/' . $component;
        $rootPath = realpath($tmp);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zipdir . $zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmp),
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
                //$zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    /**
     * @param $src
     * @param $dst
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * Create xml files for server update
     * @param $component
     * @param $filepath
     * @param null $content_list
     * @param $content_update
     * @return void
     */
    public function addXml($component, $filepath, $content_list=null) {
        # list.xml
        mkdir($filepath);
        $update_filepath = $filepath . "/" . $component;
        mkdir($update_filepath);

        # updates.xml
        $content_update = $this->makeXmlUpdate($component);
        $update = fopen($update_filepath . "/updates.xml", "w") or die("Unable to open file!");
        fwrite($update, $content_update);
        fclose($update);

        $html = fopen($update_filepath . "/" . $component ."-" . $this->version . ".html", "w") or die("Unable to open file!");
        fclose($html);
    }

    public function makeXmlUpdate($comp){
        $content_update = <<<XML
            <updates>
            </updates>
            XML;

        $updates = new SimpleXMLElement($content_update);
        //$extensionset->addAttribute('extension');
        $update=$updates->addChild('update');
        $update->addChild('name', $comp);
        $update->addChild('element', $comp);
        $update->addChild('type', 'component');
        $update->addChild('version', $this->version);
        $update->addChild('infourl', 'http://localhost/emundus-updates/'.$comp.'/'.$comp.'-'.$this->version.'.html');
        $update->addChild('downloads');
        $downloadurl=$update->downloads->addChild('downloadurl', 'http://localhost/emundus-updates/'.$comp.'/'.$comp.'-'.$this->version.'.zip');
        $downloadurl->addAttribute('type', 'full');
        $downloadurl->addAttribute('format', 'zip');
        $tags =$update->addChild('tags');
        $tags->addChild('tag', 'stable');
        $targetplatform = $update->addChild('targeplatformversion');
        $targetplatform->addAttribute('name', 'joomla');
        $targetplatform->addAttribute('version', '3.[23456789]');
        $update->addChild('php_minimum', '5.3');
        $dom = new DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($updates->asXML());
        return $dom->saveXML();
    }

    public function makeXmlList($comp_array, $filepath){
        $content_list=<<<XML
            <extensionset>
            </extensionset>
            XML;
        $extensionset = new SimpleXMLElement($content_list);
        foreach ($comp_array as $comp) {
            $name = preg_split("/[_]+/", $comp, 2);
            if (!file_exists($xml_path = JPATH_ADMINISTRATOR . "/components/" . $comp . "/" . $name[1] . '.xml')) {
                $xml_path= JPATH_SITE . "/components/" . $comp . "/" . $name[1] . '.xml';
            };

            $manifest = simplexml_load_file($xml_path);


            //$extensionset->addAttribute('extension');
            $extension = $extensionset->addChild('extension');
            $extension->addChild('name', ucwords($name[1]));
            $extension->addChild('element', $comp);
            $extension->addChild('type', $manifest->attributes()['type'][1]);
            $extension->addChild('client', $manifest->attributes()['client'][1]);
            $extension->addChild('version', $this->version);
            $extension->addChild('targeplatformversion', '3.[23456789]');
            $extension->addChild('detailsurl', 'http://localhost/emundus-updates/' . $comp . '/updates.xml');
        }
        $dom = new DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($extensionset->asXML());
        $content_list = $dom->saveXML();
        $list = fopen($filepath . "/list.xml", "w") or die("Unable to open file!");
        fwrite($list, $content_list);
        fclose($list);

        return $dom->saveXML();
    }

    public function doExecute()
    {
        $com = 'com_emundus';
        $com_onboard = 'com_emundus_onboard';
        $com_messenger = 'com_emundus_messenger';
        $this->version = "8.0.0";
        /**
         * Replace sql files in component directory
         */
        //$this->removeSql($com);


        /**
         * Make update server for the component
         */
        $sep = "</extension>";
        $sep_admin = 'folder="admin">';
        $updateserver = "\t<updateservers>\n\t\t<server type='collection' name='eMundus'>http://localhost/emundus-updates/list.xml</server>\n\t</updateservers>\n";
        $update = "\n\t<update>\n\t\t<schemas>\n\t\t\t<schemapath type='mysql'>sql/updates/mysql</schemapath>\n\t\t</schemas>\n\t</update>\n\n";
        $folder_admin = "\t\t<folder>sql</folder>";
        echo "\nUpdate main xml : " . $com;
        $this->updateServerXml($com, $sep, $sep_admin, $updateserver, $update, $folder_admin);
        echo "\nUpdate main xml : " . $com_onboard;
        $this->updateServerXml($com_onboard, $sep, $sep_admin, $updateserver, $update, $folder_admin);
        echo "\nUpdate main xml : " . $com_messenger;
        $this->updateServerXml($com_messenger, $sep, $sep_admin, $updateserver, $update, $folder_admin);

        /**
         * Create xml files for server update
         */
        $filepath = JPATH_BASE . '/emundus-updates';

        # list.xml for collection
        $comp_array =array('com_emundus', 'com_emundus_onboard', 'com_emundus_messenger');

        $this->makeXmlList($comp_array, $filepath);
        $this->addXml($com, $filepath);
        # updates.xml
        $content_list=null;
        $this->addXml($com_onboard, $filepath, $content_list);
        $this->addXml($com_messenger, $filepath, $content_list);

        /**
         * Copy all files (in tmp folder & on update server) needed for install and update
         */
        echo "\nPackaging component : " . $com;
        $this->packageComponent($com);
        echo "\nPackaging component : " . $com_onboard;
        $this->packageComponent($com_onboard);
        echo "\nPackaging component : " . $com_messenger;
        $this->packageComponent($com_messenger);

        /**
         * Create archive file for install and update the component
         */
        $comp_array =array('com_emundus', 'com_emundus_onboard', 'com_emundus_messenger');
        foreach ($comp_array as $comp) {
            echo "\nstart zip creation :" . $comp;
            $zipname = $comp . "-". $this->version .".zip";
            $zipdir_tmp =  JPATH_ROOT . '/tmp/';
            $zipdir_comp = JPATH_ROOT . '/emundus-updates/'. $comp .'/';
            $this->zipComponent($zipname, $zipdir_comp, $comp);
            echo "\nzip created for :" . $comp;
        }

        /**
         * Delete all files in tmp folder
         */
        echo "\nDelete tmp files";
        $this->deleteTmp();

    }

}

JApplicationCli::getInstance('MakeUpdateServer')->execute();
