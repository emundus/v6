(function (document) {
    Joomla.submitbutton = function(pressbutton)
    {
        var form = document.adminForm;

        if (pressbutton === 'cancel')
        {
            Joomla.submitform(pressbutton);
        }
        else if (form.discount_amount.value === "")
        {
            alert(Joomla.JText._('EB_ENTER_DISCOUNT_AMOUNT'));
            form.discount_amount.focus();
        }
        else
        {
            Joomla.submitform(pressbutton);
        }
    }
})(document);