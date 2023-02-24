<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;

jimport('joomla.form.helper');

class JFormFieldPlaceholder extends FormField
{
    public function getInput()
    {
        return "";
    }

    public function getLabel()
    {
        return "";
    }
}
