<?php
defined('_JEXEC') or die();

echo '<link href="/media/com_securitycheckpro/new/vendor/chosen/chosen.css" rel="stylesheet" type="text/css">';
?>

<script type="text/javascript" language="javascript">
    // Añadimos la función Disable cuando se cargue la página para que deshabilite (o no) el desplegable del launching interval
    jQuery(document).ready(function() {        
        Disable();
    });
        
    function Disable() {
        //Obtenemos el índice de la periodicidad y los elementos de la opción launching interval
        var element = adminForm.elements["periodicity"].selectedIndex;
        var nodes = document.getElementById("launch_time").getElementsByTagName('*');
        
        // Si se seleccionan las horas, deshabilitamos los elementos del launching interval, puesto que no serán necesarios.
        if ( element<5 ) {
            $("#launch_time").hide();
            $("#launch_time_description").hide();
            $("#launch_time_alert").show();
            $("#periodicity_description_normal").hide();
            $("#periodicity_description_alert").show();
        } else {
            $("#launch_time").show();
            $("#launch_time_description").show();
            $("#launch_time_alert").hide();
            $("#periodicity_description_normal").show();
            $("#periodicity_description_alert").hide();
        }
        
    }
</script>
