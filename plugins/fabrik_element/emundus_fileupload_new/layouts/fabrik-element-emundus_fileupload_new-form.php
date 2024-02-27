<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;


$attachId = $d->attributes['attachmentId'];


$db    = JFactory::getDBO();
$query = $db->getQuery(true);

$query->select($db->quoteName('allowed_types'))
	->from($db->quoteName('#__emundus_setup_attachments'))
	->where($db->quoteName('id') . ' = ' . $db->quote($attachId));

$allowed_types = '';
try {
	$db->setQuery($query);
	$allowed_types = $db->loadResult();
}
catch (Exception $e) {
	JLog::add('Error in fileupload plugin at query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
}

$allowed_types = str_replace(';', ',', $allowed_types);
?>

<input type="hidden" id="<?= $d->attributes['id']; ?>" name="<?= $d->attributes['name']; ?>"
       value="<?= $d->attributes['value']; ?>" />

<div id="div_<?= $d->attributes['id']; ?>" class="fabrik_element___emundus_file_upload_parent">
    <span class="fabrik_element___file_upload_formats">
        <?= JText::_('PLG_ELEMENT_FILEUPLOAD_ALLOWED_TYPES') . ' : ' . $allowed_types ?>. <?= JText::_('PLG_ELEMENT_FIELD_MAXSIZE_TIP') . $d->attributes['max_size_txt']; ?>
    </span>
    <div class="btn-upload em-pointer">
        <p class="em-flex-row"><?php echo JText::_('PLG_ELEMENT_FILEUPLOAD_DROP') ?><u
                    class="em-ml-4"><?php echo JText::_('PLG_ELEMENT_FILEUPLOAD_DROP_CLICK') ?></u><span
                    class="material-icons-outlined em-ml-12">cloud_upload</span></p>
    </div>
    <input type="file" id="file_<?= $d->attributes['id']; ?>" name="file_<?= $d->attributes['name']; ?>"
           multiple <?php foreach ($d->attributes as $key => $value) {
		echo $key . '="' . $value . '" ';
	} ?>/>
</div>
