<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

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