<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF30\Container\Container;
use FOF30\Model\Model;
use JFactory;
use JFolder;
use JLoader;
use JPath;

class FixPermissions extends Model
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

	/** @var int Default directory permissions */
	private $dirperms = 0755;

	/** @var int Default file permissions */
	private $fileperms = 0644;

	/** @var array Custom permissions */
	private $customperms = array();

	/** @var array Skip subdirectories and files of these directories */
	private $skipDirs = array();

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$params = Storage::getInstance();

		$dirperms  = '0' . ltrim(trim($params->getValue('dirperms', '0755')), '0');
		$fileperms = '0' . ltrim(trim($params->getValue('fileperms', '0644')), '0');

		$dirperms = octdec($dirperms);
		if (($dirperms < 0400) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}
		$this->dirperms = $dirperms;

		$fileperms = octdec($fileperms);
		if (($fileperms < 0400) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}
		$this->fileperms = $fileperms;

		$db = $this->container->db;
		$query = $db->getQuery(true)
			->select(array(
				$db->qn('path'),
				$db->qn('perms')
			))->from($db->qn('#__admintools_customperms'))
			->order($db->qn('path') . ' ASC');

		$this->customperms = $db->setQuery($query)->loadAssocList('path');

		// Add cache, tmp and log to the exceptions
		$this->skipDirs[] = rtrim(JPATH_CACHE, '/');
		$this->skipDirs[] = rtrim(JPATH_ROOT . '/cache', '/');
		$this->skipDirs[] = rtrim(JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp'), '/');
		$this->skipDirs[] = rtrim(JFactory::getConfig()->get('log_path', JPATH_ROOT . '/logs'), '/');
		$this->skipDirs[] = JPATH_ADMINISTRATOR . '/logs';
		$this->skipDirs[] = JPATH_ADMINISTRATOR . '/log';
		$this->skipDirs[] = JPATH_ROOT . '/logs';
		$this->skipDirs[] = JPATH_ROOT . '/log';
	}

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
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q('fixperms_stack'));

		$db->setQuery($query)->execute();

		$object = (object)array(
			'key'   => 'fixperms_stack',
			'value' => json_encode(array(
				'folders' => $this->folderStack,
				'files'   => $this->filesStack,
				'total'   => $this->totalFolders,
				'done'    => $this->doneFolders
			))
		);

		$db->insertObject('#__admintools_storage', $object);
	}

	/**
	 * Resets the file/folder stack saved in the session
	 */
	private function resetStack()
	{
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q('fixperms_stack'));

		$db->setQuery($query)->execute();

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
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->select(array($db->qn('value')))
			->from($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q('fixperms_stack'));

		$stack = $db->setQuery($query)->loadResult();

		if (empty($stack))
		{
			$this->folderStack = array();
			$this->filesStack = array();
			$this->totalFolders = 0;
			$this->doneFolders = 0;

			return;
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
		if (empty($root))
		{
			$root = JPATH_ROOT;
		}

		JLoader::import('joomla.filesystem.folder');

		if (in_array(rtrim($root, '/'), $this->skipDirs))
		{
			return;
		}

		$folders = JFolder::folders($root, '.', false, true);
		$this->totalFolders += count($folders);

		if (!empty($folders))
		{
			$this->folderStack = array_merge($this->folderStack, $folders);
		}
	}

	/**
	 * Scans $root for files and updates $filesStack
	 *
	 * @param string $root The full path of the directory to scan
	 */
	public function getFiles($root = null)
	{
		if (empty($root))
		{
			$root = JPATH_ROOT;
		}

		if (empty($root))
		{
			$root = '..';
			$root = realpath($root);
		}

		if (in_array(rtrim($root, '/'), $this->skipDirs))
		{
			return;
		}

		$root = rtrim($root, '/') . '/';

		JLoader::import('joomla.filesystem.folder');

		// Should I include dot files, too?
		$params = Storage::getInstance();

		$excludeFilter = $params->getValue('perms_show_hidden', 0) ? array('.*~') : array('^\..*', '.*~');

		$folders = JFolder::files($root, '.', false, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter);
		$this->filesStack = array_merge($this->filesStack, $folders);

		$this->totalFolders += count($folders);
	}

	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getDirectories();
		$this->getFiles();
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

	public function chmod($path, $mode)
	{
		if (is_string($mode))
		{
			$mode = octdec($mode);

			if (($mode <= 0) || ($mode > 0777))
			{
				$mode = 0755;
			}
		}

		// Initialize variables
		JLoader::import('joomla.client.helper');
		$ftpOptions = \JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			JLoader::import('joomla.client.ftp');

			$ftp = \JClientFtp::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], array(),
				$ftpOptions['user'], $ftpOptions['pass']
			);
		}

		if (@chmod($path, $mode))
		{
			$ret = true;
		}
		elseif ($ftpOptions['enabled'] == 1)
		{
			// Translate path and delete
			JLoader::import('joomla.client.ftp');

			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');

			// FTP connector throws an error
			$ret = $ftp->chmod($path, $mode);
		}
		else
		{
			return false;
		}
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
		while (empty($this->filesStack) && !empty($this->folderStack))
		{
			// Get a directory
			$dir = null;

			while (empty($dir) && !empty($this->folderStack))
			{
				// Get the next directory
				$dir = array_shift($this->folderStack);

				// Skip over non-directories and symlinks
				if (!@is_dir($dir) || @is_link($dir))
				{
					$dir = null;
					continue;
				}
				// Skip over . and ..
				$checkDir = str_replace('\\', '/', $dir);

				if (in_array(basename($checkDir), array('.', '..')) || (substr($checkDir, -2) == '/.') || (substr($checkDir, -3) == '/..'))
				{
					$dir = null;
					continue;
				}

				// Check for custom permissions
				$reldir = $this->getRelativePath($dir);

				if (array_key_exists($reldir, $this->customperms))
				{
					$perms = $this->customperms[$reldir]['perms'];
				}
				else
				{
					$perms = $this->dirperms;
				}

				// Apply new permissions
				$this->chmod($dir, $perms);
				$this->doneFolders++;
				$this->getDirectories($dir);
				$this->getFiles($dir);

				if (!$this->haveEnoughTime())
				{
					// Gotta continue in the next step
					return true;
				}
			}
		}

		if (empty($this->filesStack) && empty($this->folderStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		if (!empty($this->filesStack) && $this->haveEnoughTime())
		{
			while (!empty($this->filesStack))
			{
				$file = array_shift($this->filesStack);

				// Skip over symlinks and non-files
				if (@is_link($file) || !@is_file($file))
				{
					continue;
				}

				$reldir = $this->getRelativePath($file);

				if (array_key_exists($reldir, $this->customperms))
				{
					$perms = $this->customperms[$reldir]['perms'];
				}
				else
				{
					$perms = $this->fileperms;
				}

				$this->chmod($file, $perms);
				$this->doneFolders++;
			}
		}

		if (empty($this->filesStack) && empty($this->folderStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		return true;
	}

	public function getRelativePath($somepath)
	{
		$path = JPath::clean($somepath, '/');

		// Clean up the root
		$root = JPath::clean(JPATH_ROOT, '/');

		// Find the relative path and get the custom permissions
		$relpath = ltrim(substr($path, strlen($root)), '/');

		return $relpath;
	}
}