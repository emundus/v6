<?php 
defined('_JEXEC') or die('Restricted access'); 

JHTML::_('behavior.modal'); 
JHTML::_('behavior.tooltip'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
JHTML::stylesheet( 'light2.css', JURI::Base().'templates/rt_afterburner/css/' );
JHTML::stylesheet( 'general.css', JURI::Base().'templates/system/css/' );
JHTML::stylesheet( 'system.css', JURI::Base().'templates/system/css/' );

$current_user = JFactory::getUser();

$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);		
if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') 
	$user = JFactory::getUser($student_id);
else
	$user = JFactory::getUser();

	$chemin = EMUNDUS_PATH_REL;
?>

<table width="100%" id="legend">
  <tr>
    <td class="need_missing"><?php echo JText::_('MISSING_DOC'); ?></td>
    <td class="need_ok"><?php echo JText::_('SENT_DOC'); ?></td>
    <!-- <td class="need_missing_fac"><?php echo JText::_('MISSING_DOC_FAC'); ?></td> -->
  </tr>
</table>
<div id="attachment_list">
  <h2><?php echo JText::_('ATTACHMENTS'); ?></h2>
  <h4><?php echo JText::_('UPLOAD_MAX_FILESIZE') . ' = ' . ini_get("upload_max_filesize") . ' '. JText::_('octets'); ?></h4>
  <br />
  <form id="checklistForm" name="checklistForm" action="index.php?option=com_emundus&task=upload&layout=attachments&sid=<?php echo $user->id; ?>"  method="post" enctype="multipart/form-data">
    <input name="sendAttachment" type="submit" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ATTACHMENT'); ?>"/>
    <?php 
foreach($this->attachments as $attachment) { 
	if ($attachment->nb==0) {
		$class= $attachment->mandatory?'need_missing':'need_missing_fac';
	} else {
		$class= 'need_ok';
	}
?>
    <fieldset>
      <legend class="<?php echo $class; ?>"><?php echo $attachment->value; ?> <a href="javascript:toggleVisu('<?php echo $attachment->id; ?>')">[+/-]</a></legend>
      <p class="description"><?php echo $attachment->description; ?></p>
      <table width="100%" border="0" id="<?php echo $attachment->id; ?>">
        <thead>
          <tr>
            <th><?php echo JText::_('SHORT_DESC'); ?></th>
            <th><?php echo JText::_('FILE'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php 	
		  if ($attachment->nb>0) 
			foreach($attachment->liste as $item) { ?>
          <tr>
            <td>• <?php echo empty($item->description)?JText::_('NO_DESC'):$item->description; ?></td>
            <td><?php if($item->can_be_viewed==1 || $current_user->profile <= 4) {?>
              <a href="<?php echo $chemin.$user->id.'/'.$item->filename; ?>" target="_blank"><img src="media/com_emundus/images/icones/viewmag_16x16.png" alt="show" style="vertical-align:middle"/><?php echo JText::_('VIEW'); ?></a>
              <?php } else { echo JText::_('CANT_VIEW'); } ?>
              &nbsp;-&nbsp;
              <?php if($item->can_be_deleted==1 || $current_user->profile <= 4) {?>
              <a href="?option=com_emundus&task=delete&layout=attachments&sid=<?php echo $user->id; ?>&aid=<?php echo $item->id; ?>"><img src="media/com_emundus/images/icones/trashcan_full.png"  style="vertical-align:middle" alt="delete"/><?php echo JText::_('DELETE'); ?></a>
              <?php } else { echo JText::_('CANT_DELETE'); } ?></td>
          </tr>
          <?php 	} ?>
          <?php 	if ($attachment->nb<$attachment->nbmax || $attachment->id != 10) { ?>
          <tr>
            <td><input type="hidden" name="attachment[]" value="<?php echo $attachment->id; ?>"/>
              <input type="hidden" name="label[]" value="<?php echo $attachment->lbl; ?>"/>
              <input type="text" name="description[]"/></td>
            <td><input type="file" name="nom[]"/></td>
          </tr>
          <tr>
            <td colspan="2">
            <label for="can_be_viewed_<?php echo $attachment->id; ?>"><?php echo JText::_('CAN_BE_VIEWED'); ?></label>
              <input name="can_be_viewed_<?php echo $attachment->id; ?>" value="1" id="can_be_viewed_<?php echo $attachment->id; ?>" type="radio"><?php echo JText::_('YES'); ?>
              <input name="can_be_viewed_<?php echo $attachment->id; ?>" value="0" id="cannot_be_viewed_<?php echo $attachment->id; ?>" checked="checked" type="radio"><?php echo JText::_('NO'); ?> 
              <br />
            <label for="can_be_deleted_<?php echo $attachment->id; ?>"><?php echo JText::_('CAN_BE_DELETED'); ?></label>
              <input name="can_be_deleted_<?php echo $attachment->id; ?>" value="1" id="can_be_deleted_<?php echo $attachment->id; ?>" type="radio"><?php echo JText::_('YES'); ?>
              <input name="can_be_deleted_<?php echo $attachment->id; ?>" value="0" id="cannot_be_deleted_<?php echo $attachment->id; ?>" checked="checked" type="radio"><?php echo JText::_('NO'); ?>
            </td>
          </tr>
        </tbody>
        <?php 	} else { ?>
        <tr>
          <td colspan="2"><p class="description"><?php echo JText::_('NO_MORE').' '.$attachment->value.'<br />'.JText::_('MAX_ALLOWED').' '.$attachment->nbmax; ?></p></td>
        </tr>
        </tbody>
        
        <?php 	} ?>
      </table>
    </fieldset>
    <?php
} 
?>
    <input name="sendAttachment" type="submit" onclick="document.pressed=this.name" value="<?php echo JText::_('SEND_ATTACHMENT'); ?>"/>
  </form>
</div>
<script>
function toggleVisu(baliseId)
  {
  if (document.getElementById && document.getElementById(baliseId) != null)
    {
	if (document.getElementById(baliseId).style.visibility=='visible')
		{
		document.getElementById(baliseId).style.visibility='hidden';
		document.getElementById(baliseId).style.display='none';
		}
	else
		{
		document.getElementById(baliseId).style.visibility='visible';
		document.getElementById(baliseId).style.display='block';
		}
    }
  }
<?php foreach($this->attachments as $attachment) { ?>
  document.getElementById('<?php echo $attachment->id; ?>').style.visibility='<?php echo ($attachment->mandatory && $attachment->nb==0)?'visible':'hidden'; ?>';
  document.getElementById('<?php echo $attachment->id; ?>').style.display='<?php echo ($attachment->mandatory && $attachment->nb==0)?'block':'none'; ?>';
<?php } ?>

function OnSubmitForm() {
	var btn = document.getElementsByName(document.pressed);
	btn[0].disabled="disabled";
	btn[0].value="<?php echo JText::_('SENDING_ATTACHMENT'); ?>";
	btn[1].disabled="disabled";
	btn[1].value="<?php echo JText::_('SENDING_ATTACHMENT'); ?>";
	//alert(btn+' '+btn.disabled+' : '+btn.value);
	switch(document.pressed) {
		case 'sendAttachment': 
			document.checklistForm.action ="index.php?option=com_emundus&task=upload&layout=attachments&sid=<?php echo $user->id; ?>";
		break;
		default: return false;
	}
	return true;
}
</script>
