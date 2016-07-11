<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="eom"/>
		<input type="hidden" name="task" value="offline"/>
		<input type="submit" class="btn btn-large btn-danger" value="<?php echo JText::_('ATOOLS_LBL_APPLY') ?>"/>
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
	</form>

<?php if (!$this->offline): ?>
	<p><?php echo JText::_('ATOOLS_LBL_EOM_PREAPPLY') ?></p>
	<p><?php echo JText::_('ATOOLS_LBL_EOM_PREAPPLYMANUAL') ?></p>
	<pre><?php echo $this->htaccess ?></pre>
<?php else: ?>
	<p>
	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="eom"/>
		<input type="hidden" name="task" value="online"/>
		<input type="submit" class="btn btn-large btn-success" value="<?php echo JText::_('ATOOLS_LBL_UNAPPLY') ?>"/>
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
	</form>
	</p>
	<p><?php echo JText::_('ATOOLS_LBL_EOM_PREUNAPPLY') ?></p>
	<p><?php echo JText::_('ATOOLS_LBL_EOM_PREUNAPPLYMANUAL') ?></p>
<?php endif; ?>