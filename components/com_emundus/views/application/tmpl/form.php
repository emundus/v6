<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');

$pids = json_decode($this->pids);
$user = $this->userid;
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
                    <a id="download-pdf" class="  clean" target="_blank" href="<?php echo JURI::base(); ?>index.php?option=com_emundus&task=pdf&user=<?php echo $this->sid; ?>&fnum=<?php echo $this->fnum; ?>">
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

            <select class="chzn-select" style="width: 100%" id="select_profile">
                <option value="%">-- <?= JText::_('COM_EMUNDUS_VIEW_FORM_SELECT_PROFILE'); ?> --</option>
                <?php foreach($pids as $pid) : ?>
                    <option value="<?= $pid->pid; ?>"> <?= $pid->label; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" id="user_hidden" value="<?php echo $user ?>">
            <input type="hidden" id="fnum_hidden" value="<?php echo $this->fnum ?>">

            <div class="active content" id="show_profile">
                <?php echo $this->forms; ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    $('#select_profile').on('change', function() {
        /* get the selected profile id*/
        var profile = $(this).children(":selected").attr('value');      /* or just $(this).val() */

        /*http://localhost:8888/index.php?option=com_emundus&task=pdf&user=1883&fnum=2021121312404500000110001883*/
        /*http://localhost:8888/index.php?option=com_emundus&task=pdf&user=1883&fnum=2021121312404500000110001883&profile=1019 */

        if(profile !== "%") {
            /* call to ajax */
            $.ajax({
                type: 'post',
                url: 'index.php?option=com_emundus&controller=application&task=getform',
                dataType: 'json',
                data: { profile: profile, user: $('#user_hidden').attr('value'), $fnum: $('#fnum_hidden').attr('value') },
                success: function(result) {
                    var form = result.data;

                    $('#show_profile').empty();
                    $('#show_profile').append(form.toString());

                    $('#download-pdf').attr('href', 'index.php?option=com_emundus&task=pdf&user=' + $('#user_hidden').attr('value') + '&fnum=' + $('#fnum_hidden').attr('value') + '&profile=' + profile);

                }, error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        }
    })
</script>
