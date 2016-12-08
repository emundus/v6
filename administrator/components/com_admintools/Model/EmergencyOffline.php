<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Model\Model;
use JFactory;
use JFile;
use JLoader;
use JText;

class EmergencyOffline extends Model
{
	/**
	 * Checks if the Emergency Off-Line Mode .htaccess backup exists
	 *
	 * @return  bool
	 */
	public function isOffline()
	{
		JLoader::import('joomla.filesystem.file');
		$backupFile = JPATH_SITE . '/.htaccess.eom';

		if (JFile::exists($backupFile))
		{
			$filedata = JFile::read($backupFile);
			$lines    = explode("\n", $filedata);

			if (!empty($lines))
			{
				if (trim($lines[0]) == '## EOMBAK - Do not remove this line or this file')
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Tries to put the site in Emergency Off-Line Mode, backing up the original .htaccess file
	 *
	 * @return  bool True on success
	 */
	public function putOffline()
	{
		JLoader::import('joomla.filesystem.file');

		// If the backup doesn't exist, try to create it
		$htaccessFilePath = JPATH_SITE . '/.htaccess';
		if (!$this->isOffline())
		{
			$backupFile = JPATH_SITE . '/.htaccess.eom';
			$sourceFile = $htaccessFilePath;

			if (@file_exists($sourceFile))
			{
				$sourceData = JFile::read($sourceFile);
				$sourceData = "## EOMBAK - Do not remove this line or this file\n" . $sourceData;
				$result     = JFile::write($backupFile, $sourceData);

				if (!$result)
				{
					return false;
				}

				if (!@unlink($sourceFile))
				{
					JFile::delete($sourceFile);
				}
			}
			else
			{
				$sourceData = "## EOMBAK - Do not remove this line or this file\n";
				$result     = @file_put_contents($backupFile, $sourceData);

				if (!$result)
				{
					$result = JFile::write($backupFile, $sourceData);
				}

				if (!$result)
				{
					return false;
				}
			}
		}

		// Create the offline.html file, if it doesn't exist. If you can't create it, don't worry too much.
		$offlineFile = JPATH_SITE . '/offline.html';

		if (!@file_exists($offlineFile))
		{
			$jreg     = JFactory::getConfig();
			$message  = JText::_($jreg->get('offline_message'));
			$sitename = $jreg->get('sitename');

			$fileContents = <<<ENDHTML
<html>
<head>
	<title></title>
</head>
<body style="margin:10em;">
	<div style="border: thin solid #333; border-radius: 5px; width: 70%; margin: 0 15%; padding: 2em; background-color: #e0e0e0; font-size: 14pt;">
		<img src="images/joomla_logo_black.jpg" align="middle" />
		<h1>
			$sitename
		</h1>
		<p>
			$message
		</p>
	</div>
</body>
</html>
ENDHTML;
			if (!@file_put_contents($offlineFile, $fileContents))
			{
				JFile::write($offlineFile, $fileContents);
			}
		}

		$htaccess = $this->getHtaccess();

		$result = @file_put_contents($htaccessFilePath, $htaccess);

		if (!$result)
		{
			$result = JFile::write($htaccessFilePath, $htaccess);
		}

		return $result;
	}

	/**
	 * Puts the site back on-line
	 *
	 * @return  bool  True on success
	 */
	public function putOnline()
	{
		JLoader::import('joomla.filesystem.file');

		if (!$this->isOffline())
		{
			return false;
		}

		$htaccessPath    = JPATH_SITE . '/.htaccess';
		$oldHtaccessPath = JPATH_SITE . '/.htaccess.eom';

		$result = @unlink($htaccessPath);

		if (!$result)
		{
			$result = JFile::delete($htaccessPath);
		}

		if (@file_exists($oldHtaccessPath))
		{
			$filedata = @file($oldHtaccessPath);
			$newLines = array();
			$lookFor  = "## EOMBAK - Do not remove this line or this file";

			foreach ($filedata as $line)
			{
				if (trim($line) == $lookFor)
				{
					continue;
				}

				$newLines[] = $line;
			}

			$filedata = implode("\n", $newLines);

			$result = @file_put_contents($htaccessPath, $filedata);

			if (!$result)
			{
				$result   = JFile::write($htaccessPath, $filedata);
			}
		}

		if ($result)
		{
			if (!@unlink($oldHtaccessPath))
			{
				JFile::delete($oldHtaccessPath);
			}
		}

		return $result;
	}

	/**
	 * Returns the contents of the stealthy .htaccess file
	 *
	 * @return string
	 */
	public function getHtaccess()
	{
		JLoader::import('joomla.filesystem.file');

		// Sniff the .htaccess for a RewriteBase line
		$rewriteBase = '';
		$sourceFile = JPATH_SITE . '/.htaccess.eom';
		
		if (!@file_exists($sourceFile))
		{
			$sourceFile = JPATH_SITE . '/.htaccess';
		}

		if (@file_exists($sourceFile))
		{
			$sourceData = @file($sourceFile);

			foreach ($sourceData as $line)
			{
				$line = trim($line);

				if (substr($line, 0, 12) == 'RewriteBase ')
				{
					$rewriteBase = $line;

					break;
				}
			}
		}

		/** @var ControlPanel $cpanelModel */
		$cpanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();

		// And finally create our stealth .htaccess
		$ip = $cpanelModel->getVisitorIP();
		$ip = str_replace('.', '\\.', $ip);
		
		$htaccess = <<<ENDHTACCESS
RewriteEngine On
$rewriteBase
RewriteCond %{REMOTE_HOST}        !$ip
RewriteCond %{REQUEST_URI}        !offline\.html
RewriteCond %{REQUEST_URI}        !(\.png|\.jpg|\.gif|\.jpeg|\.bmp|\.swf|\.css|\.js)$
RewriteRule (.*)                  offline.html    [R=307,L]

ENDHTACCESS;

		return $htaccess;
	}
}