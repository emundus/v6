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
class JFormFieldDocmanimport extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Docmanimport';

    /**
     * Field docman
     *
     * @return string
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $class = $this->element['class'] ? ' ' . (string)$this->element['class'] . '' : '';
        if (JComponentHelper::isInstalled('com_docman')) {
            $return = '<div class="import-name isinstall-docmanName"><label class="ju-setting-label">'. JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_DOCMAN_NAME') .'</label></div>';
            $return .= '<select name="' . $this->name . '" id="' . $this->id . '" class="' . $class . '">';
            $return .= '<option value="0">' . JText::_('COM_DROPFILES_CONFIG_SELECT_A_CATEGORY') . '</option>';
            $url_obj_docman = 'com://admin/docman.controller.category';
            $cats = KObjectManager::getInstance()->getObject($url_obj_docman)->limit(0)->sort('title')->browse();
            foreach ($cats as $cat) {
                $return .= '<option value="' . $cat->id . '">';
                $return .= str_repeat('&#8211;', $cat->level - 1) . ' ' . $cat->title;
                $return .= '</option>';
            }
            $return .= '</select>';
            $return .= '<style type="text/css">.docman_title {margin-bottom: 10px;}';
            $return .= '.docman_desc {font-weight: normal;}</style>';
            $return .= '<button id="docman_import_button" class="btn btn-small">';
            $return .= JText::_('COM_DROPFILES_CONFIG_RUN_DOCMAN_IMPORT') . '</button>';
        } else {
            $return = '<span class="check-import-hidden no-docman" >';
            $return .= JText::_('COM_DROPFILES_CONFIG_IMPORT_AVAILABLE_DOCMAN') . '</span>';
        }
        return $return;
    }
}
