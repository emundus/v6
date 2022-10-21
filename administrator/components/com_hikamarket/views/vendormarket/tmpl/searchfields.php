<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script language="javascript" type="text/javascript">
<!--
	var selectedContents = [], allElements = <?php echo count($this->rows);?>;
<?php
	foreach($this->rows as $oneRow){
		if(!empty($oneRow->selected))
			echo '	selectedContents["'.$oneRow->namekey.'"] = "content";'."\r\n";
	}
?>
	function applyContent(contentid,rowClass) {
		var d = document;
		if(selectedContents[contentid]) {
			d.getElementById('content'+contentid).className = rowClass;
			delete selectedContents[contentid];
		}else{
			d.getElementById('content'+contentid).className = 'selectedrow';
			selectedContents[contentid] = 'content';
		}
	}
	function insertTag() {
		var tag = '', d = window.top.document;
		for(var i in selectedContents) {
			if(selectedContents[i] == 'content') {
				allElements--;
				if(tag != '')
					tag += ',';
				tag = tag + i;
			}
		}
		d.getElementById('<?php echo $this->controlName; ?>fields').value = tag;
		d.getElementById('link<?php echo $this->controlName; ?>fields').href = 'index.php?option=<?php echo HIKAMARKET_COMPONENT ?>&tmpl=component&ctrl=vendor&task=searchfields&control=<?php echo $this->controlName; ?>&values='+tag;
		window.top.hikashop.closeBox();
	}
//-->
</script>
<style type="text/css">
table.adminlist tr.selectedrow td {
	background-color:#FDE2BA;
}
</style>
<form action="index.php?option=<?php echo HIKAMARKET_COMPONENT ?>" method="post" name="adminForm" id="adminForm">
	<div style="float:right;margin-bottom : 10px">
		<button class="btn" id="insertButton" onclick="insertTag(); return false;"><?php echo JText::_('HIKA_APPLY'); ?></button>
	</div>
	<div style="clear:both"></div>
	<table class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title"><?php echo 'Field'; ?></th>
				<th class="title titleid"><?php echo JText::_('ID'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$k = 0;
	foreach($this->rows as $i => $row){
?>
			<tr class="<?php echo empty($row->selected) ? ('row'.$k) : 'selectedrow'; ?>" id="content<?php echo $row->namekey; ?>" onclick="applyContent('<?php echo $row->namekey."','row".$k."'"?>);" style="cursor:pointer;">
				<td><?php echo $row->namekey; ?></td>
				<td align="center"><?php echo $i; ?></td>
			</tr>
<?php
		$k = 1-$k;
	}
?>
		</tbody>
	</table>
</form>
