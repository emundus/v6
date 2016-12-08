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
use JFolder;
use JLoader;

class CleanTempDirectory extends Model
{
	/** @var float The time the process started */
	private $startTime = null;

	/** @var array The folders to process */
	private $folderStack = array();

	/** @var array The files to process */
	private $filesStack = array();

	/** @var int Total numbers of folders in this site */
	public $totalFolders = 0;

	/** @var int Numbers of folders already processed */
	public $doneFolders = 0;

	/**
	 * Returns the current timestampt in decimal seconds
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float)$usec + (float)$sec);
	}

	/**
	 * Starts or resets the internal timer
	 */
	private function resetTimer()
	{
		$this->startTime = $this->microtime_float();
	}

	/**
	 * Makes sure that no more than 3 seconds since the start of the timer have
	 * elapsed
	 *
	 * @return bool
	 */
	private function haveEnoughTime()
	{
		$now = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 2;
	}

	/**
	 * Saves the file/folder stack in the session
	 */
	private function saveStack()
	{
		$stack = array(
			'folders' => $this->folderStack,
			'files'   => $this->filesStack,
			'total'   => $this->totalFolders,
			'done'    => $this->doneFolders
		);
		$stack = json_encode($stack);
		
		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzdeflate($stack, 9);
			}
			
			$stack = base64_encode($stack);
		}
		
		$session = JFactory::getSession();
		$session->set('cleantmp_stack', $stack, 'admintools');
	}

	/**
	 * Resets the file/folder stack saved in the session
	 */
	private function resetStack()
	{
		$session = JFactory::getSession();
		$session->set('cleantmp_stack', '', 'admintools');
		
		$this->folderStack = array();
		$this->filesStack = array();
		$this->totalFolders = 0;
		$this->doneFolders = 0;
	}

	/**
	 * Loads the file/folder stack from the session
	 */
	private function loadStack()
	{
		$session = JFactory::getSession();
		$stack = $session->get('cleantmp_stack', '', 'admintools');

		if (empty($stack))
		{
			$this->folderStack = array();
			$this->filesStack = array();
			$this->totalFolders = 0;
			$this->doneFolders = 0;

			return;
		}

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			$stack = base64_decode($stack);
			
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzinflate($stack);
			}
		}
		
		$stack = json_decode($stack, true);

		$this->folderStack = $stack['folders'];
		$this->filesStack = $stack['files'];
		$this->totalFolders = $stack['total'];
		$this->doneFolders = $stack['done'];
	}

	/**
	 * Scans $root for directories and updates $folderStack
	 *
	 * @param string $root The full path of the directory to scan
	 */
	public function getDirectories($root = null)
	{
		$jreg = JFactory::getConfig();
		$tmpdir = $jreg->get('tmp_path');

		if (empty($root))
		{
			$root = $tmpdir;
		}
		
		JLoader::import('joomla.filesystem.folder');

		$folders = JFolder::folders($root, '.', false, true, array());
		
		if (empty($folders))
		{
			$folders = array();
		}
		
		$this->totalFolders += count($folders);

		if (count($folders))
		{
			foreach ($folders as $folder)
			{
				$this->getDirectories($folder);
				$this->getFiles($folder);

				$this->folderStack = array_merge($this->folderStack, $folders);
			}
		}
	}

	/**
	 * Scans $root for files and updates $filesStack
	 *
	 * @param string $root The full path of the directory to scan
	 */
	public function getFiles($root = null)
	{
		$jreg   = JFactory::getConfig();
		$tmpdir = $jreg->get('tmp_path');

		if (empty($root))
		{
			$root = $tmpdir;
		}

		if (empty($root))
		{
			return;
		}

		$root   = rtrim($root, '/');
		$tmpdir = rtrim($tmpdir, '/');

		JLoader::import('joomla.filesystem.folder');

		$folders = JFolder::files($root, '.', false, true, array(), array(), true);
		
		if (empty($folders))
		{
			$folders = array();
		}

		if ($root == $tmpdir)
		{
			if (count($folders))
			{
				foreach ($folders as $folder)
				{
					if (basename($folder) == 'index.html')
					{
						continue;
					}
					if (basename($folder) == '.htaccess')
					{
						continue;
					}
					
					$this->filesStack[] = $folder;
				}
			}
		}
		else
		{
			$this->filesStack = array_merge($this->filesStack, $folders);
		}

		$this->totalFolders += count($folders);
	}

	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getDirectories();
		$this->getFiles();
		
		if (empty($this->folderStack))
		{
			$this->folderStack = array();
		}
		
		if (empty($this->filesStack))
		{
			$this->filesStack = array();
		}
		
		asort($this->folderStack);
		asort($this->filesStack);

		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}
		else
		{
			return $this->run(false);
		}
	}

	private function deletePath($path)
	{
		// Initialize variables
		JLoader::import('joomla.client.helper');
		$ftpOptions = \JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$n_path = @realpath($path);
		$path   = empty($n_path) ? $path : $n_path;

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp = \JClientFtp::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], array(),
				$ftpOptions['user'], $ftpOptions['pass']
			);
		}

		if (@unlink($path))
		{
			$ret = true;
		}
		elseif (@rmdir($path))
		{
			$ret = true;
		}
		elseif ($ftpOptions['enabled'] == 1)
		{
			if (substr($path, 0, strlen(JPATH_ROOT)) !== JPATH_ROOT)
			{
				return false;
			}
			// Translate path and delete
			$path = \JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->delete($path);
		}
		else
		{
			return false;
		}

		return $ret;
	}

	public function run($resetTimer = true)
	{
		if ($resetTimer)
		{
			$this->resetTimer();
		}

		$this->loadStack();

		$result = true;
		while ($result && $this->haveEnoughTime())
		{
			$result = $this->RealRun();
		}

		$this->saveStack();

		return $result;
	}

	private function RealRun()
	{
		if (!empty($this->filesStack))
		{
			while (!empty($this->filesStack) && $this->haveEnoughTime())
			{
				$file = array_pop($this->filesStack);
				$this->doneFolders++;
				$this->deletePath($file);
			}
		}

		if (empty($this->filesStack) && !empty($this->folderStack))
		{
			while (!empty($this->folderStack) && $this->haveEnoughTime())
			{
				$folder = array_pop($this->folderStack);
				$this->doneFolders++;
				$this->deletePath($folder);
			}
		}

		if (empty($this->filesStack) && empty($this->folderStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		// If we have more folders or files, continue in the next step
		return true;
	}
}