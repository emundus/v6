<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldText2 extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Text2';

    /**
     * Field input remote url
     *
     * @return string
     */
    protected function getInput()
    {
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        $size = $this->element['size'] ? ' size="' . $this->element['size'] . '""' : '';
        $help = $this->element['help'] ? JText::_($this->element['help']) : '';
        // Initialize JavaScript field attributes.
//        $id = JFactory::getApplication()->input->get('id');
//        $modelFiles = JModelLegacy::getInstance('Files', 'dropfilesModel');
//        $file = $modelFiles->getFile($id);

//        $this->value = ($this->value == '') ? isset($file->file) ? $file->file : '' : $this->value;
        $return = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'.$this->value.'" class=" ' . $class . '" ' . $size . '>';
        $return .= '<div class="ju-settings-help">'. $help .'</div>';

        return $return;
    }
}
