<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 17/09/14
 * Time: 10:30
 */?>

<form action="index.php?option=com_emundus&controller=users&task=addgroup" id="em-add-group" class="em-addGroup" role="form" method="post">

    <h3><?= JText::_('COM_EMUNDUS_USERS_ADD_GROUP'); ?></h3>

    <fieldset class="em-addGroup-defineGroup">
        <div class="form-group em-addGroup-defineGroup-name">
            <label class="control-label em-addGroup-defineGroup-description" for="gname"><?= JText::_('COM_EMUNDUS_GROUPS_GROUP_NAME'); ?></label>
            <input type="text" class="form-control" id="gname" name="gname">
        </div>
        <div class="form-group em-addGroup-defineGroup-description">
            <label class="control-label" for="gdescription"><?= JText::_('COM_EMUNDUS_GROUPS_GROUP_DESCRIPTION'); ?></label>
            <textarea class="form-control" name="gdescription" id="gdescription" cols="30" rows="3"></textarea>
        </div>
        <div class="form-group em-addGroup-defineGroup-assocProgram">
            <label class="control-label" for="gprogs"><?= JText::_('COM_EMUNDUS_GROUPS_COURSES'); ?></label>
            <select name="gprogs" id="gprogs" data-placeholder="<?= JText::_("COM_EMUNDUS_GROUPS_CHOOSE_PROGRAMME"); ?>" multiple>
                <?php foreach ($this->progs as $prog) :?>
                    <option value="<?= $prog['code']; ?>"><?= trim($prog['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('form').css({padding:"26px"});
        $('#gprogs').chosen({width:'100%'});
    });
</script>

