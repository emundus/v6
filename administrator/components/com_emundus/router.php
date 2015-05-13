<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

/**
 * @param	array	A named array
 * @return	array
 */
function EmundusBuildRoute(&$query) {
    $segments = array();

    if (isset($query['task'])) {
        $segments[] = implode('/', explode('.', $query['task']));
        unset($query['task']);
    }
    if (isset($query['view'])) {
        $segments[] = $query['view'];
        unset($query['view']);
    }
    if (isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }

    return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/emundus/task/id/Itemid
 *
 * index.php?/emundus/id/Itemid
 */
function EmundusParseRoute($segments) {
    $vars = array();

    // view is always the first element of the array
    $vars['view'] = array_shift($segments);

    while (!empty($segments)) {
        $segment = array_pop($segments);
        if (is_numeric($segment)) {
            $vars['id'] = $segment;
        } else {
            $vars['task'] = $vars['view'] . '.' . $segment;
        }
    }

    return $vars;
}
