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
JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldDateformat extends JFormFieldText
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Dateformat';

    /**
     * Form field input tag date format
     *
     * @return string
     */
    protected function getInput()
    {
        return parent::getInput() . '<a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="__blank">'
            . JText::_('COM_DROPFILES_CONFIG_DATEFORMAT_LABEL') .
            '</a>';
    }
}
