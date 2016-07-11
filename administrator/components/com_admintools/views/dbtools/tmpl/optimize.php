<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
<?php if (!empty($this->table)): ?>
	<h1><?php echo JText::_('ATOOLS_LBL_OPTIMIZEINPROGRESS'); ?></h1>
<?php else: ?>
	<h1><?php echo JText::_('ATOOLS_LBL_OPTIMIZECOMPLETE'); ?></h1>
<?php endif; ?>

	<div class="progress progress-striped active">
		<div class="bar" style="width: <?php echo $this->percentage ?>%"></div>
	</div>

<?php if (!empty($this->table)): ?>
	<form action="index.php" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="dbtools"/>
		<input type="hidden" name="task" value="optimize"/>
		<input type="hidden" name="from" value="<?php echo $this->table ?>"/>
		<input type="hidden" name="tmpl" value="component"/>
	</form>
<?php endif; ?>

<?php if ($this->percent == 100): ?>
	<div class="alert alert-info">
		<p><?php echo JText::_('ATOOLS_LBL_AUTOCLOSE_IN_3S'); ?></p>
	</div>
<?php endif; ?>