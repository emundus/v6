<?php
/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
//var_dump($user->fnums); echo "<hr>"; var_dump($applications);
echo $description;

?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew && !isset($admission_fnum)) : ?>
  <a class="btn btn-success" href="<?= JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?= JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<?php elseif (isset($admission_fnum) && ($position_add_application == 0 || $position_add_application == 2)) :?>
  <a class="btn btn-success" href="<?= JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?= $admission_fnum ?>"><span class="icon-plus-sign"> <?= JText::_('COMPLETE_ADMISSION'); ?></span></a>
<?php endif; ?>
<hr>
<?php if (!empty($applications)) : ?>
<div class="<?= $moduleclass_sfx ?>"> 
  <?php foreach($applications as $application) : ?>
    <?php $state=$states[$application->fnum]['published'];?>
    <?php if ($show_remove_files == 1 && $state == '1' || $show_remove_files == 0 && $state == '1' ) : ?>
        <?php if ($show_archive_files == 1 && $state == '1' || $show_archive_files == 0 && $state == '1' ) : ?>
  <div class="row" id="row<?= $application->fnum; ?>">
    <div class="col-xs-6 col-md-4">
      <p class="">
        <a href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>" >
          <?php
            echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <b>'.$application->label.'</b>':$application->label;
          ?>
        </a> 
    </div>

    <div class="col-xs-6 col-md-4">
      <p>
        <?= JText::_('FILE_NUMBER'); ?> : <i><?= $application->fnum; ?></i>
      </p>
      <a class="btn btn-warning btn-xs" href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode("index.php?fnum=".$application->fnum).'&Itemid='.$Itemid.'#em-panel'); ?>"  role="button">
          <i class="folder open outline icon"></i> <?= JText::_('OPEN_APPLICATION'); ?>
      </a>

      <?php if (!empty($attachments) && ((int)($attachments[$application->fnum])>=100 && $application->status==0 && !$is_dead_line_passed) || in_array($user->id, $applicants) ) : ?>
        <a class="btn btn-success btn-xs" href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($confirm_form_url)); ?>" title="<?= JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?= JText::_('SEND_APPLICATION_FILE'); ?></a>
      <?php endif; ?>

      <a id='print' class="btn btn-info btn-xs" href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=pdf&fnum='.$application->fnum); ?>" title="<?= JText::_('PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>
      
      <?php if ($application->status<=1) : ?>
        <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?= $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?= JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>

        <?php 
        if (!empty($forms) && $forms[$application->fnum] == 0) {
          echo '
<div class="ui segments">
  <div class="ui yellow segment">
    <p><i class="info circle icon"></i> '.JText::_('MOD_EMUNDUS_FLOW_EMPTY_FILE_ACTION').'</p></p>
  </div>
</div>';
        }
        ?>
      <?php endif; ?>
    </div>

    <div class="col-xs-6 col-md-4">
      <?= JText::_('STATUS'); ?> : 
      <span class="label label-<?= $application->class; ?>"> 
        <?= $application->value; ?>
      </span>
      <section class="container" style="width:150px; float: left;">
      <?php if ($show_progress == 1) : ?>
        <div id="file<?= $application->fnum; ?>"></div>
        <script type="text/javascript">
          $( document ).ready(function() { 
              $("#file<?= $application->fnum; ?>").circliful({
                  animation: 1,
                  animationStep: 5,
                  foregroundBorderWidth: 15,
                  backgroundBorderWidth: 15,
                  percent: <?= (int)(($forms[$application->fnum]+$attachments[$application->fnum]))/2; ?>,
                  textStyle: 'font-size: 12px;',
                  textColor: '#000',
                  foregroundColor:'<?= $show_progress_color; ?>'
              });
          });
        </script>
      <?php endif; ?>

      <?php if ($show_progress_forms == 1) : ?>
        <div id="forms<?= $application->fnum; ?>"></div>
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
      <?php endif; ?>

      <?php if ($show_progress_documents == 1) : ?>
        <div id="documents<?= $application->fnum; ?>"></div>
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
      <?php endif; ?>
    </section>
    </div>
  </div>
  <hr>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0') : ?>
    <div class="row" id="row<?= $application->fnum; ?>">
        <div class="col-xs-6 col-md-4">
            <p class="">
                <a href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>" >
                    <?php
                    echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <b>'.$application->label.'</b>':$application->label;
                    ?>
                </a>
        </div>

        <div class="col-xs-6 col-md-4">
            <p>
                <?= JText::_('FILE_NUMBER'); ?> : <i><?= $application->fnum; ?></i>
            </p>
            <a class="btn btn-secondary btn-xs" disabled="disabled" data-toggle="tooltip" data-placement="top" title="<?= JText::_('MOD_EMUNDUS_APPLICATION_TOOLTIP_REMOVE_OR_ARCHIVE_FILES'); ?>"  role="button">
                <i class="folder open outline icon"></i> <?= JText::_('OPEN_APPLICATION'); ?>
            </a>

            <?php if (!empty($attachments) && ((int)($attachments[$application->fnum])>=100 && $application->status==0 && !$is_dead_line_passed) || in_array($user->id, $applicants) ) : ?>
                <a class="btn btn-success btn-xs" disabled="disabled" data-toggle="tooltip" data-placement="top" title="<?= JText::_('MOD_EMUNDUS_APPLICATION_TOOLTIP_REMOVE_OR_ARCHIVE_FILES'); ?>"><i class="icon-envelope"></i> <?= JText::_('SEND_APPLICATION_FILE'); ?></a>
            <?php endif; ?>

            <a id='print' class="btn btn-info btn-xs" href="<?= JRoute::_(JURI::base().'index.php?option=com_emundus&task=pdf&fnum='.$application->fnum); ?>" title="<?= JText::_('PRINT_APPLICATION_FILE'); ?>" target="_blank"><i class="icon-print"></i></a>

            <?php if ($application->status<=1) : ?>
                <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?= $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?= JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>


            <?php endif; ?>
        </div>

        <div class="col-xs-6 col-md-4">
            <?= JText::_('STATUS'); ?> :
            <span class="label label-<?= $application->class; ?>">
        <?= $application->value; ?>
      </span>
            <section class="container" style="width:150px; float: left;">
                <?php if ($show_progress == 1) : ?>
                    <div id="file<?= $application->fnum; ?>"></div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $("#file<?= $application->fnum; ?>").circliful({
                                animation: 1,
                                animationStep: 5,
                                foregroundBorderWidth: 15,
                                backgroundBorderWidth: 15,
                                percent: <?= (int)(($forms[$application->fnum]+$attachments[$application->fnum]))/2; ?>,
                                textStyle: 'font-size: 12px;',
                                textColor: '#000',
                                foregroundColor:'<?= $show_progress_color; ?>'
                            });
                        });
                    </script>
                <?php endif; ?>

                <?php if ($show_progress_forms == 1) : ?>
                    <div id="forms<?= $application->fnum; ?>"></div>
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
                <?php endif; ?>

                <?php if ($show_progress_documents == 1) : ?>
                    <div id="documents<?= $application->fnum; ?>"></div>
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
                <?php endif; ?>
            </section>
        </div>
    </div>
    <hr>
      <?php endif; ?>
      <?php if ($show_remove_files == 0 && $state == '-1' || $show_archive_files == 0 && $state == '0') : ?>

          <div class="ui segments">
              <div class="ui yellow segment">
                  <div class="container-fluid">
                      <div class="col-md-10">
                          <?= JTEXT::sprintf('Mod_emundus_application_info_remove_or_archive_files', $application->label);?>
                      </div>
                      <div class="col-md-2">
                          <a id="trash" class="btn btn-danger btn-xs" onClick="deletefile('<?= $application->fnum; ?>');" href="#row<?php !empty($attachments)?$attachments[$application->fnum]:''; ?>" title="<?= JText::_('DELETE_APPLICATION_FILE'); ?>"><i class="icon-trash"></i> </a>
                      </div>

                  </div>
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
  <a class="btn btn-success" href="<?= JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?= JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<?php elseif (isset($admission_fnum) && $position_add_application > 0) :?>
  <a class="btn btn-success" href="<?= JURI::base(); ?>index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?= $admission_fnum ?>"><span class="icon-plus-sign"> <?= JText::_('COMPLETE_ADMISSION'); ?></span></a>
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
        <img src="<?= JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
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
    url = "<?= JURI::base().'index.php?option=com_emundus&task=deletefile&fnum='; ?>";
    document.location.href=url+fnum;
  }
}

</script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>