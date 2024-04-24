<?php

use Joomla\CMS\Language\Text;

$m_users = new EmundusModelUsers();
$jinput = JFactory::getApplication()->input;

$euser_columns = $m_users->getColumnsForm();
$user_columns = $m_users->getJoomlaUserColumns();

?>

<div>
    <div>
        <span class="mt-2 mb-4 block"><?= JText::_('COM_EMUNDUS_EXPORTS_SELECT_INFORMATIONS'); ?></span>
    </div>
    <div class="form-group flex items-center">
        <div class="all-boxes flex items-center">
            <input type="checkbox" id="checkbox-all" name="checkbox-all" value="all"
                   onchange="checkAllUserElement(this)" class="mr-2">
            <label for="checkbox-all" class="checkbox-label font-bold mt-1"><?= JText::_('ALL_FEMININE'); ?></label>
        </div>
    </div>
</div>
<hr class="w-full border-t border-gray-300 my-2">
<div class="flex justify-between items-start">
    <div class="w-1/2">
        <?php
        foreach ($euser_columns as $column) {
            ?>
            <div class="form-group flex items-center mb-1">
                <input type="checkbox" id="checkbox-<?= $column->name ?>" name="checkbox-csv" value="<?= $column->label ?>" onchange="uncheckCheckboxAllElement(this)" class="mr-1 mt-2">
                <label for="checkbox-<?= $column->name ?>" class="checkbox-label align-middle mt-1.5"><?= Text::_($column->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="w-1/2">
        <?php
        foreach ($user_columns as $field) {
            ?>
            <div class="form-group flex items-center mb-1">
                <input type="checkbox" id="checkbox-<?= $field->name ?>" name="checkbox-csv" value="<?= $field->label ?>" onchange="uncheckCheckboxAllElement(this)" class="mr-1 mt-2">
                <label for="checkbox-<?= $field->name ?>" class="checkbox-label align-middle mt-1.5"><?= Text::_($field->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
</div>









