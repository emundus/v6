(function (document) {
    Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;

        if (pressbutton === 'cancel') {
            Joomla.submitform(pressbutton);
        } else if (form.name.value === '') {
            alert(Joomla.JText._('EB_ENTER_SPEAKER_NAME'));
            form.name.focus();
        } else {
            Joomla.submitform(pressbutton);
        }
    };
})(document);