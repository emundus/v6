<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelTmplogcheck extends F0FModel
{
	public function checkFolders()
	{
		$tmpDir = $this->checkTmpFolder();
		$logDir = $this->checkLogFolder();

		return array(
			'<strong>' . JText::_('COM_ADMINTOOLS_TEMP_PATH') . '</strong>: ' . $tmpDir,
			'<strong>' . JText::_('COM_ADMINTOOLS_LOG_PATH') . '</strong>: ' . $logDir
		);
	}

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
					throw new Exception(JText::_('COM_ADMINTOOLS_ERR_CHMOD_TMPFOLDER'));
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

				JFile::write($tmpDir . '/.htaccess', $contents);

				$this->saveConfigurationValue('tmp_path', $tmpDir);

				return JText::sprintf('COM_ADMINTOOLS_TMPDIR_WORKAROUND', $tmpDir);
			}
		}

		throw new Exception(JText::_('COM_ADMINTOOLS_ERR_TMPDIR_CREATION'));
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
					throw new Exception(JText::_('COM_ADMINTOOLS_ERR_CHMOD_LOGFOLDER'));
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

				JFile::write($logDir . '/.htaccess', $contents);

				$this->saveConfigurationValue('log_path', $logDir);

				return JText::sprintf('COM_ADMINTOOLS_LOGDIR_WORKAROUND', $logDir);
			}
		}

		throw new Exception(JText::_('COM_ADMINTOOLS_ERR_LOGDIR_CREATION'));
	}

	/**
	 * Checks if the directory has a correct value: not empty, not the site root and it's writable
	 *
	 * @param   string $dir Absolute path to the folder
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

		if (!JFile::write(JPATH_CONFIGURATION . '/configuration.php', $configuration))
		{
			throw new Exception(JText::_('COM_ADMINTOOLS_ERR_SAVING_JCONFIG'));
		}
	}

	/**
	 * Chmods the directory to 0777
	 *
	 * @param $dir
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	private function chmod($dir)
	{
		if (!JFolder::exists($dir))
		{
			throw new Exception('Can not chmod directory ' . $dir . ' because it doesn\'t exist');
		}

		$FTPOptions = JClientHelper::getCredentials('ftp');
		$pathObject = new JFilesystemWrapperPath;

		$dir = $pathObject->clean($dir);

		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp =
				JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

			// Translate path to FTP path
			$path = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dir), '/');

			return $ftp->chmod($path, 0777);
		}
		else
		{
			return chmod($dir, 0777);
		}
	}
}