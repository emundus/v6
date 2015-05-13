<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2013 Simon Poghosyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

function JumiBuildRoute(&$query) {
    $db = JFactory::getDBO();
    $segments = array();

    if(isset($query['fileid'])) {
        $db->setQuery('select alias from #__jumi where id = '.$query['fileid']);
        $segments[] = $db->loadResult();
        unset($query['fileid']);
    }

    return $segments;
}

function JumiParseRoute($segments) {
    $db = JFactory::getDBO();
    $vars = array();

    $db->setQuery('select id from #__jumi where alias = "'.$segments[0].'"');
    $vars['fileid'] = $db->loadResult();

    return $vars;
}