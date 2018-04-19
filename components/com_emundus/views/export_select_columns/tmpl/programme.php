<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
//JHTML::stylesheet('media/com_emundus/css/emundus.css' );

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
         padding: 0px;
         margin-left: -6px;
         border-radius: 0px;
         background: inherit;
         border-bottom: 0px;
         margin-bottom: 0;
         line-height: 0;
    }
    .panel-info legend {
        color: #000000;
        font-size: 16px;
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
    .label-element {
        color: #000000!important;
        font-weight: normal!important;
    }
    .panel-info>.panel-heading label {
        color: #000000!important;
        font-weight: bold!important;
        text-shadow: none ;
        font-size: 14px;
    }
</style>

	<?php

    if (count($this->elements)>0) {

        echo '<div id="emundus_elements">';
        $tbl_tmp='';
		$grp_tmp='';

		foreach($this->elements as $t){
			if ($tbl_tmp == '') {
				echo '<div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
						<div class="panel-heading"><legend>';
                $label = explode("-", $t->table_label);
                $label = $label[1];
				echo ' <label for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.'</label></legend></div><div class="panel-body">
					  <div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
						<div class="panel-heading"><legend>';

				echo ' <label for="emundus_checkall_grp_'.$t->group_id.'"> '.$t->group_label.'</label></legend></div><div class="panel-body">';
			} elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
					echo '</div></div></div></div>
						<div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
							<div class="panel-heading"><legend>';
                $label = explode("-", $t->table_label);
                $label = $label[1];
					echo ' <label for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.'</label></legend></div><div class="panel-body">
						<div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
							<div class="panel-heading"><legend>';

					echo ' <label for="emundus_checkall_grp_'.$t->group_id.'"> '.$t->group_label.'</label></legend></div><div class="panel-body">';
			} else {
				if ($t->group_id != $grp_tmp && $grp_tmp != '') {
						echo '</div></div><div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
								<div class="panel-heading"><legend>';

						echo ' <label for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</legend></div><div class="panel-body">';
				}
			}

			echo ' <label class="label-element" for="emundus_elm_'.$t->id.'">${'.$t->id.'} '.$t->element_label.'</label><br> ';

			$tbl_tmp=$t->table_id;
			$grp_tmp=$t->group_id;
		}
		echo '</div></div></div></div>';
		echo '</div>';
    }
    else {
        echo JText::_('NO_FORM_DEFINED');
    }
?>
