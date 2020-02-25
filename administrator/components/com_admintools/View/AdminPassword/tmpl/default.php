<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/** @var \Akeeba\AdminTools\Admin\View\AdminPassword\Html $this */

?>
<div class="akeeba-panel--teal">
	<header class="akeeba-block-header">
		<h3><?= JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_HOWITWORKS') ?></h3>
	</header>
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INFO'); ?>
	</p>

	<p class="akeeba-block--warning">
		<?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_WARN'); ?>
	</p>
</div>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-form-group">
		<label for="resetErrorPages"><?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES'); ?></label>
		<?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'resetErrorPages', $this->resetErrorPages); ?>
		<p class="akeeba-help-text">
			<?= JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES_HELP') ?>
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="username"><?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME'); ?></label>
		<input type="text" name="username" id="username" value="<?php echo $this->escape($this->username); ?>" autocomplete="off"/>
		<p class="akeeba-help-text">
			<?= JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME_HELP') ?>
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="password"><?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD'); ?></label>
		<input type="password" name="password" id="password" value="<?php echo $this->escape($this->password); ?>" autocomplete="off"/>
		<p class="akeeba-help-text">
			<?= JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD_HELP') ?>
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="password2"><?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2'); ?></label>
		<input type="password" name="password2" id="password2" value="<?php echo $this->escape($this->password); ?>"  autocomplete="off"/>
		<p class="akeeba-help-text">
			<?= JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2_HELP') ?>
		</p>
	</div>

	<div class="akeeba-form-group--pull-right">
		<div class="akeeba-form-group--actions">
			<button type="submit" class="akeeba-btn--orange">
				<span class="akion-android-lock"></span>
				<?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PROTECT'); ?>
			</button>
		<?php if ($this->adminLocked): ?>
			<a class="akeeba-btn--green"
			   href="index.php?option=com_admintools&view=AdminPassword&task=unprotect&<?php echo $this->container->platform->getToken(true); ?>=1"
			>
				<span class="akion-android-unlock"></span>
				<?php echo JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNPROTECT'); ?>
			</a>
		<?php endif; ?>
		</div>
	</div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="AdminPassword"/>
    <input type="hidden" name="task" id="task" value="protect"/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
