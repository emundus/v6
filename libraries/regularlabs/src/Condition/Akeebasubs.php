<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Condition;

/**
 * Class Akeebasubs
 * @package RegularLabs\Library\Condition
 */
abstract class Akeebasubs extends Condition
{
	var $agent  = null;
	var $device = null;

	public function initRequest(&$request)
	{
		if ($request->id || $request->view != 'level')
		{
			return;
		}

		$slug = JFactory::getApplication()->input->getString('slug', '');

		if ( ! $slug)
		{
			return;
		}

		$query = $this->db->getQuery(true)
			->select('l.akeebasubs_level_id')
			->from('#__akeebasubs_levels AS l')
			->where('l.slug = ' . $this->db->quote($slug));
		$this->db->setQuery($query);
		$request->id = $this->db->loadResult();
	}
}
