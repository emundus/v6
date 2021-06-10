<?php
$template_type = array(
	1 => JText::_('FILE'),
	2 => JText::_('PDF'),
	3 => JText::_('DOCX'),
	4 => JText::_('XLSX'),
);
?>
<input name="em-doc-fnums" type="hidden" value="<?= $this->fnums ?>"/>

<label for="em-doc-tmpl"><?= JText::_('DOCUMENT_TYPE'); ?></label>
<select name="docs" id="em-doc-tmpl" class="form-control form-control-lg">
<!--	--><?php //foreach ($this->docs as $doc) :?>
<!--		<option value = "--><?//= $doc['file_id'] ?><!--">--><?//= $doc['title'].' ('.$template_type[$doc['template_type']]?><!--)</option>-->
<!--	--><?php //endforeach;?>
</select>

<label for="em-doc-cansee"><?= JText::_('CAN_BE_VIEWED'); ?></label>
<select name="cansee" id="em-doc-cansee" class="form-control">
    <option value="0"><?= JText::_('JNO'); ?></option>
    <option value="1"><?= JText::_('JYES'); ?></option>
</select>

<script type="text/javascript">
    // get all letters from fnums
    var fnums = $('input:hidden[name="em-doc-fnums"]').val();

    $.ajax({
        type: 'post',
        url: 'index.php?option=com_emundus&controller=evaluation&task=getattachmentletters',
        dataType: 'JSON',
        data: { fnums: fnums },
        success: function(result) {
            let attachment_letters = result.attachment_letters;
            attachment_letters.forEach(letter => {
                $('#em-doc-tmpl').append('<option value="' + letter.id + '">' + letter.value + "   " + '(' + (letter.allowed_types).toUpperCase() + ')' + '</option>');
                $('#em-doc-tmpl').trigger("chosen:updated");
            })
        }
    })
</script>