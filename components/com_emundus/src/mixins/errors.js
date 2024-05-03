import Swal from 'sweetalert2';

var errors = {
    methods: {
        async displayError(title, text, type = 'error', showCancelButton = false, confirm = 'COM_EMUNDUS_OK', cancel = 'COM_EMUNDUS_ACTIONS_CANCEL', html = false, callback = null) {
            let options = {
                title: this.translate(title),
                text: this.translate(text),
                type: type,
                showCancelButton: showCancelButton,
                confirmButtonText: this.translate(confirm),
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    confirmButton: 'em-swal-confirm-button',
                    header: 'tw-flex tw-justify-center tw-items-center tw-w-full'
                },
            };

            if (showCancelButton) {
                options.cancelButtonText = this.translate(cancel);
                options.customClass.cancelButton = 'em-swal-cancel-button';
            } else {
                options.customClass.actions = 'em-swal-single-action';
            }

            if(html){
                options.html = text;
            } else {
                options.text = this.translate(text);
            }

            return Swal.fire(options).then((result) => {
                if (result.value) {
                    if (callback != null) {
                        callback();
                    }
                    return true;
                } else {
                    return false;
                }
            });
        }
    }
};

export default errors;
