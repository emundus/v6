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

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Class Language
 * @package RegularLabs\Library
 */
class License
{
	/**
	 * Render the license message for Free versions
	 *
	 * @param string $name
	 * @param bool   $check_pro
	 *
	 * @return string
	 */
	public static function getMessage($name, $check_pro = false)
	{
		if ( ! $name)
		{
			return '';
		}

		$alias = Extension::getAliasByName($name);
		$name  = Extension::getNameByAlias($name);

		if ($check_pro && self::isPro($alias))
		{
			return '';
		}

		$displayData = [
			'msgList' => [
				'' => [
					JText::sprintf('RL_IS_FREE_VERSION', $name),
					JText::_('RL_FOR_MORE_GO_PRO'),
					'<a href="https://regularlabs.com/purchase/cart/add/' . $alias . '" target="_blank" class="btn btn-small btn-primary">'
					. '<span class="icon-basket"></span> '
					. StringHelper::html_entity_decoder(JText::_('RL_GO_PRO'))
					. '</a>',
				],
			],
		];

		return LayoutHelper::render('joomla.system.message', $displayData);
	}

	/**
	 * Check if the installed version of the extension is a Pro version
	 *
	 * @param string $element_name
	 *
	 * @return bool
	 */
	private static function isPro($element_name)
	{
		if ( ! $version = Extension::getXMLValue('version', $element_name))
		{
			return false;
		}

		return (stripos($version, 'PRO') !== false);
	}
}
