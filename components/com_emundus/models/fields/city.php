<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
 
class JFormFieldCity extends JFormField {
 
        protected $type = 'City';
 
        // getLabel() left out
 
        public function getInput() {
                return '<select id="'.$this->id.'" name="'.$this->name.'">'.
                       '<option value="1" >New York</option>'.
                       '<option value="2" >Chicago</option>'.
                       '<option value="3" >San Francisco</option>'.
                       '</select>';
        }
}