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

class plgContentEBEvent extends CMSPlugin
{
	/**
	 * Display event detail in the article
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

		if (strpos($article->text, 'ebevent') === false)
		{
			return true;
		}

		$regex         = "#{ebevent (\d+)}#s";
		$article->text = preg_replace_callback($regex, [&$this, 'displayEvent'], $article->text);

		return true;
	}

	/**
	 * Display detail information of the given event
	 *
	 * @param $matches
	 *
	 * @return string
	 * @throws Exception
	 */
	public function displayEvent(&$matches)
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		EventbookingHelper::loadLanguage();

		$id = $matches[1];

		$event = EventbookingHelperDatabase::getEvent($id);

		// Invalid event or the event was unpublished, return to prevent the article from being accessible
		if (!$event || !$event->published)
		{
			return '';
		}

		$request = ['option' => 'com_eventbooking', 'view' => 'event', 'id' => $id, 'limit' => 0, 'hmvc_call' => 1, 'Itemid' => EventbookingHelper::getItemid()];
		$input   = new RADInput($request);
		$config  = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';
		ob_start();

		//Initialize the controller, execute the task
		RADController::getInstance('com_eventbooking', $input, $config)
			->execute();

		return '<div class="clearfix"></div>' . ob_get_clean();
	}
}
