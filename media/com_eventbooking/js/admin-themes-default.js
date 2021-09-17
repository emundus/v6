(function (document, $) {
    $(document).ready(function () {
        $('#btn-install-theme').on('click', function () {
            var form = document.adminForm;
            if (form.theme_package.value === "") {
                alert(Joomla.JText._('EB_CHOOSE_THEME'));
                return;
            }
            form.task.value = 'install';
            form.submit();
        });
    });
})(document, jQuery);