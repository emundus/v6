<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use FOF40\Model\Model;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Client\FtpClient;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;

class CleanTempDirectory extends Model
{
	/** @var int Total numbers of folders in this site */
	public $totalFolders = 0;

	/** @var int Numbers of folders already processed */
	public $doneFolders = 0;

	/**
	 * Minimum age (in seconds) of files and folders to delete.
	 *
	 * Default: 60 seconds.
	 *
	 * @var   int
	 * @since 6.1.0
	 */
	private $minAge = 60;

	/** @var float The time the process started */
	private $startTime = null;

	/** @var array The folders to process */
	private $folderStack = [];

	/** @var array The files to process */
	private $filesStack = [];

	/**
	 * Scans $root for directories and updates $folderStack
	 *
	 * @param   string  $root  The full path of the directory to scan
	 */
	public function getDirectories($root = null)
	{
		$jreg       = $this->container->platform->getConfig();
		$tmpdir     = $jreg->get('tmp_path');
		$cutoffTime = PHP_INT_MAX;

		if (empty($root))
		{
			$root = $tmpdir;
		}

		if ($root === $tmpdir)
		{
			$cutoffTime = time() - $this->minAge;
		}

		$folders = Folder::folders($root, '.', false, true, []);

		if (empty($folders))
		{
			$folders = [];
		}

		$this->totalFolders += count($folders);

		// Filter folders by date
		$folders = array_filter($folders, function ($folder) use ($cutoffTime) {
			return (@filemtime($folder) ?: (@filemtime($folder . '/.') ?: 0)) < $cutoffTime;
		});

		if (count($folders))
		{
			foreach ($folders as $folder)
			{
				$this->getDirectories($folder);
				$this->getFiles($folder);

				$this->folderStack[] = $folder;
			}
		}
	}

	/**
	 * Scans $root for files and updates $filesStack
	 *
	 * @param   string  $root  The full path of the directory to scan
	 */
	public function getFiles($root = null)
	{
		$jreg       = $this->container->platform->getConfig();
		$tmpdir     = $jreg->get('tmp_path');
		$cutoffTime = PHP_INT_MAX;

		if (empty($root))
		{
			$root = $tmpdir;
		}

		if ($root === $tmpdir)
		{
			$cutoffTime = time() - $this->minAge;
		}

		if (empty($root))
		{
			return;
		}

		$root   = rtrim($root, '/');
		$tmpdir = rtrim($tmpdir, '/');

		$files = Folder::files($root, '.', false, true, [], [], true);

		if (empty($files))
		{
			$files = [];
		}

		// Filter files by modified date
		$files = array_filter($files, function ($file) use ($cutoffTime) {
			return (@filemtime($file) ?: 0) < $cutoffTime;
		});

		if ($root == $tmpdir)
		{
			foreach ($files as $file)
			{
				if (in_array(basename($file), ['index.html', 'index.htm', '.htaccess', 'web.config']))
				{
					continue;
				}

				$this->filesStack[] = $file;
			}
		}
		else
		{
			$this->filesStack = array_merge($this->filesStack, $files);
		}

		$this->totalFolders += count($files);
	}

	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getDirectories();
		$this->getFiles();

		if (empty($this->folderStack))
		{
			$this->folderStack = [];
		}

		if (empty($this->filesStack))
		{
			$this->filesStack = [];
		}

		asort($this->folderStack);
		asort($this->filesStack);

		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}

		return $this->run(false);
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

	/**
	 * Starts or resets the internal timer
	 */
	private function resetTimer()
	{
		$this->startTime = microtime(true);
	}

	/**
	 * Makes sure that no more than 3 seconds since the start of the timer have
	 * elapsed
	 *
	 * @return bool
	 */
	private function haveEnoughTime()
	{
		$now     = microtime(true);
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 2;
	}

	/**
	 * Saves the file/folder stack in the session
	 */
	private function saveStack()
	{
		$stack = [
			'folders' => $this->folderStack,
			'files'   => $this->filesStack,
			'total'   => $this->totalFolders,
			'done'    => $this->doneFolders,
		];
		$stack = json_encode($stack);

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzdeflate($stack, 9);
			}

			$stack = base64_encode($stack);
		}

		$this->container->platform->setSessionVar('cleantmp_stack', $stack, 'admintools');
	}

	/**
	 * Resets the file/folder stack saved in the session
	 */
	private function resetStack()
	{
		$this->container->platform->setSessionVar('cleantmp_stack', '', 'admintools');

		$this->folderStack  = [];
		$this->filesStack   = [];
		$this->totalFolders = 0;
		$this->doneFolders  = 0;
	}

	/**
	 * Loads the file/folder stack from the session
	 */
	private function loadStack()
	{
		$stack = $this->container->platform->getSessionVar('cleantmp_stack', '', 'admintools');

		if (empty($stack))
		{
			$this->folderStack  = [];
			$this->filesStack   = [];
			$this->totalFolders = 0;
			$this->doneFolders  = 0;

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

		$this->folderStack  = $stack['folders'];
		$this->filesStack   = $stack['files'];
		$this->totalFolders = $stack['total'];
		$this->doneFolders  = $stack['done'];
	}

	private function deletePath($path)
	{
		// Initialize variables
		$ftpOptions = ClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$n_path = @realpath($path);
		$path   = empty($n_path) ? $path : $n_path;

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp = FtpClient::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], [],
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
			$path = Path::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->delete($path);
		}
		else
		{
			return false;
		}

		return $ret;
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
