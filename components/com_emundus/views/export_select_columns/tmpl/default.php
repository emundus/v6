<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );

$eMConfig = JComponentHelper::getParams('com_emundus');
$current_user = JFactory::getUser();
$view = JRequest::getVar('v', null, 'GET', 'none',0); 
//$view = JRequest::getVar('view', null, 'GET', 'none',0); 
$comments = JRequest::getVar('comments', null, 'POST', 'none', 0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
// Starting a session.
$session = JFactory::getSession();
$s_elements = $session->get('s_elements');
$comments = $session->get('comments');

if(!empty($s_elements))
foreach($s_elements as $s){
	$t = explode('.',$s);
	$table_name[] = $t[0];
	$element_name[] = $t[1];
}
?>

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" >
	<input type='button' onclick='location.href="index.php?option=com_emundus&view=<?php echo $view;?>&Itemid=<?php echo $itemid; ?>"' value="<?php echo JText::_('RETURN_BACK'); ?>"/>
	<input type="submit" name="send_elements" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS'); ?>"/> 
	<!-- <input type="submit" name="send_elements_csv" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS_CSV'); ?>"/> -->
	<input type="hidden" name="option" value="com_emundus"/>
    <input type="hidden" name="view" value="<?php echo $view; ?>"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
	<?php
		echo JText::_('SELECT_ALL');
		echo '<input type="checkbox" id="emundus_checkall" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall\', \'emundus_elements\', 3)" /><div id="emundus_elements">';
		$tbl_tmp='';
		$grp_tmp='';
		
		foreach($this->elements as $t){
			if ($tbl_tmp == '') {
				echo '<fieldset id="emundus_table_'.$t->table_id.'">
						<legend><input type="checkbox" ';
				if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
				echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\', \'emundus_table_'.$t->table_id.'\', 2)" /> '.$t->table_label.'</legend>
					  <fieldset id="emundus_grp_'.$t->group_id.'">
						<legend><input type="checkbox" ';
				
				if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
				echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\', \'emundus_grp_'.$t->group_id.'\', 1)" /> '.$t->group_label.'</legend>';
			} elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
					echo '</fieldset></fieldset>
						<fieldset id="emundus_table_'.$t->table_id.'">
							<legend><input type="checkbox" ';
					if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
					echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\', \'emundus_table_'.$t->table_id.'\', 2)" /> '.$t->table_label.'</legend>
						<fieldset id="emundus_grp_'.$t->group_id.'">
							<legend><input type="checkbox" ';
					
					if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
					echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\', \'emundus_grp_'.$t->group_id.'\', 1)" /> '.$t->group_label.'</legend>';
			} else {
				if ($t->group_id != $grp_tmp && $grp_tmp != '') {
						echo '</fieldset><fieldset id="emundus_grp_'.$t->group_id.'">
								<legend><input type="checkbox" ';
						
						if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
						echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusraw" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\', \'emundus_grp_'.$t->group_id.'\', 1)"/> '.$t->group_label.'</legend>';
				} 
			}
			echo ' <input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusraw" ';
			if((!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) || ($t->created_by_alias == 'comment' && $comments == 1)) echo "checked=checked";
			echo ' value="'.$t->id.'"/><label for="emundus_elm_'.$t->id.'">'.$t->element_label.'</label> ';
		
			$tbl_tmp=$t->table_id;
			$grp_tmp=$t->group_id;
		}
		echo '</fieldset></fieldset>';
		echo '</div>';
		?>
	<input type="submit" name="send_elements" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS'); ?>"/> 
	<!-- <input type="submit" name="send_elements_csv" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS_CSV'); ?>"/>  -->
</form>    

<script>
function check_all(box, obj, level) {
 var checked = document.getElementById(box).checked;
 var node = document.getElementById(obj);
// var parent = node.parentNode; //place la variable parent sur le noeud parent de node
 var childList = node.childNodes; //récupère tous les enfants de node dans un tableau childNodesList
// var child1 = node.firstChild; //récupère le premier enfant de node
// var childx = node.lastChild; //récupère le dernier enfant de node
// var frerePrec = node.previousSibling; //récupère le frère précédent de node (l'enfant précédent du parent de node)
// var frereSuiv = node.nextSibling; //récupère le frère suivant
 if(level == 1) {
	 for (i=1 ; i < childList.length ; i++) {
		childList[i].checked = checked;
	 }
 }
 if(level == 2) {
	 for (i=1 ; i < childList.length ; i++) {
		var nodeStr = childList[i].id; 
		if(nodeStr) {
			var tabId = nodeStr.split('_');
			var toCheck = document.getElementById('emundus_checkall_grp_'+tabId[2]);
			toCheck.checked = checked;
		}
		var grp = childList[i].childNodes;
		for (j=1 ; j < grp.length ; j++) {
			grp[j].checked = checked;
		}
	 }
 }
 if(level == 3) {
	 for (i=0 ; i < childList.length ; i++) {
		var nodeStr = childList[i].id; 
		if(nodeStr) {
			var tabId = nodeStr.split('_');
			var toCheck = document.getElementById('emundus_checkall_tbl_'+tabId[2]);
			toCheck.checked = checked;
		}
		var grp = childList[i].childNodes;
		for (j=1 ; j < grp.length ; j++) {
			var nodeGrpStr = grp[j].id; 
			if(nodeGrpStr) {
				var tabId = nodeGrpStr.split('_');
				var toCheck = document.getElementById('emundus_checkall_grp_'+tabId[2]);
				toCheck.checked = checked;
			}
			var elm = grp[j].childNodes;
			for (k=1 ; k < elm.length ; k++) {
				elm[k].checked = checked;
			}
		}
	 }
 }
}

<?php 
if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) { 
?>
	function hidden_all() {
		document.getElementById('checkall').style.visibility='hidden';
		<?php foreach ($this->elements as $t) { ?>
			document.getElementById('emundus_elm_<?php echo $t->id; ?>').style.visibility='hidden';
		<?php } ?>
	}
	hidden_all();
	<?php 
}?>

function OnSubmitForm() {
	var button_name=document.pressed.split("|");
//alert(button_name[0]);
	switch(button_name[0]) {
		case 'send_elements': 
			document.adminForm.task.value = "send_elements";
			document.adminForm.action ="index.php?option=com_emundus&task=send_elements&v=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>"; 
		break;
		case 'send_elements_csv': 
			document.adminForm.task.value = "send_elements_csv";
			document.adminForm.action ="index.php?option=com_emundus&task=send_elements_csv&v=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>";
		break;
		default: return false;
	}
	return true;
} 
</script>