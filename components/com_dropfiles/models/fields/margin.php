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
class JFormFieldMargin extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Margin';

    /**
     * Add field margin
     *
     * @return string
     * @since  version
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        $stype = $this->element['stype'] ? ' data-slider-stype="' . (string)$this->element['stype'] . '"' : '';

        $return = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"';
        $return .= ' value="' . (int)$this->value . '" class="slider hide ' . $class . '" ' . $stype . '"';
        $return .= ' data-slider-min="0" data-slider-max="100" data-slider-step="1"';
        $return .= ' data-slider-value="' . (int)$this->value . '" data-slider-orientation="horizontal"';
        $return .= ' data-slider-selection="after" data-slider-tooltip="always">';

        return $return;
    }
}
