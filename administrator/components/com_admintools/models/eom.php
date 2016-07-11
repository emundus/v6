<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

/**
 * Emergency Off-Line Mode
 *
 * @author nicholas
 */
class AdmintoolsModelEom extends F0FModel
{

	/**
	 * Returns the current client's IP
	 *
	 * @return string
	 */
	public function getMyIP()
	{
		static $ip = null;

		if (empty($ip))
		{
			$ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
			if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
			{
				$ip = substr($ip, strrpos($ip, ':') + 1);
			}
		}

		return $ip;
	}

	/**
	 * Checks if the Emergency Off-Line Mode .htaccess backup exists
	 *
	 * @return bool
	 */
	public function isOffline()
	{
		JLoader::import('joomla.filesystem.file');
		$backupFile = JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom';
		if (JFile::exists($backupFile))
		{
			$filedata = JFile::read($backupFile);
			$lines = explode("\n", $filedata);
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
	 * @return bool True on success
	 */
	public function putOffline()
	{
		JLoader::import('joomla.filesystem.file');

		// If the backup doesn't exist, try to create it
		if (!$this->isOffline())
		{
			$backupFile = JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom';
			$sourceFile = JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess';

			if (JFile::exists($sourceFile))
			{
				$sourceData = JFile::read($sourceFile);
				$sourceData = "## EOMBAK - Do not remove this line or this file\n" . $sourceData;
				$result = JFile::write($backupFile, $sourceData);
				if (!$result)
				{
					return false;
				}
				JFile::delete($sourceFile);
			}
			else
			{
				$sourceData = "## EOMBAK - Do not remove this line or this file\n";
				$result = JFile::write($backupFile, $sourceData);
				if (!$result)
				{
					return false;
				}
			}
		}

		// Create the offline.html file, if it doesn't exist. If you can't create it, don't worry too much.
		$offlineFile = JPATH_SITE . DIRECTORY_SEPARATOR . 'offline.html';
		if (!JFile::exists($offlineFile))
		{
			$jreg = JFactory::getConfig();
			$message = JText::_($jreg->get('offline_message'));

			$app = JFactory::getApplication();
			$sitename = $app->getCfg('sitename');

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
			JFile::write($offlineFile, $fileContents);
		}

		$htaccess = $this->getHtaccess();

		return JFile::write(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess', $htaccess);
	}

	/**
	 * Puts the site back on-line
	 *
	 * @return bool True on success
	 */
	public function putOnline()
	{
		JLoader::import('joomla.filesystem.file');
		if (!$this->isOffline())
		{
			return false;
		}

		$result = JFile::delete(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess');

		if (JFile::exists(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom'))
		{
			$filedata = JFile::read(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom');
			$filedata = explode("\n", $filedata);
			$newLines = array();
			$lookFor = "## EOMBAK - Do not remove this line or this file";
			foreach ($filedata as $line)
			{
				if (trim($line) == $lookFor)
				{
					continue;
				}
				$newLines[] = $line;
			}
			$filedata = implode("\n", $newLines);
			$result = JFile::write(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess', $filedata);
		}

		if ($result)
		{
			JFile::delete(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom');
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
		$sourceFile = JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.eom';
		if (!JFile::exists($sourceFile))
		{
			$sourceFile = JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess';
		}

		if (JFile::exists($sourceFile))
		{
			$sourceData = JFile::read($sourceFile);
			$sourceData = explode("\n", $sourceData);
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

		// And finally create our übercool stealth .htaccess
		$ip = $this->getMyIP();
		$ip = str_replace('.', '\\.', $ip);
		$htaccess = <<<ENDHTACCESS
RewriteEngine On
$rewriteBase
RewriteCond %{REMOTE_HOST}        !$ip
RewriteCond %{REQUEST_URI}        !offline\.html
RewriteCond %{REQUEST_URI}        !(\.png|\.jpg|\.gif|\.jpeg|\.bmp|\.swf|\.css|\.js)$
RewriteRule (.*)                offline.html    [R=307,L]

ENDHTACCESS;

		return $htaccess;
	}
}