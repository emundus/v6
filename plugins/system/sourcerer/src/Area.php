<?php
/**
 * @package         Sourcerer
 * @version         9.2.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use RegularLabs\Library\RegEx as RL_RegEx;

class Area
{
	static $prefix = 'SRC';

	public static function get(&$string, $area = '')
	{
		if (empty($string) || empty($area))
		{
			return [];
		}

		$start = '<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->';
		$end   = '<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->';

		$matches = explode($start, $string);
		array_shift($matches);

		foreach ($matches as $i => $match)
		{
			[$text] = explode($end, $match, 2);
			$matches[$i] = [
				$start . $text . $end,
				$text,
			];
		}

		return $matches;
	}

	public static function tag(&$string, $area = '')
	{
		if (empty($string) || empty($area))
		{
			return false;
		}

		$string = '<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->' . $string . '<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->';

		if ($area != 'article_text')
		{
			return true;
		}

		$string = RL_RegEx::replace(
			'#(<hr class="system-pagebreak".*?>)#si',
			'<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->\1<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->',
			$string
		);

		return true;
	}
}
