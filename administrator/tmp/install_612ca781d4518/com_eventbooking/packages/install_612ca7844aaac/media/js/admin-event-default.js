(function (document, $) {
    Joomla.submitbutton = function (pressbutton)
    {
        var form = document.adminForm;

        if (pressbutton === 'cancel')
        {
            Joomla.submitform(pressbutton);
        }
        else
        {
            if (form.title.value === '')
            {
                alert(Joomla.JText._('EB_PLEASE_ENTER_TITLE'));
                form.title.focus();
                return;
            }

            if (form.main_category_id.value == 0)
            {
                alert(Joomla.JText._('EB_CHOOSE_CATEGORY'));
                form.category_id.focus();
                return;
            }

            if (form.event_date.value === '')
            {
                alert(Joomla.JText._('EB_ENTER_EVENT_DATE'));
                form.event_date.focus();
                return;
            }



            if ($('#activate_recurring_events').val() === '1')
            {
                var recurringType = $('#recurring_type').val();

                if (recurringType > 0)
                {
                    if (form.recurring_frequency.value == '')
                    {
                        alert(Joomla.JText._('EB_ENTER_RECURRING_INTERVAL'));
                        form.recurring_frequency.focus();
                        return;
                    }

                    // Weekly recurring, at least one weekday needs to be selected
                    if (recurringType == 2)
                    {
                        //Check whether any days in the week
                        var checked = false;

                        for (var i = 0; i < form['weekdays[]'].length; i++)
                        {
                            if (form['weekdays[]'][i].checked)
                            {
                                checked = true;
                            }
                        }

                        if (!checked)
                        {
                            alert(Joomla.JText._('EB_CHOOSE_ONE_DAY'));
                            form['weekdays[]'][0].focus();
                            return;
                        }
                    }

                    if (recurringType == 3)
                    {
                        if (form.monthdays.value === '')
                        {
                            alert(Joomla.JText._('EB_ENTER_DAY_IN_MONTH'));
                            form.monthdays.focus();

                            return;
                        }
                    }

                    if (form.recurring_end_date.value === '' && form.recurring_occurrencies.value === '')
                    {
                        alert(Joomla.JText._('EB_ENTER_RECURRING_ENDING_SETTINGS'));
                        form.recurring_end_date.focus();

                        return;
                    }
                }

            }

            Joomla.submitform(pressbutton);
        }
    };

    addRow = function() {
        var table = document.getElementById('price_list');
        var newRowIndex = table.rows.length - 1;
        var row = table.insertRow(newRowIndex);
        var registrantNumber = row.insertCell(0);
        var price = row.insertCell(1);
        registrantNumber.innerHTML = '<input type="text" class="input-mini form-control" name="registrant_number[]" size="10" />';
        price.innerHTML = '<input type="text" class="input-mini form-control" name="price[]" size="10" />';
    };

    removeRow = function()
    {
        var table = document.getElementById('price_list');
        var deletedRowIndex = table.rows.length - 2;

        if (deletedRowIndex >= 1)
        {
            table.deleteRow(deletedRowIndex);
        }
        else
        {
            alert(Joomla.JText._('EB_NO_ROW_TO_DELETE'));
        }
    }
})(document, jQuery);