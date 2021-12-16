<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');

$pids = json_decode($this->pids);
?>

<!--<div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> </div>
-->
<div class="row">
    <div class="panel panel-default widget em-container-form">
        <div class="panel-heading em-container-form-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-list"></span>
                <?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?>
                <?php if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
                    <a class="  clean" target="_blank" href="<?php echo JURI::base(); ?>index.php?option=com_emundus&task=pdf&user=<?php echo $this->sid; ?>&fnum=<?php echo $this->fnum; ?>">
                        <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>" data-toggle="tooltip" data-placement="right" title="<?= JText::_('DOWNLOAD_APPLICATION_FORM'); ?>"><span class="glyphicon glyphicon-save"></span></button>
                    </a>
                <?php endif;?>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
            </div>
        </div>
        <div class="panel-body Marginpanel-body em-container-form-body">

            <div class="em_label">
                <label class="control-label em-filter-label"><?= JText::_('PROFILE_FORM'); ?></label>
            </div>

            <select class="chzn-select em-filt-select" id="profiles" style="width: 100%">
                <option value="%">-- <?= JText::_('COM_EMUNDUS_VIEW_FORM_SELECT_PROFILE'); ?> --</option>
                <?php foreach($pids as $pid) : ?>
                    <option value="<?= $pid->pid; ?>"> <?= $pid->label; ?></option>
                <?php endforeach; ?>
            </select>

            <div class="active content">
                <?php echo $this->forms; ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
