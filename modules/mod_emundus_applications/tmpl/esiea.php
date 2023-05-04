<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
echo $description;
?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
    <a id="add-application" class="btn btn-success" href="<?= $cc_list_url; ?>">
        <span class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
    </a>
    <hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
    <div class="<?= $moduleclass_sfx ?>">
    <?php foreach ($applications as $application) : ?>

        <?php
        $is_admission = in_array($application->status, $admission_status);
        $state = $application->published;
        $confirm_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&confirm=1';
        $first_page_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum;
        if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0' ) : ?>
            <?php
            if ($file_tags != '') {

                $post = array(
                    'APPLICANT_ID'  => $user->id,
                    'DEADLINE'      => JHTML::_('date', $application->end_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                    'CAMPAIGN_LABEL' => $application->label,
                    'CAMPAIGN_YEAR'  => $application->year,
                    'CAMPAIGN_START' => JHTML::_('date', $application->start_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                    'CAMPAIGN_END'  => JHTML::_('date', $application->end_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                    'CAMPAIGN_CODE' => $application->training,
                    'FNUM'          => $application->fnum
                );

                $tags = $m_email->setTags($user->id, $post, $application->fnum, '', $file_tags);
                $file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
                $file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($application->fnum));
               }
            ?>
            <div class="row" id="row<?= $application->fnum; ?>">
                <div class="col-md-12 main-page-application-title">

                    <a href="<?php echo JRoute::_($first_page_url); ?>">
                        <?= ($is_admission &&  $add_admission_prefix)?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?>
                    </a>

                </div>

                <div class="col-xs-12 col-md-6 main-page-file-info">
                    <p class="em-tags-display"><?= $file_tags_display; ?></i></p>
                    <a class="btn btn-warning" href="<?php echo JRoute::_($first_page_url); ?>" role="button">
                        <i class="folder open outline icon"></i> <?= ($is_admission)?JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_ADMISSION'):JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION'); ?>
                    </a>

                    <?php if (!empty($attachments) && ((int) ($attachments[$application->fnum]) >= 100 && (int) ($forms[$application->fnum]) >= 100 && in_array($application->status, $status_for_send) && !$is_dead_line_passed) || in_array($user->id, $applicants)) : ?>

                        <a id='send' class="btn btn-xs" href="<?= JRoute::_($confirm_url); ?>" title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SEND_APPLICATION_FILE'); ?></a>

                    <?php endif; ?>

                    <a id='print' class="btn btn-info btn-xs" href="<?= JRoute::_('index.php?option=com_emundus&task=pdf&fnum=' . $application->fnum); ?>" title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>
                    <?php if ((in_array($application->status, $status_for_send) && empty($status_for_delete)) || (in_array($application->status, $status_for_delete))) : ?>
                        <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?= $application->fnum; ?>');" href="#row<?php !empty($attachments) ? $attachments[$application->fnum] : ''; ?>" title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>
                    <?php endif; ?>
                </div>

                <div class="col-xs-12 <?= ($show_state_files == 1) ? "col-md-3" : "col-md-6" ?> main-page-file-progress">
                    <section class="container" style="width:150px; float: left;">
                    <?php if ($show_progress == 1) : ?>
                        <div id="file<?= $application->fnum; ?>"></div>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery("#file<?= $application->fnum; ?>").circliful({
                                    animation: 1,
                                    animationStep: 5,
                                    foregroundBorderWidth: 15,
                                    backgroundBorderWidth: 15,
                                    percent: <?= (int) (($forms[$application->fnum] + $attachments[$application->fnum])) / 2; ?>,
                                    textStyle: 'font-size: 12px;',
                                    textColor: '#000',
                                    foregroundColor: '<?= $show_progress_color; ?>'
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if ($show_progress_forms == 1) : ?>
                        <div id="forms<?= $application->fnum; ?>"></div>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery("#forms<?= $application->fnum; ?>").circliful({
                                    animation: 1,
                                    animationStep: 5,
                                    foregroundBorderWidth: 15,
                                    backgroundBorderWidth: 15,
                                    percent: <?= (int) ($forms[$application->fnum]); ?>,
                                    text: '<?= JText::_("MOD_EMUNDUS_APPLICATIONS_FORMS"); ?>',
                                    textStyle: 'font-size: 12px;',
                                    textColor: '#000',
                                    foregroundColor: '<?= $show_progress_color_forms; ?>'
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if ($show_progress_documents == 1) : ?>
                        <div id="documents<?= $application->fnum; ?>"></div>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery("#documents<?= $application->fnum; ?>").circliful({
                                    animation: 1,
                                    animationStep: 5,
                                    foregroundBorderWidth: 15,
                                    backgroundBorderWidth: 15,
                                    percent: <?= (int) ($attachments[$application->fnum]); ?>,
                                    text: '<?= JText::_("MOD_EMUNDUS_APPLICATIONS_DOCUMENTS"); ?>',
                                    textStyle: 'font-size: 12px;',
                                    textColor: '#000',
                                    foregroundColor: '<?= $show_progress_color_documents; ?>'
                                });
                            });
                        </script>
                    <?php endif; ?>
                </section>
                <div class="main-page-file-progress-label">
                    <strong><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</strong>
                    <span class="label label-<?= $application->class; ?>">
                        <?= $application->value; ?>
                    </span>
                    <?php if(!empty($application->order_status)): ?>
                        <br>
                        <strong><?= JText::_('ORDER_STATUS'); ?> :</strong>
                        <span class="label" style="background-color: <?= $application->order_color; ?>">
                            <?= JText::_(strtoupper($application->order_status)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($show_state_files == 1) :?>
                <div class="col-xs-12 col-md-3 main-page-file-progress">
                    <div class="main-page-file-progress-label">
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

            <div class="col-md-12">
                <?php if (!empty($forms) && $forms[$application->fnum] == 0 && $state == '1') :?>
                    <div class="ui segments">
                        <div class="ui yellow segment">
                            <p><i class="info circle icon"></i> <?= JText::_('MOD_EMUNDUS_FLOW_EMPTY_FILE_ACTION'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php else :
    echo JText::_('NO_FILE');
    echo '<hr>';
endif; ?>

<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
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
</script>
