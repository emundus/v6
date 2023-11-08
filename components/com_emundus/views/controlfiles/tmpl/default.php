<?php
JHTML::_('behavior.modal');
JHTML::stylesheet('media/com_emundus/css/emundus.css');
defined('_JEXEC') or die('Restricted access');
$path = JPATH_BASE . DS . 'images' . DS . 'emundus' . DS . 'files' . DS;


?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" class="em-controlfiles-form"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="controlfiles"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>

<fieldset class="em-controlfiles-form-contentServ">
    <legend>
        <img src="<?php JURI::base(); ?>media/com_emundus/images/icones/viewmag_22x22.png"
             alt="<?php JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_SERVER'); ?>"/>
		<?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_SERVER'); ?>
    </legend>
	<?php
	if (count($this->files) > 0 && isset($this->files) && is_array($this->files)) {
		?>
        <table id="userlist" class="em-controlfiles-form-contentServ-userlist" width="100%">
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="checkall" onClick="javascript:check_all()"/>
					<?php echo JHTML::_('grid.sort', JText::_('#'), 'user_id', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_NAME'); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('FILE'), 'filename', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="10">
					<?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_TOTAL_FILES_IN_DB') . ' = ' . $this->total; ?>
                </td>
            </tr>
            </tfoot>
            <tbody>

			<?php
			$j = 0;
			foreach ($this->files as $f) {
			if (!file_exists($path . $f->user_id . DS . $f->filename)) {
			$user = JFactory::getUser($f->user_id);
			?>

            <tr class="row<?php echo $j++ % 2; ?>">
                <td width="80">
					<?php echo $j; ?>
                    <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]"
                           value="<?php echo $user->id; ?>"/>
					<?php echo '#' . $f->user_id; ?>
                </td>
                <td>
					<?php echo $user->name; ?>
                </td>
                <td>
					<?php echo $path . $f->user_id . DS . $f->filename; ?>
                </td>
				<?php }
				} ?>
            </tbody>
        </table>
		<?php
	}
	?>
</fieldset>


<fieldset class="em-controlfiles-form-contentBdd">
    <legend>
        <img src="<?php JURI::base(); ?>media/com_emundus/images/icones/viewmag_22x22.png"
             alt="<?php JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_DB'); ?>"/>
		<?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_DB'); ?>
    </legend>
	<?php
	if (count($this->listFiles) > 0 && isset($this->listFiles) && is_array($this->listFiles)) {
		?>
        <table id="userlist" class="em-controlfiles-form-contentBdd-userlist" width="100%">
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="checkall" onClick="javascript:check_all()"/>
					<?php echo JHTML::_('grid.sort', JText::_('#'), 'user_id', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_NAME'); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('FILE'), 'filename', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="10">
					<?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_TOTAL_FILES_IN_SERVER') . ' = ' . count($this->listFiles); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>

			<?php
			$j = 0;
			foreach ($this->listFiles as $f) {
			$db    = JFactory::getDBO();
			$query = 'SELECT count(id)
			FROM #__emundus_uploads
			WHERE filename like "' . $f["file"] . '"';
			$db->setQuery($query);
			$is_in_db = $db->loadResult();

			$user = JFactory::getUser($f["id"]);
			//if( $is_in_db == 0 || $user->name == "") {
			if ($is_in_db == 0 || $user->name == "") {
			?>
            <tr class="row<?php echo $j++ % 2; ?>">
                <td width="80">
                    <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]"
                           value="<?php echo $user->id; ?>"/>
					<?php
					echo '#' . $f["id"];
					?>
                </td>
                <td>
					<?php

					echo $user->name;
					?>
                </td>
                <td>
					<?php
					//echo $f->filename;
					echo $path . $f["id"] . DS . $f["file"];
					?>
                </td>
				<?php }
				} ?>
            </tbody>
        </table>
		<?php
	}
	?>
</fieldset>
</form>

</form>
<script>
    function check_all() {
        var checked = document.getElementById('checkall').checked;
		<?php foreach ($this->files as $file) { ?>
        document.getElementById('cb<?php echo $file->id; ?>').checked = checked;
		<?php } ?>
    }

    function tableOrdering(order, dir, task) {
        var form = document.adminForm;
        //var form = document.getElementById('adminForm')[0];
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    }

    function OnSubmitForm() {
        switch (document.pressed) {
            case 'delete_on_db':
                if (confirm("<?php echo JText::_("CONFIRM_DELETING"); ?>")) {
                    document.adminForm.action = "index.php?option=com_emundus&controller=groups&task=deleteOnDB";
                } else
                    return false;
                break;
            case 'delete_on_server':
                if (confirm("<?php echo JText::_("CONFIRM_DELETING"); ?>")) {
                    document.adminForm.action = "index.php?option=com_emundus&controller=groups&task=deleteOnServer";
                } else
                    return false;
                break;
        }
        return true;
    }
</script>
