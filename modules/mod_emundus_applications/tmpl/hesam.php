<?php

/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

echo $description;
?>


<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
    <a class="btn btn-success" href="<?php echo JURI::base(); ?>component/fabrik/form/102"><span class="icon-plus"></span> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></a>
<?php endif; ?>


<?php if (!empty($applications)) : ?>
    <div class="em-hesam-applications">
		<?php foreach ($applications as $application) : ?>
            <div class="col-md-4 em-hesam-application-card" id="row<?php echo $application->fnum; ?>">

                <div class="col-xs-6 col-md-8 em-bottom-space em-top-space">
                    <span class="label label-<?php echo $application->class; ?>"><?php echo $application->value; ?></span>
                </div>

                <div class="col-md-8 em-bottom-space">
                    <?php if (!empty($application->titre)) :?>
                        <?php echo ($application->fnum == $user->fnum)?'<b>'.$application->titre.'</b>':$application->titre; ?>
                    <?php else: ?>
                        <?php echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'<b>'.JText::_('NO_TITLE').'</b>':JText::_('NO_TITLE'); ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-8 em-bottom-space">
                    <a class="btn btn-warning btn-xs" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode("index.php?fnum=".$application->fnum.'&Itemid='.$Itemid.'#em-panel')); ?>"  role="button">
                        <i class="folder open outline icon"></i> <?php echo JText::_('OPEN_APPLICATION'); ?>
                    </a>

					<?php if ((!empty($attachments) && (int)($attachments[$application->fnum])>=100 && $application->status==0 && !$is_dead_line_passed) || in_array($user->id, $applicants)) : ?>
                        <a class="btn btn-success btn-xs" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($confirm_form_url)); ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
                    <?php endif; ?>

                    <a id='print' class="btn btn-info btn-xs" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=pdf&fnum='.$application->fnum); ?>" title="<?php echo JText::_('PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>

					<?php if ($application->status <= 1) : ?>
                        <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?php echo $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?php echo JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>
					<?php endif; ?>
                </div>
            </div>
		<?php endforeach;  ?>
    </div>
<?php else : echo JText::_('NO_FILE'); ?>
<?php endif; ?>


<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
    <a class="btn btn-success" href="<?php echo JURI::base(); ?>component/fabrik/form/102"><span class="icon-plus"></span> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></a>
<?php endif; ?>


<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
    <div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title" id="em-modal-form-title"><?php echo JText::_('LOADING');?></h4>
                    <img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var poll_url = "<?php echo $poll_url; ?>";
        $(".modal-body").html('<iframe src="'+poll_url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>');
        setTimeout(function(){
            $('#em-modal-form').modal({backdrop:true, keyboard:true},'toggle');
        }, 1000);
    </script>

<?php endif; ?>


<script type="text/javascript">
    function deletefile(fnum) {
        if (confirm("<?php echo JText::_('CONFIRM_DELETE_FILE'); ?>")) {
            url = "<?php echo JURI::base().'index.php?option=com_emundus&task=deletefile&fnum='; ?>";
            document.location.href = url+fnum;
        }
    }
</script>
