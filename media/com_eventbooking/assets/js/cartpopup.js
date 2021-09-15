(function ($) {
    closeCartPopup = function () {
        $.colorbox.close();
    };

    checkOut = function (checkoutUrl) {
        document.location.href = checkoutUrl;
    };

    updateCart = function (Itemid) {
        var ret = checkQuantity();
        if (ret) {
            var eventId = $("input[name='event_id[]']").map(function () {
                return $(this).val();
            }).get();

            var quantity = $("input[name='quantity[]']").map(function () {
                return $(this).val();
            }).get();

            if (typeof EBBaseAjaxUrl === 'undefined') {
                var requestUrl = 'index.php?option=com_eventbooking&task=cart.update_cart&Itemid=' + Itemid + '&redirect=0&event_id=' + eventId + '&quantity=' + quantity;
            } else {
                var requestUrl = EBBaseAjaxUrl + '&task=cart.update_cart&Itemid=' + Itemid + '&redirect=0&event_id=' + eventId + '&quantity=' + quantity;
            }

            $.ajax({
                type: 'POST',
                url: requestUrl,
                dataType: 'html',
                beforeSend: function () {
                    $('#add_more_item').before('<span class="wait"><i class="fa fa-2x fa-refresh fa-spin"></i></span>');
                },
                success: function (html) {
                    $('#cboxLoadedContent').html(html);
                    $('.wait').remove();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    };

    removeCart = function (id, Itemid) {
        if (typeof EBBaseAjaxUrl === 'undefined') {
            var requestUrl = 'index.php?option=com_eventbooking&task=cart.remove_cart&id=' + id + '&Itemid=' + Itemid + '&redirect=0';
        } else {
            var requestUrl = EBBaseAjaxUrl + '&task=cart.remove_cart&id=' + id + '&Itemid=' + Itemid + '&redirect=0';
        }

        $.ajax({
            type: 'POST',
            url: requestUrl,
            dataType: 'html',
            beforeSend: function () {
                $('#add_more_item').before('<span class="wait"><i class="fa fa-2x fa-refresh fa-spin"></i></span>');
            },
            success: function (html) {
                $('#cboxLoadedContent').html(html);
                jQuery.colorbox.resize();
                $('.wait').remove();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    };

    checkQuantity = function () {
        var eventId, quantity, enteredQuantity, index;

        var eventIds = $("input[name='event_id[]']").map(function () {
            return $(this).val();
        });

        var quantities = $("input[name='quantity[]']").map(function () {
            return $(this).val();
        });

        for (var i = 0; i < eventIds.length; i++) {
            eventId = eventIds[i];
            enteredQuantity = quantities[i];
            index = findIndex(eventId, arrEventIds);

            if (index != -1) {
                availableQuantity = arrQuantities[index];

                if ((availableQuantity != -1) && (enteredQuantity > availableQuantity)) {
                    alert(EB_INVALID_QUANTITY + availableQuantity);
                    $('input[name="quantity[]"]')[i].focus();

                    return false;
                }
            }
        }

        return true;
    }

    findIndex = function (eventId, eventIds) {
        for (var i = 0; i < eventIds.length; i++) {
            if (eventIds[i] == eventId) {
                return i;
            }
        }

        return -1;
    }
})(Eb.jQuery);