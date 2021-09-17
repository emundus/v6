(function (document, $) {
    $(document).ready(function(){
        $('#filter_state').addClass('input-medium').removeClass('inputbox');
    });

    Joomla.submitbutton = function(pressbutton)
    {
        if (pressbutton === 'cancel_event')
        {
            if (!confirm('Are you sure what to cancel this event'))
            {
                return;
            }
        }

        Joomla.submitform( pressbutton );

        if (pressbutton == 'export')
        {
            var form = document.adminForm;
            form.task.value = '';
        }
    }
})(document, jQuery);