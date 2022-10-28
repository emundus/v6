(function (document, $) {
    Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;

        if (pressbutton === 'new_item') {
            newLanguageItem();
        } else if (pressbutton === 'apply' || pressbutton === 'save') {
            var values = [];
            var newKeys = [];
            var newValues = [];

            $('.eb-language-item-value').each(function () {
                values.push($(this).val())
            });

            $('.eb-new-key').each(function () {
                newKeys.push($(this).val())
            });

            $('.eb-new-value').each(function () {
                newValues.push($(this).val())
            });

            $('#translate_values').val(values.join('@@@'));

            $('#translate_new_keys').val(newKeys.join(','));
            $('#translate_new_values').val(newValues.join(','));

            $('#translate_filter_search').val($('#filter_search').val());
            $('#translate_filter_language').val($('#filter_language').val());
            $('#translate_filter_item').val($('#filter_item').val());

            Joomla.submitform(pressbutton, document.getElementById('translateForm'));

        } else {
            Joomla.submitform(pressbutton);
        }
    };

    function newLanguageItem() {
        table = document.getElementById('lang_table');
        row = table.insertRow(1);
        cell0 = row.insertCell(0);
        cell0.innerHTML = '<input type="text" name="extra_keys[]" class="eb-new-key" size="50" />';
        cell1 = row.insertCell(1);
        cell2 = row.insertCell(2);
        cell2.innerHTML = '<input type="text" name="extra_values[]" class="eb-new-value" size="100" />';
    }

    function searchTable() {
        var tableBody = $('#eb-translation-table');
        var searchTerm = $("#filter_search").val().toLowerCase();

        $.each(tableBody.find("tr"), function () {
            var text = $(this)
                .text();

            var inputValue = $(this).find('input[type="text"]').val();

            if (inputValue.length) {
                text = text + '' + inputValue;
            }
            text = text.replace(/(\r\n|\n|\r)/gm, "").toLowerCase();

            if (text.indexOf(searchTerm) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }

    $(document).ready(function () {
        if ($("#filter_search").val()) {
            searchTable();
        }

        $("#filter_search").on("change", searchTable);

        $('#eb-clear-button').on('click', function () {
            $("#filter_search").val('');
            searchTable();
        })
    });
})(document, jQuery);