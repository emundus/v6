<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\ConfigureFixPermissions;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions;
use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * Default permissions for directories
	 *
	 * @var  string
	 */
	public $dirperms;

	/**
	 * Default permissions for files
	 *
	 * @var  string
	 */
	public $fileperms;

	/**
	 * Filesystem listing
	 *
	 * @var  array
	 */
	public $listing;

	/**
	 * Current path
	 *
	 * @var  string
	 */
	public $at_path;

	/**
	 * Should I display hidden (dot) files?
	 *
	 * @var bool
	 */
	public $perms_show_hidden;

	protected function onBeforeBrowse()
	{
		// Default permissions
		$params = Storage::getInstance();

		$dirperms  = '0' . ltrim(trim($params->getValue('dirperms', '0755')), '0');
		$fileperms = '0' . ltrim(trim($params->getValue('fileperms', '0644')), '0');

		$dirperms = octdec($dirperms);

		if (($dirperms < 0600) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}

		$this->dirperms = '0' . decoct($dirperms);

		$fileperms = octdec($fileperms);

		if (($fileperms < 0600) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}

		$this->fileperms = '0' . decoct($fileperms);

		// File lists
		/** @var ConfigureFixPermissions $model */
		$model = $this->getModel();
		$listing = $model->getListing();
		$this->listing = $listing;

		$relpath = $model->getState('filter_path', '');
		$this->at_path = $relpath;

		$this->perms_show_hidden = $params->getValue('perms_show_hidden', 0);
	}

	protected function renderPermissions($perms)
	{
		if ($perms === false)
		{
			return '&mdash;';
		}

		return decoct($perms & 0777);
	}

	protected function renderUGID($uid, $gid)
	{
		static $users = array();
		static $groups = array();

		$user = '&mdash;';
		$group = '&mdash;';

		if ($uid !== false)
		{
			if (!array_key_exists($uid, $users))
			{
				$users[$uid] = $uid;

				if (function_exists('posix_getpwuid'))
				{
					$uArray = posix_getpwuid($uid);
					$users[$uid] = $uArray['name']; //." ($uid)";
				}
			}

			$user = $users[$uid];
		}

		if ($gid !== false)
		{
			if (!array_key_exists($gid, $groups))
			{
				$groups[$gid] = $gid;

				if (function_exists('posix_getgrgid'))
				{
					$gArray = posix_getgrgid($gid);
					$groups[$gid] = $gArray['name']; //." ($gid)";
				}
			}

			$group = $groups[$gid];
		}

		return "$user:$group";
	}
}