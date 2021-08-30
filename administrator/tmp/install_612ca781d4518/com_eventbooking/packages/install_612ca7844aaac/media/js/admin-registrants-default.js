(function (document, $) {
    Joomla.submitbutton = function(pressbutton)
    {
        if (pressbutton === 'export' && document.registrantsExportForm)
        {
            var variables = ['filter_search', 'filter_from_date', 'filter_to_date', 'filter_event_id', 'filter_published', 'filter_checked_in'];
            var variable;

            for (i = 0 ; i < variables.length; i++)
            {
                variable = variables[i];

                $('#export_' + variable).val($('#' + variable).val());
            }

            var cids = [];

            $('input[name="cid[]"]:checked').each(function() {
                cids.push(this.value);
            });

            $('#export_cid').val(cids.join(','));

            Joomla.submitform(pressbutton, document.getElementById('registrantsExportForm'));

            return;
        }
        else if (pressbutton === 'add')
        {
            var form = document.adminForm;

            if (form.filter_event_id.value == 0)
            {
                alert(Joomla.JText._('EB_SELECT_EVENT_TO_ADD_REGISTRANT'));
                form.filter_event_id.focus();

                return;
            }
        }
        else if(pressbutton === 'registrant.batch_mail')
        {
            var form = document.adminForm;

            if (form.subject.value === '')
            {
                alert('Please enter email subject');
                form.subject.focus();

                return;
            }
        }

        Joomla.submitform( pressbutton );
    }
})(document, jQuery);