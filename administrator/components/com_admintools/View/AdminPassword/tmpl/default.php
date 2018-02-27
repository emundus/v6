<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/** @var \Akeeba\AdminTools\Admin\View\AdminPassword\Html $this */

?>
<div class="akeeba-block--info">
	<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INTRO'); ?>
</div>

<p class="akeeba-block--warning">
	<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_WARN'); ?>
</p>

<p class="help-block"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INFO'); ?></p>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
    <div class="akeeba-container--25-75">
        <div>
            <div class="akeeba-form-group">
                <label for="username"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME'); ?></label>

                <input type="text" name="username" id="username" value="<?php echo $this->escape($this->username); ?>" autocomplete="off"/>
            </div>

            <div class="akeeba-form-group">
                <label for="password"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD'); ?></label>

                <input type="password" name="password" id="password" value="<?php echo $this->escape($this->password); ?>" autocomplete="off"/>
            </div>

            <div class="akeeba-form-group">
                <label for="password2"><?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2'); ?></label>

                <input type="password" name="password2" id="password2" value="<?php echo $this->escape($this->password); ?>"  autocomplete="off"/>
            </div>
        </div>
    </div>

    <div>
        <input type="submit" class="akeeba-btn--orange"
               value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PROTECT'); ?>"/>
        <?php if ($this->adminLocked): ?>
            <a class="akeeba-btn--grenn"
               href="index.php?option=com_admintools&view=AdminPassword&task=unprotect&<?php echo $this->container->platform->getToken(true); ?>=1"
            >
                <?php echo \JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNPROTECT'); ?>
            </a>
        <?php endif; ?>
    </div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="AdminPassword"/>
    <input type="hidden" name="task" id="task" value="protect"/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
