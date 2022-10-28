(function (document) {
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton === 'cancel') {
            Joomla.submitform( pressbutton );
        } else {
            if (form.event_id.value == 0) {
                alert(Joomla.JText._('EB_CHOOSE_EVENT'));
                form.event_id.focus() ;
                return ;
            }
            Joomla.submitform( pressbutton );
        }
    };
})(document);