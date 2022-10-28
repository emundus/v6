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
class JFormFieldTextcopy extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Textcopy';

    /**
     * Field input remote url
     *
     * @return string
     */
    protected function getInput()
    {
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        $size = $this->element['size'] ? ' size="' . $this->element['size'] . '""' : '';

        // Initialize JavaScript field attributes.
//        $id = JFactory::getApplication()->input->get('id');
//        $modelFiles = JModelLegacy::getInstance('Files', 'dropfilesModel');
//        $file = $modelFiles->getFile($id);

//        $this->value = ($this->value == '') ? isset($file->file) ? $file->file : '' : $this->value;
        $return = '<input disabled type="text" name="' . $this->name . '" id="' . $this->id . '" value="" class=" ' . $class . '" ' . $size . '>';
        $return .= '<button type="button" class="btn copy-btn"><i class="material-icons btn_' . $this->id . '" title="Copy">content_copy</i>'. JText::_('COM_DROPFILES_COPY') .'</button>';

        return $return;
    }
}
