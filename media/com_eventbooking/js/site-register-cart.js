(function (document, $) {
    $(document).ready(function () {
        $("#adminForm").validationEngine('attach', {
            onValidationComplete: function(form, status){
                if (status == true) {
                    form.on('submit', function(e) {
                        e.preventDefault();
                    });

                    form.find('#btn-submit').prop('disabled', true);

                    var paymentMethod;

                    if($('input:radio[name^=payment_method]').length)
                    {
                        paymentMethod = $('input:radio[name^=payment_method]:checked').val();
                    }
                    else
                    {
                        paymentMethod = $('input[name^=payment_method]').val();
                    }

                    if (paymentMethod === undefined)
                    {
                        return true;
                    }

                    // Stripe payment method
                    if (paymentMethod.indexOf('os_stripe') == 0)
                    {
                        // Old Stripe method
                        if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible"))
                        {
                            Stripe.card.createToken({
                                number: $('#x_card_num').val(),
                                cvc: $('#x_card_code').val(),
                                exp_month: $('select[name^=exp_month]').val(),
                                exp_year: $('select[name^=exp_year]').val(),
                                name: $('#card_holder_name').val()
                            }, stripeResponseHandler);

                            return false;
                        }

                        // Stripe card element
                        if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible"))
                        {
                            stripe.createToken(card).then(function(result) {
                                if (result.error) {
                                    // Inform the customer that there was an error.
                                    //var errorElement = document.getElementById('card-errors');
                                    //errorElement.textContent = result.error.message;
                                    alert(result.error.message);
                                    $('#btn-submit').prop('disabled', false);
                                } else {
                                    // Send the token to your server.
                                    stripeTokenHandler(result.token);
                                }
                            });

                            return false;
                        }
                    }

                    if (paymentMethod === 'os_squareup' && $('#tr_card_number').is(':visible'))
                    {
                        sqPaymentForm.requestCardNonce();

                        return false;
                    }

                    return true;
                }
                return false;
            }
        });

        buildStateFields('state', 'country', Joomla.getOptions('selectedState'));

        var numberMembers = Joomla.getOptions('numberMembers');

        for (var i = 1; i <= numberMembers; i++)
        {
            buildStateFields('state_' + i, 'country_' + i, '');
        }

        if (typeof stripe !== 'undefined' && $('#stripe-card-element').length > 0)
        {
            var style = {
                base: {
                    // Add your base input styles here. For example:
                    fontSize: '16px',
                    color: "#32325d",
                }
            };

            // Create an instance of the card Element.
            var card = elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>.
            card.mount('#stripe-card-element');
        }

        if ($('#email').val())
        {
            $('#email').validationEngine('validate');
        }

        var euVatNumberField = Joomla.getOptions('euVatNumberField');

        if (euVatNumberField) {
            var euVatNumberFieldInput = $('#' + euVatNumberField);
            euVatNumberFieldInput.after('<span class="invalid" id="vatnumber_validate_msg" style="display: none;">' + Joomla.JText._('EB_INVALID_VATNUMBER') + '</span></div>');

            $("#country").change(function () {
                calculateCartRegistrationFee();
            });

            euVatNumberFieldInput.change(function () {
                calculateCartRegistrationFee();
            });

            var showVatNumberField = Joomla.getOptions('showVatNumberField');

            if (showVatNumberField)
            {
                $('#field_' + euVatNumberField).show();
            }
            else
            {
                $('#field_' + euVatNumberField).hide();
            }
        }

        if (Joomla.getOptions('hidePaymentInformation'))
        {
            $('.payment_information').css('display', 'none');
        }
    });

    updateCart = function () {
        location.href = Joomla.getOptions('cartUrl');
    }
})(document, Eb.jQuery);