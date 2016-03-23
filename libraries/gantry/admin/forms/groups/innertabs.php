<?php
/**
 * @version   $Id: innertabs.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformgroup');


class GantryFormGroupInnerTabs extends GantryFormGroup {

    protected $type = 'innertabs';
    protected $baseetype = 'group';

    public function getInput() {

        foreach ($this->fields as $field) {
            if ( is_subclass_of($field,'GantryFormGroup'))
                $field->setLabelWrapperFunctions($this->prelabel_function, $this->postlabel_function);
        }

        $buffer = '';
        $buffer .= <<< EOS
<div>
	<div class="inner-tabs">
		<ul>
EOS;
        $i = 0;
        foreach ($this->fields as $field) {
            $classes = '';
            if (!$i) $classes .= "first active";
            if ($i == count($this->fields) - 1) $classes .= 'last';
            $buffer .= '<li class="' . $classes . '"><span>' . JText::_($field->getLabel()) . '</span></li>'."\n";
            $i++;
        }
        $buffer .= <<< EOS
        </ul>
    </div>
    <div class="inner-panels">
EOS;
		$i = 0;
        foreach ($this->fields as $field) {
			$i++;
            $buffer .=  '<div class="inner-panel inner-panel-'.$i.'">'."\n";
            $buffer .= $field->getInput();
            $buffer .= '</div>'."\n";
        }
        $buffer .= <<< EOS
	</div>
</div>
EOS;
        return $buffer;
    }
}
