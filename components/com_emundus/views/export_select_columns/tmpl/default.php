<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
//JHTML::stylesheet(JURI::Base().'media/com_emundus/css/emundus.css' );

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
<style>
    .component-content legend {
         border: 0px;
         padding: none;
         margin-left: -6px;
         border-radius: 0px;
         background: inherit;
         border-bottom: 0px;
         margin-bottom: 0;
         line-height: 0;
    }
    .panel-info legend {
        color: #000000;
        font-size: 14px;
    }
    .panel-info.excel {
        min-height: inherit!important;
    }
    .panel-primary {
        border-color: #00316b;
    }
    .panel-primary.excel>.panel-heading {
        background-color: #00316b;
    }
    .panel-heading label {
        color: #FFFFFF!important;
        font-weight: bold!important;
        text-shadow: none ;
        font-size: 18px;
    }
    .panel-info>.panel-heading label {
        color: #000000!important;
        font-weight: bold!important;
        text-shadow: none ;
        font-size: 14px;
    }
</style>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" >
    <!--<input type='button' onclick='location.href="index.php?option=com_emundus&view=<?php echo $view;?>&Itemid=<?php echo $itemid; ?>"' value="<?php echo JText::_('RETURN_BACK'); ?>"/>
	<input type="submit" name="send_elements" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS'); ?>"/> 
	 <input type="submit" name="send_elements_csv" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS_CSV'); ?>"/> -->
	<input type="hidden" name="option" value="com_emundus"/>
    <input type="hidden" name="view" value="<?php echo $view; ?>"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
	<?php
		echo '<input type="checkbox" id="emundus_checkall" class="emundusall" data-check=".emunduspage" onClick="javascript:check_all(\'emundus_checkall\')" /> ';
        echo '<label for="emundus_checkall">'.JText::_('SELECT_ALL').'</label>';
        echo '<div id="emundus_elements">';
        $tbl_tmp='';
		$grp_tmp='';
		
		foreach($this->elements as $t){
			if ($tbl_tmp == '') {
				echo '<div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
						<div class="panel-heading"><legend><input type="checkbox" ';
				if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
                $label = explode("-", $t->table_label);
                $label = $label[1];
				echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.'</label></legend></div><div class="panel-body">
					  <div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
						<div class="panel-heading"><legend><input type="checkbox" ';
				
				if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
				echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$t->group_id.'"> 2'.$t->group_label.'</label></legend></div><div class="panel-body">';
			} elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
					echo '</div></div></div></div>
						<div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
							<div class="panel-heading"><legend><input type="checkbox" ';
					if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
                $label = explode("-", $t->table_label);
                $label = $label[1];
					echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$t->table_id.'"> 3'.$label.'</label></legend></div><div class="panel-body">
						<div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
							<div class="panel-heading"><legend><input type="checkbox" ';
					
					if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
					echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$t->group_id.'">4 '.$t->group_label.'</label></legend></div><div class="panel-body">';
			} else {
				if ($t->group_id != $grp_tmp && $grp_tmp != '') {
						echo '</div></div><div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
								<div class="panel-heading"><legend><input type="checkbox" ';
						
						if($t->created_by_alias == 'comment' && $comments == 1) echo "checked=checked";
						echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')"/><label for="emundus_checkall_grp_'.$t->group_id.'">5 '.$t->group_label.'</legend></div><div class="panel-body">';
				} 
			}
			echo ' <input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_elm_'.$t->id.'\')" ';
			if((!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) || ($t->created_by_alias == 'comment' && $comments == 1)) echo "checked=checked";
			echo ' value="'.$t->id.'"/><label for="emundus_elm_'.$t->id.'">'.$t->element_label.'</label> ';
		
			$tbl_tmp=$t->table_id;
			$grp_tmp=$t->group_id;
		}
		echo '</div></div></div></div>';
		echo '</div>';
		?>
    <!-- <input type="submit" name="send_elements" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS'); ?>"/>
	<input type="submit" name="send_elements_csv" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ELEMENTS_CSV'); ?>"/>  -->
</form>    

