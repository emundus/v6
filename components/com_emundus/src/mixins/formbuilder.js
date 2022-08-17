import moment from 'moment';
import Swal from 'sweetalert2';

export default {
    methods: {
        updateLastSave() {
            moment.locale('fr');
            this.$store.dispatch('formBuilder/updateLastSave', moment().format('LT'));
        },
        async swalConfirm(title, text, confirm, cancel, callback = null, showCancelButton = true) {
            return Swal.fire({
                title: title,
                text: text,
                type: 'warning',
                showCancelButton: showCancelButton,
                confirmButtonText: confirm,
                cancelButtonText: cancel,
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',
                    cancelButton: 'em-swal-cancel-button',
                    confirmButton: 'em-swal-confirm-button',
                },
            }).then((result) => {
                if (result.value) {
                    if (callback != null) {
                        callback();
                    }
                    return true;
                } else {
                    return false;
                }
            });
        },
    }
};
