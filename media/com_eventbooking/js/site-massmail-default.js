(function (document) {
    Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;

        if (pressbutton === 'cancel') {
            Joomla.submitform(pressbutton);
        } else {
            //Need to check something here
            if (form.event_id.value == 0) {
                Joomla.JText._('EB_CHOOSE_EVENT');
                form.event_id.focus();
                return;
            }

            if (form.subject.value === '') {
                Joomla.JText._('EB_ENTER_MASSMAIL_SUBJECT');
                form.subject.focus();
                return;
            }

            Joomla.submitform(pressbutton);
        }
    }
})(document);