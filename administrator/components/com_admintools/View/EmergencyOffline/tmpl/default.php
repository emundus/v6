<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
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
    <p>
        <input type="submit" class="akeeba-btn--red--big" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_SETOFFLINE'); ?>"/>
    </p>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>

<?php if ( ! ($this->offline)): ?>
    <p class="akeeba-block--info">
	    <?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLY'); ?>
    </p>
	<p class="akeeba-block--warning">
        <?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLYMANUAL'); ?>
    </p>
	<pre><?php echo $this->htaccess ?></pre>
<?php endif; ?>

<?php if ($this->offline): ?>

	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="EmergencyOffline"/>
		<input type="hidden" name="task" value="online"/>
        <p>
            <input type="submit" class="akeeba-btn--green--big" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLY'); ?>"/>
        </p>
		<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
	</form>
	<p class="akeeba-block--info"><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLY'); ?></p>
	<p class="akeeba-block--warning"><?php echo \JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLYMANUAL'); ?></p>
<?php endif; ?>
