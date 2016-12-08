<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\DatabaseTools\Html */
defined('_JEXEC') or die;

?>
<?php if (!empty($this->table)): ?>
	<h1><?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_INPROGRESS'); ?></h1>
<?php else: ?>
	<h1><?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_COMPLETE'); ?></h1>
<?php endif; ?>

	<div class="progress progress-striped active">
		<div class="bar" style="width: <?php echo $this->percent ?>%"></div>
	</div>

<?php if (!empty($this->table)): ?>
	<form action="index.php" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="DatabaseTools"/>
		<input type="hidden" name="task" value="optimize"/>
		<input type="hidden" name="from" value="<?php echo $this->escape($this->table); ?>"/>
		<input type="hidden" name="tmpl" value="component"/>
	</form>
<?php endif; ?>

<?php if ($this->percent == 100): ?>
	<div class="alert alert-info" id="admintools-databasetools-autoclose">
		<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S'); ?></p>
	</div>
<?php endif; ?>