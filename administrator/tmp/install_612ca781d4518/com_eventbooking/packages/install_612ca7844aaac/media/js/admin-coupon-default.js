(function (document, $) {
    Joomla.submitbutton = function (pressbutton)
    {
        var form = document.adminForm;

        if (pressbutton === 'cancel')
        {
            Joomla.submitform(pressbutton);
        }
        else if (form.code.value === "")
        {
            alert(Joomla.JText._('EB_ENTER_COUPON'));
            form.code.focus();
        }
        else if (form.discount.value == "")
        {
            alert(Joomla.JText._('EN_ENTER_DISCOUNT_AMOUNT'));
            form.discount.focus();
        }
        else
        {
            Joomla.submitform(pressbutton);
        }
    };

    showHideEventsSelection = function(assignment)
    {
        if (assignment.value == 0)
        {
            $('#events_selection_container').hide();
        }
        else
        {
            $('#events_selection_container').show();
        }
    };
})(document, jQuery);