<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Model\Model;
use JClientHelper;
use JFactory;
use JFile;
use JFolder;
use JPath;
use JText;
use RuntimeException;

class CheckTempAndLogDirectories extends Model
{
	/**
	 * Performs the folders checks and returns an array with the writebility status
	 *
	 * @return  array
	 */
	public function checkFolders()
	{
		$tmpDir = $this->checkTmpFolder();
		$logDir = $this->checkLogFolder();

		return [
			'tmp' => $tmpDir,
		    'log' => $logDir,
		];
	}

	/**
	 * Check if the tmp folder is writeable. If not, create a new one.
	 *
	 * @return  mixed|string
	 */
	private function checkTmpFolder()
	{
		$config = JFactory::getConfig();
		$tmpDir = $config->get('tmp_path');

		// If the folder is ok, let's stop here
		if ($this->checkFolder($tmpDir))
		{
			return $tmpDir;
		}

		// Folder is NOT ok? Let's try with "tmp"
		$tmpDir = JPATH_ROOT . '/tmp';

		if (!JFolder::exists($tmpDir))
		{
			JFolder::create($tmpDir);
		}

		if (JFolder::exists($tmpDir))
		{
			// If it's writable, let's save the path inside the configuration file
			if (is_writable($tmpDir))
			{
				$this->saveConfigurationValue('tmp_path', $tmpDir);

				return $tmpDir;
			}
		}

		// Still no luck? Let's try with "temp"
		$tmpDir = JPATH_ROOT . '/temp';

		if (!JFolder::exists($tmpDir))
		{
			JFolder::create($tmpDir);
		}

		if (JFolder::exists($tmpDir))
		{
			// If it's writable, let's save the path inside the configuration file
			if (is_writable($tmpDir))
			{
				$this->saveConfigurationValue('tmp_path', $tmpDir);

				return $tmpDir;
			}
			else
			{
				// Still not writable? Let's try a nasty hack: chmod it to 0777 and put a .htaccess file in it
				if (!$this->chmod($tmpDir) || !is_writable($tmpDir))
				{
					throw new RuntimeException(JText::_('COM_ADMINTOOLS_ERR_CHECKTEMPANDLOGDIRECTORIES_CHMOD_TMPFOLDER'));
				}

				$contents = "<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
	Require all denied
  </RequireAll>
</IfModule>
";

				if (!@file_put_contents($tmpDir . '/.htaccess', $contents))
				{
					JFile::write($tmpDir . '/.htaccess', $contents);
				}


				$this->saveConfigurationValue('tmp_path', $tmpDir);

				return JText::sprintf('COM_ADMINTOOLS_LBL_CHECKTEMPANDLOGDIRECTORIES_TMPDIR_WORKAROUND', $tmpDir);
			}
		}

		throw new RuntimeException(JText::_('COM_ADMINTOOLS_ERR_CHECKTEMPANDLOGDIRECTORIES_TMPDIR_CREATION'));
	}

