<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelFixpermsconfig extends F0FModel
{
	public function  __construct($config = array())
	{
		parent::__construct($config);

		$this->table = 'customperm';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('*'))
			->from($db->quoteName('#__admintools_customperms'));

		$fltPath = $this->getState('filter_path', null, 'string');
		if ($fltPath)
		{
			$fltPath = $fltPath . '%';
			$query->where($db->quoteName('path') . ' LIKE ' . $db->quote($fltPath));
		}

		$fltReason = $this->getState('reason', null, 'cmd');
		if ($fltReason)
		{
			$fltReason = '%' . $fltReason . '%';
			$query->where($db->quoteName('reason') . ' LIKE ' . $db->quote($fltReason));
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');
			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = 'id';
			}
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order . ' ' . $dir);
		}

		return $query;
	}

	public function saveDefaults()
	{
		$dirperms = $this->getState('dirperms');
		$fileperms = $this->getState('fileperms');

		$dirperms = octdec($dirperms);
		if (($dirperms < 0600) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}

		$fileperms = octdec($fileperms);
		if (($fileperms < 0600) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}

		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$params->setValue('dirperms', '0' . decoct($dirperms));
		$params->setValue('fileperms', '0' . decoct($fileperms));

		$params->save();
	}

	public function applyPath()
	{
		JLoader::import('joomla.filesystem.folder');

		// Get and clean up the path
		$path = $this->getState('path', '');
		$relpath = $this->getRelativePath($path);
		$this->setState('filter_path', $relpath);

		$this->getItemList(true);
	}

	public function getRelativePath($somepath)
	{
		$path = JPATH_ROOT . DIRECTORY_SEPARATOR . $somepath;
		$path = JPath::clean($path, '/');

		// Clean up the root
		$root = JPath::clean(JPATH_ROOT, '/');

		// Find the relative path and get the custom permissions
		$relpath = ltrim(substr($path, strlen($root)), '/');

		return $relpath;
	}

	public function getListing()
	{
		JLoader::import('joomla.filesystem.folder');
		$this->applyPath();

		$relpath = $this->getState('filter_path', '');
		$path = JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath;

		$folders_raw = JFolder::folders($path);
		$files_raw = JFolder::files($path);

		if (!empty($relpath))
		{
			$relpath .= '/';
		}

		$folders = array();
		if (!empty($folders_raw))
		{
			foreach ($folders_raw as $folder)
			{
				$perms = $this->getPerms($relpath . $folder);
				$currentperms = @fileperms(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $folder);
				$owneruser = function_exists('fileowner') ? fileowner(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $folder) : false;
				$ownergroup = function_exists('filegroup') ? filegroup(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $folder) : false;
				$folders[] = array(
					'item'      => $folder,
					'path'      => $relpath . $folder,
					'perms'     => $perms,
					'realperms' => $currentperms,
					'uid'       => $owneruser,
					'gid'       => $ownergroup
				);
			}
		}

		$files = array();
		if (!empty($files_raw))
		{
			foreach ($files_raw as $file)
			{
				$perms = $this->getPerms($relpath . $file);
				$currentperms = @fileperms(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $file);
				$owneruser = function_exists('fileowner') ? @fileowner(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $file) : false;
				$ownergroup = function_exists('filegroup') ? @filegroup(JPATH_ROOT . DIRECTORY_SEPARATOR . $relpath . $file) : false;
				$files[] = array(
					'item'      => $file,
					'path'      => $relpath . $file,
					'perms'     => $perms,
					'realperms' => $currentperms,
					'uid'       => $owneruser,
					'gid'       => $ownergroup
				);
			}
		}

		$crumbs = explode('/', $relpath);

		return array('folders' => $folders, 'files' => $files, 'crumbs' => $crumbs);
	}

	public function getPerms($path)
	{
		if (!empty($this->list))
		{
			foreach ($this->list as $item)
			{
				if ($item->path == $path)
				{
					return $item->perms;
				}
			}
		}

		return '';
	}

	public function savePermissions($apply = false)
	{
		if ($apply)
		{
			$fixmodel = F0FModel::getTmpInstance('Fixperms', 'AdmintoolsModel');
		}

		$db = $this->getDBO();
		$relpath = $this->getState('filter_path', '');

		if (!empty($relpath))
		{
			$path_esc = $db->escape($relpath);
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__admintools_customperms'))
				->where(
					$db->quoteName('path') . ' REGEXP ' .
					$db->quote('^' . $path_esc . '/[^/]*$')
				);
			$db->setQuery($query);
			$db->execute();
		}

		$folders = $this->getState('folders', array());
		if (!empty($folders))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__admintools_customperms'));

				$sqlparts = array();
				foreach ($folders as $folder => $perms)
				{
					$sqlparts[] = $db->Quote($folder);
				}

				$query->where($db->quoteName('path') . ' IN (' . implode(', ', $sqlparts) . ')');
				$db->setQuery($query);
				$db->execute();
			}

			$sqlparts = array();
			foreach ($folders as $folder => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->Quote($folder) . ', ' . $db->Quote($perms);
					if ($apply)
					{
						$fixmodel->chmod(JPATH_ROOT . DIRECTORY_SEPARATOR . $folder, $perms);
					}
				}
			}
			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__admintools_customperms'))
					->columns(array(
						$db->quoteName('path'),
						$db->quoteName('perms')
					))->values($sqlparts);
				$db->setQuery($query);
				$db->execute();
			}
		}

		$files = $this->getState('files', array());
		if (!empty($files))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__admintools_customperms'));

				$sqlparts = array();
				foreach ($files as $file => $perms)
				{
					$sqlparts[] = $db->Quote($file);
				}

				$query->where($db->quoteName('path') . ' IN (' . implode(', ', $sqlparts) . ')');
				$db->setQuery($query);
				$db->execute();
			}

			$sqlparts = array();
			foreach ($files as $file => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->Quote($file) . ', ' . $db->Quote($perms);
					if ($apply)
					{
						$fixmodel->chmod(JPATH_ROOT . DIRECTORY_SEPARATOR . $file, $perms);
					}
				}
			}
			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__admintools_customperms'))
					->columns(array(
						$db->quoteName('path'),
						$db->quoteName('perms')
					))->values($sqlparts);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
}