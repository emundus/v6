<?php
/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

echo $description;
?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
  <?php if(!isset($admission_fnum)) :?>
    <a class="btn btn-success" href="<?php echo JURI::base(true); ?>index.php?option=com_emundus&view=renew_application"><span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span></a>
  <?php else :?>
    <a class="btn btn-success" href="<?php echo JURI::base(true); ?>index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?php echo $admission_fnum ?>"><span class="icon-plus-sign"> <?php echo JText::_('COMPLETE_ADMISSION'); ?></span></a>
  <?php endif; ?>
<hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
<div class="<?php echo $moduleclass_sfx ?>"> 
 <?php foreach($applications as $application) : ?>
  <?php 
    $progress = (int)(($forms[$application->fnum]+$attachments[$application->fnum]))/2;
  ?>
  <a href="<?php echo JURI::base(true).'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&Itemid='.$Itemid; ?>" class="list-group-item<?php echo ($application->fnum == @$user->fnum)?'-active':''; ?>">
    <b class="list-group-item-heading">
      <span class="label label-<?php echo $application->class; ?>"> <?php echo $application->value; ?></span> 
        <span class="badge <?php echo($progress>=100)?'badge-success':'badge-inverse'; ?>"><?php echo $progress; ?>%</span>
        <?php if($progress>=100 && $application->status==0) : ?>
          <a class="btn btn-mini" href="<?php echo $confirm_form_url; ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
      <?php endif; ?>
      <?php echo $application->label; ?>
    </b>
    <p class="list-group-item-text <?php echo ($application->fnum == @$user->fnum)?'label-success':''; ?>"><span class="badge <?php echo ($application->fnum == @$user->fnum)?'badge-success':''; ?>"><?php echo $application->fnum; ?></span></p>
  </a><hr>
 <?php endforeach;  ?>
</div> 
<?php endif; ?>
<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
  <?php if(!isset($admission_fnum)) :?>
    <a class="btn btn-success" href="<?php echo JURI::base(true); ?>index.php?option=com_emundus&view=renew_application"><span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span></a>
  <?php else :?>
    <a class="btn btn-success" href="<?php echo JURI::base(true); ?>index.php?option=com_fabrik&view=form&formid=272&Itemid=2720&usekey=fnum&rowid=<?php echo $admission_fnum ?>"><span class="icon-plus-sign"> <?php echo JText::_('COMPLETE_ADMISSION'); ?></span></a>
  <?php endif; ?>
<hr>
<?php endif; ?>
