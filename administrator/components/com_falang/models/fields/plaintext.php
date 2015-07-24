<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_falang
 *
 * @author      Stéphane Bouey
 * @copyright	Copyright (C) 2014 Faboba
 * @license		GNU/GPL, see LICENSE.php
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

class JFormFieldPlaintext extends JFormField
{
    public $type = 'Plaintext';

    public function getInput()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root().'administrator/components/com_falang/assets/css/falang.css');

        $text = trim($this->value);

        if (!$text)
        {
            return '';
        }

        return '<fieldset class="plaintext">' . JTEXT::_($text) . '</fieldset>';

    }

}