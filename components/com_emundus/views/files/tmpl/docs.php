<?php
$template_type = array(
	1 => JText::_('FILE'),
	2 => JText::_('PDF'),
	3 => JText::_('DOCX'),
	4 => JText::_('XLSX'),
);
?>
<input name="em-doc-fnums" type="hidden" value="<?= $this->fnums ?>"/>

<div id="em-documents" style="display:none;">
    <div>
        <label for="em-doc-tmpl-label"><?= JText::_('DOCUMENT_TYPE'); ?></label>
        <div id="exp-document">
            <select name="docs" id="em-doc-tmpl" class="chzn-select" multiple></select>
        </div>
    </div>

    <div class="em-mt-16" id="doc_can_see">
        <label for="em-doc-cansee"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_ACTIONS_CAN_BE_VIEWED'); ?></label>
        <label class="em-switch">
            <input type="checkbox" name="type" id="em-doc-cansee" checked="checked">
            <span class="em-slider em-round"></span>
        </label>
    </div>

    <hr id='breaking-line' style="border: 1.5px dashed #ccc"/>

    <div id="export-div" style="display: none">

        <div>
            <label for="em-export-mode"><?= JText::_('COM_EMUNDUS_EXPORT_MODE'); ?></label>
            <select name="mode" id="em-doc-export-mode" class="form-control">
                <option value="0"><?= JText::_('COM_EMUNDUS_EXPORT_BY_CANDIDAT'); ?></option>
                <option value="1"><?= JText::_('COM_EMUNDUS_EXPORT_BY_DOCUMENT'); ?></option>
                <option value="2" selected><?= JText::_('COM_EMUNDUS_EXPORT_BY_FILES'); ?></option>
            </select>
        </div>

        <div id="export-tooltips" class="em-mt-8"></div>
    </div>

    <div id="merge-div" class="em-mt-8" style="display: none">
        <div>
            <label for="em-combine-pdf"><?= JText::_('COM_EMUNDUS_PDF_MERGE'); ?></label>
            <label class="em-switch">
                <input type="checkbox" name="type" id="em-doc-pdf-merge">
                <span class="em-slider em-round"></span>
            </label>
        </div>

        <div id="merge-tooltips" class="em-mt-8"></div>
    </div>
</div>

<script type="text/javascript">
    addLoader();
    let fnums = document.querySelector('input[name="em-doc-fnums"]').value;

    if (fnums.split(',').length === 1) {
        document.getElementById('merge-div').remove();
        document.querySelector("#em-doc-export-mode option[value='0']").remove();
        document.querySelector("#em-doc-export-mode option[value='1']").remove();
    }

    const select = document.getElementById("em-doc-tmpl");
    $.ajax({
        type: 'post',
        url: 'index.php?option=com_emundus&controller=evaluation&task=getattachmentletters',
        dataType: 'JSON',
        data: {fnums: fnums},
        success: function (result) {
            if (result.status) {
                let attachment_letters = result.attachment_letters;
                $('#export-div').show();

                attachment_letters.forEach((letter, index) => {
                    const opt = document.createElement("option");
                    opt.value = letter.id;
                    opt.text = letter.value;
                    if (index == 0) {
                        opt.selected = true;
                    }

                    select.add(opt, select.options[1]);
                })
            } else {
                const opt = document.createElement("option");
                opt.value = -1;
                opt.disabled = true;
                opt.selected = true;
                opt.text = Joomla.JText._('NO_LETTER_FOUND');

                select.add(opt, select.options[1]);
                document.getElementById('doc_can_see').remove();
                document.getElementById('export-div').remove();
                const merge_div = document.getElementById('merge-div');
                if (merge_div != null) {
                    document.getElementById('merge-div').remove();
                }
                document.getElementsByClassName('swal2-confirm')[0].remove();
            }

            $('#em-doc-tmpl').chosen({width: '100%'});
            document.getElementById('em-documents').style.display = 'block';
        }
    })

    $('#em-doc-tmpl').on('change', function () {
        let tmpl = $(this).val();
        if (tmpl == null || tmpl.includes('-1')) {
            $('#em-generate').remove();
            $('#export-div').hide();
            $('#merge-div').hide();
        } else {
            $('#export-div').show();

            $('#em-doc-export-mode option[value="2"]').prop('selected', true);
            $('#em-doc-export-mode').trigger("change");
        }
    })
</script>

<style>
    .em-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .em-slider {
        /*position: absolute;*/
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #a2a1a1;
        -webkit-transition: .4s;
        transition: .4s;
        display: block;
    }

    .em-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        /*left: 20px;*/
        transition: .4s;
        background: #f9f5f5;
    }

    input:checked + .em-slider {
        background-color: #12db42;
    }

    input:focus + .em-slider {
        box-shadow: 0 0 1px #F4F4F6;
    }

    input:checked + .em-slider:before {
        -webkit-transform: translateX(32px);
        -ms-transform: translateX(32px);
        transform: translateX(32px);
        /*display: block;*/
    }

    .em-slider.em-round {
        border-radius: 34px;
        width: 60px;
        height: 30px;
        display: flex;
        align-items: center;
    }

    .em-slider.em-round:before {
        border-radius: 50%;
    }
</style>
