<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\CleanTempDirectory\Html */

defined('_JEXEC') or die;

JHtml::_('behavior.modal');
?>
<?php if ($this->more): ?>
	<h1><?php echo \JText::_('COM_ADMINTOOLS_LBL_CLEANTEMPDIRECTORY_CLEANTMPINPROGRESS'); ?></h1>
<?php else: ?>
	<h1><?php echo \JText::_('COM_ADMINTOOLS_LBL_CLEANTEMPDIRECTORY_CLEANTMPDONE'); ?></h1>
<?php endif; ?>

	<div class="akeeba-progress">
        <div class="akeeba-progress-fill" style="width:<?php echo (int)$this->percentage ?>%;"></div>
        <div class="akeeba-progress-status">
			<?php echo (int)$this->percentage ?>%
        </div>
    </div>

	<form action="index.php" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="CleanTempDirectory"/>
		<input type="hidden" name="task" value="run"/>
		<input type="hidden" name="tmpl" value="component"/>
	</form>

<?php if (!$this->more): ?>
	<div class="akeeba-block--info" id="admintools-cleantmp-autoclose">
		<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S'); ?></p>
	</div>
<?php endif; ?>
