<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

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
		if ($this->helper->isBackend())
		{
			return false;
		}

		// The feature must be enabled
		if ($this->cparams->getValue('httpsizer', 0) != 1)
		{
			return false;
		}

		// Make sure we're accessed over SSL (HTTPS)
		$uri = JUri::getInstance();
		$protocol = $uri->toString(array('scheme'));

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
		if (method_exists($this->app, 'getBody'))
		{
			$buffer = $this->app->getBody();
		}
		else
		{
			$buffer = JResponse::getBody();
		}

		$buffer = str_replace('http://', 'https://', $buffer);

		if (method_exists($this->app, 'setBody'))
		{
			$this->app->setBody($buffer);
		}
		else
		{
			JResponse::setBody($buffer);
		}

		unset($buffer);
	}
}