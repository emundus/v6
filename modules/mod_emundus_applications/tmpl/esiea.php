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
    <a id="add-application" class="btn btn-success" href="<?php echo JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=102">
        <span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span>
    </a>
    <hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
    <div class="<?php echo $moduleclass_sfx ?>">
		<?php foreach ($applications as $application) : ?>
            <div class="row" id="row<?php echo $application->fnum; ?>">
                <div class="col-md-12 main-page-application-title">
                    <a href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>">
                        <?php echo (in_array($application->status, $admission_status))?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?>
                    </a>
                </div>

                <div class="col-xs-12 col-md-6 main-page-file-info">
                    <p>
						<?php echo JText::_('FILE_NUMBER'); ?> : <i><?php echo $application->fnum; ?></i>
                    </p>
                    <a class="btn btn-warning" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode("index.php?fnum=".$application->fnum).'&Itemid='.$Itemid.'#em-panel'); ?>" role="button">
                        <i class="folder open outline icon"></i> <?php echo (in_array($application->status, $admission_status))?JText::_('OPEN_ADMISSION'):JText::_('OPEN_APPLICATION'); ?>
                    </a>

					<?php if (!empty($attachments) && ((int) ($attachments[$application->fnum]) >= 100 && $application->status == 0 && !$is_dead_line_passed) || in_array($user->id, $applicants)) : ?>
                        <a id='send' class="btn btn-xs" href="<?php echo JRoute::_(JURI::base() . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&redirect=' . base64_encode($confirm_form_url)); ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
					<?php endif; ?>

                    <a id='print' class="btn btn-info btn-xs" href="<?php echo JRoute::_(JURI::base() . 'index.php?option=com_emundus&task=pdf&fnum=' . $application->fnum); ?>" title="<?php echo JText::_('PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>

					<?php if ($application->status <= 1) : ?>
                        <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?php echo $application->fnum; ?>');" href="#row<?php !empty($attachments) ? $attachments[$application->fnum] : ''; ?>" title="<?php echo JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>
					<?php endif; ?>
                </div>

                <div class="col-xs-12 col-md-6 main-page-file-progress">
                    <section class="container" style="width:150px; float: left;">
						<?php if ($show_progress == 1) : ?>
                            <div id="file<?php echo $application->fnum; ?>"></div>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $("#file<?php echo $application->fnum; ?>").circliful({
                                        animation: 1,
                                        animationStep: 5,
                                        foregroundBorderWidth: 15,
                                        backgroundBorderWidth: 15,
                                        percent: <?php echo (int) (($forms[$application->fnum] + $attachments[$application->fnum])) / 2; ?>,
                                        textStyle: 'font-size: 12px;',
                                        textColor: '#000',
                                        foregroundColor: '<?php echo $show_progress_color; ?>'
                                    });
                                });
                            </script>
						<?php endif; ?>

						<?php if ($show_progress_forms == 1) : ?>
                            <div id="forms<?php echo $application->fnum; ?>"></div>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $("#forms<?php echo $application->fnum; ?>").circliful({
                                        animation: 1,
                                        animationStep: 5,
                                        foregroundBorderWidth: 15,
                                        backgroundBorderWidth: 15,
                                        percent: <?php echo (int) ($forms[$application->fnum]); ?>,
                                        text: '<?php echo JText::_("FORMS"); ?>',
                                        textStyle: 'font-size: 12px;',
                                        textColor: '#000',
                                        foregroundColor: '<?php echo $show_progress_color_forms; ?>'
                                    });
                                });
                            </script>
						<?php endif; ?>

						<?php if ($show_progress_documents == 1) : ?>
                            <div id="documents<?php echo $application->fnum; ?>"></div>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $("#documents<?php echo $application->fnum; ?>").circliful({
                                        animation: 1,
                                        animationStep: 5,
                                        foregroundBorderWidth: 15,
                                        backgroundBorderWidth: 15,
                                        percent: <?php echo (int) ($attachments[$application->fnum]); ?>,
                                        text: '<?php echo JText::_("DOCUMENTS"); ?>',
                                        textStyle: 'font-size: 12px;',
                                        textColor: '#000',
                                        foregroundColor: '<?php echo $show_progress_color_documents; ?>'
                                    });
                                });
                            </script>
						<?php endif; ?>
                    </section>
                    <div class="main-page-file-progress-label">
                        <strong><?php echo JText::_('STATUS'); ?> :</strong>
                        <span class="label label-<?php echo $application->class; ?>">
                            <?php echo $application->value; ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-12">
					<?php if (!empty($forms) && $forms[$application->fnum] == 0) :?>
						<div class="ui segments">
                            <div class="ui yellow segment">
                                <p><i class="info circle icon"></i> <?php echo JText::_('MOD_EMUNDUS_FLOW_EMPTY_FILE_ACTION'); ?></p>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
            </div>
            <hr>
		<?php endforeach; ?>
    </div>
<?php else :
    echo JText::_('NO_FILE');
    echo '<hr>';
endif; ?>

<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
    <a class="btn btn-success" href="<?php echo JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<?php endif; ?>

<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
    <div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title" id="em-modal-form-title"><?php echo JText::_('LOADING'); ?></h4>
                    <img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var poll_url = "<?php echo $poll_url; ?>";
        $(".modal-body").html('<iframe src="' + poll_url + '" style="width:' + window.getWidth() * 0.8 + 'px; height:' + window.getHeight() * 0.8 + 'px; border:none"></iframe>');
        setTimeout(function () {
            $('#em-modal-form').modal({backdrop: true, keyboard: true}, 'toggle');
        }, 1000);
    </script>

<?php endif; ?>

<script type="text/javascript">
    function deletefile(fnum) {
        if (confirm("<?php echo JText::_('CONFIRM_DELETE_FILE'); ?>")) {
            document.location.href = "<?php echo JRoute::_(JURI::base() . 'index.php?option=com_emundus&task=deletefile&fnum='); ?>" + fnum;
        }
    }
</script>
