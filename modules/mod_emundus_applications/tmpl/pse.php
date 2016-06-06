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
  <a class="btn btn-success" href="<?php echo JURI::Base(); ?>index.php?option=com_emundus&view=renew_application"><span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
<div class="<?php echo $moduleclass_sfx ?>"> 
 <?php foreach($applications as $application) : ?>
  <fieldset>
  <a href="<?php echo JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum; ?>" class="list-group-item<?php echo ($application->fnum == $user->fnum)?'-active':''; ?>">
    <h4><?php echo $application->label; ?></h4>
      <span class="label label-<?php echo $application->class; ?>"> <?php echo $application->value; ?></span> 
      <?php if($application->fnum == $user->fnum) : ?>
        <span class="badge <?php echo($progress>=100)?'badge-success':'badge-inverse'; ?>"><?php echo $progress; ?>%</span>
        <?php if($progress>=100 && $application->status==0) : ?>
          <a class="btn btn-mini" href="<?php echo $confirm_form_url; ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
        <?php endif; ?>
      <?php endif; ?>

    <p class="list-group-item-text"><i class="<?php echo ($application->fnum == $user->fnum)?'icon-folder-open':'icon-folder-close'; ?>"></i> N° <?php echo $application->fnum; ?></p>
  </a>
  </fieldset>
 <?php endforeach;  ?>
</div> 
<?php endif; ?>
<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
  <a class="btn btn-success" href="<?php echo JURI::Base(); ?>index.php?option=com_emundus&view=renew_application"><span class="icon-plus-sign"> <?php echo JText::_('ADD_APPLICATION_FILE'); ?></span></a>
<hr>
<?php endif; ?>
