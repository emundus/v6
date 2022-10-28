<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Object\CMSObject as JObject;
use Joomla\CMS\Plugin\CMSPlugin as JCMSPlugin;
use ReflectionClass;
use RegularLabs\Library\ParametersNew as Parameters;

/**
 * Class EditorButtonPlugin
 * @package RegularLabs\Library
 */
class EditorButtonPlugin extends JCMSPlugin
{
	var     $check_installed      = null;
	var     $enable_on_acymailing = false;
	var     $folder               = null; // The type of extension that holds the parameters
	var     $main_type            = 'plugin'; // The types of extensions that need to be checked (will default to main_type)
	var     $require_core_auth    = true; // Whether or not the core content create/edit permissions are required
	private $_helper              = null; // The path to the original caller file
	private $_init                = false; // Whether or not to enable the editor button on AcyMailing

	/**
	 * Display the button
	 *
	 * @param string $editor_name
	 *
	 * @return JObject|null A button object
	 */
	public function onDisplay($editor_name)
	{
		if ( ! $this->getHelper())
		{
			return null;
		}

		return $this->_helper->render($editor_name, $this->_subject);
	}

	/*
	 * Below methods are general functions used in most of the Regular Labs extensions
	 * The reason these are not placed in the Regular Labs Library files is that they also
	 * need to be used when the Regular Labs Library is not installed
	 */

	/**
	 * Create the helper object
	 *
	 * @return object|null The plugins helper object
	 */
	private function getHelper()
	{
		// Already initialized, so return
		if ($this->_init)
		{
			return $this->_helper;
		}

		$this->_init = true;

		if ( ! Extension::isFrameworkEnabled())
		{
			return null;
		}

		if ( ! Extension::isAuthorised($this->require_core_auth))
		{
			return null;
		}

		if ( ! $this->isInstalled())
		{
			return null;
		}

		if ( ! $this->enable_on_acymailing && JFactory::getApplication()->input->get('option') == 'com_acymailing')
		{
			return null;
		}

		$params = $this->getParams();

		if ( ! Extension::isEnabledInComponent($params))
		{
			return null;
		}

		if ( ! Extension::isEnabledInArea($params))
		{
			return null;
		}

		if ( ! $this->extraChecks($params))
		{
			return null;
		}

		require_once $this->getDir() . '/helper.php';
		$class_name    = 'PlgButton' . ucfirst($this->_name) . 'Helper';
		$this->_helper = new $class_name($this->_name, $params);

		return $this->_helper;
	}

	private function isInstalled()
	{
		$extensions = ! is_null($this->check_installed)
			? $this->check_installed
			: [$this->main_type];

		return Extension::areInstalled($this->_name, $extensions);
	}

	private function getParams()
	{
		switch ($this->main_type)
		{
			case 'component':
				if ( ! Protect::isComponentInstalled($this->_name))
				{
					return null;
				}

				// Load component parameters
				return Parameters::getComponent($this->_name);

			case 'plugin':
			default:
				if ( ! Protect::isSystemPluginInstalled($this->_name))
				{
					return null;
				}

				// Load plugin parameters
				return Parameters::getPlugin($this->_name);
		}
	}

	public function extraChecks($params)
	{
		return true;
	}

	private function getDir()
	{
		// use static::class instead of get_class($this) after php 5.4 support is dropped
		$rc = new ReflectionClass(get_class($this));

		return dirname($rc->getFileName());
	}
}
