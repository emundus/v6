<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperArray {
    static function removeDuplicateObjectsByProperty($array,$property) {
        $array_properties = array_map(function($value) use ($property) {
            return $value->{$property};
        }, $array);

        $unique_array = array_unique($array_properties);

        return array_values(array_intersect_key($array, $unique_array));
    }

    static function mergeAndSumPropertyOfSameObjects($array,$property_unique,$property_to_sum){
        return array_reduce($array, function($carry, $item) use ($property_unique,$property_to_sum) {
            $idx = null;
            // trying to find the object that has the same property as the current item
            foreach($carry as $k => $v)
                if($v->{$property_unique} == $item->{$property_unique}) {
                    $idx = $k;
                    break;
                }
            // if nothing found, add $item to the result array, otherwise sum the points attributes
            $idx === null ? $carry[] = $item:$carry[$idx]->{$property_to_sum} = $item->{$property_to_sum};
            // return the result array for the next iteration
            return $carry;
        }, []);
    }
}
