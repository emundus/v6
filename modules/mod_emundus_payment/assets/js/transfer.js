function validateFile(file, validExtension) {
    let isValid = false;

    if (validExtension.indexOf(file.type) !== -1) {
        isValid = true;
    }

    return isValid;
}

function uploadFile(file, attachmentId, label) {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();
        const url = window.location.origin + '/index.php?option=com_emundus&task=upload';
        xhr.open('POST', url);

        xhr.onload = function() {
            if (xhr.status === 200 || xhr.status === 303) {
                resolve(true);
            } else {
                reject(xhr.statusText);
            }
        };

        xhr.onerror = function() {
            reject(xhr.statusText);
        };

        const formData = new FormData();
        formData.append('attachment', attachmentId);
        formData.append('file', file);
        formData.append('label', label);
        formData.append('duplicate', 1);
        formData.append('description', '');
        formData.append('required_desc', 0);

        xhr.send(formData);
    });
}

function updateFilePaymentState() {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();
        const url = window.location.origin +
            '/index.php?option=com_emundus&controller=payment&task=updateFileTransferPayment';
        xhr.open('POST', url);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                resolve(response);
            } else {
                reject(xhr.statusText);
            }
        };

        xhr.onerror = function() {
            reject(xhr.statusText);
        };
        xhr.send();
    });
}

document.addEventListener('click', (e) => {
    if (e.target.id == 'submit-transfer') {
        const inputProof = document.getElementById('proof-file');

        if (inputProof.files.length > 0) {
            if (validateFile(inputProof.files[0], inputProof.getAttribute('accept'))) {
                document.querySelector('.panier').classList.add('hidden');
                document.querySelector('.em-loader').classList.remove('hidden');

                uploadFile(
                    inputProof.files[0],
                    inputProof.getAttribute('data-attachment'),
                    inputProof.getAttribute('data-attachment-label'))
                    .then((response) => {
                        if (response) {
                            updateFilePaymentState().then((update_response) => {
                                if (update_response.status == false) {
                                    Swal.fire({
                                        title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TITLE'),
                                        text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TEXT'),
                                        type: 'error',
                                        showCancelButton: false,
                                        confirmButtonText: Joomla.JText._('MOD_EMUNDUS_PAYMENT_OK'),
                                        reverseButtons: true,
                                        customClass: {
                                            title: 'em-swal-title',
                                            confirmButton: 'em-swal-confirm-button',
                                            actions: 'em-swal-single-action em-flex-center',
                                        },
                                    });
                                    document.querySelector('.panier').classList.add('hidden');
                                    document.querySelector('.em-loader').classList.remove('hidden');
                                }
                                window.location.href = window.location.origin;
                            });
                        } else {
                            Swal.fire({
                                title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TITLE'),
                                text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TEXT'),
                                type: 'error',
                                confirmButtonText: 'Ok'
                            });
                            document.querySelector('.panier').classList.remove('hidden');
                            document.querySelector('.em-loader').classList.add('hidden');
                        }
                    });
            } else {
                Swal.fire({
                    title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_SWAL_TITLE_ERROR'),
                    text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_SWAL_INVALID_FILE_TYPE'),
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonText: Joomla.JText._('MOD_EMUNDUS_PAYMENT_OK'),
                    reverseButtons: true,
                    customClass: {
                        title: 'em-swal-title',
                        confirmButton: 'em-swal-confirm-button',
                        actions: 'em-swal-single-action em-flex-center',
                    },
                });
            }
        } else {
            Swal.fire({
                title: Joomla.JText._('MOD_EMUNDUS_PAYMENT_SWAL_TITLE_ERROR'),
                text: Joomla.JText._('MOD_EMUNDUS_PAYMENT_SWAL_NO_FILE_UPLOADED'),
                type: 'error',
                showCancelButton: false,
                confirmButtonText: Joomla.JText._('MOD_EMUNDUS_PAYMENT_OK'),
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    confirmButton: 'em-swal-confirm-button',
                    actions: 'em-swal-single-action em-flex-center',
                },
            });
        }
    }
});