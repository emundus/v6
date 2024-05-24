import Swal from 'sweetalert2';

const alerts = {
    methods: {
        async alertSuccess(title, text = '', textIsHTML = false, callback = null) {
            let options = {
                title: this.translate(title),
                type: 'success',
                showCancelButton: false,
                showConfirmButton: false,
                duration: 1500,
                customClass: {
                    title: 'em-swal-title',
                }
            }

            if (text.length > 0) {
                if (textIsHTML) {
                    options.html = text;
                } else {
                    options.text = this.translate(text);
                }
            }

            return await this.displayAlert(options, callback);
        },
        async alertError(title, text = '', textIsHTML = false, callback = null) {
            let options = {
                title: this.translate(title),
                type: 'error',
                showCancelButton: false,
                confirmButtonText: this.translate('COM_EMUNDUS_OK'),
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    confirmButton: 'em-swal-confirm-button',
                    header: 'tw-flex tw-justify-center tw-items-center tw-w-full'
                },
            }

            if (text.length > 0) {
                if (textIsHTML) {
                    options.html = text;
                } else {
                    options.text = this.translate(text);
                }
            }

            return await this.displayAlert(options, callback);
        },
        async alertConfirm(title, text = '', textIsHTML = false, confirmText = 'COM_EMUNDUS_OK', cancelText = 'COM_EMUNDUS_ACTIONS_CANCEL', callback = null) {
            let options = {
                title: this.translate(title),
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: this.translate(confirmText),
                cancelButtonText: this.translate(cancelText),
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    cancelButton: 'em-swal-cancel-button',
                    confirmButton: 'em-swal-confirm-button',
                },
            }

            if (text.length > 0) {
                if (textIsHTML) {
                    options.html = text;
                } else {
                    options.text = this.translate(text);
                }
            }

            return await this.displayAlert(options, callback);
        },
        displayAlert(options, callback) {
            return Swal.fire(options).then((result) => {
                if (result.value) {
                    if (callback != null) {
                        callback();
                    }
                }
                return result;
            });
        }
    }
};

export default alerts;