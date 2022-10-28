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
     * Field input display hit
     *
     * @return string
     */
    protected function getInput()
    {
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        // Initialize JavaScript field attributes.
        $html_hits_reset = '<input size="6" type="text" name="' . $this->name . '" id="' . $this->id . '"';
        $html_hits_reset .= ' readonly="true" value="' . (int)$this->value . '" class=" ' . $class . '">';
        $html_hits_reset .= '<button type="button" class="btn" onclick="jQuery(\'#' . $this->id . '\').val(0);";" >';
        $html_hits_reset .= '<i class="material-icons reset-icons">autorenew</i>' . JText::_('COM_DROPFILES_FIELD_HITS_RESET') . '</button><div class="clearfix"></div>';
        return $html_hits_reset;
    }
}
