<?php ?>
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
    <h4 style="margin-top: 10px; margin-bottom: 10px;"><?= JText::_('COM_EMUNDUS_EXPORTS_SELECT_INFORMATIONS'); ?></h4>

    <div class="form-group">
        <input type="checkbox" id="checkbox-id" name="checkbox-id" value="id">
        <label for="checkbox-id" class="checkbox-label">ID</label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="checkbox-nom" name="checkbox-nom" value="nom">
        <label for="checkbox-nom" class="checkbox-label"><?= JText::_('COM_EMUNDUS_LASTNAME'); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="checkbox-prenom" name="checkbox-prenom" value="prenom">
        <label for="checkbox-prenom" class="checkbox-label"><?= JText::_('COM_EMUNDUS_FIRSTNAME'); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="checkbox-mail" name="checkbox-mail" value="mail">
        <label for="checkbox-mail" class="checkbox-label"><?= JText::_('COM_EMUNDUS_EMAIL'); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="checkbox-registerdate" name="checkbox-registerdate" value="registerdate">
        <label for="checkbox-registerdate" class="checkbox-label"><?= JText::_('COM_EMUNDUS_REGISTERDATE'); ?></label>
    </div>
</div>
