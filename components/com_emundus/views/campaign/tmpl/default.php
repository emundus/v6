<?php
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', 'media/com_emundus/css/' );

$tmpl = JFactory::getApplication()->input->get('tmpl', null, 'GET', 'none',0);
$itemid = JFactory::getApplication()->input->get('Itemid', null, 'GET', 'none',0);
$limitstart = JFactory::getApplication()->input->get('limitstart', null, 'GET', 'none',0);
$ls = JFactory::getApplication()->input->get('limitstart', null, 'GET', 'none',0);
$filter_order = JFactory::getApplication()->input->get('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JFactory::getApplication()->input->get('filter_order_Dir', null, 'GET', 'none',0);
$v = JFactory::getApplication()->input->get('view', null, 'GET', 'none',0);

$user = JFactory::getUser();

?>
<div id="em_campaigns" class="em_campaigns">
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" class="em_campaigns_form" />
	 <input type="hidden" name="option" value="com_emundus"/>
	 <input type="hidden" name="view" value="<?php echo $v; ?>"/>
	 <input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
	 <input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	 <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	 <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
<?php
if(isset($this->active_campaigns)&&!empty($this->active_campaigns)){ ?>
 <table id="userlist" width="100%" class="em_campaigns_form_table">
  <thead>
  <tr>
	<td align="center" colspan="18"><?php echo $this->pagination->getResultsCounter(); ?></td>
  </tr>
  <tr>
<?php
 foreach ($this->active_campaigns[0] as $key=>$value){
	if($key == 'id'){
		echo '<th align="center" style="font-size:9px;"><input type="checkbox" id="checkall" class="emundusraw" onClick="check_all(\'ud\',this)" /></th>';
	}else
		echo '<th>'.JHTML::_('grid.sort', JText::_(strtoupper($key)), $key, $this->lists['filter_order_Dir'], $this->lists['filter_order']).'</th>';
	}
?>
  </tr>
  </thead>
  <tbody><?php
  $i=1; $j=0;
  foreach($this->active_campaigns as $ac) { ?>
		<tr class="row<?php echo $j++%2; ?>">
		<?php
	foreach ($ac as $key=>$value){
	if($key=='id'){ ?>
		 <td> <?php
		 echo $i+$limitstart; $i++;
		 echo '<input id="cb'.$value.'" type="checkbox" name="ud[]" value="'.$value.'"/>';
		 ?>
		 </td><?php
	}elseif($key == 'profile'){
		 echo '<td>';
		 echo $this->profile[$evalu['user_id']];
		 echo '</td>';
	}else
		echo '<td>'.$value.'</td>';
	}
?>
	</tr><?php
  } ?>
  </tbody>
  <tfoot>
  <tr>
	<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
  </tr>
  </tfoot>
 </table>
<?php } else { ?>
<h2><?php echo JText::_('COM_EMUNDUS_NO_RESULT'); ?></h2>
<?php
@$j++;
}
?>

<script>
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->active_campaigns as $ac) { ?>
  document.getElementById('cb<?php echo $ac->id; ?>').checked = checked;
<?php } ?>
}

function is_check() {
	var cpt = 0;
	<?php foreach ($this->active_campaigns as $ac) { ?>
  		if(document.getElementById('cb<?php echo $ac->id; ?>').checked == true) cpt++;
	<?php } ?>
	if(cpt > 0) return true;
	else return false;
}

function tableOrdering( order, dir, task ) {
  var form = document.adminForm;
  //var form = document.getElementById('adminForm')[0];
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}

function OnSubmitForm() {
	if(typeof document.pressed !== "undefined") {
		var button_name=document.pressed.split("|");
		// alert(button_name[0]);
		switch(button_name[0]) {
			case 'validate':
				document.adminForm.action ="index.php?option=com_emundus&controller=campaign&task=setcampaign&uid="+button_name[1]+"&limitstart=<?php echo $ls; ?>";
			break;
			case 'search_button':
				document.adminForm.action ="index.php?option=com_emundus&view=campaign";
			break;
			case 'clear_button':
				document.adminForm.action ="index.php?option=com_emundus&controller=campaign&task=clear";
			break;
			default: return false;
		}
		return true;
	}
}
<?php JHTML::script( 'emundus.js', 'media/com_emundus/js/' ); ?>
</script>
</div>
