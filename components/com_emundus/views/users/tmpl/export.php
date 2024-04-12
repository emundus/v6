<?php

$modelUsers = new EmundusModelUsers();

$userDetails = $modelUsers->getColumnsForm();

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

<?php
foreach ($userDetails as $field) {
    ?>
    <div class="form-group">
        <input type="checkbox" id="checkbox-<?= $field->name ?>" name="checkbox-<?= $field->name ?>" value="<?= $field->name ?>">
        <label for="checkbox-<?= $field->name ?>" class="checkbox-label"><?= $field->name ?></label>
    </div>
    <?php
}
?>
</div>