<script>
    function check_all( id )
    {
        var inputname = $('#'+id).data('check');
        if (inputname != null) { // Si on a cliqué sur Select All, Page ou groupe
            $('#emundus_elements').find('input:checkbox' + inputname).each(function () {
                $(this).prop("checked", $('#' + id).is(':checked'));
                var datacheck = $(this).attr('data-check');
                if (datacheck != null) {
                    var classdatacheck = datacheck.split('_');
                    classdatacheck = classdatacheck[0];
                    if (classdatacheck == ".emundusgroup") { // Si on a coché Select All, alors il faut parcourir les groupes de chaque page
                        $('#emundus_elements').find('input:checkbox' + datacheck).each(function () { // Pour chaque groupe
                            $(this).prop("checked", $('#' + id).is(':checked'));
                            datacheck = $(this).attr('data-check');
                            $('#emundus_elements').find('input:checkbox' + datacheck).each(function () { // pour chaque item
                                var itemid = $(this).attr('id');
                                itemid = itemid.split('_');
                                itemid = itemid[2];
                                var checked = $('#' + id).is(':checked');
                                $(this).prop("checked", checked);
                                if (checked) {
                                    var text = $("label[for='emundus_elm_" + itemid + "']").text();
                                    $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                                } else {
                                    $('#' + itemid + '-item').remove();
                                }
                            });
                        });
                    }
                    else if (classdatacheck == ".emundusitem") {  // Dans le cas où on clique sur page, les groupes sont cochés et il faut donc ensuite parcourir les items de chaque groupe
                        $('#emundus_elements').find('input:checkbox' + datacheck).each(function () {
                            var itemid = $(this).attr('id');
                            itemid = itemid.split('_');
                            itemid = itemid[2];
                            var checked = $('#' + id).is(':checked');
                            $(this).prop("checked", checked);
                            if (checked) {
                                var text = $("label[for='emundus_elm_" + itemid + "']").text();
                                $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                            } else {
                                $('#' + itemid + '-item').remove();
                            }
                        });
                    }

                } else { // Sinon c'est que l'on a coché directement un groupe (les item n'ayant pas de data-check)
                    var itemid = $(this).attr('id');
                    itemid = itemid.split('_');
                    itemid = itemid[2];
                    var checked = $('#' + id).is(':checked');
                    $(this).prop("checked", checked);
                    if (checked) {
                        var text = $("label[for='emundus_elm_" + itemid + "']").text();
                        $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                    } else {
                        $('#' + itemid + '-item').remove();
                    }
                }
            });
        } else { // Sinon on a coché directement un item
            var itemid = id.split('_');
            itemid = itemid[2];
            var checked = $('#' + id).is(':checked');
            $('#' + id).prop("checked", checked);
            if (checked) {
                var text = $("label[for='emundus_elm_" + itemid + "']").text();
                $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
            } else {
                $('#' + itemid + '-item').remove();
            }

        }
    };

    /*function check_all(box, obj, level) {
         var checked = document.getElementById(box).checked;
         var node = document.getElementById(obj);

        // var parent = node.parentNode; //place la variable parent sur le noeud parent de node
         var childList = node.childNodes; //récupère tous les enfants de node dans un tableau childNodesList
        // var child1 = node.firstChild; //récupère le premier enfant de node
        // var childx = node.lastChild; //récupère le dernier enfant de node
        // var frerePrec = node.previousSibling; //récupère le frère précédent de node (l'enfant précédent du parent de node)
        // var frereSuiv = node.nextSibling; //récupère le frère suivant

            console.log(childList);
            if(level == 1) {
             for (i=1 ; i < childList.length ; i++) {
                 childList[i].checked = checked;
                 var itemid = childList[i].id;
                 console.log(itemid);
                 if (itemid) {
                     itemid = itemid.split('_');
                     itemid = itemid[2];
                     if (checked) {
                         var text = $("label[for='emundus_elm_" + itemid + "']").text();
                         $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                     } else {
                         $('#' + itemid + '-item').remove();
                     }
                 }
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
                    var itemid = grp[j].id;
                    console.log(itemid);
                    if (itemid) {
                        itemid = itemid.split('_');
                        itemid = itemid[2];
                        if (checked) {
                            var text = $("label[for='emundus_elm_" + itemid + "']").text();
                            $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');

                        } else {
                            $('#' + itemid + '-item').remove();
                        }
                    }
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
                        var itemid = elm[k].id;
                        if (itemid) {
                            itemid = itemid.split('_');
                            itemid = itemid[2];
                            if (checked) {
                                var text = $("label[for='emundus_elm_" + itemid + "']").text();
                                $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');

                            } else {
                                $('#' + itemid + '-item').remove();
                            }
                        }
                    }
                }
             }
         }
    }*/

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