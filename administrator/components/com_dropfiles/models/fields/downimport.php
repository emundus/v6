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
class JFormFieldDownimport extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Downimport';

    /**
     * Field download import
     *
     * @return string
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';

        if (JComponentHelper::isInstalled('com_jdownloads')) {
            if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jdownloads/models/categories.php')) {
                $return = '<div class="import-name"><label class="ju-setting-label">'. JText::_('COM_DROPFILES_CONFIG_JDOWN_IMPORT_NAME') .'</label></div>';
                $return .= '<select name="' . $this->name . '" id="' . $this->id . '" class="' . $class . '">';
                $return .= '<option value="0">' . JText::_('COM_DROPFILES_CONFIG_SELECT_A_CATEGORY') . '</option>';
                $path_admin_model = JPATH_ADMINISTRATOR . '/components/com_jdownloads/models/';
                JModelLegacy::addIncludePath($path_admin_model, 'jdownloadsModel');
                $modelCategories = JModelLegacy::getInstance('categories', 'jdownloadsModel');
                $cats = $modelCategories->getItems();
                foreach ($cats as $cat) {
                    $return .= '<option value="' . $cat->id . '">';
                    $return .= str_repeat('&#8211;', $cat->level - 1) . ' ' . $cat->title . '</option>';
                }
                $return .= '</select>';
                $return .= '<style type="text/css">.docman_title {margin-bottom: 10px;} ';
                $return .= '.docman_desc {font-weight: normal;}</style>';
                $return .= '<button id="jdownloads_import_button" class="btn btn-small">';
                $return .= JText::_('COM_DROPFILES_CONFIG_RUN_JDOWN_IMPORT') . '</button>';
            } else {
                $return = '<span class="check-import-hidden" >';
                $return .= JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_JDOWNLOADS') . '</span>';
            }
        } else {
            $return = '<span class="check-import-hidden no-jdownload" >';
            $return .= JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_JDOWNLOADS') . '</span>';
        }
        return $return;
    }
}
