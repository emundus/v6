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

        async swalParameter(type ,title , textDesc , textDesc2 , confirmButtonText, callback = null)
        {
            let text=''
            if (type === 'emundus_fileUpload')
            { text = textDesc + '<br><br>' + textDesc2}
            else{
                text = textDesc
            }
            let options = {
                title: title,
                type: 'warning',
                width: 610, // to avoid the user on the elements below
                html: text,
                reverseButtons: true,
                confirmButtonText: confirmButtonText,
                customClass: {
                    title: 'em-swal-title',
                    confirmButton: 'em-swal-confirm-button',
                    actions: "em-swal-single-action",
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
