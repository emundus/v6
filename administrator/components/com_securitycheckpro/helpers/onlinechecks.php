<?php
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {    
    
        jQuery( "#filter_onlinechecks_search_button" ).click(function() {
            document.getElementById('filter_onlinechecks_search').value='';
            this.form.submit();
        });
                    
        // Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
        $(function() {
            $("#buttonclose").click(function() {
                setTimeout(function () {window.location.reload()},1000);                
            });
        });        
        
        contenido = '<?php 
        $mainframe = JFactory::getApplication();
        $contenido = $mainframe->getUserState('contenido', "vacio");            
        if ($contenido != "vacio") {
            echo "no vacio";
        } else {
            echo "vacio";                                
        }
        ?>';    
        
        if (contenido != "vacio") {    
            jQuery("#view_file").modal('show');
        } 
    });        
</script>
