<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\MasterPassword\Html */

// Protect from unauthorized access
defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Select;

?>
<form action="index.php" method="post" name="adminForm" id="adminForm"
	  class="form form-horizontal form-horizontal-wide">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="MasterPassword"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>

	<fieldset>
		<legend><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PASSWORD'); ?></legend>

		<div class="control-group">
			<label for="masterpw" class="control-label"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PWPROMPT'); ?></label>

			<div class="controls">
				<input id="masterpw" type="password" name="masterpw" value="<?php echo $this->escape($this->masterpw); ?>"/>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PROTVIEWS'); ?></legend>

		<div class="control-group">
			<label class="control-label"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_QUICKSELECT'); ?>&nbsp;</label>

			<div class="controls">
				<div class="btn-group">
					<button class="btn"
							onclick="return admintools.MasterPassword.doMassSelect(1);"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_ALL'); ?></button>
					<button class="btn"
							onclick="return admintools.MasterPassword.doMassSelect(0);"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_NONE'); ?></button>
				</div>
			</div>
		</div>
		<?php foreach ($this->items as $view => $x):
			list($locked, $langKey) = $x;
			?>
			<div class="control-group">
				<label for="views[<?php echo $this->escape($view); ?>]"
					   class="control-label"><?php echo \JText::_($langKey); ?></label>

				<div class="controls">
					<?php echo Select::booleanlist('views[' . $view . ']', array('class' => 'masterpwcheckbox input-small'), ($locked ? 1 : 0), false); ?>

				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>
</form>