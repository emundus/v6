<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2021. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class JFormFieldPlaintext extends FormField
{

    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'Plaintext';

    public function getInput()
    {
        $document = Factory::getDocument();
        $document->addStyleSheet(JURI::root().'administrator/components/com_falang/assets/css/falang.css');

        $text = trim($this->value);

        if (!$text)
        {
            return '';
        }

        return '<fieldset class="plaintext">' . JTEXT::_($text) . '</fieldset>';

    }

}