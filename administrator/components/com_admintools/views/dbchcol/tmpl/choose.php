<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

$option = 'com_admintools';

JHTML::_('behavior.framework', true);
?>

<div class="alert alert-info">
	<h3>
		<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_INFO_HEAD'); ?>
	</h3>
	<p>
		<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_INFO_BODY'); ?>
	</p>
</div>

<div class="alert">
	<p>
		<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_INFO_WARN'); ?>
	</p>
</div>

<form action="index.php" name="adminForm" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="dbchcol"/>
	<input type="hidden" name="task" value="apply"/>
	<input type="hidden" name="tmpl" value="component"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken() ?>" value="1"/>

	<div class="control-group">
		<label class="control-label" for="inputCollation"><?php echo trim(JText::_('ATOOLS_LBL_DBCHCOLCHOOSE'), ' -–'); ?></label>
		<div class="controls">
			<select id="quickCollation" onchange="atools_dbchcol_change();">
				<option value="">
					<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_CUSTOM'); ?>
				</option>
				<option value="utf8_general_ci">
					<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_UTF8'); ?>
				</option>
				<option value="utf8mb4_general_ci" selected="selected">
					<?php echo JText::_('ATOOLS_LBL_DBCHCOLCHOOSE_UTF8MB4'); ?>
				</option>
			</select>
			<input type="text" id="inputCollation" name="collation" placeholder="collation" value="utf8mb4_general_ci" style="display: none;">
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn btn-warning btn-large">
				<span class="icon icon-white icon-plane"></span>
				<?php echo JText::_('ATOOLS_LBL_DBCHCOLAPPLY'); ?>
			</button>

			<a class="btn" href="index.php?option=com_admintools">
				<span class="icon icon-leftarrow"></span>
				<?php echo JText::_('JTOOLBAR_BACK') ?>
			</a>
		</div>
	</div>
</form>

<script type="text/javascript">
	function atools_dbchcol_change()
	{
		var collation = window.jQuery('#quickCollation').val();

		if (collation == '')
		{
			window.jQuery('#inputCollation').show();
		}
		else
		{
			window.jQuery('#inputCollation').hide();
		}

		window.jQuery('#inputCollation').val(collation);
	}
</script>