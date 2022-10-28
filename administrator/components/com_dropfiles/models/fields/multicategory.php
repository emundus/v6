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
class JFormFieldMultiCategory extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'MultiCategory';

    /**
     * Set output
     *
     * @param array|object        $element Element object
     * @param string|array|object $value   Value of element
     * @param array|object        $group   Group of element
     *
     * @return array|object Result List
     *
     * @throws Exception Thrown if there's an error
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $type = JFactory::getApplication()->input->getString('type', '');

        if ($type !== 'default') {
            return false;
        }
        return parent::setup($element, $value, $group);
    }

    /**
     * Get label
     *
     * @return string
     */
    protected function getLabel()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $app = JFactory::getApplication();
        $type = $app->input->getString('type', '');
        if ($type === 'default') {
            return parent::getLabel();
        } else {
            return '';
        }
    }

    /**
     * Add field get category value
     *
     * @return string
     */
    public function getInput()
    {
        $modelFiles = JModelLegacy::getInstance('Files', 'dropfilesModel');
        $app = JFactory::getApplication();
        $type = $app->input->getString('type', '');
        $html = '';
        if ($type === 'default') {
            $select = (isset($this->value)) ? explode(',', $this->value) : array();
            unset($select[count($select) - 1]);
            $formFile = $this->form->getData();
            $idFile = 0;
            foreach ($formFile as $key => $value) {
                if ($key === 'id') {
                    $idFile = (int)$value;
                }
            }
            $file = $modelFiles->getFile($idFile);
            $idCategory = (isset($file->catid)) ? $file->catid : null;
            $selectionValues = $this->getSelectionValues();
            $selection_value = (count($selectionValues)) ? $selectionValues : array();
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            $html .= '<div class="controls-multi-cat-button">';
            $data_placeholder = 'data-placeholder="' . JText::_('COM_DROPFILES_FIELD_FILE_MULTI_CATEGORY_DEFAULT') . '" class="chosen-select chosen inputbox ju-input file_multi_category" multiple="true"';
            $html .= $this->dropfilesSelect($selection_value, 'jform[file_multi_category][]', $select, $data_placeholder, $idCategory);
            $html .= '</div>';
        }

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
        $modelCategories = JModelLegacy::getInstance('categories', 'dropfilesModel');
        $category = $modelCategories->getItems();
        $category = $modelCategories->extractOwnCategories($category);
        $options = array();
        foreach ($category as $key => $value) {
            if ($value->type === 'default') {
                $temp = new stdClass();
                $temp->value = $category[$key]->id;
                $temp->text = str_repeat('&nbsp&nbsp', ($category[$key]->level - 1)) . ' ' . $category[$key]->title;
                $options[$temp->value] = $temp->text;
            }
        }

        return $options;
    }

    /**
     * Render a select html
     *
     * @param array   $options  Options array
     * @param string  $name     Name
     * @param string  $select   Select
     * @param string  $attr     Attr
     * @param boolean $disabled Disable
     *
     * @return string
     */
    public function dropfilesSelect(array $options = array(), $name = '', $select = '', $attr = '', $disabled = false)
    {
        $html = '';
        $html .= '<select';
        if ($name !== '') {
            $html .= ' name="' . $name . '"';
        }
        if ($attr !== '') {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $select_option = '';
                if (is_array($select)) {
                    if (in_array($key, $select)) {
                        $select_option = 'selected="selected"';
                    } elseif ((string)$key === (string)$disabled) {
                        $select_option = $this->disabled($disabled, $key, false);
                    } else {
                        $select_option = '';
                    }
                } else {
                    if ($disabled) {
                        $select_option = $this->disabled($disabled, $key, false);
                    } else {
                        $select_option = $this->selected($select, $key, false);
                    }
                }
                $html .= '<option value="' . $key . '" ' . $select_option . '>' . $value . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Outputs the html disabled attribute.
     *
     * Compares the first two arguments and if identical marks as disabled
     *
     * @param mixed   $disabled One of the values to compare
     * @param mixed   $current  The other value to compare if not just true
     * @param boolean $echo     Whether to echo or just return the string
     *
     * @return string Html attribute or empty string
     *
     * @since 3.0.0
     */
    public function disabled($disabled, $current = true, $echo = true)
    {
        return $this->checkedSelectedHelper($disabled, $current, $echo, 'disabled');
    }

    /**
     * Outputs the html selected attribute.
     *
     * Compares the first two arguments and if identical marks as selected
     *
     * @param mixed   $selected One of the values to compare
     * @param mixed   $current  The other value to compare if not just true
     * @param boolean $echo     Whether to echo or just return the string
     *
     * @return string Html attribute or empty string
     *
     * @since 1.0.0
     */
    public function selected($selected, $current = true, $echo = true)
    {
        return $this->checkedSelectedHelper($selected, $current, $echo, 'selected');
    }

    /**
     * Private helper function for checked, selected, disabled and readonly.
     *
     * Compares the first two arguments and if identical marks as $type
     *
     * @param mixed   $helper  One of the values to compare
     * @param mixed   $current The other value to compare if not just true
     * @param boolean $echo    Whether to echo or just return the string
     * @param string  $type    The type of checked|selected|disabled|readonly we are doing
     *
     * @return string Html attribute or empty string
     *
     * @since  2.8.0
     * @access private
     */
    public function checkedSelectedHelper($helper, $current, $echo, $type)  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionDoubleUnderscore,PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.FunctionDoubleUnderscore
    {
        if ((string) $helper === (string) $current) {
            $result =  $type.'="'. $type .'"';
        } else {
            $result = '';
        }

        if ($echo) {
            echo $result;
        }

        return $result;
    }
}
