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
use FOF30\Model\DataModel;
use JFolder;
use JLoader;
use JPath;

/**
 * Class ConfigureFixPermissions
 *
 * @property   string  $path
 * @property   string  $perms
 *
 * @method  $this  filter_path()  filter_path(string $v)
 * @method  $this  perms()  perms(string $v)
 */
class ConfigureFixPermissions extends DataModel
{
	public function __construct(Container $container, array $config)
	{
		$config['tableName']   = '#__admintools_customperms';
		$config['idFieldName'] = 'id';

		parent::__construct($container, $config);
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = parent::buildQuery($overrideLimits);

		$fltPath = $this->getState('filter_path', null, 'string');

		if ($fltPath)
		{
			$fltPath = $fltPath . '%';
			$query->where($db->qn('path') . ' LIKE ' . $db->q($fltPath));
		}

		$fltPerms = $this->getState('perms', null, 'cmd');

		if ($fltPerms)
		{
			$query->where($db->qn('perms') . ' = ' . $db->q($fltPerms));
		}

		return $query;
	}

	public function saveDefaults()
	{
		$dirperms  = $this->getState('dirperms');
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

		$params = Storage::getInstance();

		$params->setValue('dirperms', '0' . decoct($dirperms));
		$params->setValue('fileperms', '0' . decoct($fileperms));
		$params->setValue('perms_show_hidden', $this->getState('perms_show_hidden', 0));

		$params->save();
	}

	public function applyPath()
	{
		\JLoader::import('joomla.filesystem.folder');

		// Get and clean up the path
		$path    = $this->getState('path', '');
		$relpath = $this->getRelativePath($path);

		$this->setState('filter_path', $relpath);

		$this->list = $this->getRawDataArray(0, 0, true);
	}

	public function getRelativePath($somepath)
	{
		$path = JPATH_ROOT . '/' . $somepath;
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
		$path = JPATH_ROOT . '/' . $relpath;

		$folders_raw = JFolder::folders($path);

		$params = Storage::getInstance();

		$excludeFilter = $params->getValue('perms_show_hidden', 0) ? array('.*~') : array('^\..*', '.*~');
		$files_raw     = JFolder::files($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter);

		if (!empty($relpath))
		{
			$relpath .= '/';
		}

		$folders = array();

		if (!empty($folders_raw))
		{
			foreach ($folders_raw as $folder)
			{
				$perms        = $this->getPerms($relpath . $folder);
				$currentperms = @fileperms(JPATH_ROOT . '/' . $relpath . $folder);
				$owneruser    = function_exists('fileowner') ? fileowner(JPATH_ROOT . '/' . $relpath . $folder) : false;
				$ownergroup   = function_exists('filegroup') ? filegroup(JPATH_ROOT . '/' . $relpath . $folder) : false;

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
				$perms        = $this->getPerms($relpath . $file);
				$currentperms = @fileperms(JPATH_ROOT . '/' . $relpath . $file);
				$owneruser    = function_exists('fileowner') ? @fileowner(JPATH_ROOT . '/' . $relpath . $file) : false;
				$ownergroup   = function_exists('filegroup') ? @filegroup(JPATH_ROOT . '/' . $relpath . $file) : false;

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
		if (count($this->list))
		{
			foreach ($this->list as $item)
			{
				if ($item['path'] == $path)
				{
					return $item['perms'];
				}
			}
		}
		return '';
	}

	public function savePermissions($apply = false)
	{
		if ($apply)
		{
			/** @var FixPermissions $fixmodel */
			$fixmodel = $this->container->factory->model('FixPermissions')->tmpInstance();
		}

		$db = $this->getDbo();
		$relpath = $this->getState('filter_path', '');

		if (!empty($relpath))
		{
			$path_esc = $db->escape($relpath);
			$query = $db->getQuery(true)
				->delete($db->qn('#__admintools_customperms'))
				->where(
					$db->qn('path') . ' REGEXP ' .
					$db->q('^' . $path_esc . '/[^/]*$')
				);

			$db->setQuery($query)->execute();
		}

		$folders = $this->getState('folders', array());

		if (!empty($folders))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_customperms'));

				$sqlparts = array();
				foreach ($folders as $folder => $perms)
				{
					$sqlparts[] = $db->q($folder);
				}

				$query->where($db->qn('path') . ' IN (' . implode(', ', $sqlparts) . ')');

				$db->setQuery($query)->execute();
			}

			$sqlparts = array();

			foreach ($folders as $folder => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->q($folder) . ', ' . $db->q($perms);

					if ($apply)
					{
						$fixmodel->chmod(JPATH_ROOT . '/' . $folder, $perms);
					}
				}
			}

			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__admintools_customperms'))
					->columns(array(
						$db->qn('path'),
						$db->qn('perms')
					))->values($sqlparts);

				$db->setQuery($query)->execute();
			}
		}

		$files = $this->getState('files', array());

		if (!empty($files))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_customperms'));

				$sqlparts = array();
				foreach ($files as $file => $perms)
				{
					$sqlparts[] = $db->q($file);
				}

				$query->where($db->qn('path') . ' IN (' . implode(', ', $sqlparts) . ')');

				$db->setQuery($query)->execute();
			}

			$sqlparts = array();

			foreach ($files as $file => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->q($file) . ', ' . $db->q($perms);

					if ($apply)
					{
						$fixmodel->chmod(JPATH_ROOT . '/' . $file, $perms);
					}
				}
			}

			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__admintools_customperms'))
					->columns(array(
						$db->qn('path'),
						$db->qn('perms')
					))->values($sqlparts);

				$db->setQuery($query)->execute();
			}
		}
	}
}