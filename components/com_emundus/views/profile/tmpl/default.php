<?php
JHTML::_('behavior.modal');
JHTML::stylesheet( 'media/com_emundus/css/emundus.css' );
defined('_JEXEC') or die('Restricted access');
$Itemid = JFactory::getApplication()->input->get('Itemid', null, 'GET', 'none',0);
?>

<div id="profilebasics" class="em-container-profiles">
    <h2><?php echo $this->profile->label; ?> <a href="index.php?option=com_fabrik&view=list&listid=67"><?php echo '['.JText::_('COM_EMUNDUS_PROFILES_BACK_TO_PROFILE').']';?></a></h2>
    <table class="table-striped em-container-profiles-table">
        <tr>
            <th> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_PUBLISHED_FOR_APP'); ?> </th>
            <td> <?php echo $this->profile->published>0?JText::_('Yes'):JText::_('No'); ?> </td>
        </tr>
        <tr>
            <th> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION'); ?> </th>
            <td> <?php echo $this->profile->description; ?> </td>
        </tr>
    </table>
    <form action="<?= JUri::base(); ?>/index.php?option=com_emundus&task=updateprofile&rowid=<?php echo JFactory::getApplication()->input->get('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0); ?>&Itemid=<?php echo $Itemid; ?>" method="POST" class="em-form-documents">
        <input type="hidden" name="pid" value="<?php echo $this->profile->id; ?>" />
        <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />

        <h2><?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS'); ?>  <a href="index.php?option=com_fabrik&view=list&listid=36"><?php echo '['.JText::_('COM_EMUNDUS_ATTACHMENTS_SETUP_ATTACHMENTS').']';?></a></h2>
        <div class="table-responsive">
            <table id="attachmentlist" class="table-striped em-form-documents-table">
                <thead>
                    <tr height="30px">
                        <th><?php echo JText::_('COM_EMUNDUS_FORM_TITLE'); ?></th>
                        <th align="center"> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_USED'); ?> </th>
                        <th align="center"> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_DISPLAYED'); ?> </th>
                        <th align="center"> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_REQUIRED'); ?> </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->attachments as $attachment) :?>
                        <tr>
                            <td>
                                <a href="index.php?option=com_fabrik&view=form&formid=34&rowid=<?php echo $attachment->id; ?>&listid=36">[<?php echo JText::_('COM_EMUNDUS_ACTIONS_EDIT'); ?>]</a>
                                <?php echo $attachment->value; ?> (<?php echo $attachment->allowed_types; ?>)
                                <input type="hidden" name="aid[]" value="<?php echo $attachment->id; ?>" />
                            </td>
                            <td align="center"><input type="checkbox" name="as[]" value="<?php echo $attachment->id; ?>" id="selecteda<?php echo $attachment->id; ?>" <?php if ($attachment->selected > 0) echo 'checked'; ?> onClick="javascript:toggleA('<?php echo $attachment->id; ?>')"/>
                            </td>
                            <td align="center"><input type="checkbox" name="ad[]" value="<?php echo $attachment->id; ?>" id="displayeda<?php echo $attachment->id; ?>" <?php if ($attachment->displayed > 0) echo 'checked'; ?>/>
                            </td>
                            <td align="center"><input type="checkbox" name="ar[]" value="<?php echo $attachment->id; ?>" id="requireda<?php echo $attachment->id; ?>" <?php if ($attachment->mandatory > 0) echo 'checked'; ?> onClick="javascript:toggleD('<?php echo $attachment->id; ?>')"/>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" align="center"><input type="submit" class="btn btn-warning" value="<?php echo JText::_('COM_EMUNDUS_ACCESS_UPDATE'); ?>" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
function toggleA(baliseId) {
	if (!document.getElementById('selecteda'+baliseId).checked) {
		document.getElementById('displayeda'+baliseId).disabled = true;
		document.getElementById('requireda'+baliseId).disabled = true;
	} else {
		document.getElementById('displayeda'+baliseId).disabled = false;
		document.getElementById('requireda'+baliseId).disabled = false;
	}
}

function toggleF(baliseId) {
	if (!document.getElementById('selectedf'+baliseId).checked)
		document.getElementById('orderf'+baliseId).disabled = true;
	else
		document.getElementById('orderf'+baliseId).disabled = false;
}

function toggleD(baliseId) {
	if (document.getElementById('requireda'+baliseId).checked)
		document.getElementById('displayeda'+baliseId).checked = true;
}
<?php foreach($this->attachments as $attachment) :?>
    if (!document.getElementById('selecteda<?php echo $attachment->id; ?>').checked) {
      document.getElementById('displayeda<?php echo $attachment->id; ?>').disabled = true;
      document.getElementById('requireda<?php echo $attachment->id; ?>').disabled = true;
    }
<?php endforeach; ?>

<?php foreach ($this->forms as $form) :?>
    if (!document.getElementById('selectedf<?php echo $form->id; ?>').checked)
      document.getElementById('orderf<?php echo $form->id; ?>').disabled = true;
<?php endforeach; ?>
</script>
