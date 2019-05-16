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
$uri = JUri::getInstance();
?>

<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
    <a class="btn btn-success" href="<?php echo JURI::base(); ?>component/fabrik/form/102"><span class="icon-plus"></span> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></a>
<?php endif; ?>

<?php if (!empty($applications)) : ?>

    <div class="em-hesam-applications">
        <?php foreach ($applications as $application) : ?>
            <div class="col-md-4 em-hesam-application-card" id="row<?php echo $application->fnum; ?>">

                <div class="em-hesam-application-card-details">
                    <div class="col-md-12 em-bottom-space em-top-space">
                        <span class="label label-<?php echo $application->class; ?>"><?php echo $application->value; ?></span>
                    </div>

                    <div class="row em-bottom-space">
                        <?php if (!empty($application->titre)) :?>
                            <?php echo ($application->fnum == $user->fnum)?'<b>'.$application->titre.'</b>':$application->titre; ?>
                        <?php else: ?>
                            <?php echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'<b>'.JText::_('NO_TITLE').'</b>':JText::_('NO_TITLE'); ?>
                        <?php endif; ?>
                    </div>

                    <div class="row em-bottom-space">
                        <a class="btn btn-warning btn-xs" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($first_page[$application->fnum]['link'])); ?>" role="button">
                            <i class="folder open outline icon"></i> <?php echo JText::_('OPEN_APPLICATION'); ?>
                        </a>
                        <?php if ((!empty($attachments) && (int)($attachments[$application->fnum])>=100 && $application->status==0 && !$is_dead_line_passed) || in_array($user->id, $applicants)) : ?>
                            <a class="btn btn-success btn-xs" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($confirm_form_url[$application->fnum]['link'])); ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
                        <?php endif; ?>

                        <a id='print' class="btn btn-info btn-xs" href="<?php echo JRoute::_(JURI::base().'les-offres/consultez-les-offres/details/299/'. modemundusApplicationsHelper::getSearchEngineId($application->fnum) .'?format=pdf'); ?>" title="<?php echo JText::_('PRINT_APPLICATION_FILE'); ?>"><i class="icon-print"></i></a>

                        <?php if ($application->status != 3) : ?>
                            <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?php echo $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?php echo JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>
                        <?php endif; ?>
                    </div>

                    <div class="row em-bottom-space">
                        <?php if ($application->status == 1) : ?>
                            <a class="btn btn-success btn-xs em-cloturer" onClick="completefile('<?php echo $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?php echo JText::_('COMPLETE_APPLICATION'); ?>">
                                <i class="check icon"></i> <?php echo JText::_('COMPLETE_APPLICATION'); ?>
                            </a>
                        <?php elseif ($application->status == 2) : ?>
                            <a class="btn btn-success btn-xs em-publier" onClick="publishfile('<?php echo $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?php echo JText::_('PUBLISH_APPLICATION'); ?>">
                                <i class="check icon"></i> <?php echo JText::_('PUBLISH_APPLICATION'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="row em-bottom-space em-interested">
                        <?php if (modemundusApplicationsHelper::getNumberOfContactOffers($application->fnum) == 1) :?>
                            <p><?php echo JText::_('MOD_EMUNDUS_ONE_PERSON'); ?></p>
                        <?php elseif (modemundusApplicationsHelper::getNumberOfContactOffers($application->fnum) > 1) :?>
                            <p><?php echo modemundusApplicationsHelper::getNumberOfContactOffers($application->fnum); ?><?php echo JText::_('MOD_EMUNDUS_MORE_ONE_PERSON'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach;  ?>
    </div>
<?php else : ?>
    <span class="em-no-file-found">
        <?php echo JText::_('NO_FILE'); ?>
    </span>
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
            document.location.href = url+fnum+"&redirect=<?php echo base64_encode($uri->getPath()); ?>";
        }
    }

    function completefile(fnum) {
        if (confirm("<?php echo JText::_('CONFIRM_COMPLETE_FILE'); ?>")) {
            url = "<?php echo JURI::base().'index.php?option=com_emundus&task=completefile&fnum='; ?>";
            document.location.href = url+fnum+"&redirect=<?php echo base64_encode($uri->getPath()); ?>";
        }
    }

    function publishfile(fnum) {
        if (confirm("<?php echo JText::_('CONFIRM_PUBLISH_FILE'); ?>")) {
            url = "<?php echo JURI::base().'index.php?option=com_emundus&task=publishfile&fnum='; ?>";
            document.location.href = url+fnum+"&redirect=<?php echo base64_encode($uri->getPath()); ?>";
        }
    }
</script>
