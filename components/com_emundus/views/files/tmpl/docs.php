<?php
$template_type = array(
	1 => JText::_('FILE'),
	2 => JText::_('PDF'),
	3 => JText::_('DOCX'),
	4 => JText::_('XLSX'),
);
?>
<input name="em-doc-fnums" type="hidden" value="<?= $this->fnums ?>"/>
<div style="padding-left:30px" id="em-documents">
    <label for="em-doc-tmpl-label"><?= JText::_('DOCUMENT_TYPE'); ?></label>
    <div id="exp-document" >
        <select name="docs" id="em-doc-tmpl" class="chzn-select" multiple></select>
    </div>

    </br>

    <label for="em-doc-cansee"><?= JText::_('CAN_BE_VIEWED'); ?></label>
    <select name="cansee" id="em-doc-cansee" class="form-control">
        <option value="0"><?= JText::_('JNO'); ?></option>
        <option value="1"><?= JText::_('JYES'); ?></option>
    </select>

    </br>

    <label for="em-export-mode"><?= JText::_('COM_EMUNDUS_EXPORT_MODE'); ?></label>
    <select name="mode" id="em-doc-export-mode" class="form-control">
        <option value="0"><?= JText::_('COM_EMUNDUS_EXPORT_BY_FNUM'); ?></option>
        <option value="1"><?= JText::_('COM_EMUNDUS_EXPORT_BY_DOCUMENT'); ?></option>
        <option value="2"><?= JText::_('COM_EMUNDUS_EXPORT_BY_FILES'); ?></option>
    </select>

    </br>

    <label for="em-combine-pdf"><?= JText::_('COM_EMUNDUS_PDF_MERGE'); ?></label>
    <select name="merge" id="em-doc-pdf-merge" class="form-control">
        <option value="0"><?= JText::_('JNO'); ?></option>
        <option value="1"><?= JText::_('JYES'); ?></option>
    </select>

</div>
<script type="text/javascript">
    $('#em-doc-tmpl').chosen({width:'100%'});
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
                $('#em-doc-tmpl').append('<option value="' + letter.id + '">' + letter.value + '</option>');
                $('#em-doc-tmpl').trigger("chosen:updated");
            })
        }
    })
</script>