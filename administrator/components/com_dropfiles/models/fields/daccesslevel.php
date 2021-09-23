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
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('JPATH_PLATFORM') || die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 */
class JFormFieldDAccessLevel extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'AccessLevel';

    /**
     * Method to get the field input markup.
     *
     * @return string  The field input markup.
     *
     * @since 5.5
     */
    protected function getInput()
    {
        $attr = '';

        // Initialize some field attributes.
        $attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr .= $this->disabled ? ' disabled' : '';
        $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr .= $this->multiple ? ' multiple' : '';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Get the field options.
        $options = $this->getOptions();
        array_unshift($options, (object) array(
            'text'  => 'Inherited',
            'value' => '-1'
        ));
        return JHtml::_('access.level', $this->name, $this->value, $attr, $options, $this->id);
    }
}
