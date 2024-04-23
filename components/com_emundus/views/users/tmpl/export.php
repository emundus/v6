<?php

$modelUsers = new EmundusModelUsers();
$jinput = JFactory::getApplication()->input;

$users = $jinput->getString('user');

if ($users == 'em-check-all') {
    $allUsers = $modelUsers->getUsers(0, 0);
    $userDetails = $modelUsers->getAllInformationsToExport($allUsers[0]->id);
} else {
    $userDetails = $modelUsers->getAllInformationsToExport($users);
}

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
        foreach ($userDetails['columns'] as $column) {
            ?>
            <div class="form-group flex items-center mb-1">
                <input type="checkbox" id="checkbox-<?= $column->name ?>" name="checkbox-csv" value="<?= $column->name ?>" onchange="uncheckCheckboxAllElement(this)" class="mr-1 mt-2">
                <label for="checkbox-<?= $column->name ?>" class="checkbox-label align-middle mt-1.5"><?= JText::_($column->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="w-1/2">
        <?php
        foreach ($userDetails['user_data'] as $field) {
            ?>
            <div class="form-group flex items-center mb-1">
                <input type="checkbox" id="checkbox-<?= $field->name ?>" name="checkbox-csv" value="<?= $field->name ?>" onchange="uncheckCheckboxAllElement(this)" class="mr-1 mt-2">
                <label for="checkbox-<?= $field->name ?>" class="checkbox-label align-middle mt-1.5"><?= JText::_($field->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
</div>









