<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.helper');

class JFormFieldPlaceholder extends JFormField
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
