<?php
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

$formid = $jinput->get('formid');
?>
<script>
    jQuery('button[type="submit"]').on("click", (e) => {
        e.stopImmediatePropagation();
        Swal.fire({
            title: 'Changement de vos voeux',
            text:'Voulez-vous sauvegarder le choix de vos voeux ? N\'oubliez pas de remplir tous vos dossiers afin que tous vos voeux soit traités',
            type: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            backdrop: false,
            confirmButtonText: 'Sauvegarder',
            cancelButtonText: 'Annuler',
        }).then((result) => {
            console.log(result);
            if (result.value) {
                document.querySelectorAll('[name=form_' + <?php echo $formid ?>)[0].submit();
            } else {
                document.getElementById('fabrikSubmit_' + <?php echo $formid ?>).disabled = false;
                document.getElementById('fabrikSubmit_' + <?php echo $formid ?>).style.opacity = 1;
            }
        });
    });
</script>
