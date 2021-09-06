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
class JFormFieldPhocadownloadsimport extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Phocadownloadsimport';


    /**
     * Add import file in phocadownload
     *
     * @return string
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $return = '';
        if (JComponentHelper::isInstalled('com_phocadownload')) {
            $dbo = JFactory::getDbo();
            $query = 'SELECT a.id,a.parent_id,a.title,a.alias FROM #__phocadownload_categories AS a ';
            $dbo->setQuery($query);
            $cats = $dbo->loadObjectList();
            $data = array();
            foreach ($cats as $cat) {
                $object = new stdClass();
                $object->text = $cat->title;
                $object->value = (int)$cat->id;
                $object->parentid = (int)$cat->parent_id;
                array_push($data, $object);
            }
            $catid =  -1;
            $tree = array();
            $text = '';
            $tree = self::DRCatTreeOption($data, $tree, $catid, 0, $text);
            $text_slCate = JText::_('COM_DROPFILES_CONFIG_SELECT_A_CATEGORY');
            array_unshift($tree, JHTML::_('select.option', '', '- ' . $text_slCate . ' -', 'value', 'text'));
            $return = '<div class="import-name"><label class="ju-setting-label">'. JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_PHOCA_DOWNLOADS_NAME') .'</label></div>';
            $return .= JHTML::_(
                'select.genericlist',
                $tree,
                $this->name,
                trim(''),
                'value',
                'text',
                $this->value,
                $this->id
            );
            $return .= '<style type="text/css">.docman_title {margin-bottom: 10px;}';
            $return .= '.docman_desc {font-weight: normal;}</style>';
            $return .= '<button id="phocadownload_import_button" class="btn btn-small">';
            $return .= JText::_('COM_DROPFILES_CONFIG_RUN_PHOCADOWNLOAD_IMPORT') . '</button>';
        } else {
            $return = '<span class="check-import-hidden no-phoca" >';
            $return .= JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_PHOCA_DOWNLOADS') . '</span>';
        }
        return $return;
    }


    /**
     * Cat tree option
     *
     * @param array   $DrData      Data
     * @param array   $DrTree      Tree
     * @param integer $DrCurrentId Current Id
     * @param integer $DrId        Id
     * @param string  $DrText      Text
     *
     * @return mixed
     */
    public static function DRCatTreeOption($DrData, $DrTree, $DrCurrentId, $DrId = 0, $DrText = '')
    {
        foreach ($DrData as $key) {
            $show_text = $DrText . $key->text;
            if ($key->parentid === $DrId && $DrCurrentId !== $DrId && $DrCurrentId !== $key->value) {
                $DrTree[$key->value] = new JObject();
                $DrTree[$key->value]->text = $show_text;
                $DrTree[$key->value]->value = $key->value;
                $DrTree = self::DRCatTreeOption($DrData, $DrTree, $DrCurrentId, $key->value, $show_text . ' - ');
            }
        }
        return ($DrTree);
    }
}
