<?php
defined('_JEXEC') or die('Restricted access');

$Itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

echo JText::_('PAYMENT_RECEIVED');

if (!empty($this->applications)) : ?>
<hr>
<div class="<?php echo $moduleclass_sfx ?> em-container-paid">
  <?php foreach($this->applications as $application) : ?>
  <div class="row" id="row<?php echo $application->fnum; ?>">
    <div class="col-xs-6 col-md-4">
      <p class="">
        <a href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>"  >
          <?php
            echo ($application->fnum == $this->_user->fnum)?'<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <b>'.$application->label.'</b>':$application->label;
          ?>
        </a>
    </div>

    <div class="col-xs-6 col-md-4">
      <p>
        <?php echo JText::_('COM_EMUNDUS_FILE_NUMBER'); ?> : <i><?php echo $application->fnum; ?></i>
      </p>

      <a id='print' class="btn btn-info em-container-paid-print" href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=pdf'); ?>" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_PRINT_APPLICATION_FILE'); ?> "> <i class="icon-print"></i> <?php echo JText::_('COM_EMUNDUS_APPLICATION_PRINT_APPLICATION_FILE'); ?></a>

    </div>

    <div class="col-xs-6 col-md-4">
      <?php echo JText::_('COM_EMUNDUS_STATUS'); ?> :
      <span class="label label-<?php echo $application->class; ?>">
        <?php echo $application->value; ?>
      </span>
      <section class="container" style="width:150px; float: left;">
        <div id="file<?php echo $application->fnum; ?>"></div>
        <div id="forms<?php echo $application->fnum; ?>"></div>
        <div id="documents<?php echo $application->fnum; ?>"></div>
    </section>
    </div>
  </div>
  <hr>
  <?php endforeach;  ?>
 </div>
<?php else :
  echo JText::_('NO_FILE');
?>
<?php endif; ?>
<script type="text/javascript">
$( document ).ready(function() {
    $('div.rt-block.submit_form').hide();
    $('div.rt-block.application_fee').hide();
});
</script>
