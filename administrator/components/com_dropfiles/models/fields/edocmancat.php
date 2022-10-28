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

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldEdocmancat extends JFormField
{
    /**
     * Type
     *
     * @var string The form field type.
     */
    public $type = 'Edocmancat';

    /**
     * Method to get the field options.
     *
     * @return array The field option objects.
     */
    protected function getInput()
    {
        if (JComponentHelper::isInstalled('com_edocman') && file_exists(JPATH_ROOT . '/components/com_edocman/helper/helper.php')) {
            // Initialise variables.
            require_once JPATH_ROOT . '/components/com_edocman/helper/helper.php';
            $app = JFactory::getApplication();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id, title, parent_id')
                ->from('#__edocman_categories AS tbl')
                ->where('published = 1')
                ->order('ordering');

            $parentId = 0;
            $catId = 0;
            if ($app->isClient('site')) {
                $catId = $app->input->getInt('catid', 0);
                if ($catId) {
                    $query->where('id IN (' . implode(',', EdocmanHelper::getChildrenCategories($catId)) . ')');
                    $sql = 'SELECT parent_id FROM #__edocman_categories WHERE id=' . $catId;
                    $db->setQuery($sql);
                    $parentId = (int)$db->loadResult();
                }
            }
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            $children = array();
            // first pass - collect children
            if (count($rows)) {
                foreach ($rows as $v) {
                    $pt = $v->parent_id;
                    $list = isset($children[$pt]) ? $children[$pt] : array();
                    array_push($list, $v);
                    $children[$pt] = $list;
                }
            }
            $list = JHtml::_('menu.treerecurse', $parentId, '', array(), $children, 9999);
            $options = array();
            $options[] = JHtml::_('select.option', 0, JText::_('COM_DROPFILES_CONFIG_SELECT_A_CATEGORY'));
            if (count($list)) {
                foreach ($list as $row) {
                    $options[] = JHtml::_('select.option', $row->id, $row->treename);
                }
            }

            if ($this->element['readonly']) {
                $disabled = ' disabled="true" ';
            } else {
                $disabled = '';
            }

            if ((string) $this->element['multiple'] === 'true') {
                $multiple = ' multiple="multiple "';
            } else {
                $multiple = '';
            }

            if ($this->element['class']) {
                $class = 'class="' . $this->element['class'] . '" ';
            } else {
                $class = 'class="inputbox" ';
            }

            $return = '';
            $return .= '<div class="import-name"><label class="ju-setting-label">'. JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_EDOCMAN_NAME') .'</label></div>';
            if ($disabled) {
                $return .= JHtml::_('select.genericlist', $options, $this->name, array(
                        'option.text.toHtml' => false,
                        'list.attr' => $class . $disabled . $multiple,
                        'option.text' => 'text',
                        'option.key' => 'value',
                        'list.select' => $this->value
                    )) . '<input type="hidden" name="' . $this->name . '" value="' . $catId . '" />';
            } else {
                $return .= JHtml::_('select.genericlist', $options, $this->name, array(
                    'option.text.toHtml' => false,
                    'list.attr' => $class . $multiple,
                    'option.text' => 'text',
                    'option.key' => 'value',
                    'list.select' => $this->value
                ));
            }

            $return .= '<style type="text/css">.docman_title {margin-bottom: 10px;} ';
            $return .= '.docman_desc {font-weight: normal;}</style>';
            $return .= '<button id="edoc_import_button" class="btn btn-small">';
            $return .= JText::_('COM_DROPFILES_CONFIG_RUN_EDOC_IMPORT') . '</button>';

            return $return;
        } else {
            $return = '<span class="check-import-hidden no-edocman" >';
            $return .= JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_DOCMAN') . '</span>';

            return $return;
        }
    }
}
