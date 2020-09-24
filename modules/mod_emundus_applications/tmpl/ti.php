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
$confirm_form_url = $m_application->getConfirmUrl($fnums);
$first_page = $m_application->getFirstPage('index.php', $fnums);
?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew && !isset($admission_fnum)) : ?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?= JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<?php elseif (isset($admission_fnum) && ($position_add_application == 0 || $position_add_application == 2)) :?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?= $admission_fnum ?>"><span class="icon-plus-sign"> <?= JText::_('COMPLETE_ADMISSION'); ?></span></a>
<?php endif; ?>
<hr>

<?php if (!empty($applications)) : ?>
    <div class="<?= $moduleclass_sfx ?>">
        <?php foreach($applications as $application) : ?>
            <?php $state=$states[$application->fnum]['published'];?>

            <?php if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0' ) : ?>
              <div class="row" id="row<?= $application->fnum; ?>">
                  <div class="col-xs-6 col-md-4">
                      <p class="">
                          <a href="<?= JRoute::_('index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>" >
                              <?= (!empty($user->fnum) && $application->fnum == $user->fnum)?'<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <b>'.$application->label.'</b>':$application->label; ?>
                          </a>
                  </div>
                  <div class="col-xs-6 col-md-4">
                      <p>
                          <?= JText::_('FILE_NUMBER'); ?> : <i><?= $application->fnum; ?></i>
                      </p>
                      <a class="btn btn-warning btn-xs" href="<?= JRoute::_('index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($first_page[$application->fnum]['link'])); ?>"  role="button">
                          <i class="folder open outline icon"></i> <?= JText::_('OPEN_APPLICATION'); ?>
                      </a>

                      <?php if (!empty($attachments) && ((int)($attachments[$application->fnum])>=100 && (int) ($forms[$application->fnum]) && in_array($application->status, $status_for_send) && !$is_dead_line_passed) || in_array($user->id, $applicants) ) : ?>
                        <a class="btn btn-success btn-xs" href="<?= JRoute::_('index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($confirm_form_url[$application->fnum]['link'])); ?>" title="<?= JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?= JText::_('SEND_APPLICATION_FILE'); ?></a>
                      <?php endif; ?>

                      <a id='print' class="btn btn-info btn-xs" href="<?= JRoute::_('index.php?option=com_emundus&task=pdf&fnum='.$application->fnum); ?>" title="<?= JText::_('PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>

                      <?php if ($application->status<=1) : ?>
                          <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?= $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?= JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>

                          <?php if (!empty($forms) && $forms[$application->fnum] == 0 && $state == 1) :?>
                              <?=
                              '<div class="ui segments">
                                    <div class="ui yellow segment">
                                        <p><i class="info circle icon"></i> '.JText::_('MOD_EMUNDUS_FLOW_EMPTY_FILE_ACTION').'</p>
                                    </div>
                                </div>'; ?>
                          <?php endif; ?>
                      <?php endif; ?>
                  </div>

                  <section class="col-xs-6 col-md-2" style="width:180px; float: left;">
                      <?php if ($show_progress == 1) : ?>
                          <div class="col-12"><div id="file<?= $application->fnum; ?>"></div>
                              <script type="text/javascript">
                                  $( document ).ready(function() {
                                      $("#file<?= $application->fnum; ?>").circliful({
                                          animation: 1,
                                          animationStep: 5,
                                          foregroundBorderWidth: 15,
                                          backgroundBorderWidth: 15,
                                          percent: <?= (int)($forms[$application->fnum]+$attachments[$application->fnum])/2; ?>,
                                          textStyle: 'font-size: 12px;',
                                          textColor: '#000',
                                          foregroundColor:'<?= $show_progress_color; ?>'
                                      });
                                  });
                              </script>
                          </div>
                      <?php endif; ?>

                      <?php if ($show_progress_forms == 1) : ?>
                          <div class="col-xs-6 col-md-2"><div id="forms<?= $application->fnum; ?>"></div>
                              <script type="text/javascript">
                                  $( document ).ready(function() {
                                      $("#forms<?= $application->fnum; ?>").circliful({
                                          animation: 1,
                                          animationStep: 5,
                                          foregroundBorderWidth: 15,
                                          backgroundBorderWidth: 15,
                                          percent: <?= (int)($forms[$application->fnum]); ?>,
                                          text: '<?= JText::_("FORMS"); ?>',
                                          textStyle: 'font-size: 12px;',
                                          textColor: '#000',
                                          foregroundColor:'<?= $show_progress_color_forms; ?>'
                                      });
                                  });
                              </script>
                          </div>
                      <?php endif; ?>

                      <?php if ($show_progress_documents == 1) : ?>
                          <div class="col-xs-6 col-md-2"><div id="documents<?= $application->fnum; ?>"></div>
                              <script type="text/javascript">
                                  $( document ).ready(function() {
                                      $("#documents<?= $application->fnum; ?>").circliful({
                                          animation: 1,
                                          animationStep: 5,
                                          foregroundBorderWidth: 15,
                                          backgroundBorderWidth: 15,
                                          percent: <?= (int)($attachments[$application->fnum]); ?>,
                                          text: '<?= JText::_("DOCUMENTS"); ?>',
                                          textStyle: 'font-size: 12px;',
                                          textColor: '#000',
                                          foregroundColor:'<?= $show_progress_color_documents; ?>'
                                      });
                                  });
                              </script>
                          </div>
                      <?php endif; ?>
                  </section>

                  <div class="col-xs-6 col-md-2">
                      <?= JText::_('STATUS'); ?> : <span class="label label-<?= $application->class; ?>"><?= $application->value; ?></span>

                      <?php if($show_state_files == 1): ?>
                        <p><?= JText::_('MOD_EMUNDUS_STATE'); ?>
                        <?php if ($state == 1):?>
                            <span class="label alert-success" role="alert"> <?= JText::_('MOD_EMUNDUS_PUBLISH'); ?></span>
                        <?php elseif ($state == 0):?>
                            <span class="label alert-secondary" role="alert"> <?= JText::_('MOD_EMUNDUS_ARCHIVE'); ?></span>
                        <?php else: ?>
                            <span class="label alert-danger" role="alert"><?= JText::_('MOD_EMUNDUS_DELETE'); ?></span>
                        <?php endif; ?>
                        </p>
                      <?php endif; ?>
                    </div>
                </div>
            <hr>
            <?php endif; ?>
        <?php endforeach;  ?>
    </div>
<?php else :
    echo JText::_('NO_FILE');
?>
<?php endif; ?>

<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew && !isset($admission_fnum)) : ?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?= JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<?php elseif (isset($admission_fnum) && $position_add_application > 0) :?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?= $admission_fnum ?>"><span class="icon-plus-sign"> <?= JText::_('COMPLETE_ADMISSION'); ?></span></a>
<?php endif; ?>

<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
<div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-form" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body">
        <h4 class="modal-title" id="em-modal-form-title"><?= JText::_('LOADING');?></h4>
        <img src="media/com_emundus/images/icones/loader-line.gif">
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var poll_url = "<?= $poll_url; ?>";
  $(".modal-body").html('<iframe src="'+poll_url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>');
  setTimeout(function(){$('#em-modal-form').modal({backdrop:true, keyboard:true},'toggle');}, 1000);
</script>

<?php endif; ?>

<script type="text/javascript">
function deletefile(fnum){
  if (confirm("<?= JText::_('CONFIRM_DELETE_FILE'); ?>")) {
    url = "index.php?option=com_emundus&task=deletefile&fnum=";
    document.location.href=url+fnum;
  }
}

</script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>