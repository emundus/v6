<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');

$pids = json_decode($this->pids);
$defaultpid = $this->defaultpid;
$user = $this->userid;
?>

<!--<div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> </div>
-->

<style type="text/css">
    .group-result { color: #16afe1 !important; }
</style>

<div class="row">
    <div class="panel panel-default widget em-container-form">
        <div class="panel-heading em-container-form-heading">
            <h3 class="panel-title">
                <span class="material-icons">list_alt</span>
                <?php echo JText::_('COM_EMUNDUS_APPLICATION_APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COM_EMUNDUS_APPLICATION_COMPLETED"); ?>
                <?php if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
                    <a id="download-pdf" class="  clean" target="_blank" href="<?php echo JURI::base(); ?>index.php?option=com_emundus&task=pdf&user=<?php echo $this->sid; ?>&fnum=<?php echo $this->fnum; ?>">
                        <button class="btn btn-default" data-title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_DOWNLOAD_APPLICATION_FORM'); ?>" data-toggle="tooltip" data-placement="right" title="<?= JText::_('COM_EMUNDUS_APPLICATION_DOWNLOAD_APPLICATION_FORM'); ?>"><span class="material-icons">file_download</span></button>
                    </a>
                <?php endif;?>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
            </div>
        </div>
        <div class="panel-body Marginpanel-body em-container-form-body">
            <input type="hidden" id="dpid_hidden" value="<?php echo $defaultpid->pid ?>"/>

            <div id="em-switch-profiles">
                <div class="em_label">
                    <label class="control-label em-filter-label"><?= JText::_('PROFILE_FORM'); ?></label>
                </div>

                <select class="chzn-select em-chosen-select" id="select_profile">
                    <option value="<?= $defaultpid->pid; ?>" selected style=""> <?= $defaultpid->label; ?></option>
                    <?php foreach($pids as $pid) : ?>
                        <optgroup class="step_group_profile" label ="<?= strtoupper($pid->lbl) ?>" style="">
                            <?php if(is_array($pid->data)) : ?>
                                <?php foreach($pid->data as $data) : ?>
                                    <?php if($data->pid != $defaultpid->pid): ?>
                                        <?php if($data->step !== null) : ?>
                                            <option style="" value="<?= $data->pid; ?>"> <?= $data->label; ?></option>
                                        <?php else: ?>
                                            <option style="" value="<?= $data->pid; ?>"><?= $data->label; ?></option>
                                        <?php endif ?>
                                    <?php endif ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option style="" value="<?= $pid->data->pid; ?>"> <?= $pid->data->label; ?></option>
                            <?php endif;?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="active content" id="show_profile">
                <?php echo $this->forms; ?>
            </div>

            <input type="hidden" id="user_hidden" value="<?php echo $user ?>">
            <input type="hidden" id="fnum_hidden" value="<?php echo $this->fnum ?>">
        </div>
    </div>
</div>
<script>
    $(".chzn-select").chosen();
    var dpid = $('#dpid_hidden').attr('value');

    if($('#select_profile option').length == 1) {
        $('#em-switch-profiles').remove();
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    $('#select_profile').on('change', function() {
        /* get the selected profile id*/
        var profile = $(this).val();      /* or just $(this).val() */

        $('#show_profile').empty();
        $('#show_profile').before('<div id="loading"><img src="'+loading+'" alt="loading"/></div>');

        /* all other options will be normal */
        $('#select_profile option').each(function() {
            if($(this).attr('value') !== profile) {
                $(this).prop('disabled', false);
                $(this).css('font-style', 'unset');
            }
        })

        /* call to ajax */
        $.ajax({
            type: 'post',
            url: 'index.php?option=com_emundus&controller=application&task=getform',
            dataType: 'json',
            data: { profile: profile, user: $('#user_hidden').attr('value'), fnum: $('#fnum_hidden').attr('value') },
            success: function(result) {
                var form = result.data;

                $('#loading').remove();

                if(form) {
                    $('#show_profile').append(form.toString());
                    $('#download-pdf').attr('href', 'index.php?option=com_emundus&task=pdf&user=' + $('#user_hidden').attr('value') + '&fnum=' + $('#fnum_hidden').attr('value') + '&profile=' + profile);
                }

            }, error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    })
</script>
