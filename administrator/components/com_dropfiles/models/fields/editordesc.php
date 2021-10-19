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
JFormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldEditordesc extends JFormFieldEditor
{
    /**
     * Type
     *
     * @var string
     */
    public $type = 'Editordesc';

    /**
     * Form field input Editor description
     *
     * @return string
     */
    protected function getInput()
    {
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        // Get the field id
        $desc = isset($this->element['desc']) ? $this->element['desc'] : null;

        if ($this->value === '') {
            if ((string)$this->element['name'] === 'add_event_editor') {
                $this->value = DropfilesHelper::getHTMLEmail('file-added.html');
            } elseif ((string)$this->element['name'] === 'edit_event_editor') {
                $this->value = DropfilesHelper::getHTMLEmail('file-edited.html');
            } elseif ((string)$this->element['name'] === 'delete_event_editor') {
                $this->value = DropfilesHelper::getHTMLEmail('file-deleted.html');
            } elseif ((string)$this->element['name'] === 'download_event_editor') {
                $this->value = DropfilesHelper::getHTMLEmail('file-downloaded.html');
            }
        }

        $input = parent::getInput();
        $input .= '<p>' . JText::_('COM_DROPFILES_CONFIG_SUPPORT_TAG') . ': ' . $desc . '</p>';
        return $input;
    }
}
