<?php

$modelUsers = new EmundusModelUsers();
$jinput = JFactory::getApplication()->input;

$users = $jinput->getString('user');

if($users == 'em-check-all')
{
    $allUsers = $modelUsers->getUsers(0,0);
    $userDetails = $modelUsers->getAllInformationsToExport($allUsers[0]->id);

}
else
{
    $userDetails = $modelUsers->getAllInformationsToExport($users);
}

?>

<style>
    .form-container {
        text-align: center;
    }

    .form-group {
        margin: 0 auto;
        text-align: left;
        width: fit-content;
        margin-bottom: 10px;
    }

    .checkbox-label {
        display: inline-block;
        vertical-align: top;
    }
</style>

<div class="form-container">
    <h5 style="margin-top: 10px; margin-bottom: 10px;"><?= JText::_('COM_EMUNDUS_EXPORTS_SELECT_INFORMATIONS'); ?></h5>
    <br>

    <div class="form-group">
        <input type="checkbox" id="checkbox-all" name="checkbox-all" value="all">
        <label for="checkbox-all" class="checkbox-label">Tout</label>
    </div>

    <?php
    foreach ($userDetails as $field) {
        ?>
        <div class="form-group">
            <input type="checkbox" id="checkbox-<?= $field->label ?>" name="checkbox-<?= $field->label ?>" value="<?= $field->label ?>">
            <label for="checkbox-<?= $field->label ?>" class="checkbox-label"><?= JText::_($field->label) ?></label>
        </div>
        <?php
    }
    ?>
</div>





