import moment from 'moment';
import Swal from 'sweetalert2';

export default {
    methods: {
        updateLastSave() {
            moment.locale('fr');
            this.$store.dispatch('formBuilder/updateLastSave', moment().format('LT'));
        },
        async swalConfirm(title, text, confirm, cancel, callback = null, showCancelButton = true, html = false)
        {
            let options = {
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
        },

        async swalParameter(title , textDesc ,callback = null)
        {
            let options = {
                title: title,
                type: 'warning',

                html: `<div class="flex items-center">${textDesc}<i class="material-icons-outlined scale-150" style="user-select: none;">north_east</i></div>`,
                reverseButtons: true,
                customClass: {
                    title: 'em-swal-title',

                },
            };

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
