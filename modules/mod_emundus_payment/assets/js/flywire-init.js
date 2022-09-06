let globalConfig = {};

function getConfig(body) {
    let config = {};

    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();
        const url = window.location.origin + '/index.php?option=com_emundus&controller=payment&task=getFlywireConfig&format=json';
        xhr.open('POST', url);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                    config = response.data;
                    config.return_url = window.location.href;

                    resolve(config);
                } else {
                    console.error(response.error);
                    reject(xhr.statusText);
                }
            } else {
                console.error(xhr.statusText);
                reject(xhr.statusText);
            }
        };

        xhr.onerror = function() {
            reject(xhr.statusText);
        };

        xhr.send(JSON.stringify(body));
    });
}

window.addEventListener('click', function (e) {
    if (e.target.id === 'submit-payer-infos') {
        e.preventDefault();

        // get all inputs under payer-infos
        const inputs = window.document.getElementById('payer-infos').querySelectorAll('input');

        // map all inputs to an object with key equal to input id and value equal to input value
        const inputsValues = Array.from(inputs).reduce(function(obj, input) {
                obj[input.id] = input.value;
                return obj;
            }, {}
        );

        // do the same with select
        const selects = window.document.getElementById('payer-infos').querySelectorAll('select');
        const selectsValues = Array.from(selects).reduce(function(obj, select) {
                // return only selected value
                obj[select.id] = select.options[select.selectedIndex].value;
                return obj;
            }, {}
        );


        if (inputsValues.sender_first_name &&
            inputsValues.sender_last_name &&
            inputsValues.sender_address1 &&
            inputsValues.sender_city &&
            selectsValues.sender_country && (inputsValues.sender_email || inputsValues.sender_phone))
        {
            getConfig({
                ...inputsValues,
                ...selectsValues
            }).then(function(config) {
                // check that all elements of form are filled
                if (config.sender_first_name &&
                    config.sender_last_name &&
                    config.sender_address1 &&
                    config.sender_city &&
                    config.sender_country && (config.sender_email || config.sender_phone))
                {
                    window.flywire.Checkout.render(config, '#open-flywire');
                    globalConfig = config;

                    document.getElementById('open-flywire-div').classList.remove('hidden');
                    document.getElementById('modify-payer-infos').classList.remove('hidden');
                    const submitPayerInfos = document.getElementById('submit-payer-infos');
                    submitPayerInfos.setAttribute('disabled', 'disabled');
                    submitPayerInfos.classList.add('em-front-secondary-btn');
                    submitPayerInfos.classList.remove('em-front-primary-btn');

                    inputs.forEach(function(input) {
                        input.setAttribute('disabled', 'disabled');
                        input.classList.add('em-opacity-low');
                    });

                    selects.forEach(function(select) {
                        select.setAttribute('disabled', 'disabled');
                        select.classList.add('em-opacity-low');
                    });
                } else {
                    Swal.fire({
                        title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TITLE'),
                        text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TEXT'),
                        type: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            }).catch(function(error) {
                console.error(error);
            });
        } else {
            Swal.fire({
                title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TITLE'),
                text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TEXT'),
                type: 'error',
                confirmButtonText: 'Ok'
            });
        }
    } else if (e.target.id === 'open-flywire') {
        e.preventDefault();
    } else if (e.target.id === 'modify-payer-infos') {
        e.preventDefault();
        window.location.reload();
    }
});
