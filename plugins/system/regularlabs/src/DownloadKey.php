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

namespace RegularLabs\Plugin\System\RegularLabs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;

class DownloadKey
{
	public static function cloak()
	{
		// Save the download key from the Regular Labs Extension Manager config to the update sites
		if (
			RL_Document::isClient('site')
			|| JFactory::getApplication()->input->get('option') != 'com_installer'
			|| JFactory::getApplication()->input->get('view') != 'updatesites'
		)
		{
			return;
		}

		$html = JFactory::getApplication()->getBody();

		RL_RegEx::matchAll('(regularlabs\.com[^<]*</a>\s*<br/?>\s*<pre>k=)(.*?)([A-Z0-9]{4}</pre>)', $html, $matches);

		foreach ($matches as $match)
		{
			$cloaked_key = str_repeat('*', strlen($match[2]));

			$html = str_replace(
				$match[0],
				$match[1] . $cloaked_key . $match[3],
				$html
			);
		}

		JFactory::getApplication()->setBody($html);
	}

	public static function update()
	{
		// Save the download key from the Regular Labs Extension Manager config to the update sites
		if (
			RL_Document::isClient('site')
			|| JFactory::getApplication()->input->get('option') != 'com_config'
			|| JFactory::getApplication()->input->get('task') != 'config.save.component.apply'
			|| JFactory::getApplication()->input->get('component') != 'com_regularlabsmanager'
		)
		{
			return;
		}

		$form = JFactory::getApplication()->input->post->get('jform', [], 'array');

		if ( ! isset($form['key']))
		{
			return;
		}

		$key = $form['key'];

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set($db->quoteName('extra_query') . ' = ' . $db->quote('k=' . $key))
			->where($db->quoteName('location') . ' LIKE ' . $db->quote('%download.regularlabs.com%'));
		$db->setQuery($query);
		$db->execute();
	}
}
