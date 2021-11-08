export default {
    methods: {
        runError(title = 'COM_EMUNDUS_ONBOARD_ERROR', text = 'COM_EMUNDUS_ONBOARD_ERROR_MESSAGE'){
            Swal.fire({
                title: Joomla.JText._(title),
                text: Joomla.JText._(text),
                type: "warning",
                showConfirmButton: false,
                timer: 2000,
            }).then(rep => {
                history.go(-1);
            });
        }
    }
}
