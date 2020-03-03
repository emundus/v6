<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Minify;

abstract class Base
{

	//regex for double quoted strings
	// language=RegExp
	const DOUBLE_QUOTE_STRING = '"(?>(?:\\\\.)?[^\\\\"]*+)+?(?:"|(?=$))';

	//regex for single quoted string
	// language=RegExp
	const SINGLE_QUOTE_STRING = "'(?>(?:\\\\.)?[^\\\\']*+)+?(?:'|(?=$))";

	//regex for block comments
	// language=RegExp
	const BLOCK_COMMENT = '/\*(?>[^/\*]++|//|\*(?!/)|(?<!\*)/)*+\*/';

	//regex for line comments
	// language=RegExp
	const LINE_COMMENT = '//[^\r\n]*+';

	//regex for HTML comments
	// language=RegExp
	const HTML_COMMENT = '(?:(?:<!--|(?<=[\s/^])-->)[^\r\n]*+)';

	//Regex for HTML attributes
	// language=RegExp
	const HTML_ATTRIBUTE = '[^\s/"\'=<>]*+(?:\s*=(?>\s*+"[^"]*+"|\s*+\'[^\']*+\'|[^\s>]*+[\s>]))?';

	//Regex for HTML attribute values
	// language=RegExp
	const ATTRIBUTE_VALUE = '(?>(?<=")[^"]*+|(?<=\')[^\']*+|(?<==)[^\s*+>]*+)';

	// language=RegExp
	const URI = '(?<=url)\(\s*+(?:"[^"]*+"|\'[^\']*+\'|[^)]*+)\s*+\)';

	protected $_debug = false;
	protected $_regexNum = -1;
	protected $_limit = 0;

	/**
	 *
	 * @param   string   $regex
	 * @param   string   $code
	 * @param   integer  $regexNum
	 *
	 * @return boolean|void
	 */
	protected function _debug($regex, $code, $regexNum = 0)
	{
		if (!$this->_debug) return false;

		/** @var float $pstamp */
		static $pstamp = 0;

		if ($pstamp === 0)
		{
			$pstamp = microtime(true);

			return;
		}

		$nstamp = microtime(true);
		$time   = $nstamp - $pstamp;

		if ($time > $this->_limit)
		{
			print 'num=' . $regexNum . "\n";
			print 'time=' . $time . "\n\n";
		}

		if ($regexNum == $this->_regexNum)
		{
			print $regex . "\n";
			print $code . "\n\n";
		}

		$pstamp = $nstamp;
	}

	/**
	 *
	 * @staticvar bool $tm
	 *
	 * @param   string    $regex
	 * @param   string    $replacement
	 * @param   string    $code
	 * @param   mixed     $regex_num
	 * @param   callable  $callback
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function _replace($regex, $replacement, $code, $regex_num, $callback = null)
	{
		static $tm = false;

		if ($tm === false)
		{
			$this->_debug('', '');
			$tm = true;
		}

		if (empty($callback))
		{
			$op_code = preg_replace($regex, $replacement, $code);
		}
		else
		{
			$op_code = preg_replace_callback($regex, $callback, $code);
		}

		$this->_debug($regex, $code, $regex_num);

		$error = array_flip(array_filter(get_defined_constants(true)['pcre'], function ($value) {
			return substr($value, -6) === '_ERROR';
		}, ARRAY_FILTER_USE_KEY))[preg_last_error()];

		if (preg_last_error() != PREG_NO_ERROR)
		{
			throw new \Exception($error);
		}

		return $op_code;
	}

}
