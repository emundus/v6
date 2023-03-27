<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
$dateTime = $dateTime->setTimezone(new DateTimeZone($site_offset));
$now = $dateTime->format('Y-m-d H:i:s');

$tmp_applications = $applications;
foreach ($applications as $key => $application) {
    if ($application->published == '1' || ($show_remove_files == 1 && $application->published == '-1') || ($show_archive_files == 1 && $application->published == '0')){
        continue;
    } else {
        unset($tmp_applications[$key]);
    }
}

$applications = [];
$status_group = [];
$missing_status = [];

if(!empty($groups) && !empty($tmp_applications)) {
    foreach ($groups as $key => $group) {
        $status_to_check = explode(',', $group->mod_em_application_group_status);
        foreach ($status_to_check as $step) {
            $status_group[] = $step;
        }
    }

    foreach ($status as $step){
        if(!in_array($step['step'],$status_group)){
            $missing_status[] = $step['step'];
        }
    }
    if(!empty($missing_status)){
        $groups->{'mod_em_application_group'.sizeof($groups)} = new stdClass();
        $groups->{'mod_em_application_group'.sizeof($groups)}->{'mod_em_application_group_status'} = implode(',',$missing_status);
        $groups->{'mod_em_application_group'.sizeof($groups)}->{'mod_em_application_group_title'} = $title_other_section;
    }

    foreach ($groups as $key => $group) {
        $applications[$key]['applications'] = array_filter($tmp_applications, function ($application) use ($group) {
            $status_to_check = explode(',', $group->mod_em_application_group_status);
            return in_array($application->status,$status_to_check) !== false;
        });
        $applications[$key]['label'] = $group->mod_em_application_group_title;
    }
} elseif(!empty($tmp_applications)) {
    $applications['all']['applications'] = $tmp_applications;
}

ksort($applications);

?>
<div class="mod_emundus_applications___header">
    <?php if ($mod_em_applications_show_hello_text == 1) : ?>
        <p class="em-h3 em-mb-8"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_HELLO') . $user->firstname ?></p>
    <?php endif; ?>

    <?php if (sizeof($applications) > 0) : ?>
        <div class="em-flex-column em-flex-align-start">
            <?php if ($show_add_application && ($position_add_application == 3 || $position_add_application == 4) && $applicant_can_renew) : ?>
                <a id="add-application" class="btn btn-success em-mt-32" style="width: 40%" href="<?= $cc_list_url; ?>">
                    <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($show_show_campaigns) : ?>
                <a id="add-application" class="btn btn-success em-mt-16 em-mb-8" style="width: 40%" href="<?= $campaigns_list_url; ?>">
                    <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <span class="mod_emundus_applications___header_desc"><?php echo $description; ?></span>

        <?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
            <a id="add-application" class="btn btn-success em-mt-32"  href="<?= $cc_list_url; ?>">
                <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
            </a>
            <hr>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($mod_em_applications_show_search && sizeof($applications) > 0): ?>
    <div class="em-searchbar em-flex-row-justify-end em-mt-32">
        <label for="searchword" style="display: inline-block">
            <input name="searchword" type="text" id="applications_searchbar" class="form-control" placeholder="<?php echo JText::_('MOD_EM_APPLICATIONS_SEARCH') ?>">
        </label>
    </div>
<?php endif; ?>

