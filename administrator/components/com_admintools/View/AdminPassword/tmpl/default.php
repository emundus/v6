<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;
?>
<p class="alert alert-info">
	<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INTRO'); ?>
</p>

<p class="alert">
	<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_WARN'); ?>
</p>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="AdminPassword"/>
	<input type="hidden" name="task" id="task" value="protect"/>
	<input type="hidden" name="<?php echo \JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<p class="help-block"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INFO'); ?></p>

	<div class="control-group">
		<label class="control-label" for="username"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME'); ?></label>

		<div class="controls">
			<input type="text" name="username" id="username" value="<?php echo $this->escape($this->username); ?>" autocomplete="off"/>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="password"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD'); ?></label>

		<div class="controls">
			<input type="password" name="password" id="password" value="<?php echo $this->escape($this->password); ?>"
				   autocomplete="off"/>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="password2"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2'); ?></label>

		<div class="controls">
			<input type="password" name="password2" id="password2" value="<?php echo $this->escape($this->password); ?>"
				   autocomplete="off"/>
		</div>
	</div>

	<div class="form-actions">
		<input type="submit" class="btn btn-warning"
			   value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PROTECT'); ?>"/>
		<?php if ($this->adminLocked): ?>
			&nbsp;&nbsp;
			<a class="btn btn-success"
			   href="index.php?option=com_admintools&view=AdminPassword&task=unprotect&<?php echo \JFactory::getSession()->getFormToken(); ?>=1"
			>
				<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNPROTECT'); ?>
			</a>
		<?php endif; ?>
	</div>
</form>