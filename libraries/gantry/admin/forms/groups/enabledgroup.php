<?php
/**
 * @version   $Id: enabledgroup.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformgroup');
gantry_import('core.config.gantryformfield');


class GantryFormGroupEnabledGroup extends GantryFormGroup
{
    protected $type = 'enabledgroup';
    protected $baseetype = 'group';

    protected $sets = array();


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
				$enabler = false;

				if ($field->element['enabler'] && (string)$field->element['enabler'] == true){
                    $this->enabler = $field;
					$enabler = true;
				}
                $itemName = $this->fieldname . "-" . $field->fieldname;
                $buffer .= '<div class="chain ' . $itemName . ' chain-' . strtolower($field->type) . '">' . "\n";
                if (strlen($field->getLabel())) $buffer .= '<span class="chain-label">' . JText::_($field->getLabel()) . '</span>' . "\n";
				if ($enabler) $buffer .= '<div class="enabledset-enabler">'."\n";
                $buffer .= $field->getInput();
                if ($enabler) $buffer .= '</div>'."\n";
                $buffer .= "</div>" . "\n";
            }
        }
        $buffer .= "</div>" . "\n";
        return $buffer;
    }

    public function render($callback)
    {
        $buffer = parent::render($callback);
		$cls = ' enabledset-hidden-field';
        if (!empty($this->sets)){
            $set = array_shift($this->sets);

            if (isset($this->enabler) && (int)$this->enabler->value == 0){
                $cls = ' enabledset-hidden-field';
            }

            $buffer .= '<div class="enabledset-fields'.$cls.'" id="set-'.(string)$set->element['name'].'">';
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