<?php
$template_type = array(
	1 => JText::_('FILE'),
	2 => JText::_('PDF'),
	3 => JText::_('DOCX'),
	4 => JText::_('XLSX'),
);
?>
<input name="em-doc-fnums" type="hidden" value="<?= $this->fnums ?>"/>

<!--<label for="em-doc-trainings">--><?//= JText::_('PROGRAM_NAME'); ?><!--</label>-->
<!--<select name="trainings" id="em-doc-trainings" class="form-control form-control-lg">-->
<!--	--><?php //foreach ($this->prgs as $code => $label) :?>
<!--		<option value = "--><?//= $code ?><!--">--><?//= $label ?><!--</option>-->
<!--	--><?php //endforeach;?>
<!--</select>-->
<!--<br/>-->
<label for="em-doc-tmpl"><?= JText::_('DOCUMENT_TYPE'); ?></label>
<select name="docs" id="em-doc-tmpl" class="form-control form-control-lg">
	<?php foreach ($this->docs as $doc) :?>
		<option value = "<?= $doc['file_id'] ?>"><?= $doc['title'].' ('.$template_type[$doc['template_type']]?>)</option>
	<?php endforeach;?>
</select>

<label for="em-doc-cansee"><?= JText::_('CAN_BE_VIEWED'); ?></label>
<select name="cansee" id="em-doc-cansee" class="form-control">
    <option value="0"><?= JText::_('JNO'); ?></option>
    <option value="1"><?= JText::_('JYES'); ?></option>
</select>

<script type="text/javascript">
    $(document).on('change', '#em-doc-trainings', function() {
	    var code = $('#em-doc-trainings').val();
	    $.ajax({
            type:'get',
            url:'index.php?option=com_emundus&controller=files&task=getdocs&code='+code,
            dataType:'json',
            success: function(result) {
                if (result.status) {
                    $('#em-doc-tmpl').empty();
                    var options = '';
                    for (var i = 0; i < result.options.length; i++) {
                        switch(result.options[i].template_type) {
                          case "1":
                            var template_type = "<?php echo JText::_('FILE'); ?>";
                            break;
                          case "2":
                            var template_type = "<?php echo JText::_('PDF'); ?>";
                            break;
                          case "3":
                            var template_type = "<?php echo JText::_('DOCX'); ?>";
                            break;
                          case "4":
                            var template_type = "<?php echo JText::_('XLSX'); ?>";
                            break;
                          default:
                            var template_type = "";
                        }
                        options += '<option value="'+result.options[i].file_id+'">'+result.options[i].title+' ('+template_type+')</option>';
                    }
                    $('#em-doc-tmpl').append(options);
                }
            },
			error: function (jqXHR) {
                console.log(jqXHR.responseText);
                if (jqXHR.status === 302) {
                    window.location.replace('/user');
                }
            }
        });
    })
</script>