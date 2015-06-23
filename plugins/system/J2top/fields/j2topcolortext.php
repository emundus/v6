<?php
/**
 * Copyright (C) 2014  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Set flag that this is a parent file
defined('_JEXEC') or die('Restricted access');

class JFormFieldJ2topcolortext extends JFormField {

    public function getInput() {

        $document = JFactory::getDocument();

		// Color Picker
		$document->addStyleSheet(JURI::root() . 'media/J2top/css/picker.css');
		$document->addScript(JURI::root() . 'media/J2top/js/picker.js');

        $value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
        $class = (isset($this->element['class']) ? 'class="' . $this->element['class'] . '"' : 'class="text_area"');
        $size = (isset($this->element['size']) ? ' size="' . $this->element['size'] . '"' : '');
        $onfocus = ' onfocus="this.style.background=\'\';"';
        $onchange = ' onchange="if (this.value != \'\' && this.value != \'transparent\') {this.style.background=this.value;}"';
        $background = ' style="background-color: ' . $value . '"';

		$html = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="' . $value . '" '
            . $class . $size . $background . $onchange . $onfocus . ' />
		    <span style="margin-left:10px" onclick="openPicker(\'' . $this->id . '\')" class="picker_buttons">'
            . JText::_('Pick color') . '</span>';

	    return $html;
	}
}
