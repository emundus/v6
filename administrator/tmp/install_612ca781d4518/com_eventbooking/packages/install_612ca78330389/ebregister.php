<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentEBRegister extends CMSPlugin
{
	/**
	 * Display Individual Registration Form for the event in article
	 *
	 * @param $context
	 * @param $article
	 * @param $params
	 * @param $limitstart
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
	{
		$app = Factory::getApplication();

		if ($app->getName() != 'site')
		{
			return true;
		}

		if (strpos($article->text, 'ebregister') === false)
		{
			return true;
		}

		$regex         = "#{ebregister (\d+)}#s";
		$article->text = preg_replace_callback($regex, [&$this, 'displayIndividualRegistrationForm'], $article->text);

		return true;
	}

	/**
	 * Display individual registration form for the event
	 *
	 * @param $matches
	 *
	 * @return string
	 * @throws Exception
	 */
	public function displayIndividualRegistrationForm(&$matches)
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		EventbookingHelper::loadLanguage();
		$eventId = $matches[1];

		$event = EventbookingHelperDatabase::getEvent($eventId);

		// Invalid event or the event was unpublished, return to prevent the article from being accessible
		if (!$event || !$event->published)
		{
			return '';
		}

		$request = ['option' => 'com_eventbooking', 'view' => 'register', 'event_id' => $eventId, 'layout' => 'default', 'hmvc_call' => 1, 'Itemid' => EventbookingHelper::getItemid()];
		$input   = new RADInput($request);
		$config  = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';
		ob_start();

		//Initialize the controller, execute the task
		RADController::getInstance('com_eventbooking', $input, $config)
			->execute();

		return '<div class="clearfix"></div>' . ob_get_clean();
	}
}
