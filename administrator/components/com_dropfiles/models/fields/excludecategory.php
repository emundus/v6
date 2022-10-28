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
class JFormFieldExcludeCategory extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'ExcludeCategory';

    /**
     * Get label
     *
     * @return string
     *
     * @throws Exception Throw when something wrong
     */
    protected function getLabel()
    {
        $params = JComponentHelper::getParams('com_dropfiles');

        if (!$params->get('ref_exclude_category_id')) {
            return parent::getLabel();
        }
        return parent::getLabel();
    }

    /**
     * Add field get category value
     *
     * @return string
     *
     * @throws Exception Throw when something wrong
     */
    public function getInput()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $excludeCategory = $params->get('ref_exclude_category_id');
        $select = (isset($excludeCategory)) ? $excludeCategory : array();
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        $html = '';
        $selectionValues = $this->getSelectionValues();
        $selection_value = (count($selectionValues)) ? $selectionValues : array();
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        $html .= '<div class="controls-exclude-category">';
        $data_placeholder = 'data-placeholder="' . JText::_('COM_DROPFILES_CONFIG_SEARCH_EXCLUDE_CATEGORIES_PLACEHOLDER') . '" class="exclude-category-select chosen inputbox ju-input exclude_category" multiple="true"';
        $html .= DropfilesHelper::dropfilesSelect($selection_value, 'jform[ref_exclude_category_id][]', $select, $data_placeholder, false);
        $html .= '</div>';

        return $html;
    }

    /**
     * Get selection values
     *
     * @return array
     */
    public function getSelectionValues()
    {
        $path_model = JPATH_ROOT . '/administrator/components/com_dropfiles/models/';
        JModelLegacy::addIncludePath($path_model, 'DropfilesModelCategories');
        $modelCategories = JModelLegacy::getInstance('Categories', 'dropfilesModel');
        $categories      = $modelCategories->getAllCategories();
        $options         = array();
        foreach ($categories as $key => $value) {
            if ($value->type === 'default') {
                $temp = new stdClass();
                $temp->value = $categories[$key]->id;
                $temp->text = str_repeat('-', ($categories[$key]->level - 1)) . ' ' . $categories[$key]->title;
                $options[$temp->value] = $temp->text;
            } elseif ($value->type === 'googledrive' || $value->type === 'onedrive' || $value->type === 'dropbox') {
                $temp = new stdClass();
                $temp->value = $categories[$key]->cloud_id;
                $temp->text = str_repeat('-', ($categories[$key]->level - 1)) . ' ' . $categories[$key]->title;
                $options[$temp->value] = $temp->text;
            }
        }

        return $options;
    }
}
