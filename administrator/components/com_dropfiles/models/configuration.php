<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package    Joomla.Administrator
 * @subpackage com_config
 *
 * @copyright Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

/**
 * Model for component configuration
 */
class DropfilesModelConfiguration extends JModelAdmin
{
    /**
     * Method to get form file config
     *
     * @param array   $data     File data
     * @param boolean $loadData Load data
     *
     * @return boolean
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Add the search path for the admin component config.xml file.
        JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dropfiles');

        $form = $this->loadForm('com_dropfiles.config', 'config', array('control' => 'jform', 'load_data' => $loadData), false, '/config');

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to save config
     *
     * @param array $jformData Form data
     *
     * @return boolean
     */
    public function saveGlobalConfig($jformData)
    {
        //Load the data form
        $form = $this->getForm();

        $default_parameter = array();
        foreach ($form->getFieldsets() as $name => $fieldsets) {
            foreach ($form->getFieldset($name) as $fieldset) {
                if ($fieldset->type === 'Radio') {
                    $fieldset->value = 0;
                }
                $default_parameter[$fieldset->fieldname] = $fieldset->value;
            }
        }

        $new_parameter = array_replace($default_parameter, $jformData);


        //Set configuration
        if (!DropfilesComponentHelper::setParams($new_parameter)) {
            return false;
        }

        return true;
    }

    /**
     * Method to save permissions
     *
     * @param array $jformData Form data
     *
     * @return boolean
     */
    public function savePermissions($jformData)
    {
        // Save the rules.
        if (isset($jformData) && isset($jformData['rules'])) {
            $form = $this->getForm($jformData);

            // Validate the posted data.
            $postedRules = $this->validate($form, $jformData);

            $rules = new JAccessRules($postedRules['rules']);
            $asset = JTable::getInstance('asset');

            if (!$asset->loadByName($jformData['component'])) {
                $root = JTable::getInstance('asset');
                $root->loadByName('root.1');
                $asset->name = $jformData['component'];
                $asset->title = $jformData['component'];
                $asset->setLocation($root->id, 'last-child');
            }
            $asset->rules = (string)$rules;

            if (!$asset->check() || !$asset->store()) {
                $this->setError($asset->getError());
                return false;
            }
        }

        return true;
    }
}
