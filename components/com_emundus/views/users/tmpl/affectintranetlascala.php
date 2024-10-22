<?php
?>

<style>
    form {
        margin: 0;
    }
</style>

<form action = "index.php?option=com_emundus&controller=users&task=affectjoomlagroups" id="em-affect-groups" class="em-affect-groups" role="form" method="post">
    <fieldset class="em-affect-groups-groupList">
        <div class="form-group">
            <label class="control-label em-affect-groups-groupList-label" for="agroups"><?php echo JText::_('COM_EMUNDUS_GROUPS_LIST'); ?></label>
            <select name = "agroups" id = "agroups" data-placeholder="<?php echo JText::_("COM_EMUNDUS_GROUPS_CHOOSE_GROUPS")?>" multiple>
				<?php foreach($this->groups as $group):?>
                    <option value = "<?php echo $group->group_id?>"><?php echo $group->category_label?></option>
				<?php endforeach?>
            </select>
        </div>
    </fieldset>
</form>
<script type="text/javascript">
    $(document).ready(function()
    {
        $('#agroups').chosen({width:'100%'});
    });
</script>
