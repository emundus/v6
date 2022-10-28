(function (document, $) {
    Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;

        if (pressbutton === 'cancel') {
            Joomla.submitform(pressbutton);
            return;
        } else {
            if (form.name.value === "") {
                alert(Joomla.JText._('EB_ENTER_FIELD_NAME'));
                form.name.focus();
                return;
            }

            if (form.title.value === "") {
                alert(Joomla.JText._('EB_ENTER_FIELD_TITLE'));
                form.title.focus();
                return;
            }

            Joomla.submitform(pressbutton);
        }
    };

    showHideEventsSelection = function (assignment) {
        if (assignment.value == 0) {
            $('#events_selection_container').hide();
        } else {
            $('#events_selection_container').show();
        }
    };

    function setFieldValidationRules() {

        if ($('input[name="name"]').val() === 'email') {
            //Hard code the validation rule for email
            $('input[name="validation_rules"]').val('validate[required,custom[email],ajax[ajaxEmailCall]]');
            return;
        }

        var rules = [];
        var validationString = '';
        var validateRules = Joomla.getOptions('validateRules');
        var validateType = parseInt($('#datatype_validation').val());
        var required = $("input[name='required']:checked").val();

        if (required === '1') {
            rules.push('required');
        }

        if (validateRules[validateType].length) {
            rules.push(validateRules[validateType]);
        }

        if (rules.length > 0) {
            validationString = 'validate[' + rules.join(',') + ']';
        }

        $('input[name="validation_rules"]').val(validationString);
    }

    $(document).ready(function () {
        $('#name').change(function () {
            var name = $(this).val();
            name = name.replace('eb_', '');
            name = name.replace(/[^a-zA-Z0-9_]*/ig, '');
            $(this).val(name);
        });

        $("input[name='required']").click(function () {
            setFieldValidationRules();
        });

        $("#datatype_validation").change(function () {
            setFieldValidationRules();
        });

        $('#depend_on_field_id').change(function () {
            var fieldId = $('#depend_on_field_id').val();
            var siteUrl = Joomla.getOptions('siteUrl');
            if (fieldId > 0) {
                $.ajax({
                    type: 'POST',
                    url: siteUrl + '/index.php?option=com_eventbooking&view=field&format=raw&field_id=' + fieldId,
                    dataType: 'html',
                    success: function (msg, textStatus, xhr) {
                        $('#options_container').html(msg);
                        $('#depend_on_options_container').show();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(textStatus);
                    }
                });
            } else {
                $('#options_container').html('');
                $('#depend_on_options_container').hide();
            }
        });
    });

})(document, jQuery);