<?php
$template_type = array(
	1 => JText::_('FILE'),
	2 => JText::_('PDF'),
	3 => JText::_('DOCX'),
	4 => JText::_('XLSX'),
);
?>
<input name="em-doc-fnums" type="hidden" value="<?= $this->fnums ?>"/>
<br style="padding-left:30px" id="em-documents">
    <label for="em-doc-tmpl-label"><?= JText::_('DOCUMENT_TYPE'); ?></label>
    <div id="exp-document" >
        <select name="docs" id="em-doc-tmpl" class="chzn-select" multiple></select>
    </div>

    </br>

    <label for="em-doc-cansee"><?= JText::_('CAN_BE_VIEWED'); ?></label>
    </br>

    <label class="em-switch">
        <input type="checkbox" name="type" id="em-doc-cansee">
        <span class="em-slider em-round"></span>
    </label>

    </br>

    <hr id='breaking-line' style="border: 1.5px dashed #ccc"/>

    <div id="export-div" style="display: none">
        <label for="em-export-mode"><?= JText::_('COM_EMUNDUS_EXPORT_MODE'); ?></label>

        </br>

        <select name="mode" id="em-doc-export-mode" class="form-control">
            <option value="0"><?= JText::_('COM_EMUNDUS_EXPORT_BY_CANDIDAT'); ?></option>
            <option value="1"><?= JText::_('COM_EMUNDUS_EXPORT_BY_DOCUMENT'); ?></option>
            <option value="2" selected><?= JText::_('COM_EMUNDUS_EXPORT_BY_FILES'); ?></option>
        </select>

        </br>
        <div id="export-tooltips"></div>
    </div>

    </br>

    <div id="merge-div" style="display: none">
        <label for="em-combine-pdf"><?= JText::_('COM_EMUNDUS_PDF_MERGE'); ?></label>

        </br>

        <label class="em-switch">
            <input type="checkbox" name="type" id="em-doc-pdf-merge">
            <span class="em-slider em-round"></span>
        </label>

        </br>
        <div id="merge-tooltips"></div>
    </div>

    </div>

<script type="text/javascript">
    $('#em-doc-tmpl').chosen({width:'100%'});

    // get all letters from fnums
    var fnums = $('input:hidden[name="em-doc-fnums"]').val();

    if(fnums.split(',').length === 1) {
        $('#merge-div').remove();
        $("#em-doc-export-mode option[value='0']").remove();        /// remove "regrouper par candidat"
        $("#em-doc-export-mode option[value='1']").remove();        /// remove "regrouper par type de document"
    }

    $.ajax({
        type: 'post',
        url: 'index.php?option=com_emundus&controller=evaluation&task=getattachmentletters',
        dataType: 'JSON',
        data: { fnums: fnums },
        success: function(result) {
            if(result.status) {
                let attachment_letters = result.attachment_letters;
                $('#can-val').append('<button id="em-generate" style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('GENERATE_DOCUMENT')+'</button>');
                $('#export-div').show();
                attachment_letters.forEach(letter => {
                    $('#em-doc-tmpl').append('<option value="' + letter.id + '">' + letter.value + '</option>');           /// append data
                    $('#em-doc-tmpl').trigger("chosen:updated");
                })

                //// should select by default the first option
                $('#em-doc-tmpl option:first').prop('selected', true);
                $('#em-doc-tmpl').trigger("chosen:updated");

                /// uncomment this line to hide btn "Generer le(s) document(s)" if needed
                //$('#em-generate').remove();
            } else {
                $('#em-doc-tmpl').append('<option value="-1" selected disabled>' + Joomla.JText._('NO_LETTER_FOUND') + '</option>');
                $('#em-doc-tmpl').trigger("chosen:updated");
                $('#export-div').remove();
                $('#merge-div').remove();
                $('.modal-body').append('<span id="unavailable-msg" style="color: red">' + Joomla.JText._('COM_EMUNDUS_UNAVAILABLE_FEATURES') + '</span');
            }
        }
    })

    $('#em-doc-tmpl').on('change', function() {
        let tmpl = $(this).val();
        if(tmpl == null || tmpl.includes('-1')) {
            $('#em-generate').remove();
            $('#export-div').hide();
            $('#merge-div').hide();
            $('.modal-body').append('<span id="unavailable-msg" style="color: red">' + Joomla.JText._('COM_EMUNDUS_UNAVAILABLE_FEATURES') + '</span');
        } else {
            if($('#em-generate').length == 0){ $('#can-val').append('<button id="em-generate" style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('GENERATE_DOCUMENT')+'</button>');}

            $('#export-div').show();

            /// reset to default option of #export-div
            $('#em-doc-export-mode option[value="2"]').prop('selected', true);
            $('#em-doc-export-mode').trigger("change");

            $('#unavailable-msg').remove();
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
        left: 20px;
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