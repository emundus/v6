<?php
/**
 * @version		$Id: list.php 14401 2010-01-26 14:10:00Z louis $
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a list element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldMultilist extends JFormFieldList
{
    /**
     * Element type
     *
     * @access	protected
     * @var		string
     */
    var	$_name = 'Multilist';

    function getInput()
    {
        $name = $this->fieldname;
        $value = $this->value;
        /*$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );*/

        $options = array ();
        $db	= JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__emundus_widgets'));
        $db->setQuery($query);
        $widgets = $db->loadObjectList();
        $size = 0;
        foreach($widgets as $widget){
            $options[] = JHTML::_('select.option', $widget->name, $widget->label);
        }
        return JHTML::_('select.genericlist',  $options, $this->name.'[]', 'multiple="multiple" size="10"' , 'value', 'text', $value, $name);
    }
}
