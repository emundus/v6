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

<div class="form-container">
    <div class="left-column">
        <div class="column-title">
                <span style="margin-top: 10px; margin-bottom: 10px;">
                    <?= JText::_('COM_EMUNDUS_EXPORTS_SELECT_INFORMATIONS'); ?>
                </span>
        </div>
        <div class="form-group">
            <div class="all-boxes">
                <input type="checkbox" id="checkbox-all" name="checkbox-all" value="all"
                       onchange="checkAllUserElement(this)">
                <label for="checkbox-all" class="checkbox-label"><?= JText::_('ALL_FEMININE'); ?></label>
            </div>

        </div>
    </div>
</div>
<hr class="horizontal-line">
<div class="form-container">
    <div class="left-column">
        <?php
        foreach ($userDetails['columns'] as $column) {
            ?>
            <div class="form-group">
                <input type="checkbox" id="checkbox-<?= $column->name ?>" name="checkbox-csv"
                       value="<?= $column->name ?>"
                       onchange="uncheckCheckboxAllElement(this)">
                <label for="checkbox-<?= $column->name ?>"
                       class="checkbox-label"><?= JText::_($column->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="right-column">
        <?php
        foreach ($userDetails['user_data'] as $field) {
            ?>
            <div class="form-group">
                <input type="checkbox" id="checkbox-<?= $field->name ?>" name="checkbox-csv"
                       value="<?= $field->name ?>"
                       onchange="uncheckCheckboxAllElement(this)">
                <label for="checkbox-<?= $field->name ?>" class="checkbox-label"><?= JText::_($field->label) ?></label>
            </div>
            <?php
        }
        ?>
    </div>
</div>


<style>
    .form-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .left-column,
    .right-column {
        width: 55%;
    }

    .form-group {
        text-align: left;
        margin-bottom: 10px;
    }

    .checkbox-label {
        display: inline-block;
        vertical-align: top;
    }

    .horizontal-line {
        width: 100%;
        border-top: 1px solid #ccc;
        margin-bottom: 20px;
    }

    .column-title {
        white-space: nowrap;
        margin-top: 10px;
        color: #0B0C0F;
    }

    #checkbox-all + .checkbox-label {
        font-weight: bold;
    }

    .all-boxes {
        margin-top: 25px;
        margin-bottom: -10px;
    }

</style>