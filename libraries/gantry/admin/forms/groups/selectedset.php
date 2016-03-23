<?php
/**
 * @version   $Id: selectedset.php 6960 2013-01-30 21:19:03Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformgroup');
gantry_import('core.config.gantryformfield');


class GantryFormGroupSelectedSet extends GantryFormGroup
{
    protected $type = 'selectedset';
    protected $baseetype = 'group';

    protected $sets = array();
	protected $activeSet = array();
	protected $enabler;

    public function getInput()
    {
		/** @global $gantry Gantry */
		global $gantry;

        $buffer = '';

        // get the sets just below
        foreach ($this->fields as $field)
        {
            if ($field->type == 'set')
            {
                $this->sets[] = $field;
            }
        }

        $buffer .= "<div class='wrapper'>\n";
        foreach ($this->fields as $field)
        {
            if ((string)$field->type != 'set')
            {
                $selector = false;
				$enabler = false;
				

				if ($field->element['enabler'] && strtolower((string)$field->element['enabler']) == 'true'){
	                $this->enabler = $field;
					$enabler = true;
	            }
	
                if ($field->element['selector'] && (string)$field->element['selector'] == true)
                {
					$field->detached = false;
                    $selector = true;

		            if ($field != $this->enabler && isset($this->enabler) && (int)$this->enabler->value == 0){
		                $field->detached = true;
		            }
		
                    foreach ($this->sets as $set)
                    {
                        //Create a new option object based on the <option /> element.
                        $tmp = GantryHtmlSelect::option((string)$set->element['name'], JText::_(trim((string)$set->element['label'])), 'value', 'text', ((string)$set->element['disabled'] == 'true'));
                        // Set some option attributes.
                        $tmp->class = (string)$set->element['class'];
                        // Set some JavaScript option attributes.
                        $tmp->onclick = (string)$set->element['onclick'];
                        // Add the option object to the result set.
                        //$options[] = $tmp;
                        $field->addOption($tmp);
                    }
                }

				$this->activeSet[$field->type] = $field->value;
				//array_push(array($field->type => $field->value), $this->activeSet);
                $itemName = $this->fieldname . "-" . $field->fieldname;
                $buffer .= '<div class="chain ' . $itemName . ' chain-' . strtolower($field->type) . '">' . "\n";
                if (strlen($field->getLabel())) $buffer .= '<span class="chain-label">' . JText::_($field->getLabel()) . '</span>' . "\n";
                if ($selector) $buffer .= '<div class="selectedset-switcher">'."\n";
				if ($enabler) $buffer .= '<div class="selectedset-enabler">'."\n";
                $buffer .= $field->getInput();
                if ($selector || $enabler) $buffer .= '</div>'."\n";
                $buffer .= "</div>" . "\n";
            }
        }
        $buffer .= "</div>" . "\n";
        return $buffer;
    }

    public function render($callback)
    {

        $buffer = parent::render($callback);
		$cls = '';// ' selectedset-hidden-field';
        foreach ($this->sets as $set)
        {
			if (isset($this->activeSet['selectbox'])){
				if ($this->activeSet['selectbox'] == (string) $set->element['name']) $cls = '';
				else $cls = ''; //' selectedset-hidden-field';
				
				if (isset($this->activeSet['toggle']) && $this->activeSet['toggle'] == '0'){
					$cls = '';//' selectedset-hidden-field';
				}
			}
			
            $buffer .= '<div class="selectedset-fields'.$cls.'" id="set-'.(string)$set->element['name'].'">';
            foreach ($set->fields as $field)
            {
                if ($field->type == 'hidden')
                    $buffer .= $field->getInput();
                else
                {
                    $buffer .= $field->render($callback);
                }
            }
            $buffer .= '</div>';
        }
        return $buffer;
    }
}