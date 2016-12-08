<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var    $this   Akeeba\AdminTools\Admin\View\EmergencyOffline\Html */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="EmergencyOffline"/>
		<input type="hidden" name="task" value="offline"/>
		<input type="submit" class="btn btn-large btn-danger" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_SETOFFLINE'); ?>"/>
		<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>
	</form>

<?php if ( ! ($this->offline)): ?>
	<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLY'); ?></p>
	<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLYMANUAL'); ?></p>
	<pre><?php echo $this->htaccess ?></pre>
<?php endif; ?>

<?php if ($this->offline): ?>
	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="EmergencyOffline"/>
		<input type="hidden" name="task" value="online"/>
		<input type="submit" class="btn btn-large btn-success" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLY'); ?>"/>
		<input type="hidden" name="<?php echo \JFactory::getSession()->getFormToken(); ?>" value="1"/>
	</form>
	<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLY'); ?></p>
	<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLYMANUAL'); ?></p>
<?php endif; ?>