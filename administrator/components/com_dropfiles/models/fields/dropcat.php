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
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldDropcat extends JFormFieldList
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Dropcat';

    /**
     * Method to get the field input dropcat
     *
     * @return string
     */
    protected function getInput()
    {
        // Get the field id
        $input = parent::getInput();
        return $input;
    }

    /**
     * Method to get a list of cats
     *
     * @return array  The field option objects.
     *
     * @since 3.1
     */
    protected function getOptions()
    {
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        $path_model = JPATH_ROOT . '/administrator/components/com_dropfiles/models/';
        JModelLegacy::addIncludePath($path_model, 'DropfilesModelCategories');
        $modelCategories = JModelLegacy::getInstance('categories', 'dropfilesModel');
        $all_cats = $modelCategories->getAllCategories();

        $options = array();
        $hideNone = (string)$this->element['hide_none'];
        $this->hideNone = ($hideNone === 'true' || $hideNone === 'hideNone' || $hideNone === '1');

        // Prepend some default options based on field attributes.
        if (!$this->hideNone) {
            $fieldname_replace = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
            $options[] = JHtml::_('select.option', '-1', JText::alt('JOPTION_DO_NOT_USE', $fieldname_replace));
        }
        $fieldname_replace = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options[] = JHtml::_('select.option', '', JText::alt('JGLOBAL_SELECTION_ALL', $fieldname_replace));

        $totalCat = count($all_cats);
        for ($i = 0; $i < $totalCat; $i++) {
            $temp = new stdClass();
            $temp->value = $all_cats[$i]->id;
            $temp->text = str_repeat('-', ($all_cats[$i]->level - 1)) . ' ' . $all_cats[$i]->title;
            $options[] = $temp;
        }
        return $options;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $val = parent::getValue();
        if (is_array($val)) {
            $val = implode(',', $val);
        }
        return $val;
    }
}
