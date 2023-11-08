<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 29/01/15
 * Time: 11:32
 */ ?>

<input name="em-doc-fnums" type="hidden" value="<?php echo $this->fnums ?>"/>
<select name="trainings" id="em-doc-trainings">
	<?php foreach ($this->prgs as $code => $label): ?>
        <option value="<?php echo $code ?>"><?php echo $label ?></option>
	<?php endforeach; ?>
</select>
<br/>
<select name="docs" id="em-doc-tmpl">
	<?php foreach ($this->docs as $doc): ?>
        <option value="<?php echo $doc['file_id'] ?>"><?php echo $doc['title'] ?></option>
	<?php endforeach; ?>
</select>

<script type="text/javascript">
    $(document).on('change', '#em-doc-trainings', function () {
        var code = $('#em-doc-trainings').val();
        $.ajax(
            {
                type: 'get',
                url: '/index.php?option=com_emundus&controller=files&task=getdocs&code=' + code,
                dataType: 'json',
                success: function (result) {
                    if (result.status) {
                        $('#em-doc-tmpl').empty();
                        var options = '';
                        for (var i = 0; i < result.options.length; i++) {
                            options += '<option value="' + result.options[i].file_id + '">' + result.options[i].title + '</option>';
                        }
                        $('#em-doc-tmpl').append(options);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                    if (jqXHR.status === 302) {
                        window.location.replace('/user');
                    }
                }
            });
    })
</script>