<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('_JEXEC') or die;

/**
 * This helper class will be used inside the System plugin. It must have as minimum dependencies as possible
 * since we don't want to slow down page loading
 *
 * @package Akeeba\AdminTools\Admin\Helper
 */
class Plugin
{
	/** @var null|bool Is this a CLI application? */
	protected static $isCLI;

	/** @var null|bool Is this an administrator application? */
	protected static $isAdmin;

	/**
	 * Is this the administrative section of the component?
	 *
	 * Copied from FOF library in order to remove dependencies to it
	 *
	 * @return  boolean
	 */
	public function isBackend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return $isAdmin && !$isCli;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * Copied from FOF library in order to remove dependencies to it
	 *
	 * @return  boolean
	 */
	public function isFrontend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return !$isAdmin && !$isCli;
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin
	 *
	 * Copied from FOF library in order to remove dependencies to it
	 *
	 * @return  array  isCLI and isAdmin. It's not an associative array, so we can use list.
	 */
	public function isCliAdmin()
	{
		if (is_null(static::$isCLI) && is_null(static::$isAdmin))
		{
			try
			{
				if (is_null(\JFactory::$application))
				{
					static::$isCLI = true;
				}
				else
				{
					$app = \JFactory::getApplication();
					static::$isCLI = $app instanceof \Exception || $app instanceof \JApplicationCli;
				}
			}
			catch (\Exception $e)
			{
				static::$isCLI = true;
			}

			if (static::$isCLI)
			{
				static::$isAdmin = false;
			}
			else
			{
				static::$isAdmin = !\JFactory::$application ? false : \JFactory::getApplication()->isAdmin();
			}
		}

		return array(static::$isCLI, static::$isAdmin);
	}
}