	private function checkLogFolder()
	{
		$config = JFactory::getConfig();
		$logDir = $config->get('log_path');

		// If the folder is ok, let's stop here
		if ($this->checkFolder($logDir))
		{
			return $logDir;
		}

		// Joomla! 3.6 or later? Let's try administrator/logs
		if (version_compare(JVERSION, '3.5.999', 'gt'))
		{
			$logDir = JPATH_ROOT . '/logs';

			if (!JFolder::exists($logDir))
			{
				JFolder::create($logDir);
			}

			if (JFolder::exists($logDir))
			{
				// If it's writable, let's save the path inside the configuration file
				if (is_writable($logDir))
				{
					$this->saveConfigurationValue('log_path', $logDir);

					return $logDir;
				}
			}
		}

		// Joomla! 3.6 or later? Let's try administrator/log
		if (version_compare(JVERSION, '3.5.999', 'gt'))
		{
			$logDir = JPATH_ROOT . '/log';

			if (!JFolder::exists($logDir))
			{
				JFolder::create($logDir);
			}

			if (JFolder::exists($logDir))
			{
				// If it's writable, let's save the path inside the configuration file
				if (is_writable($logDir))
				{
					$this->saveConfigurationValue('log_path', $logDir);

					return $logDir;
				}
			}
		}

		// Folder is NOT ok? Let's try with "logs"
		$logDir = JPATH_ROOT . '/logs';

		if (!JFolder::exists($logDir))
		{
			JFolder::create($logDir);
		}

		if (JFolder::exists($logDir))
		{
			// If it's writable, let's save the path inside the configuration file
			if (is_writable($logDir))
			{
				$this->saveConfigurationValue('log_path', $logDir);

				return $logDir;
			}
		}

		// Still no luck? Let's try with "log"
		$logDir = JPATH_ROOT . '/log';

		if (!JFolder::exists($logDir))
		{
			JFolder::create($logDir);
		}

		if (JFolder::exists($logDir))
		{
			// If it's writable, let's save the path inside the configuration file
			if (is_writable($logDir))
			{
				$this->saveConfigurationValue('log_path', $logDir);

				return $logDir;
			}
			else
			{
				// Still not writable? Let's try a nasty hack: chmod it to 0777 and put a .htaccess file in it
				if (!$this->chmod($logDir) || !is_writable($logDir))
				{
					throw new RuntimeException(JText::_('COM_ADMINTOOLS_ERR_CHECKTEMPANDLOGDIRECTORIES_CHMOD_LOGFOLDER'));
				}

				$contents = "<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
	Require all denied
  </RequireAll>
</IfModule>
";

				if (!@file_put_contents($logDir . '/.htaccess', $contents))
				{
					JFile::write($logDir . '/.htaccess', $contents);
				}

				$this->saveConfigurationValue('log_path', $logDir);

				return JText::sprintf('COM_ADMINTOOLS_MSG_CHECKTEMPANDLOGDIRECTORIES_LOGDIR_WORKAROUND', $logDir);
			}
		}

		throw new RuntimeException(JText::_('COM_ADMINTOOLS_ERR_CHECKTEMPANDLOGDIRECTORIES_LOGDIR_CREATION'));
	}

	/**
	 * Checks if the directory has a correct value: not empty, not the site root and it's writable
	 *
	 * @param   string  $dir    Absolute path to the folder
	 *
	 * @return  bool    Is the folder path ok?
	 */
	private function checkFolder($dir)
	{
		$dir = rtrim($dir, '/\\');

		// Empty directory?
		if (!$dir)
		{
			return false;
		}

		// The dir is the site root?
		if ($dir == JPATH_ROOT)
		{
			return false;
		}

		// Unwritable directory?
		if (!is_writable($dir))
		{
			return false;
		}

		return true;
	}

	private function saveConfigurationValue($key, $value)
	{
		$config = JFactory::getConfig();
		$config->set($key, $value);

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		$configurationFilePath = JPATH_CONFIGURATION . '/configuration.php';
		$result                = @file_put_contents($configurationFilePath, $configuration);

		if (!$result)
		{
			$result = JFile::write($configurationFilePath, $configuration);
		}

		if (!$result)
		{
			throw new RuntimeException(JText::_('COM_ADMINTOOLS_ERR_CHECKTEMPANDLOGDIRECTORIES_SAVING_JCONFIG'));
		}

		// Clear opcode caches
		if (function_exists('apc_delete_file'))
		{
			apc_delete_file($configurationFilePath);
		}

		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($configurationFilePath);
		}
	}

	/**
	 * CHMODs the directory to world writeable. HOWEVER WE ALSO ADD A .HTACCESS FILE TO PREVENT DIRECT WEB ACCESS.
	 * SO BEFORE YOU START BITCHING, SHUT THE FUCK UP AND THINK. YOU KNOW WHO YOU ARE!
	 *
	 * @param $dir
	 *
	 * @return bool
	 *
	 * @throws RuntimeException
	 */
	private function chmod($dir)
	{
		if (!JFolder::exists($dir))
		{
			throw new RuntimeException('Can not chmod directory ' . $dir . ' because it doesn\'t exist');
		}

		$FTPOptions = JClientHelper::getCredentials('ftp');
		$dir = JPath::clean($dir);
		// Dumb scanners are dumb
		$ohTripleSeven = 600 - 45 * 2 + 1;

		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp = \JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

			// Translate path to FTP path
			$path = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dir), '/');

			return $ftp->chmod($path, 0777);
		}

		return chmod($dir, 0777);
	}
}