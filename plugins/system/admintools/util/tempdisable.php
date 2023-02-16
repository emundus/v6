<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use FOF40\Container\Container;

defined('_JEXEC') || die;

trait AtsystemUtilTempdisable
{
	/**
	 * Sets the temporary disable flag, used when saving temporary super users
	 *
	 * @param   bool  $tempDisableFlag
	 */
	public static function setTempDisableFlag($tempDisableFlag)
	{
		// Create a secure temp token for the flag
		$container = Container::getInstance('com_admintools');
		$class     = static::class;
		$sig       = md5($class . '_tempDisableFlag_' . $container->platform->getToken());

		// Make sure we are being called by an explicitly allowed method
		ServerTechnology::checkCaller([
			'Akeeba\AdminTools\Admin\Model\TempSuperUsers::setNoCheckFlags',
		]);

		// Set the flag in the session
		$container->platform->setSessionVar($sig, (bool) $tempDisableFlag, 'com_admintools');
	}

	/**
	 * Gets the temporary disable flag, used when saving temporary super users
	 *
	 * @return  bool
	 */
	protected static function getTempDisableFlag()
	{
		// Get a secure temp token for the flag and retrieve the current value
		$container = Container::getInstance('com_admintools');
		$class     = static::class;
		$sig       = md5($class . '_tempDisableFlag_' . $container->platform->getToken());
		$ret       = (bool) $container->platform->getSessionVar($sig, false, 'com_admintools');

		// Reset the flag on retrieval
		$container->platform->unsetSessionVar($sig, 'com_admintools');

		// Return the retrieved value
		return $ret;
	}
}
