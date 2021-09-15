(function ($) {
    function previousMonthClick()
    {
        var itemId = $('[name^=itemId]').val();
        var month = $('.month_ajax').val();
        var year = $('.year_ajax').val();
        var categoryId = $('.category_id_ajax').val();
        if (month == 1) {
            month = 13;
            year--;
        }
        month--;

        reloadMiniCalendar(itemId, month, year, categoryId);
    }

    function nextMonthClick()
    {
        var itemId = $('[name^=itemId]').val();
        var month = $('.month_ajax').val();
        var year = $('.year_ajax').val();
        var categoryId = $('.category_id_ajax').val();
        if (month == 12) {
            month = 0;
            year++;
        }
        month++;

        reloadMiniCalendar(itemId, month, year, categoryId);
    }

    function previousYearClick()
    {
        var itemId = $('[name^=itemId]').val();
        var month = $('.month_ajax').val();
        var year = $('.year_ajax').val();
        var categoryId = $('.category_id_ajax').val();
        year--;

        reloadMiniCalendar(itemId, month, year, categoryId);
    }

    function nextYearClick()
    {
        var itemId = $('[name^=itemId]').val();
        var month = $('.month_ajax').val();
        var year = $('.year_ajax').val();
        var categoryId = $('.category_id_ajax').val();
        year++;

        reloadMiniCalendar(itemId, month, year, categoryId);
    }

    function reloadMiniCalendar(itemId, month, year, categoryId)
    {
        $.ajax({
            url: siteUrl + 'index.php?option=com_eventbooking&view=calendar&layout=mini&format=raw&month=' + month + '&year=' + year + '&id=' + categoryId + '&Itemid=' + itemId,
            dataType: 'html',
            success: function (html) {
                $('#calendar_result').html(html);
                $('.month_ajax').val(month);
                $('.year_ajax').val(year);

                $('#prev_month').bind('click', previousMonthClick);
                $('#next_month').bind('click', nextMonthClick);
                $('#prev_year').bind('click', previousYearClick);
                $('#next_year').bind('click', nextYearClick);

            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        })
    }

    $(document).ready(function () {
        $('#prev_month').bind('click', previousMonthClick);
        $('#next_month').bind('click', nextMonthClick);
        $('#prev_year').bind('click', previousYearClick);
        $('#next_year').bind('click', nextYearClick);
    });
})(Eb.jQuery);