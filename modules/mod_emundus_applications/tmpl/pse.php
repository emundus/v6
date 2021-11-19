<?php
/**
 * @package		Joomla.Site
 * @subpackage	eMundus
 * @copyright Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

echo $description;

$confirm_form_url = $m_application->getConfirmUrl($fnums);
$first_page = $m_application->getFirstPage('index.php', $fnums);
?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
<hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
<div class="<?php echo $moduleclass_sfx ?>">
 <?php foreach($applications as $application) : ?>
  <fieldset>
  <a href="<?php echo (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum='.$application->fnum.'&redirect='.base64_encode($first_page[$application->fnum]['link']); ?>" class="list-group-item<?php echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'-active':''; ?>">
    <h4><?php echo $application->label; ?></h4>
    	<span class="label label-<?php echo $application->class; ?>"> <?php echo $application->value; ?></span>
    	<?php if (!empty($user->fnum) && $application->fnum == $user->fnum) : ?>
    		<span class="badge <?php echo($progress>=100)?'badge-success':'badge-inverse'; ?>"><?php echo $progress; ?>%</span>
    		<?php if (($progress>=100 && in_array($application->status, $status_for_send) && !$is_dead_line_passed) || in_array($user->id, $applicants) ) : ?>
    			<a class="btn btn-mini" href="<?php echo $confirm_form_url[$application->fnum]['link']; ?>" title="<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_SEND_APPLICATION_FILE'); ?></a>
    		<?php endif; ?>
    	<?php endif; ?>

    <p class="list-group-item-text"><i class="<?php echo (!empty($user->fnum) && $application->fnum == $user->fnum)?'icon-folder-open':'icon-folder-close'; ?>"></i> N° <?php echo $application->fnum; ?></p>
  </a>
  </fieldset>
 <?php endforeach;  ?>
</div>
<?php endif; ?>
<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
  <a class="btn btn-success" href="index.php?option=com_fabrik&view=form&formid=102"><span class="icon-plus-sign"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
<hr>
<?php endif; ?>
