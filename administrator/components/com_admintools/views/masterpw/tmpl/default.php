<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

function booleanlist($name, $attribs = null, $selected = null, $yes = 'yes', $no = 'no', $id = false)
{
	$arr = array(
		JHTML::_('select.option', '0', JText::_($no)),
		JHTML::_('select.option', '1', JText::_($yes))
	);

	return JHTML::_('select.genericlist', $arr, $name, $attribs, 'value', 'text', (int)$selected, $id);
}

$jyes = 'JYES';
$jno = 'JNO';

?>
<form action="index.php" method="post" name="adminForm" id="adminForm"
	  class="form form-horizontal form-horizontal-wide">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="masterpw"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_MASTERPW_PASSWORD') ?></legend>

		<div class="control-group">
			<label for="masterpw" class="control-label"><?php echo JText::_('ATOOLS_LBL_MASTERPW_PWPROMPT'); ?></label>

			<div class="controls">
				<input type="password" name="masterpw" value="<?php echo $this->masterpw ?>"/>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_MASTERPW_PROTVIEWS'); ?></legend>

		<div class="control-group">
			<label class="control-label"><?php echo JText::_('ATOOLS_LBL_MASTERPW_QUICKSELECT') ?>&nbsp;</label>

			<div class="controls">
				<div class="btn-group">
					<button class="btn"
							onclick="return doMassSelect(1);"><?php echo JText::_('ATOOLS_LBL_MASTERPW_ALL') ?></button>
					<button class="btn"
							onclick="return doMassSelect(0);"><?php echo JText::_('ATOOLS_LBL_MASTERPW_NONE') ?></button>
				</div>
			</div>
		</div>
		<?php foreach ($this->items as $view => $locked): ?>
			<?php $fieldname = 'views[' . $view . ']' ?>
			<div class="control-group">
				<label for="<?php echo $fieldname ?>"
					   class="control-label"><?php echo JText::_('ADMINTOOLS_TITLE_' . strtoupper($view)); ?></label>

				<div class="controls">
					<?php echo booleanlist($fieldname, array('class' => 'masterpwcheckbox input-small'), ($locked ? 1 : 0), $jyes, $jno); ?>
				</div>
			</div>

		<?php endforeach; ?>
	</fieldset>
</form>

<script type="text/javascript">
	function doMassSelect(value)
	{
		$$('.masterpwcheckbox>option').setProperty('selected', '');
		$$('.masterpwcheckbox').setProperty('value', value);
		return false;
	}
</script>