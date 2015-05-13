<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

class AdmintoolsModelSeoandlink extends F0FModel
{
	var $defaultConfig = array(
		'linkmigration' => 0,
		'migratelist'   => '',
		'httpsizer'     => 0,
	);

	function getConfig()
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
		$config = array();
		foreach ($this->defaultConfig as $k => $v)
		{
			$config[$k] = $params->getValue($k, $v);
		}

		return $config;
	}

	function saveConfig($newParams)
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		foreach ($newParams as $key => $value)
		{
			$params->setValue($key, $value);
		}

		$params->save();
	}
}