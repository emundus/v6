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

defined('_JEXEC') || die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldJutranslation extends JFormField
{
    /**
     * Ju Translation input
     *
     * @return string
     */
    protected function getInput()
    {
        $path_jutranslation = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        $path_jutranslation .= 'jutranslation.php';
        include_once($path_jutranslation);

        return Jutranslation::getInput();
    }
}
