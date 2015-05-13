<?php 
JHTML::_('behavior.modal'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
defined('_JEXEC') or die('Restricted access'); 
$user = JFactory::getUser();
$Itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

?>
<div id="profilebasics">
<h1><?php echo $this->profile->label; ?> <a href="index.php?option=com_fabrik&view=list&listid=67&Itemid=3&lang=en"><?php echo '['.JText::_('BACK_TO_PROFILE').']';?></a></h1>
<table>
	<tr><th><?php echo JText::_('PUBLISHED_FOR_APP'); ?></th><td><?php echo $this->profile->published>0?JText::_('Yes'):JText::_('No'); ?></td></tr>
	<tr><th><?php echo JText::_('DESCRIPTION'); ?></th><td><?php echo $this->profile->description; ?></td></tr>
	<!--<tr><th><?php //echo JText::_('SCHOOLYEAR'); ?></th><td><?php //echo $this->profile->schoolyear; ?></td></tr>
	<tr><th><?php //echo JText::_('CANDIDATURE_PERIOD'); ?></th><td><?php //echo JText::printf('CANDIDATURE_PERIOD_TEXT',$this->profile->candidature_start,$this->profile->candidature_end); ?></td></tr>-->
</table>
<form action="index.php?option=com_emundus&task=updateprofile&rowid=<?php echo JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0); ?>&Itemid=<?php echo $Itemid; ?>" method="POST"/>
<input type="hidden" name="pid" value="<?php echo $this->profile->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
<!--<input type="hidden" name="Itemid" value="<?php// echo $Itemid; ?>" />-->
<table id="attachmentlist">
	<tr><th colspan="4"><h1><?php echo JText::_('ATTACHMENTS'); ?>  <a href="index.php?option=com_fabrik&view=list&listid=36&calculations=0&resetfilters=0&Itemid=4&lang=en"><?php echo '['.JText::_('SETUP_ATTACHMENTS').']';?></a></h1></th></tr>
	<tr height="30px">
		<th><?php echo JText::_('TITLE'); ?></th>
		<th align="center"><?php echo JText::_('USED'); ?></th>
		<th align="center"><?php echo JText::_('DISPLAYED'); ?></th>
		<th align="center"><?php echo JText::_('REQUIRED'); ?></th>
		<th align="center"><?php echo JText::_('BANK_NEEDED'); ?></th>
	</tr>
<?php foreach ($this->attachments as $attachment) { ?>
	<tr>
		<td><?php echo $attachment->value; ?><input type="hidden" name="aid[]" value="<?php echo $attachment->id; ?>" /></td>
		<td align="center"><input type="checkbox" name="as[]" value="<?php echo $attachment->id; ?>" id="selecteda<?php echo $attachment->id; ?>" <?php if($attachment->selected>0) echo 'checked'; ?> onClick="javascript:toggleA('<?php echo $attachment->id; ?>')"/></td>
		<td align="center"><input type="checkbox" name="ad[]" value="<?php echo $attachment->id; ?>" id="displayeda<?php echo $attachment->id; ?>" <?php if($attachment->displayed>0) echo 'checked'; ?>/></td>
		<td align="center"><input type="checkbox" name="ar[]" value="<?php echo $attachment->id; ?>" id="requireda<?php echo $attachment->id; ?>" <?php if($attachment->mandatory>0) echo 'checked'; ?> onClick="javascript:toggleD('<?php echo $attachment->id; ?>')"/></td>
		<td align="center"><input type="checkbox" name="ab[]" value="<?php echo $attachment->id; ?>" id="bank_neededa<?php echo $attachment->id; ?>" <?php if($attachment->bank_needed>0) echo 'checked'; ?>/></td>
	</tr>
<?php } ?>
	<tr><td colspan="5" align="center"><input type="submit" value="Update" /></td></tr>
</table>
</form>
</div>
<script>
function toggleA(baliseId)
{
	if(!document.getElementById('selecteda'+baliseId).checked) {
		document.getElementById('displayeda'+baliseId).disabled = true;
		document.getElementById('requireda'+baliseId).disabled = true;
		document.getElementById('bank_neededa'+baliseId).disabled = true;
	} else {
		document.getElementById('displayeda'+baliseId).disabled = false;
		document.getElementById('requireda'+baliseId).disabled = false;
		document.getElementById('bank_neededa'+baliseId).disabled = false;
	}
}
function toggleF(baliseId)
{
	if(!document.getElementById('selectedf'+baliseId).checked) {
		document.getElementById('orderf'+baliseId).disabled = true;
	} else {
		document.getElementById('orderf'+baliseId).disabled = false;
	}
}
function toggleD(baliseId)
{
	if(document.getElementById('requireda'+baliseId).checked) {
		document.getElementById('displayeda'+baliseId).checked = true;
	}
}
<?php foreach($this->attachments as $attachment) { ?>
if(!document.getElementById('selecteda<?php echo $attachment->id; ?>').checked) {
  document.getElementById('displayeda<?php echo $attachment->id; ?>').disabled = true;
  document.getElementById('requireda<?php echo $attachment->id; ?>').disabled = true;
  document.getElementById('bank_neededa<?php echo $attachment->id; ?>').disabled = true;
}
<?php } 
foreach ($this->forms as $form) { ?>
if(!document.getElementById('selectedf<?php echo $form->id; ?>').checked) {
  document.getElementById('orderf<?php echo $form->id; ?>').disabled = true;
}
<?php } ?>
</script>