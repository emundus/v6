<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="title" style="float: left;"><h1><?php echo JText::_('HIKASHOP_ACTIONS'); ?></h1></div>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-primary" type="button" onclick="process_batch();"><i class="fa fa-cogs"></i> <?php echo JText::_('PROCESS'); ?></button>
</div>
<?php
if(!isset($this->table))
	return;
$table =& $this->table;
?>
<div id="hikabase_<?php echo $table->table; ?>_massactions" style="display:none;">
	<div id="<?php echo $table->table; ?>_actions_original">
		<?php echo JHTML::_('select.genericlist', $table->typevaluesActions, "action[".$table->table."][type][__num__]", 'class="custom-select chzn-done not-processed" size="1" onchange="updateMassAction(\'action\',\''.$table->table.'\',__num__); refreshSelect(\''.$table->table.'\',\'action\', __num__) "', 'value', 'text','action'.$table->table.'__num__'); ?>
		<div class="hikamassactionarea" id="<?php echo $table->table; ?>actionarea___num__"></div>
	</div>
	<?php echo implode('',$table->actions_html); ?>
</div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&ctrl=massaction" method="post" autocomplete="off" enctype="multipart/form-data" name="adminForm" id="adminForm">
	<div class="hikashop_massaction_form" style="clear:both;">
	<div id="<?php echo $table->table; ?>">
<?php
			echo '<div id="all'.$table->table.'actions">';
			$count = 0;
			if(HIKASHOP_J30 && false){
				for($i=0;$i<=$count;$i++){
					?> <script type="text/javascript">jQuery("#<?php echo $table->table; ?>action<?php echo $i; ?> .not-processed").removeClass("not-processed").removeClass("chzn-done").chosen(); </script><?php
				}
			}
			echo '</div>';
?>
	</div>
	</div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="batch_process" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="table_type" value="<?php echo $table->table; ?>" />
	<input type="hidden" name="ctrl" value="massaction" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
addHikaMassAction('<?php echo $table->table; ?>','action');
function process_batch() {
	var checkboxes = window.parent.document.querySelectorAll('input[name="cid[]"]');
	var batchForm = document.getElementById('adminForm');
	console.log(checkboxes);
	checkboxes.forEach(elem => {
		var clone = elem.cloneNode();
		clone.style.display = 'none';
		batchForm.appendChild(clone);
	});
	batchForm.submit();
}
</script>
