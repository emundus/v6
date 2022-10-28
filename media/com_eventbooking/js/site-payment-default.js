(function (document, $) {
    $(document).ready(function () {
        $("#adminForm").validationEngine('attach', {
            onValidationComplete: function(form, status){
                if (status == true) {
                    form.on('submit', function(e) {
                        e.preventDefault();
                    });
                    form.find('#btn-submit').prop('disabled', true);

                    if($('input:radio[name^=payment_method]').length)
                    {
                        var paymentMethod = $('input:radio[name^=payment_method]:checked').val();
                    }
                    else
                    {
                        var paymentMethod = $('input[name^=payment_method]').val();
                    }

                    if (paymentMethod.indexOf('os_stripe') == 0)
                    {
                        if (typeof stripePublicKey !== 'undefined')
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
                        if (typeof stripe !== 'undefined')
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


                    if (paymentMethod == 'os_squareup')
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

        if (typeof stripe !== 'undefined')
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
    });
})(document, Eb.jQuery);