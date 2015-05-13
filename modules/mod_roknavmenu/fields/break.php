<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class JFormFieldBreak extends JFormField
{
	/**
	 * @var string
	 */
	protected $type = 'Break';

	/**
	 * @return string
	 */
	protected function getLabel()
	{
        $doc = JFactory::getDocument();
        $version = new JVersion();
        $doc->addStyleDeclaration(".rok-break {border-bottom:1px solid #eee;font-size:16px;color:#0088CC;margin-top:15px;padding:2px 0;width:100%}");

        if (isset($this->element['label']) && !empty($this->element['label'])) {
            $label = JText::_((string)$this->element['label']);
            $css   = (string)$this->element['class'];
            $version = new JVersion();
            if (version_compare($version->getShortVersion(), '3.0', '>=')) {
                return '<div class="rok-break ' . $css . '">' . $label . '</div>';
            } else {
                return '<label class="rok-break ' . $css . '">' . $label . '</label>';
            }
        } else {
            return;
        }

	}

	/**
	 * @return mixed
	 */
	protected function getInput()
	{
        return;
	}

}
