(function (document, $) {
    $(document).ready(function () {
        $("#adminForm").validationEngine('attach', {
            onValidationComplete: function(form, status){
                if (status == true) {
                    form.on('submit', function(e) {
                        e.preventDefault();
                    });
                    return true;
                }
                return false;
            }
        });

        Joomla.submitbutton = function(pressbutton)
        {
            var form = document.adminForm;

            if (pressbutton == 'cancel' || pressbutton == 'close')
            {
                $("#adminForm").validationEngine('detach');
                Joomla.submitform(pressbutton, form);
            }
            else
            {
                //Validate the entered data before submitting
                Joomla.submitform(pressbutton, form);
            }
        };

    });
})(document, Eb.jQuery);