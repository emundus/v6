(function (document) {
    Joomla.submitbutton = function(pressbutton)
    {
        if (pressbutton === 'cancel')
        {
            Joomla.submitform( pressbutton );
        }
        else
        {
             var form = document.adminForm;

             if (form.name.value === '')
             {
                 alert(Joomla.JText._('EB_ENTER_CATEGORY_TITLE'));

                 return;
             }

             Joomla.submitform( pressbutton );
        }
    }
})(document);