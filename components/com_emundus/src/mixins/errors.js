import client from "../services/axiosClient";
import Swal from "sweetalert2";

var errors = {
    methods: {
        async displayError(title, text, type = 'error', showCancelButton = false, confirm = 'COM_EMUNDUS_OK', cancel = 'COM_EMUNDUS_ACTIONS_CANCEL', html = false, callback = null) {
            let options = {
                title: title,
                text: text,
                type: type,
                showCancelButton: showCancelButton,
                confirmButtonText: confirm,
                cancelButtonText: cancel,
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    cancelButton: 'em-swal-cancel-button',
                    confirmButton: 'em-swal-confirm-button',
                },
            };
            if(html){
                options.html = text;
            } else {
                options.text = text;
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
