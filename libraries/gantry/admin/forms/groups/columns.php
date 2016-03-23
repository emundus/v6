<?php
/**
 * @version   $Id: columns.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformgroup');


class GantryFormGroupColumns extends GantryFormGroup
{
    protected $type = 'columns';
    protected $baseetype = 'group';

	public function getInput()
	{
        $buffer = '';
		
		$class = $this->element['class'];
		$name = $this->id;
		
		$buffer .= "<div class=\"wrapper ".$class."\">\n";
		
		// Columns
		$leftOpen = "<div class='group-left'>\n";
		$rightOpen = "<div class='group-right'>\n";
		$noneOpen = "<div class='group-none'>\n";
		
		$divClose = "</div>\n";
		
        foreach ($this->fields as $field) {

			$position = ($field->element['position']) ? (string) $field->element['position'] : 'none';
			$position .= "Open";
			$bufferItem = "";

			$fieldName = $this->fieldname."-".$field->element['name'];
			
			$bufferItem .= "<div class=\"group ".$fieldName." group-".$field->type."\">\n";
            if ($field->show_label) $bufferItem .= "<span class=\"group-label\">".$field->getLabel()."</span>\n";
            $bufferItem .= $field->getInput();
            $bufferItem .= "</div>\n";
			
			$$position .= $bufferItem;
        }

		$buffer .= $leftOpen . $divClose . $rightOpen . $divClose . $noneOpen . $divClose;
		$buffer .= "</div>\n";

        return $buffer;

    }
}