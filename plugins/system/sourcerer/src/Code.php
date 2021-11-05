<?php
/**
 * @package         Sourcerer
 * @version         9.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use RegularLabs\Library\Condition\Php as RL_Php;

class Code
{
	public static function run($src_string, &$src_variables, $tmp_path = '')
	{
		if ( ! is_string($src_string) || $src_string == '')
		{
			return '';
		}

		$src_pre_variables = array_keys(get_defined_vars());

		ob_start();
		$src_post_variables = self::execute($src_string, $src_variables, $tmp_path);
		$src_output         = ob_get_contents();
		ob_end_clean();

		if ( ! is_array($src_post_variables))
		{
			return $src_output;
		}

		$src_diff_variables = array_diff(array_keys($src_post_variables), $src_pre_variables);

		foreach ($src_diff_variables as $src_diff_key)
		{
			if (in_array($src_diff_key, ['Itemid', 'mainframe', 'app', 'document', 'doc', 'database', 'db', 'user'])
				|| substr($src_diff_key, 0, 4) == 'src_'
			)
			{
				continue;
			}

			$src_variables[$src_diff_key] = $src_post_variables[$src_diff_key];
		}

		return $src_output;
	}

	private static function execute($string, $src_variables, $tmp_path = '')
	{
		if ( ! $function_name = self::getFunctionName($string))
		{
			// Something went wrong!
			return [];
		}

		return $function_name($src_variables);
	}

	private static function generateFileContents($function_name = 'src_function', $string = '')
	{
		$init = RL_Php::getVarInits();

		$init[] =
			'if (is_array($src_variables)) {'
			. 'foreach ($src_variables as $src_key => $src_value) {'
			. '${$src_key} = $src_value;'
			. '}'
			. '}';

		$contents = [
			'<?php',
			'defined(\'_JEXEC\') or die;',
			'function ' . $function_name . '($src_variables){',
			implode("\n", $init),
			$string . ';',
			'return get_defined_vars();',
			';}',
		];

		return implode("\n", $contents);
	}

	private static function getFunctionName($string)
	{
		$function_name = 'regularlabs_php_' . md5($string);

		if (function_exists($function_name))
		{
			return $function_name;
		}

		$contents = self::generateFileContents($function_name, $string);
		RL_Php::createFunctionInMemory($contents);

		if ( ! function_exists($function_name))
		{
			// Something went wrong!
			return false;
		}

		return $function_name;
	}
}