<div class="em-mt-32">
    <?php if (sizeof($applications) == 0) : ?>
        <hr>
        <div class="mod_emundus_applications__list_content--default">
            <p class="em-text-neutral-900 em-h5 em-applicant-title-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE') ?></p><br/>
            <p class="em-text-neutral-900 em-default-font em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT') ?></p>
            <p class="em-applicant-text-color em-default-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT_2') ?></p><br/>
            <div class="em-flex-row-justify-end mod_emundus_campaign__buttons em-mt-32">
                <?php if ($show_show_campaigns) : ?>
                    <a id="add-application" class="em-secondary-button em-w-auto em-default-font em-applicant-border-radius" style="width: auto" href="<?= $campaigns_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($show_add_application && $applicant_can_renew) : ?>
                    <a id="add-application" class="em-applicant-primary-button em-w-auto em-ml-8 em-default-font em-applicant-border-radius" style="width: auto" href="<?= $cc_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else : ?>
        <?php foreach ($applications as $key => $group) : ?>

            <?php if (sizeof($group['applications']) > 0) : ?>
                <div class="em-mb-44">
                    <p class="em-h5 em-mb-24"><?php echo JText::_($group['label']) ?></p>

                    <div class="<?= $moduleclass_sfx ?> mod_emundus_applications___content">
                        <?php foreach ($group['applications'] as $application) : ?>

                            <?php
                            $is_admission = in_array($application->status, $admission_status);
                            $display_app = true;
                            if(!empty($show_status) && !in_array($application->status, $show_status)) {
                                $display_app = false;
                            }

                            if($display_app) {
                                $state = $application->published;
                                $confirm_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&confirm=1';
                                $first_page_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum;
                                if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0' ) : ?>
                                    <?php
                                    if ($file_tags != '') {

                                        $post = array(
                                            'APPLICANT_ID'  => $user->id,
                                            'DEADLINE'      => strftime("%A %d %B %Y %H:%M", strtotime($application->end_date)),
                                            'CAMPAIGN_LABEL' => $application->label,
                                            'CAMPAIGN_YEAR'  => $application->year,
                                            'CAMPAIGN_START' => $application->start_date,
                                            'CAMPAIGN_END'  => $application->end_date,
                                            'CAMPAIGN_CODE' => $application->training,
                                            'FNUM'          => $application->fnum
                                        );

                                        $tags = $m_email->setTags($user->id, $post, $application->fnum, '', $file_tags);
                                        $file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
                                        $file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($application->fnum));
                                    }

	                                $current_phase = $m_campaign->getCurrentCampaignWorkflow($application->fnum);

                                    ?>
                                    <div class="row em-border-neutral-300 mod_emundus_applications___content_app em-pointer" id="application_content<?php echo $application->fnum ?>" onclick="openFile(event,'<?php echo $first_page_url ?>')">
                                        <div class="em-w-100">
                                            <div class="em-flex-row mod_emundus_applications___content_text">
                                                <?php if ($show_fnum) : ?>
                                                    <div class="em-mb-8 em-font-size-14">
                                                        <span class="em-applicant-default-font em-neutral-800-color">N°<?php echo $application->fnum ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="material-icons em-text-neutral-600" id="actions_button_<?php echo $application->fnum ?>" style="font-size: 22px">more_vert</span>

                                                        <!-- ACTIONS BLOCK -->
                                                        <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color" id="actions_block_<?php echo $application->fnum ?>" style="display: none">
                                                            <a onclick="openFile(event,'<?php echo $first_page_url ?>')" class="em-text-neutral-900 em-pointer" href="<?= JRoute::_($first_page_url); ?>" id="actions_block_open_<?php echo $application->fnum ?>">
                                                                <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                                            </a>
                                                            <?php if(in_array($application->status,$status_for_delete)) :?>
                                                                <a class="em-text-neutral-900 em-pointer" onclick="deletefile('<?php echo $application->fnum; ?>');" id="actions_block_delete_<?php echo $application->fnum ?>">
                                                                    <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                                                </a>
                                                            <?php endif; ?>
	                                                        <?php
	                                                        foreach($custom_actions as $custom_action_key => $custom_action) {

		                                                        if (in_array($application->status, $custom_action->mod_em_application_custom_action_status)){
			                                                        ?>
                                                                    <a id="actions_button_custom_<?= $custom_action_key; ?>" class="em-text-neutral-900 em-pointer" href="<?= str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link) ?>" <?= $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '' ?> ><?= JText::_($custom_action->mod_em_application_custom_action_label) ?></a>
			                                                        <?php
		                                                        }
	                                                        }
	                                                        ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($mod_emundus_applications_show_programme == 1) : ?>
                                                <div class="em-flex-row em-flex-space-between em-mb-12">
                                                    <?php
                                                    $color = '#1C6EF2';
                                                    $background = '#C8E1FE';
                                                    if(!empty($application->tag_color)){
                                                        $color = $application->tag_color;
                                                        switch ($application->tag_color) {
                                                            case '#20835F':
                                                                $background = '#DFF5E9';
                                                                break;
                                                            case '#DB333E':
                                                                $background = '#FFEEEE';
                                                                break;
                                                            case '#FFC633':
                                                                $background = '#FFFBDB';
                                                                break;
                                                        }
                                                    }
                                                    ?>
                                                    <p class="em-programme-tag" style="color: <?php echo $color ?>;background-color:<?php echo $background ?>">
                                                        <?php  echo $application->programme; ?>
                                                    </p>
                                                    <?php if (!$show_fnum) : ?>
                                                        <div>
                                                            <span class="material-icons em-text-neutral-600" id="actions_button_<?php echo $application->fnum ?>" style="font-size: 16px">more_vert</span>

                                                            <!-- ACTIONS BLOCK -->
                                                            <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color" id="actions_block_<?php echo $application->fnum ?>" style="display: none">
                                                                <a class="em-text-neutral-900 em-pointer" href="<?= JRoute::_($first_page_url); ?>" id="actions_block_open_<?php echo $application->fnum ?>">
                                                                    <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                                                </a>
                                                                <?php if (in_array($application->status, $status_for_delete)) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer" onclick="deletefile('<?php echo $application->fnum; ?>');" id="actions_block_delete_<?php echo $application->fnum ?>">
                                                                        <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php
                                                                foreach($custom_actions as $custom_action_key => $custom_action) {

                                                                    if (in_array($application->status, $custom_action->mod_em_application_custom_action_status)){
                                                                        ?>
                                                                        <a id="actions_button_custom_<?= $custom_action_key; ?>" class="em-text-neutral-900 em-pointer" href="<?= str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link) ?>" <?= $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '' ?> ><?= JText::_($custom_action->mod_em_application_custom_action_label) ?></a>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="<?= JRoute::_($first_page_url); ?>" class="em-h6 mod_emundus_applications___title" id="application_title_<?php echo $application->fnum ?>">
                                                    <span><?= ($is_admission &&  $add_admission_prefix)?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?></span>
                                                </a>
                                            <?php else : ?>
                                                <div class="em-flex-row em-flex-space-between em-flex-align-start em-mb-12">
                                                    <a href="<?= JRoute::_($first_page_url); ?>" class="em-h6 mod_emundus_applications___title" id="application_title_<?php echo $application->fnum ?>">
                                                        <span><?= ($is_admission &&  $add_admission_prefix)?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?></span>
                                                    </a>
                                                    <?php if (!$show_fnum) : ?>
                                                        <div class="em-mt-4">
                                                            <span class="material-icons em-text-neutral-600" id="actions_button_<?php echo $application->fnum ?>" style="font-size: 16px">more_vert</span>

                                                            <!-- ACTIONS BLOCK -->
                                                            <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color" id="actions_block_<?php echo $application->fnum ?>" style="display: none">
                                                                <a class="em-text-neutral-900 em-pointer" href="<?= JRoute::_($first_page_url); ?>" id="actions_block_open_<?php echo $application->fnum ?>">
                                                                    <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                                                </a>
                                                                <?php if (in_array($application->status, $status_for_delete)) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer" onclick="deletefile('<?php echo $application->fnum; ?>');" id="actions_block_delete_<?php echo $application->fnum ?>">
                                                                        <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php
                                                                foreach($custom_actions as $custom_action_key => $custom_action) {

                                                                    if (in_array($application->status, $custom_action->mod_em_application_custom_action_status)){
                                                                        ?>
                                                                        <a id="actions_button_custom_<?= $custom_action_key; ?>" class="em-text-neutral-900 em-pointer" href="<?= str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link) ?>" <?= $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '' ?> ><?= JText::_($custom_action->mod_em_application_custom_action_label) ?></a>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                        </div>

                                        <div class="em-flex-row">
                                            <?php if ($mod_emundus_applications_show_end_date == 1) : ?>
                                                <?php
	                                            $closed = false;
                                                $displayInterval = false;
                                                $end_date = $application->end_date;
                                                if(!empty($current_phase)){
	                                                $end_date = $current_phase->end_date;
                                                }
                                                if($now < $end_date)
                                                {
	                                                $interval = date_create($now)->diff(date_create($end_date));
	                                                if ($interval->y == 0 && $interval->m == 0 && $interval->d == 0)
	                                                {
		                                                $displayInterval = true;
	                                                }
                                                } else {
                                                    $closed = true;
                                                }
                                                ?>
                                                <div class="mod_emundus_applications___date em-mt-8">
                                                    <?php if (!$displayInterval && !$closed) : ?>
                                                        <span class="material-icons em-text-neutral-600 em-font-size-16 em-mr-8">schedule</span>
                                                        <p class="em-applicant-text-color em-font-size-16 em-applicant-default-font"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_END_DATE'); ?> <?php echo JFactory::getDate(new JDate($end_date, $site_offset))->format($date_format); ?></p>
                                                    <?php elseif($displayInterval && !$closed) : ?>
                                                        <span class="material-icons-outlined em-text-neutral-600 em-font-size-16 em-red-500-color em-mr-8">schedule</span>
                                                        <p class="em-red-500-color"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_DAY'); ?>
                                                            <?php if ($interval->h > 0) {
                                                                echo $interval->h.'h'.$interval->i ;
                                                            } else {
                                                                echo $interval->i . 'm';
                                                            }?>
                                                        </p>
                                                    <?php elseif($closed) : ?>
                                                        <span class="material-icons em-font-size-16 em-mr-8 em-red-500-color">schedule</span>
                                                        <p class="em-font-size-16 em-applicant-default-font em-red-500-color"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_CLOSED'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($key == 'sent') : ?>
                                                <div class="mod_emundus_applications___date em-mt-8">
                                                    <span class="material-icons-outlined em-text-neutral-600 em-font-size-16 em-mr-8">insert_drive_file</span>
                                                    <p class="em-applicant-text-color em-font-size-16 em-applicant-default-font"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_SUBMITTED_DATE'); ?> <?php echo JFactory::getDate(new JDate($application->submitted_date, $site_offset))->format('d/m/Y H:i'); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <hr/>

                                        <div class="mod_emundus_applications___informations">
                                            <div>
                                                <label class="em-applicant-text-color em-applicant-default-font"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_APPLICANT'); ?> :</label>
                                                <p class="em-neutral-800-color em-applicant-default-font"><?php echo $user->name ?></p>
                                            </div>

                                            <div>
                                                <?php
                                                if(empty($application->class)) {
	                                                $application->class = 'default';
                                                }
                                                ?>
                                                <?php if(empty($visible_status)) : ?>
                                                    <label class="em-applicant-text-color em-applicant-default-font"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                                    <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row" id="application_status_<?php echo $application->fnum ?>">
                                                        <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                        <span class="mod_emundus_applications___status_label em-neutral-800-color em-applicant-default-font"><?= $application->value; ?></span>
                                                    </div>
                                                <?php elseif (in_array($application->status,$visible_status)) :?>
                                                    <label class="em-applicant-text-color"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                                    <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row" id="application_status_<?php echo $application->fnum ?>">
                                                        <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                        <span class="mod_emundus_applications___status_label"><?= $application->value; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if(!empty($application->order_status)): ?>
                                                    <br>
                                                    <label class="em-applicant-text-color"><?= JText::_('ORDER_STATUS'); ?> :</label>
                                                    <span class="label" style="background-color: <?= $application->order_color; ?>"><?= JText::_(strtoupper($application->order_status)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($show_state_files == 1) :?>
                                            <div class="">
                                                <div class="">
                                                    <strong><?= JText::_('MOD_EMUNDUS_STATE'); ?></strong>
                                                    <?php if ($state == 1) :?>
                                                        <span class="label alert-success" role="alert"> <?= JText::_('MOD_EMUNDUS_PUBLISH'); ?></span>
                                                    <?php elseif ($state == 0) :?>
                                                        <span class="label alert-secondary" role="alert"> <?= JText::_('MOD_EMUNDUS_ARCHIVE'); ?></span>
                                                    <?php else :?>
                                                        <span class="label alert-danger" role="alert"><?= JText::_('MOD_EMUNDUS_DELETE'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="em-mt-16">
                            <span class="em-tags-display em-applicant-text-color">
                                <?= $file_tags_display; ?>
                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php } ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php if ($show_add_application && ($position_add_application == 1 || $position_add_application == 2 || $position_add_application == 4) && $applicant_can_renew) : ?>
  <div class="mod_emundus_applications___footer">
    <a class="btn btn-success" href="<?= $cc_list_url; ?>"><span class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
  </div>
  <?php endif; ?>

<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
    <div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title" id="em-modal-form-title"><?= JText::_('LOADING'); ?></h4>
                    <img src="media/com_emundus/images/icones/loader-line.gif">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var poll_url = "<?= $poll_url; ?>";
        if ($poll_url !== "") {
            jQuery(".modal-body").html('<iframe src="' + poll_url + '" style="width:' + window.getWidth() * 0.8 + 'px; height:' + window.getHeight() * 0.8 + 'px; border:none"></iframe>');
            setTimeout(function () {
                jQuery('#em-modal-form').modal({backdrop: true, keyboard: true}, 'toggle');
            }, 1000);
        }
    </script>

<?php endif; ?>

<script type="text/javascript">
    function deletefile(fnum) {
        Swal.fire({
            title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_DELETE_FILE'); ?>",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#dc3545",
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('JYES');?>",
            cancelButtonText: "<?php echo JText::_('JNO');?>"
        }).then((confirm) => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=deletefile&fnum=" + fnum+"&redirect=<?php echo base64_encode(JUri::getInstance()->getPath()); ?>";
            }
        });
    }
</script>
<script>
    function delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    jQuery(function () {
        jQuery('[data-toggle="tooltip"]').tooltip()
    })

    document.addEventListener('click', function (e) {
        let target = e.target.id;
        let actions = document.querySelectorAll("[id^='actions_block_']");

        if(typeof actions !== 'undefined') {
            actions.forEach((action) => {
                if (action.style.display === 'flex') {
                    action.style.display = 'none';
                }
            });

            if(target.indexOf('actions_button_') !== -1){
                let fnum = target.split('_');
                fnum = fnum[fnum.length -1];

                let actions = document.getElementById('actions_block_' + fnum);
                if(actions.style.display === 'none'){
                    actions.style.display = 'flex';
                } else {
                    actions.style.display = 'none';
                }
            }
        }
    });

    function openFile(e, url) {
        let target = e.target.id;

        if(target.indexOf('actions_button_') !== -1 || target.indexOf('actions_block_delete_') !== -1){
            //do nothing
        } else {
            window.location.href = url;
        }
    }

    $('#applications_searchbar').keyup(delay(function (e) {
        let search = e.target.value;

        if(search !== '') {
            let campaigns = document.querySelectorAll('.mod_emundus_applications___title span');
            let status = document.querySelectorAll('.mod_emundus_applications___status_label');
            let fnums_to_hide = [];
            let fnums_to_show = [];

            for (let campaign of campaigns) {
                let fnum = campaign.parentElement.id.split('_');
                fnum = fnum[fnum.length - 1];

                if(campaign.textContent.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase().includes(search.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase()) === false){
                    fnums_to_hide.push(fnum);
                } else {
                    fnums_to_show.push(fnum);
                }
            }

            for (let step of status) {
                let fnum = step.parentElement.id.split('_');
                fnum = fnum[fnum.length - 1];
                console.log(fnums_to_show.includes(fnum.toString()));

                if(step.textContent.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase().includes(search.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase()) === false){
                    if(fnums_to_show.indexOf(fnum) !== -1) {
                        fnums_to_hide.push(fnum);
                    }
                } else {
                    fnums_to_show.push(fnum);
                    if(fnums_to_hide.indexOf(fnum) !== -1) {
                        fnums_to_hide.splice(fnums_to_hide.indexOf(fnum),1);
                    }
                }
            }

            fnums_to_hide.forEach((fnum) => {
                document.getElementById('application_content' + fnum).style.display = 'none';
            })
            fnums_to_show.forEach((fnum) => {
                document.getElementById('application_content' + fnum).style.display = 'block';
            })
        } else {
            for (let application of document.querySelectorAll("div[id^='application_content']")) {
                application.style.display = 'block';
            }

        }

    }, 500));
</script>
