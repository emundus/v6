<?php
/**
 * @version		$Id: tags.php
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2019 eMundus. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Helper
 * @since 1.5
 */
class EmundusHelperTags
{


    /**
     * Find all variables like ${var} or [var] in string.
     *
     * @param string $str
     * @param int $type type of bracket default CURLY else SQUARE
     * @return string[]
     */
    public function getVariables($str, $type = 'CURLY')
    {
        if ($type == 'CURLY') {
            preg_match_all('/\$\{(.*?)}/i', $str, $matches);
        } elseif ($type == 'SQUARE') {
            preg_match_all('/\[(.*?)]/i', $str, $matches);
        } else {
            preg_match_all('/\{(.*?)}/i', $str, $matches);
        }
        return $matches[1];
    }
}
