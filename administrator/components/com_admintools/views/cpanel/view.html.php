<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewCpanel extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		// Is this the Professional release?
		JLoader::import('joomla.filesystem.file');
		$isPro = (ADMINTOOLS_PRO == 1);

		$this->isPro = $isPro;

		// Should we show the stats and graphs?
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.application.component.helper');

		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_admintools'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		$params->loadString($rawparams, 'JSON');

		$this->showstats = $params->get('showstats', 1);

		// Load the models
		/** @var AdmintoolsModelCpanels $model */
		/** @var AdmintoolsModelStats $statsModel */
		$model = $this->getModel();
		$adminpwmodel = F0FModel::getAnInstance('Adminpw', 'AdmintoolsModel');
		$mpModel = F0FModel::getAnInstance('Masterpw', 'AdmintoolsModel');
		$geoModel = F0FModel::getAnInstance('Geoblock', 'AdmintoolsModel');
		$statsModel = F0FModel::getAnInstance('Stats', 'AdmintoolsModel');

		/** Reorder the Admin Tools plugin */
		$model->reorderPlugin();

		// Decide on the administrator password padlock icon
		$adminlocked = $adminpwmodel->isLocked();
		$this->adminLocked = $adminlocked;

		// Do we have to show a master password box?
		$this->hasValidPassword = $mpModel->hasValidPassword();

		// Is this MySQL?
		$dbType = JFactory::getDbo()->name;
		$isMySQL = strpos($dbType, 'mysql') !== false;

		// If the user doesn't have a valid master pw for some views, don't show
		// the buttons.
		$this->enable_cleantmp = $mpModel->accessAllowed('cleantmp');
		$this->enable_fixperms = $mpModel->accessAllowed('fixperms');
		$this->enable_purgesessions = $mpModel->accessAllowed('purgesessions');
		$this->enable_dbtools = $mpModel->accessAllowed('dbtools');

		$this->isMySQL = $isMySQL;

		$this->pluginid = $model->getPluginID();

		$this->hasplugin = $geoModel->hasGeoIPPlugin();
		$this->pluginNeedsUpdate = $geoModel->dbNeedsUpdate();

		// Is this a very old version? If it's older than 90 days let's warn the user
		$this->oldVersion = false;
		$relDate = new JDate(ADMINTOOLS_DATE);
		$interval = time() - $relDate->toUnix();

		if ($interval > (60 * 60 * 24 * 90))
		{
			$this->oldVersion = true;
		}

		$this->loadHelper('servertech');

		// Is .htaccess Maker supported?
		$this->htMakerSupported = AdmintoolsHelperServertech::isHtaccessSupported();

		// Is NginX Configuration Maker supported?
		$this->nginxMakerSupported = AdmintoolsHelperServertech::isNginxSupported();

		// Collect information about the site
        $this->statsIframe = F0FModel::getTmpInstance('Stats', 'AdmintoolsModel')->collectStatistics(true);

		// Post-installation messages information
		$this->hasPostInstallationMessages = $model->hasPostInstallMessages();
		$this->extension_id = $model->getState('extension_id', 0, 'int');

		return true;
	}
}