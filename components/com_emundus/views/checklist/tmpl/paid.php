<?php 
defined('_JEXEC') or die('Restricted access'); 

$Itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

if (!empty($this->applications)) : ?>
<hr>
<div class="<?php echo $moduleclass_sfx ?>"> 
  <?php foreach($this->applications as $application) : ?>
  <div class="row" id="row<?php echo $application->fnum; ?>">
    <div class="col-xs-6 col-md-4">
      <p class="">
        <a href="<?php echo JRoute::_(JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid.'#em-panel'); ?>" >
          <?php
            echo ($application->fnum == $this->_user->fnum)?'<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <b>'.$application->label.'</b>':$application->label;
          ?>
        </a> 
    </div>

    <div class="col-xs-6 col-md-4">
      <p>
        <?php echo JText::_('FILE_NUMBER'); ?> : <i><?php echo $application->fnum; ?></i>
      </p>
      <a class="btn btn-warning btn-xs" href="<?php echo JRoute::_(JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode("index.php?fnum=".$application->fnum).'&Itemid='.$Itemid.'#em-panel'); ?>"  role="button">
          <i class="folder open outline icon"></i> <?php echo JText::_('OPEN_APPLICATION'); ?>
      </a>

      <?php if((int)($this->attachments[$application->fnum])>=100 && $application->status==0) : ?>
        <a class="btn btn-xs" href="<?php echo JRoute::_(JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($this->confirm_form_url)); ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
      <?php endif; ?>
    </div>

    <div class="col-xs-6 col-md-4">
      <?php echo JText::_('STATUS'); ?> : 
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
