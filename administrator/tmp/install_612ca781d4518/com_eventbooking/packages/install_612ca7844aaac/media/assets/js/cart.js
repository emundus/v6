(function ($) {
    checkOut = function () {
        var form = document.adminForm;
        ret = checkQuantity();
        if (ret) {
            form.task.value = 'checkout';
            form.submit();
        }
    };

    continueShopping = function (continueUrl) {
        document.location.href = continueUrl;
    };

    updateCart = function () {
        var form = document.adminForm;
        var ret = checkQuantity();
        if (ret) {
            form.task.value = 'cart.update_cart';
            form.submit();
        }
    };

    removeItem= function(id) {
        if (confirm(EB_REMOVE_CONFIRM)) {
            var form = document.adminForm;
            form.id.value = id;
            form.task.value = 'cart.remove_cart';
            form.submit();
        }
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