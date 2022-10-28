<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgContentEBSpeakers extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;


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
		if ($this->app->getName() != 'site')
		{
			return true;
		}

		if (strpos($article->text, 'ebspeakers') === false)
		{
			return true;
		}

		$regex         = "#{ebspeakers (\d+)}#s";
		$article->text = preg_replace_callback($regex, [&$this, 'displayEventSpeakers'], $article->text);

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
	public function displayEventSpeakers(&$matches)
	{
		$eventId = $matches[1];

		if (!$eventId)
		{
			return '';
		}

		$db = $this->db;

		$query = $db->getQuery(true)
			->select('a.*')
			->from('#__eb_speakers AS a')
			->innerJoin('#__eb_event_speakers AS b ON a.id = b.speaker_id')
			->where('b.event_id = ' . $eventId)
			->order('b.id');

		$db->setQuery($query);
		$speakers = $db->loadObjectList();

		if (!count($speakers))
		{
			return '';
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		return EventbookingHelperHtml::loadCommonLayout('plugins/speakers.php', ['speakers' => $speakers]);
	}
}
