(function (document, $) {
    $(document).ready(function () {
        $('#btn-install-plugin').on('click', function () {
            var form = document.adminForm;
            if (form.plugin_package.value === "") {
                alert(Joomla.JText._('EB_CHOOSE_PLUGIN'));
                return;
            }
            form.task.value = 'install';
            form.submit();
        });
    });
})(document, jQuery);