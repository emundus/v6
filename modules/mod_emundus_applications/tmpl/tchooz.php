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
    <p class="em-h3 em-mb-8"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_HELLO') . $user->firstname ?></p>

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
            <a id="add-application" class="btn btn-success em-mt-32" style="width: auto" href="<?= $cc_list_url; ?>">
                <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
            </a>
            <hr>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="em-mt-32">
    <?php if (sizeof($applications) == 0) : ?>
        <hr>
        <div class="mod_emundus_applications__list_content--default">
            <p class="em-text-neutral-900 em-h5 em-applicant-title-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE') ?></p><br/>
            <p class="em-text-neutral-900 em-default-font em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT') ?></p>
            <p class="em-text-neutral-600 em-default-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT_2') ?></p><br/>
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

                    ?>
                    <div class="row em-border-neutral-300 mod_emundus_applications___content_app em-pointer" onclick="openFile(event,'<?php echo $first_page_url ?>')">
                        <div>
                            <div class="em-flex-row mod_emundus_applications___content_text">
                                <?php if ($show_fnum) : ?>
                                    <div class="em-mb-8 em-font-size-14">
                                        <span>N°<?php echo $application->fnum ?></span>
                                    </div>
                                    <div>
                                        <span class="material-icons em-text-neutral-600" id="actions_button_<?php echo $application->fnum ?>" style="font-size: 16px">more_vert</span>

                                        <!-- ACTIONS BLOCK -->
                                        <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color" id="actions_block_<?php echo $application->fnum ?>" style="display: none">
                                            <a class="em-text-neutral-900 em-pointer" href="<?= JRoute::_($first_page_url); ?>" id="actions_block_open_<?php echo $application->fnum ?>">
                                                <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                            </a>
                                            <a class="em-text-neutral-900 em-pointer" onclick="deletefile('<?php echo $application->fnum; ?>');" id="actions_block_delete_<?php echo $application->fnum ?>">
                                                <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="em-flex-row em-flex-space-between em-mb-12">
                                <?php
                                $color = '#1C6EF2';
                                $background = '#F0F6FD';
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
                                <p class="mod_emundus_applications___programme_tag" style="color: <?php echo $color ?>;background-color:<?php echo $background ?>">
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
                                        <a class="em-text-neutral-900 em-pointer" onclick="deletefile('<?php echo $application->fnum; ?>');" id="actions_block_delete_<?php echo $application->fnum ?>">
                                            <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>


                            </div>
                            <a href="<?= JRoute::_($first_page_url); ?>" class="em-h6 mod_emundus_applications___title">
                                <?= ($is_admission &&  $add_admission_prefix)?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?>
                            </a>
                        </div>

                        <div class="em-flex-row">
                            <?php
                            $displayInterval = false;
                            $interval = date_create($now)->diff(date_create($application->end_date));
                            if($interval->d == 0){
                                $displayInterval = true;
                            }
                            ?>
                            <div class="mod_emundus_applications___date em-mt-8">
                                <?php if (!$displayInterval) : ?>
                                    <span class="material-icons em-text-neutral-600 em-font-size-16 em-mr-8">schedule</span>
                                    <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_END_DATE'); ?> <?php echo JFactory::getDate(new JDate($application->end_date, $site_offset))->format('d/m/Y H:i'); ?></p>
                                <?php else : ?>
                                    <span class="material-icons-outlined em-text-neutral-600 em-font-size-16 em-red-500-color em-mr-8">schedule</span>
                                    <p class="em-red-500-color"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_DAY'); ?>
                                        <?php if ($interval->h > 0) {
                                            echo $interval->h.'h'.$interval->i ;
                                        } else {
                                            echo $interval->i . 'm';
                                        }?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if($key == 'sent') : ?>
                                <div class="mod_emundus_applications___date em-mt-8">
                                    <span class="material-icons-outlined em-text-neutral-600 em-font-size-16 em-mr-8">insert_drive_file</span>
                                    <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_SUBMITTED_DATE'); ?> <?php echo JFactory::getDate(new JDate($application->submitted_date, $site_offset))->format('d/m/Y H:i'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr/>

                        <div class="mod_emundus_applications___informations">
                            <div>
                                <label class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_APPLICANT'); ?> :</label>
                                <p><?php echo $user->name ?></p>
                            </div>

                            <div>
                                <?php if(empty($visible_status)) : ?>
                                    <label class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                    <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row">
                                        <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>"></span>
                                        <span><?= $application->value; ?></span>
                                    </div>
                                <?php elseif (in_array($application->status,$visible_status)) :?>
                                    <label class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                    <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row">
                                        <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>"></span>
                                        <span><?= $application->value; ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($application->order_status)): ?>
                                    <br>
                                    <label class="em-text-neutral-600"><?= JText::_('ORDER_STATUS'); ?> :</label>
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
                            <span class="em-tags-display">
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
    <a class="btn btn-success" href="<?= $cc_list_url; ?>"><span class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
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

    function openFile(e,url) {
        let target = e.target.id;

        if(target.indexOf('actions_button_') !== -1 || target.indexOf('actions_block_delete_') !== -1){
            //do nothing
        } else {
            window.location.href = url;
        }
    }
</script>
