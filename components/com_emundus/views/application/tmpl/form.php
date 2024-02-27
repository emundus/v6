<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');

$defaultpid = $this->defaultpid;
$user = $this->userid;
?>

<!--<div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> </div>
-->

<style type="text/css">
    .group-result { color: #16afe1 !important; }

     .profile_tab p {
         overflow: hidden;
         white-space: nowrap;
         text-overflow: ellipsis;
         max-width: 200px;
     }

</style>

<div class="row">
    <div class="panel panel-default widget em-container-form">
	    <?php if ($this->header == 1) : ?>
            <div class="panel-heading em-container-form-heading">
                <h3 class="panel-title">
                    <span class="material-icons">list_alt</span>
                    <?php echo JText::_('COM_EMUNDUS_APPLICATION_APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COM_EMUNDUS_APPLICATION_COMPLETED"); ?>
                    <?php if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
                        <button id="download-all-phase-pdf"
                                class="em-mt-8 em-ml-8"
                                data-fnum="<?= $this->fnum ?>"
                                data-toggle="tooltip"
                                data-placement="right"
                                title="<?= JText::_('COM_EMUNDUS_APPLICATION_DOWNLOAD_APPLICATION_FORM'); ?>"
                        >
                            <span class="material-icons-outlined" data-fnum="<?= $this->fnum ?>">download_2</span>
                        </button>
                    <?php endif;?>
                </h3>
                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
                </div>
            </div>
        <?php endif; ?>
	    <?php if (!EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id) && $this->header == 1) : ?>
            <div class="em-flex-row em-mt-16">
                <div class="em-flex-row em-small-flex-column em-small-align-items-start">
                    <div class="em-profile-picture-big no-hover"
					    <?php if(empty($this->applicant->profile_picture)) :?>
                            style="background-image:url(<?php echo JURI::base() ?>/media/com_emundus/images/profile/default-profile.jpg)"
					    <?php else : ?>
                            style="background-image:url(<?php echo JURI::base() ?>/<?php echo $this->applicant->profile_picture ?>)"
					    <?php endif; ?>
                    >
                    </div>
                </div>
                <div class="em-ml-24 ">
                    <p class="em-font-weight-500">
					    <?php echo $this->applicant->lastname . ' ' . $this->applicant->firstname; ?>
                    </p>
                    <p><?php echo $this->fnum ?></p>
                </div>
            </div>
	    <?php endif; ?>

        <div class="panel-body Marginpanel-body em-container-form-body">
            <input type="hidden" id="dpid_hidden" value="<?php echo $defaultpid->pid ?>"/>

            <div id="em-switch-profiles" <?php if(sizeof($this->pids) < 1): ?>style="display: none"<?php endif; ?>>

                <div class="em_label">
                    <label class="control-label em-filter-label em-font-size-14" style="margin-left: 0 !important;"><?= JText::_('PROFILE_FORM'); ?></label>
                </div>

                <div class="em-flex-row em-border-bottom-neutral-300" style="overflow:hidden; overflow-x: auto;">

                    <div id="tab_link_<?php echo $defaultpid->pid; ?>" onclick="updateProfileForm(<?php echo $defaultpid->pid ?>)" class="em-mr-16 em-flex-row em-light-tabs profile_tab em-pointer em-light-selected-tab mb-2">
                        <p class="em-font-size-14 em-neutral-900-color" title="<?= $defaultpid->label; ?>" style="white-space: nowrap"> <?= $defaultpid->label; ?></p>
                    </div>

	                <?php foreach($this->pids as $pid) : ?>
	                    <?php if(is_array($pid['data'])) : ?>
	                     <?php foreach($pid['data'] as $data) : ?>
	                      <?php if($data->pid != $defaultpid->pid): ?>
	                          <?php if($data->step !== null) : ?>
                                <div id="tab_link_<?php echo $data->pid; ?>" onclick="updateProfileForm(<?php echo $data->pid ?>)" class="em-mr-16 em-flex-row profile_tab em-light-tabs em-pointer mb-2">
                                    <p class="em-font-size-14 em-neutral-600-color" title="<?php echo $data->label; ?>" style="white-space: nowrap"><?php echo $data->label; ?></p>
                                </div>
				                <?php else: ?>
                                   <div id="tab_link_<?php echo $data->pid; ?>" onclick="updateProfileForm(<?php echo $data->pid ?>)" class="em-mr-16 profile_tab em-flex-row em-light-tabs em-pointer mb-2">
                                       <p class="em-font-size-14 em-neutral-600-color" title="<?php echo $data->label; ?>" style="white-space: nowrap"><?php echo $data->label; ?></p>
                                   </div>
				                <?php endif ?>
                          <?php endif ?>
		                <?php endforeach; ?>
                        <?php else : ?>
                                <div id="tab_link_<?php echo $pid['data']->pid; ?>" onclick="updateProfileForm(<?php echo $pid['data']->pid ?>)" class="em-mr-16 profile_tab em-flex-row em-light-tabs em-pointer mb-2">
                                    <p class="em-font-size-14 em-neutral-600-color" title="<?php echo $pid['data']->label; ?>" style="white-space: nowrap"> <?php echo $pid['data']->label; ?></p>
                                </div>
                        <?php endif;?>
	                <?php endforeach; ?>
                </div>

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
    });

    document.getElementById('download-all-phase-pdf').addEventListener('click', function (e) {
        const fnum = e.target.getAttribute('data-fnum');
        if (fnum) {
            // check if function  exists
            if (typeof export_pdf === 'function') {
                export_pdf(JSON.stringify({0: fnum}), null, true);
            } else {
                console.error('Function export_pdf does not exist');
            }
        }
    });
</script>
