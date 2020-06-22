<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFtables extends JFormFieldList
{
    public $type = 'ftables';

    public function getOptions() {
        $options = array();

        $options = array();
        // Add our options to the array
        // value=> [table name] , text => [Name to Display] //name to display content element name ?
        $options[] = array("value" => 'content', "text" => "Articles");
        $options[] = array("value" => "menu", "text" => "Menu");
	    $options[] = array("value" => "modules", "text" => "Modules");

        return $options;
//        $items = array('article'=>'Article','menu'=>'Menu');
//
//        // Build the field options.
//        if (!empty($items))
//        {
//            foreach ($items as $key =>$item)
//            {
//                $options[] = JHtml::_('select.option', $key, $item);
//            }
//        }
//
//        // Merge any additional options in the XML definition.
//        $options = array_merge(parent::getOptions(), $options);
//
//        return $options;


    }

}