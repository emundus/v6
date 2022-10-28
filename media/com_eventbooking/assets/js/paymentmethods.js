/****
 * Payment method class
 * @param name
 * @param creditCard
 * @param cardType
 * @param cardCvv
 * @param cardHolderName
 * @return
 */
function PaymentMethod(name, creditCard, cardType, cardCvv, cardHolderName)
{
    this.name = name;
    this.creditCard = creditCard;
    this.cardType = cardType;
    this.cardCvv = cardCvv;
    this.cardHolderName = cardHolderName;
}

/***
 * Get name of the payment method
 * @return string
 */
PaymentMethod.prototype.getName = function ()
{
    return this.name;
};
/***
 * This is creditcard payment method or not
 * @return int
 */
PaymentMethod.prototype.getCreditCard = function ()
{
    return this.creditCard;
};
/****
 * Show creditcard type or not
 * @return string
 */
PaymentMethod.prototype.getCardType = function ()
{
    return this.cardType;
};
/***
 * Check to see whether card cvv code is required
 * @return string
 */
PaymentMethod.prototype.getCardCvv = function ()
{
    return this.cardCvv;
};
/***
 * Check to see whether this payment method require entering card holder name
 * @return
 */
PaymentMethod.prototype.getCardHolderName = function ()
{
    return this.cardHolderName;
};

/***
 * Payment method class, hold all the payment methods
 */
function PaymentMethods()
{
    this.length = 0;
    this.methods = [];
}

/***
 * Add a payment method to array
 * @param paymentMethod
 * @return
 */
PaymentMethods.prototype.Add = function (paymentMethod)
{
    this.methods[this.length] = paymentMethod;
    this.length = this.length + 1;
};
/***
 * Find a payment method based on it's name
 * @param name
 * @return {@link PaymentMethod}
 */
PaymentMethods.prototype.Find = function (name)
{
    for (var i = 0; i < this.length; i++)
    {
        if (this.methods[i].name == name)
        {
            return this.methods[i];
        }
    }

    return null;
};

function removeSpace(obj)
{
    obj.value = obj.value.replace(/\s/g, '');
}

