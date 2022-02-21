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

use Joomla\CMS\Form\FormHelper as JFormHelper;

/**
 * Class ShowOn
 * @package RegularLabs\Library
 */
class ShowOn
{
	public static function close()
	{
		return '</div>';
	}

	public static function open($condition = '', $formControl = '', $group = '', $class = '')
	{
		if ( ! $condition)
		{
			return self::close();
		}

		Document::loadFormDependencies();

		$json = json_encode(JFormHelper::parseShowOnConditions($condition, $formControl, $group));

		$class = $class ? ' class="' . $class . '"' : '';

		return '<div data-showon=\'' . $json . '\' style="display: none;"' . $class . '>';
	}

	public static function show($string = '', $condition = '', $formControl = '', $group = '', $animate = true, $class = '')
	{
		if ( ! $condition || ! $string)
		{
			return $string;
		}

		return self::open($condition, $formControl, $group, $animate, $class)
			. $string
			. self::close();
	}
}
