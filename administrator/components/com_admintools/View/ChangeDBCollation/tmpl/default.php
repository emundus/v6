<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;
?>

<div class="alert alert-info">
	<h3>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_HEAD'); ?>
	</h3>
	<p>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_BODY'); ?>
	</p>
</div>

<div class="alert">
	<p>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE_INFO_WARN'); ?>
	</p>
</div>

<form action="index.php" name="adminForm" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ChangeDBCollation"/>
	<input type="hidden" name="task" value="apply"/>
	<input type="hidden" name="tmpl" value="component"/>
	<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>

	<div class="control-group">
		<label class="control-label" for="inputCollation"><?php echo $this->escape(trim(JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_CHOOSE'), ' -–')); ?></label>
		<div class="controls">
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

	<div class="control-group">
		<div class="controls">
			<a class="btn" href="index.php?option=com_admintools">
				<span class="icon icon-leftarrow"></span>
				<?php echo \JText::_('JTOOLBAR_BACK'); ?>
			</a>
			<button type="submit" class="btn btn-warning">
				<span class="icon icon-white icon-forward"></span>
				<?php echo \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_APPLY'); ?>
			</button>
		</div>
	</div>
</form>