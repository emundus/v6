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

class SearchHelper
{
	public static function load()
	{
		// Only in frontend search component view
		if ( ! RL_Document::isClient('site') || JFactory::getApplication()->input->get('option') != 'com_search')
		{
			return;
		}

		$classes = get_declared_classes();

		if (in_array('SearchModelSearch', $classes) || in_array('searchmodelsearch', $classes))
		{
			return;
		}

		require_once JPATH_LIBRARIES . '/regularlabs/helpers/search.php';
	}
}
