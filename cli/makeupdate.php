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
	 *
	 * @param $component
	 *
	 * @return void
	 */
	public function removeSql($component)
	{
		$source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
		$dest   = JPATH_ADMINISTRATOR . '/components/' . $component . '/sql/updates/mysql';
		mkdir($dest, 0777, true);
		$files = scandir($source);
		foreach ($files as $file)
		{
			if (strpos($file, 'em') !== false)
			{
				if (!rename($source . '/' . $file, $dest . '/' . $file))
				{
					echo $file . " can't be moved!";
				}
				else
				{
					echo $file . " has been moved!";
				}
			}
		}
	}


	/**
	 * Make update server for the component
	 *
	 * @param $component
	 * @param $sep
	 * @param $sep_admin
	 * @param $updateserver
	 * @param $update
	 * @param $folder_admin
	 *
	 * @return void
	 */
	public function addUpdateServer($component, $sep, $sep_admin, $updateserver, $update, $folder_admin)
	{
		$name = preg_split("/[_]+/", $component, 2);

		# Modify emundus.xml
		$file = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . $name[1] . '.xml';
		if ($file == false)
		{
			exit();
		}

		$contents = explode($sep, file_get_contents($file), 2);

		$fi = file_get_contents($file);
		if ((!strpos($fi, $updateserver)) or (!strpos($fi, $update)) or (!strpos($fi, $folder_admin)))
		{
			if ($component == 'com_emundus')
			{
				file_put_contents($file, $contents[0] . $updateserver . $update . $sep);
				$contents = explode($sep_admin, file_get_contents($file), 2);
				$sep      = 'folder="admin">';
				file_put_contents($file, $contents[0] . $sep . "\n\t" . $folder_admin . $contents[1]);
			}
			elseif ($component == 'com_emundus_messenger')
			{
				$file = JPATH_SITE . '/components/' . $component . '/' . $name[1] . '.xml';
			}
			else
			{
				file_put_contents($file, $contents[0] . $updateserver . $sep);
			}
		}
	}


	/**
	 * Create xml files for each component of the update server
	 *
	 * @param $comp
	 * @param $filepath
	 * @param $file
	 *
	 * @return false|string
	 * @throws Exception
	 */
	public function makeXml($root, $elem, $file, $dom)
	{
		$comp = $elem['id'];
        $zip_name = $comp;
		if ($elem['type'] == 'component') {
            $name = preg_split("/[_]+/", $comp, 2);
        }

		if (!file_exists($xml_path = JPATH_ADMINISTRATOR . "/components/" . $comp . "/" . $name[1] . '.xml')) {
			$xml_path = JPATH_SITE . "/components/" . $comp . "/" . $name[1] . '.xml';
		}
		if ($elem['type'] == 'module') {
			$xml_path = JPATH_BASE . "/" . $elem['type'] . "s/" . $comp . "/" . $comp . '.xml';

		} elseif ($elem['type'] == 'plugin') {
			$xml_path = JPATH_BASE . "/" . $elem['type'] . "s/" . $elem['group'] . "/" . $comp . "/" . $comp . '.xml';
            $zip_name = "plg_" . $comp;
		}

		$update_filepath = $this->filepath . "packages/" . $comp;
		mkdir($update_filepath);

		$manifest = simplexml_load_file($xml_path);
        $name = $manifest->name;

        switch ($file)
		{
			case 'list':
                $extension = $dom->createElement('extension');
                $extension->setAttribute('name', $name);
                $extension->setAttribute('element', $comp);
                if ($manifest->attributes()['type'][1]) {
                    $extension->setAttribute('type', $manifest->attributes()['type'][1]);
                }
                $extension->setAttribute('folder', $manifest->attributes()['group'][1]);
                $extension->setAttribute('version', $this->version);

                if ($manifest->attributes()['client'][1]) {
                    $extension->setAttribute('client', $manifest->attributes()['client'][1]);
                }
                //$extension->setAttribute('targeplatformversion', '3.[23456789]');
                $extension->setAttribute('detailsurl', 'http://localhost/emundus-updates/packages/' . $comp . '/updates.xml');

                $root->appendChild($extension);
                break;
			case 'updates':
                $update = $dom->createElement('update');
                $update->appendChild($dom->createElement('name', $name));
                $update->appendChild($dom->createElement('description', $name));
                $update->appendChild($dom->createElement('element', $comp));
                if ($manifest->attributes()['type'][1]) {
                    $update->appendChild($dom->createElement('type', $manifest->attributes()['type'][1]));
                }
                $update->appendChild($dom->createElement('version', $this->version));
                //$update->appendChild($dom->createElement('infourl', 'http://localhost/emundus-updates/packages/' . $comp . '/' . $comp . '_' . $this->version . '.html'));
                $downloads = $dom->createElement('downloads');
                $downloadurl = $downloads->appendChild($dom->createElement('downloadurl', 'http://localhost/emundus-updates/packages/' . $comp . '/' . $zip_name . '_' . $this->version . '.zip'));
                $downloadurl->setAttribute('type', 'full');
                $downloadurl->setAttribute('format', 'zip');
                $update->appendChild($downloads);
//                $tags = $dom->createElement('tags');
//                $tags->appendChild($dom->createElement('tag', 'stable'));
//                $update->appendChild($tags);
                $targetplatform = $dom->createElement('targetplatform');
                $targetplatform->setAttribute('name', 'joomla');
                $targetplatform->setAttribute('version', '3');
                $update->appendChild($targetplatform);
                //$update->appendChild($dom->createElement("php_minimum", "5.3"));

                $root->appendChild($update);
                break;
        }
		$html = fopen($update_filepath . "/" . $comp . "_" . $this->version . ".html", "w") or die("Unable to open file!");
		fclose($html);
	}


	public function package($elem)
	{
		$dest    = JPATH_ROOT . '/tmp/' . $elem['id'];
		$zipname = null;

		if (JFolder::exists($dest))
		{
			JFolder::delete($dest);
		}

		if ($elem['type'] == 'component')
		{
			$this->packageComponent($elem['id']);

		}
		elseif ($elem['type'] == 'module')
		{
			$src = JPATH_BASE . "/" . $elem['type'] . "s/" . $elem['id'];
			try
			{
				JFolder::copy($src, $dest);
			}
			catch (Exception $e)
			{
				echo $elem['id'] . " module copy failed\n";
			}

		}
		elseif ($elem['type'] == 'plugin')
		{
			$src = JPATH_BASE . "/" . $elem['type'] . "s/" . $elem['group'] . "/" . $elem['id'];
			try
			{
				JFolder::copy($src, $dest);
			}
			catch (Exception $e)
			{
				echo $elem['id'] . " plugin copy failed\n";
			}
		}
	}


	/**
	 * Copy all files (in tmp folder & on update server) needed for install and update
	 * @return void
	 */
	public function packageComponent($component)
	{
		// Set path for component's folders
		$name       = preg_split("/[_]+/", $component, 2);
		$admin_path = JPATH_ADMINISTRATOR . "/components/" . $component . "/";
		$site_path  = JPATH_BASE . "/components/" . $component . "/";
        if (is_dir($admin_path . 'language')) {
            $fr_path    = $admin_path . 'language/fr-FR/fr-FR.' . $component . '.ini';
            $en_path    = $admin_path . 'language/en-GB/en-GB.' . $component . '.ini';
        } else {
            $fr_path = JPATH_BASE . '/language/fr-FR/fr-FR.' . $component . '.ini';
            $en_path = JPATH_BASE . '/language/en-GB/en-GB.' . $component . '.ini';
        }

        $media_path = JPATH_BASE . "/media/" . $component;
		$xml_path   = $admin_path . $name[1] . '.xml';
		// Set destination path
		$dest = JPATH_ROOT . '/tmp/' . $component;
		mkdir($dest);

		// Copy files in tmp folder
		$succes = array();
		if ($component != 'com_emundus_messenger')
		{
			$succes[] = $this->custom_copy($admin_path, $dest . '/admin');
		}
		$succes[] = $this->custom_copy($site_path, $dest . '/site');
		$succes[] = $this->custom_copy($media_path, $dest . '/media/' . $component);
		foreach ($succes as $row)
		{
			if (!$row)
			{
				echo "-> Custom copy failed";
				exit();
			}
		}
		mkdir($dest . '/language');
		mkdir($dest . '/language/fr-FR/');
		mkdir($dest . '/language/en-GB/');
		if ((!copy($fr_path, $dest . '/language/fr-FR/fr-FR.' . $component . '.ini')) || (!copy($en_path, $dest . '/language/en-GB/en-GB.' . $component . '.ini')))
		{
			echo "-> Language copy failed\n";
		}
		if (!copy($xml_path, $dest . '/' . $name[1] . '.xml'))
		{
			$xml_path = $site_path . $name[1] . '.xml';
			if (!copy($xml_path, $dest . '/' . $name[1] . '.xml'))
			{
				echo '-> Xml copy failed';
				exit();
			}
		}
	}


	/**
	 * Copy directory for component packaging
	 *
	 * @param $src
	 * @param $dst
	 *
	 * @return int
	 */
	public function custom_copy($src, $dst)
	{
		// open the source directory
		$dir        = opendir($src);
		$copy_count = 0;
		// Make the destination directory if not exist
		@mkdir($dst, 0777, true);

		// Loop through the files in source directory
		while ($file = readdir($dir)) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {

					// Recursively calling custom copy function
					// for sub directory
					$this->custom_copy($src . '/' . $file, $dst . '/' . $file);

				}
				else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
			$copy_count++;
		}
		closedir($dir);
		return $copy_count != null;
	}


	/**
	 * Create archive file for install and update the component
	 *
	 * @param $zipname
	 * @param $zipdir
	 * @param $component
	 *
	 * @return void
	 */
	public function zipComponent($zipname, $zipdir, $component)
	{

		// Create new zip class
		// Get real path for our folder
		$tmp      = JPATH_ROOT . '/tmp/' . $component;
		$rootPath = realpath($tmp);
		mkdir($zipdir);
		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($zipdir . $zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($tmp),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath     = $file->getRealPath();
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
	 * Delete all files in tmp folder
	 * @return void
	 */
	public function deleteTmp()
	{
		$path = JPATH_ROOT . '/tmp/';
		if (file_exists($path))
		{
			$dir   = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file)
			{
				$file->isDir() ? rmdir($file) : unlink($file);
			}
		}
        echo 'Tmp deletes';
	}


    public function initXml($element) {
    }


	public function doExecute()
	{
		$this->version  = "8.2.0";
		$this->filepath = JPATH_BASE . '/emundus-updates/';

        # Prepare update server repository (used for local instance)
		mkdir($this->filepath, 0777, true);
		mkdir($this->filepath . 'packages');
		touch($this->filepath . 'package_list.xml');

        # Load package xml
		$xml_path = JPATH_ADMINISTRATOR . "/components/com_emundus/pkg_emundus.xml";
		$data     = simplexml_load_file($xml_path);

        # Init list xml document
        $list_dom = new DOMDocument('1.0');
        $list_dom->encoding = 'utf-8';
        $list_dom->preserveWhiteSpace = false;
        $list_dom->formatOutput = true;
        $extensionset = $list_dom->createElement('extensionset');
        $list_dom->appendChild($extensionset);

        # For each line in package_list.xml, create update xml and add it to the update server
        foreach ($data->files as $obj)
		{
			foreach ($obj->file as $elem)
			{
                # Init updates xml document
                $update_dom = new DOMDocument("1.0");
                $update_dom->encoding = 'utf-8';
                $updates = $update_dom->createElement('updates');
                $update_dom->appendChild($updates);

				echo $elem['type'] . ": " . $elem['id'] . "\n";

                if ($elem['type'] == 'component') {
                    $sep            = "</extension>";
                    $sep_admin      = 'folder="admin">';
                    $updateserver   = "\t<updateservers>\n\t\t<server type='collection' name='eMundus'>http://localhost/emundus-updates/package_list.xml</server>\n\t</updateservers>\n";
                    $update         = "\n\t<update>\n\t\t<schemas>\n\t\t\t<schemapath type='mysql'>sql/updates/mysql</schemapath>\n\t\t</schemas>\n\t</update>\n\n";
                    $folder_admin   = "\t\t<folder>sql</folder>";
                    $this->addUpdateServer($elem['id'], $sep, $sep_admin, $updateserver, $update, $folder_admin);
                }

                # Fill updates xml
                $this->makeXml($updates, $elem, $file = 'updates', $update_dom);
                $update_dom->recover=true;
                $update_dom->save($this->filepath . '/packages/' . $elem['id'] . '/updates.xml');

                # Fill list xml
                $this->makeXml($extensionset, $elem, $file = 'list', $list_dom);
                $list_dom->save($this->filepath . "package_list.xml");

                # Copy and zip element
                $this->package($elem);
                $zipname = (string) $elem['id'] . "_" . $this->version . ".zip";
                $zipdir = JPATH_ROOT . '/emundus-updates/packages/' . $elem['id'] . '/';

                if ($elem['type'] == 'plugin') {
                    $zipname = (string) "plg_" . $elem['id'] . "_" . $this->version . ".zip";
                }

                try {
                    $this->zipComponent($zipname, $zipdir, (string) $elem['id']);
                } catch (Exception $e) {
                    echo "-> " .$elem['id'] . " zip failed \n";
                }
			}
		}
		$this->deleteTmp();
	}
}

JApplicationCli::getInstance('MakeUpdateServer')->execute();
