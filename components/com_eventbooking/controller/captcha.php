<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

trait EventbookingControllerCaptcha
{
	/**
	 * Method to validate captcha
	 *
	 * @param   RADInput  $input
	 *
	 * @return bool|mixed
	 */
	protected function validateCaptcha($input)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = $this->app->get('captcha');

			if (!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}

			$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

			if ($plugin)
			{
				try
				{
					return Captcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', null, 'string'));
				}
				catch (Exception $e)
				{
					return false;
				}
			}
		}

		return true;
	}
}