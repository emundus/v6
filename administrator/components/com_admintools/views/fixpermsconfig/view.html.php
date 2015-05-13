<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewFixpermsconfig extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		// Default permissions
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$dirperms = '0' . ltrim(trim($params->getValue('dirperms', '0755')), '0');
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
		$model = $this->getModel();
		$listing = $model->getListing();
		$this->listing = $listing;

		$relpath = $model->getState('filter_path', '');
		$this->path = $relpath;
	}
}