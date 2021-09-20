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

        $('#btn-cancel').on('click', function () {
            location.href = Joomla.getOption('eventUrl');
        });
    });
})(document, Eb.jQuery);