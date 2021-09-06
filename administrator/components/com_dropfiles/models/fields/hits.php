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
class JFormFieldHits extends JFormField
{

    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Hits';

    /**
     * Field input hits file
     *
     * @return string
     */
    protected function getInput()
    {
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        // Initialize JavaScript field attributes.
        $return = '<input size="6" type="text" name="' . $this->name . '" id="' . $this->id . '"';
        $return .= 'readonly="true" value="' . (int)$this->value . '" class=" ' . $class . '">';
        $return .= '<button type="button" class="btn" onclick="jQuery(\'#' . $this->id . '\').val(0);";" >';
        $return .= '<i class="material-icons reset-icons">autorenew</i>' . JText::_('COM_DROPFILES_FIELD_HITS_RESET') . '</button><div class="clearfix"></div>';

        return $return;
    }
}
