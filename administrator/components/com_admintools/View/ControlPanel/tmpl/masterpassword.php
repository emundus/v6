<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="well">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ControlPanel"/>
	<input type="hidden" name="task" value="login"/>

	<h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWHEAD'); ?></h3>

	<p class="help-block">
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWINTRO'); ?>
	</p>

	<label for="userpw">
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPW'); ?>
	</label>

	<input type="password" name="userpw" id="userpw" value=""/>

	<div class="form-actions">
		<input type="submit" class="btn btn-primary"/>
	</div>
</form>