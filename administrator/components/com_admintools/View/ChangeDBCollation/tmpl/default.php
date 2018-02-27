<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;
?>

<div class="akeeba-block--info">
	<h3>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_HEAD'); ?>
	</h3>
	<p>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_BODY'); ?>
	</p>
</div>

<div class="akeeba-block--warning">
	<p>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_WARN'); ?>
	</p>
</div>

<form action="index.php" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
	<div class="akeeba-form-group">
		<label for="inputCollation"><?php echo $this->escape(trim(JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE'), ' -–')); ?></label>
		<div>
			<select id="quickCollation" onchange="admintools.ChangeDBCollation.change();">
				<option value="">
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_CUSTOM'); ?>
				</option>
				<option value="utf8_general_ci">
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_UTF8'); ?>
				</option>
				<option value="utf8mb4_general_ci" selected="selected">
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_UTF8MB4'); ?>
				</option>
			</select>

			<input type="text" id="inputCollation" name="collation" placeholder="collation" value="utf8mb4_general_ci" style="display: none;">
		</div>
	</div>

	<div class="akeeba-form-group">
		<div>
			<a class="akeeba-btn--ghost" href="index.php?option=com_admintools">
				<span class="akion-ios-arrow-back"></span>
				<?php echo \JText::_('JTOOLBAR_BACK'); ?>
			</a>
			<button type="submit" class="akeeba-btn--orange">
				<span class="akion-forward"></span>
				<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_APPLY'); ?>
			</button>
		</div>
	</div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="ChangeDBCollation"/>
    <input type="hidden" name="task" value="apply"/>
    <input type="hidden" name="tmpl" value="component"/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
