<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

class AtsystemFeatureHttpsizer extends AtsystemFeatureAbstract
{
	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// We only use this feature in the front-end
		if ($this->container->platform->isBackend())
		{
			return false;
		}

		// The feature must be enabled
		if ($this->cparams->getValue('httpsizer', 0) != 1)
		{
			return false;
		}

		// Make sure we're accessed over SSL (HTTPS)
		$uri      = Uri::getInstance();
		$protocol = $uri->toString(['scheme']);

		if ($protocol != 'https://')
		{
			return false;
		}


		return true;
	}

	/**
	 * Converts all HTTP URLs to HTTPS URLs when the site is accessed over SSL
	 */
	public function onAfterRenderLatebound()
	{
		$buffer = $this->app->getBody();
		$buffer = str_replace('http://', 'https://', $buffer);

		$this->app->setBody($buffer);

		unset($buffer);
	}
}