(function ($) {
    updatePaymentMethod = function()
    {
        var paymentMethod, method;

        if ($('input:radio[name^=payment_method]').length)
        {
            paymentMethod = $('input:radio[name^=payment_method]:checked').val();
        }
        else
        {
            paymentMethod = $('input[name^=payment_method]').val();
        }

        method = methods.Find(paymentMethod);

        if (!method)
        {
            return;
        }

        if (method.getCreditCard())
        {
            $('#tr_card_number').show();
            $('#tr_exp_date').show();
            $('#tr_cvv_code').show();

            if (method.getCardType())
            {
                $('#tr_card_type').show();
            }
            else
            {
                $('#tr_card_type').hide();
            }

            if (method.getCardHolderName())
            {
                $('#tr_card_holder_name').show();
            }
            else
            {
                $('#tr_card_holder_name').hide();
            }
        }
        else
        {
            $('#tr_card_number').hide();
            $('#tr_exp_date').hide();
            $('#tr_cvv_code').hide();
            $('#tr_card_type').hide();
            $('#tr_card_holder_name').hide();
        }

        if (paymentMethod == 'os_squareup')
        {
            $('#sq_field_zipcode').show();
        }
        else
        {
            $('#sq_field_zipcode').hide();
        }

        if (typeof stripe !== 'undefined')
        {
            if (paymentMethod.indexOf('os_stripe') == 0)
            {
                $('#stripe-card-form').show();
            }
            else
            {
                $('#stripe-card-form').hide();
            }
        }
    };

    calculateRegistrationFee= function()
    {
        updatePaymentMethod();

        if (document.adminForm.show_payment_fee.value == 1)
        {
            var paymentMethod,
                registrantId = $('#registrant_id').val(),
                $btnSubmit = $('#btn-submit'),
                $loadingAnimation = $('#ajax-loading-animation');

            $btnSubmit.attr('disabled', 'disabled');
            $loadingAnimation.show();

            if ($('input:radio[name^=payment_method]').length)
            {
                paymentMethod = $('input:radio[name^=payment_method]:checked').val();
            }
            else
            {
                paymentMethod = $('input[name^=payment_method]').val();
            }

            $.ajax({
                type: 'GET',
                url: siteUrl + 'index.php?option=com_eventbooking&task=register.calculate_registration_fee&payment_method=' + paymentMethod + '&registrant_id=' + registrantId,
                dataType: 'json',
                success: function (msg, textStatus, xhr)
                {
                    $btnSubmit.removeAttr('disabled');
                    $loadingAnimation.hide();

                    if ($('#amount').length)
                    {
                        $('#total_amount').val(msg.amount);
                    }

                    $('#payment_processing_fee').val(msg.payment_processing_fee);
                    $('#gross_amount').val(msg.gross_amount);
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    alert(textStatus);
                }
            });
        }
    };


    calculateRemainderFee = function()
    {
        updatePaymentMethod();

        if (document.adminForm.show_payment_fee.value == 1)
        {
            var paymentMethod,
                registrantId = $('#registrant_id').val(),
                $btnSubmit = $('#btn-submit'),
                $loadingAnimation = $('#ajax-loading-animation');

            $btnSubmit.attr('disabled', 'disabled');
            $loadingAnimation.show();

            if ($('input:radio[name^=payment_method]').length)
            {
                paymentMethod = $('input:radio[name^=payment_method]:checked').val();
            }
            else
            {
                paymentMethod = $('input[name^=payment_method]').val();
            }

            $.ajax({
                type: 'GET',
                url: siteUrl + 'index.php?option=com_eventbooking&task=register.calculate_remainder_fee&payment_method=' + paymentMethod + '&registrant_id=' + registrantId,
                dataType: 'json',
                success: function (msg, textStatus, xhr)
                {
                    $btnSubmit.removeAttr('disabled');
                    $loadingAnimation.hide();

                    if ($('#amount').length)
                    {
                        $('#total_amount').val(msg.amount);
                    }

                    $('#payment_processing_fee').val(msg.payment_processing_fee);
                    $('#gross_amount').val(msg.gross_amount);
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    alert(textStatus);
                }
            });
        }
    };

    changePaymentMethod = function(registrationType)
    {
        updatePaymentMethod();

        if (document.adminForm.show_payment_fee.value == 1)
        {
            // Re-calculate subscription fee in case there is payment fee associated with payment method
            if (registrationType == 'individual')
            {
                calculateIndividualRegistrationFee();
            }
            else if (registrationType == 'group')
            {
                calculateGroupRegistrationFee();
            }
            else
            {
                calculateCartRegistrationFee();
            }
        }
    };

    calculateIndividualRegistrationFee = function(changeTicketQuantity)
    {
        var $btnSubmit = $('#btn-submit'),
            $loadingAnimation = $('#ajax-loading-animation'),
            $totalAmount = $('#total_amount'),
            $amount = $('#amount');

        $btnSubmit.attr('disabled', 'disabled');
        $loadingAnimation.show();

        var euVatNumberField = Joomla.getOptions('euVatNumberField');

        var formFieldsSelector = 'select.ticket_type_quantity, #adminForm input[name="event_id"], #adminForm input[name="coupon_code"], #adminForm .payment-calculation input[type="text"], #adminForm .payment-calculation input[type="number"], #adminForm .payment-calculation input[type="checkbox"]:checked, #adminForm .payment-calculation input[type="radio"]:checked, #adminForm .payment-calculation select, #adminForm input.eb-hidden-field:hidden, #tickets_members_information :input, #adminForm select[name="country"], #adminForm select[name="state"]';

        if (euVatNumberField)
        {
            formFieldsSelector = formFieldsSelector + ', #adminForm input[name="' + euVatNumberField + '"]';
        }

        var ajaxUrl = Joomla.getOptions('calculateIndividualRegistrationFeeUrl');

        if (!ajaxUrl)
        {
            ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&task=register.calculate_individual_registration_fee' + langLinkForAjax;
        }

        if ($('input:radio[name^=payment_method]').length)
        {
            formFieldsSelector = formFieldsSelector + ', input:radio[name^=payment_method]:checked';
        }
        else
        {
            formFieldsSelector = formFieldsSelector + ', input[name^=payment_method]';
        }

		$.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: $(formFieldsSelector),
            dataType: 'json',
            success: function (msg, textStatus, xhr) {
                $btnSubmit.removeAttr('disabled');
                $loadingAnimation.hide();
                $totalAmount.val(msg.total_amount);
                $('#discount_amount').val(msg.discount_amount);
                $('#tax_amount').val(msg.tax_amount);
                $('#payment_processing_fee').val(msg.payment_processing_fee);
                $amount.val(msg.amount);
                $('#deposit_amount').val(msg.deposit_amount);

                if (($amount.length || $totalAmount.length) && msg.payment_amount == 0)
                {
                    $('.payment_information').css('display', 'none');
                }
                else
                {
                    $('.payment_information').css('display', '');
                    updatePaymentMethod();
                }

                if (msg.coupon_valid == 1)
                {
                    $('#coupon_validate_msg').hide();
                }
                else
                {
                    $btnSubmit.attr('disabled', 'disabled');
                    $('#coupon_validate_msg').show();
                }

                if ($('#payment_type').val() == 1)
                {
                    $('#deposit_amount_container').show();
                }
                else
                {
                    $('#deposit_amount_container').hide();
                }

                if (typeof changeTicketQuantity !== 'undefined')
                {
                    // the variable is defined
                    $('#tickets_members_information').html(msg.tickets_members);
                }

                if (euVatNumberField)
                {
                    if (msg.show_vat_number_field == 1)
                    {
                        $('#field_' + euVatNumberField).show();
                    }
                    else
                    {
                        $('#field_' + euVatNumberField).hide();
                    }

                    if (msg.vat_number_valid == 1)
                    {
                        $('#vatnumber_validate_msg').hide();
                    }
                    else
                    {
                        $('#vatnumber_validate_msg').show();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    };

    calculateGroupRegistrationFee = function()
    {
        var $btnSubmit = $('#btn-process-group-billing'),
            $loadingAnimation = $('#ajax-loading-animation'),
            $totalAmount = $('#total_amount'),
            $amount = $('#amount');

        $btnSubmit.attr('disabled', 'disabled');
        $loadingAnimation.show();

        var euVatNumberField = Joomla.getOptions('euVatNumberField');

        var formFieldsSelector = '#adminForm input[name="event_id"], #adminForm input[name="coupon_code"], #adminForm .payment-calculation input[type="text"], #adminForm .payment-calculation input[type="number"], #adminForm .payment-calculation input[type="checkbox"]:checked, #adminForm .payment-calculation input[type="radio"]:checked, #adminForm .payment-calculation select, #adminForm input.eb-hidden-field:hidden, #adminForm select[name="country"], #adminForm select[name="state"]';

        if (euVatNumberField)
        {
            formFieldsSelector = formFieldsSelector + ', #adminForm input[name="' + euVatNumberField + '"]';
        }

        if ($('input:radio[name^=payment_method]').length)
        {
            formFieldsSelector = formFieldsSelector + ', input:radio[name^=payment_method]:checked';
        }
        else
        {
            formFieldsSelector = formFieldsSelector + ', input[name^=payment_method]';
        }

        var ajaxUrl = Joomla.getOptions('calculateGroupRegistrationFeeUrl');

        if (!ajaxUrl)
        {
            ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&task=register.calculate_group_registration_fee'  + langLinkForAjax;
        }

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: $(formFieldsSelector),
            dataType: 'json',
            success: function (msg, textStatus, xhr) {
                $btnSubmit.removeAttr('disabled');
                $loadingAnimation.hide();
                $totalAmount.val(msg.total_amount);
                $('#discount_amount').val(msg.discount_amount);
                $('#tax_amount').val(msg.tax_amount);
                $('#payment_processing_fee').val(msg.payment_processing_fee);
                $amount.val(msg.amount);
                $('#deposit_amount').val(msg.deposit_amount);

                if (($amount.length || $totalAmount.length) && msg.payment_amount == 0)
                {
                    $('.payment_information').css('display', 'none');
                }
                else
                {
                    $('.payment_information').css('display', '');
                    updatePaymentMethod();
                }

                if (msg.coupon_valid == 1)
                {
                    $('#coupon_validate_msg').hide();
                }
                else
                {
                    $btnSubmit.attr('disabled', 'disabled');
                    $('#coupon_validate_msg').show();
                }

                if ($('#payment_type').val() == 1)
                {
                    $('#deposit_amount_container').show();
                }
                else
                {
                    $('#deposit_amount_container').hide();
                }

                if (euVatNumberField)
                {
                    if (msg.show_vat_number_field == 1)
                    {
                        $('#field_' + euVatNumberField).show();
                    }
                    else
                    {
                        $('#field_' + euVatNumberField).hide();
                    }

                    if (msg.vat_number_valid == 1)
                    {
                        $('#vatnumber_validate_msg').hide();
                    }
                    else
                    {
                        $('#vatnumber_validate_msg').show();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    };

    calculateCartRegistrationFee = function()
    {
        var $btnSubmit = $('#btn-submit'),
            $loadingAnimation = $('#ajax-loading-animation'),
            $totalAmount = $('#total_amount'),
            $amount = $('#amount');

        $btnSubmit.attr('disabled', 'disabled');
        $loadingAnimation.show();

        var euVatNumberField = Joomla.getOptions('euVatNumberField');

        var formFieldsSelector = '#adminForm input[name="coupon_code"], #adminForm .payment-calculation input[type="text"], #adminForm .payment-calculation input[type="number"], #adminForm .payment-calculation input[type="checkbox"]:checked, #adminForm .payment-calculation input[type="radio"]:checked, #adminForm .payment-calculation select, #adminForm input.eb-hidden-field:hidden, #adminForm select[name="country"], #adminForm select[name="state"]';

        if (euVatNumberField)
        {
            formFieldsSelector = formFieldsSelector + ', #adminForm input[name="' + euVatNumberField + '"]';
        }

        if ($('input:radio[name^=payment_method]').length)
        {
            formFieldsSelector = formFieldsSelector + ', input:radio[name^=payment_method]:checked';
        }
        else
        {
            formFieldsSelector = formFieldsSelector + ', input[name^=payment_method]';
        }

        var ajaxUrl = Joomla.getOptions('calculateCartRegistrationFeeUrl');

        if (!ajaxUrl)
        {
            ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&task=cart.calculate_cart_registration_fee' + langLinkForAjax;
        }

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: $(formFieldsSelector),
            dataType: 'json',
            success: function (msg, textStatus, xhr) {
                $btnSubmit.removeAttr('disabled');
                $loadingAnimation.hide();
                $totalAmount.val(msg.total_amount);
                $('#discount_amount').val(msg.discount_amount);
                $('#tax_amount').val(msg.tax_amount);
                $('#payment_processing_fee').val(msg.payment_processing_fee);
                $amount.val(msg.amount);
                $('#deposit_amount').val(msg.deposit_amount);

                if (($amount.length || $totalAmount.length) && msg.payment_amount == 0)
                {
                    $('.payment_information').css('display', 'none');
                }
                else
                {
                    $('.payment_information').css('display', '');
                    updatePaymentMethod();
                }

                if (msg.coupon_valid == 1)
                {
                    $('#coupon_validate_msg').hide();
                }
                else
                {
                    $btnSubmit.attr('disabled', 'disabled');
                    $('#coupon_validate_msg').show();
                }

                if (euVatNumberField)
                {
                    if (msg.show_vat_number_field == 1)
                    {
                        $('#field_' + euVatNumberField).show();
                    }
                    else
                    {
                        $('#field_' + euVatNumberField).hide();
                    }

                    if (msg.vat_number_valid == 1)
                    {
                        $('#vatnumber_validate_msg').hide();
                    }
                    else
                    {
                        $('#vatnumber_validate_msg').show();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    };

    showHideDependFields = function(fieldId, fieldName, fieldType, fieldSuffix)
    {

        var masterFieldsSelector,
            $loadingAnimation = $('#ajax-loading-animation');

        $loadingAnimation.show();

        if (fieldSuffix)
        {
            masterFieldsSelector = '.master-field-' + fieldSuffix + ' input[type=\'checkbox\']:checked,' + ' .master-field-' + fieldSuffix + ' input[type=\'radio\']:checked,' + ' .master-field-' + fieldSuffix + ' select';
        }
        else
        {
            masterFieldsSelector = '.master-field input[type=\'checkbox\']:checked, .master-field input[type=\'radio\']:checked, .master-field select';
        }

        $.ajax({
            type: 'POST',
            url: siteUrl + 'index.php?option=com_eventbooking&task=get_depend_fields_status&field_id=' + fieldId + '&field_suffix=' + fieldSuffix + langLinkForAjax,
            data: $(masterFieldsSelector),
            dataType: 'json',
            success: function (msg, textStatus, xhr) {
                $loadingAnimation.hide();
                var hideFields = [], showFields = [], i;

                if (msg.hide_fields.length > 0)
                {
                    hideFields = msg.hide_fields.split(',');
                }

                if (msg.show_fields.length > 0)
                {
                    showFields = msg.show_fields.split(',');
                }

                for (i = 0; i < hideFields.length; i++)
                {
                    $('#' + hideFields[i]).hide();
                }

                for (i= 0; i < showFields.length; i++)
                {
                    $('#' + showFields[i]).show();
                }

                if (typeof eb_current_page === 'undefined')
                {

                }
                else
                {
                    if (eb_current_page == 'default')
                    {
                        calculateIndividualRegistrationFee();
                    }
                    else if (eb_current_page == 'group_billing')
                    {
                        calculateGroupRegistrationFee();
                    }
                    else if (eb_current_page == 'cart')
                    {
                        calculateCartRegistrationFee();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    };

    buildStateField = function(stateFieldId, countryFieldId, defaultState)
    {
        var $country = $('#' + countryFieldId),
            $state = $('#' + stateFieldId);

        if ($state.length && $state.is('select'))
        {
            var countryName = '';

            //set state
            if ($country.length)
            {
                countryName = $country.val();
            }

            $.ajax({
                type: 'GET',
                url: siteUrl + 'index.php?option=com_eventbooking&task=get_states&country_name=' + countryName + '&field_name=' + stateFieldId + '&state_name=' + defaultState + langLinkForAjax,
                success: function (data) {
                    if ($('#field_' + stateFieldId + ' .controls').length) {
                        $('#field_' + stateFieldId + ' .controls').html(data);
                    }
                    else if ($('#field_' + stateFieldId + ' .col-sm-9').length) {
                        $('#field_' + stateFieldId + ' .col-sm-9').html(data);
                    }
                    else if ($('#field_' + stateFieldId + ' .col-md9').length) {
                        $('#field_' + stateFieldId + ' .col-md-9').html(data);
                    }
                    else {
                        $('#field_' + stateFieldId + ' .uk-form-controls').html(data);
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });

            //Bind onchange event to the country
            if ($country.length)
            {
                $country.change(function ()
                {
                    $.ajax({
                        type: 'GET',
                        url: siteUrl + 'index.php?option=com_eventbooking&task=get_states&country_name=' + $(this).val() + '&field_name=' + stateFieldId + '&state_name=' + defaultState + langLinkForAjax,
                        success: function (data) {
                            if ($('#field_' + stateFieldId + ' .controls').length) {
                                $('#field_' + stateFieldId + ' .controls').html(data);
                            }
                            else if ($('#field_' + stateFieldId + ' .col-sm-9').length) {
                                $('#field_' + stateFieldId + ' .col-sm-9').html(data);
                            }
                            else if ($('#field_' + stateFieldId + ' .col-md-9').length) {
                                $('#field_' + stateFieldId + ' .col-md-9').html(data);
                            }
                            else
                            {
                                $('#field_' + stateFieldId + ' .uk-form-controls').html(data);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert(textStatus);
                        }
                    });

                });
            }
        }
    };

    buildStateFields = function(stateFieldId, countryFieldId, defaultState)
    {
        var $country = $('#' + countryFieldId), $state =  $('#' + stateFieldId);

        if ($country.length && $state.length && $state.is('select'))
        {
            //Bind onchange event to the country
            $country.change(function ()
            {
                $.ajax({
                    type: 'GET',
                    url: siteUrl + 'index.php?option=com_eventbooking&task=get_states&country_name=' + $(this).val() + '&field_name=' + stateFieldId + '&state_name=' + defaultState + langLinkForAjax,
                    success: function (data) {
                        if ($('#field_' + stateFieldId + ' .controls').length)
                        {
                            $('#field_' + stateFieldId + ' .controls').html(data);
                        }
                        else if ($('#field_' + stateFieldId + ' .col-sm-9').length)
                        {
                            $('#field_' + stateFieldId + ' .col-sm-9').html(data);
                        }
                        else if ($('#field_' + stateFieldId + ' .col-md-9').length)
                        {
                            $('#field_' + stateFieldId + ' .col-md-9').html(data);
                        }
                        else
                        {
                            $('#field_' + stateFieldId + ' .uk-form-controls').html(data);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(textStatus);
                    }
                });

            });
        }
    };

    showDepositAmount = function(paymentTypeSelect)
    {
        if ($(paymentTypeSelect).val() == 1)
        {
            $('#deposit_amount_container').show();
        }
        else
        {
            $('#deposit_amount_container').hide();
        }
    };

    stripeResponseHandler = function (status, response)
    {
        var $form = $('#adminForm');

        if (response.error)
        {
            // Show the errors on the form
            //$form.find('.payment-errors').text(response.error.message);
            alert(response.error.message);
            $form.find('#btn-submit').prop('disabled', false);
            $form.find('#btn-process-group-billing').prop('disabled', false);
        }
        else
        {
            // token contains id, last4, and card type
            var token = response.id;
            // Empty card data since we now have token
            $('#x_card_num').val('');
            $('#x_card_code').val('');
            $('#card_holder_name').val('');
            // Insert the token into the form so it gets submitted to the server
            $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            // and re-submit
            $form.get(0).submit();
        }
    };

    stripeTokenHandler = function(token)
    {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('adminForm');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);

        // Submit the form
        form.submit();
    };

    EBInitReCaptcha2 = function()
    {
        var item = document.getElementById('eb_dynamic_recaptcha_1'),
            option_keys = ['sitekey', 'theme', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'],
            options = {},
            option_key_fq
        ;

        if (item.dataset) {
            options = item.dataset;
        } else {
            for (var j = 0; j < option_keys.length; j++) {
                option_key_fq = ('data-' + option_keys[j]);
                if (item.hasAttribute(option_key_fq)) {
                    options[option_keys[j]] = item.getAttribute(option_key_fq);
                }
            }
        }

        // Set the widget id of the recaptcha item
        item.setAttribute(
            'data-recaptcha-widget-id',
            grecaptcha.render(item, options)
        );
    };

    EBInitReCaptchaInvisible = function()
    {
        var item = document.getElementById('eb_dynamic_recaptcha_1'),
            option_keys = ['sitekey', 'badge', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'],
            options = {},
            option_key_fq
        ;

        if (item.dataset) {
            options = item.dataset;
        } else {
            for (var j = 0; j < option_keys.length; j++) {
                option_key_fq = ('data-' + option_keys[j]);
                if (item.hasAttribute(option_key_fq)) {
                    options[option_keys[j]] = item.getAttribute(option_key_fq);
                }
            }
        }
        // Set the widget id of the recaptcha item
        item.setAttribute(
            'data-recaptcha-widget-id',
            grecaptcha.render(item, options)
        );
        // Execute the invisible reCAPTCHA
        grecaptcha.execute(item.getAttribute('data-recaptcha-widget-id'));
    };

})(Eb.jQuery);