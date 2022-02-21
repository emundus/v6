<?php

/**
 * @package   codealfa/regextokenizer
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\RegexTokenizer;

trait Js
{
        use Base;

        public static function JS_HTML_COMMENT()
        {
                return '(?:(?:<!--|(?<=[\s/^])-->)[^\r\n]*+)';
        }
}