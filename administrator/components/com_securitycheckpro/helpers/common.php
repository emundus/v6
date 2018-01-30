<?php
defined('_JEXEC') or die();
?>

<script type="text/javascript" language="javascript">	
	jQuery(document).ready(function() {		
		// Add timer to close system messages
		window.setTimeout(function () {
			jQuery("#system-message-container").fadeTo(500, 0).slideUp(500, function () {
				jQuery(this).remove();
			});
		}, 3000);
	});	
	
	function muestra_progreso_purge(){
		jQuery("#div_boton_subida").hide();
		jQuery("#div_loading").show();
	}
	
	function hideElement(Id) {
		document.getElementById(Id).style.display = "none";
	}
	
	
	var cont_initialize = 0;
	var etiqueta_initialize = '';
	var url_initialize = '';
	var request_initialize = '';
	var ended_string_initialize = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ENDED' ); ?>';
	
	function clear_data_button() {
		if ( cont_initialize == 0 ){							
			document.getElementById('loading-container').innerHTML = '<?php echo ('<img src="../media/com_securitycheckpro/images/loading.gif" title="' . JText::_( 'loading' ) .'" alt="' . JText::_( 'loading' ) .'">'); ?>';
			document.getElementById('warning_message').innerHTML = '<?php echo addslashes(JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_WARNING_MESSAGE' )); ?>';
		} else if ( cont_initialize == 1 ){
			url_initialize = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=acciones_clear_data';
			etiqueta_initialize = 'current_task';
		} else {
			url_initialize = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=getEstadoClearData';
			etiqueta_initialize = 'warning_message';
		}
		
		jQuery.ajax({
			url: url_initialize,							
			method: 'GET',
			success: function(response){				
				document.getElementById(etiqueta_initialize).innerHTML = response;
				request_initialize = response;						
			}
		});
			
		cont_initialize = cont_initialize + 1;
		
		if ( request_initialize == ended_string_initialize ) {			
			hideElement('loading-container');
			hideElement('warning_message');
			document.getElementById('completed_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED' ); ?>';
			document.getElementById('buttonclose').style.display = "block";			
		} else {
			var t = setTimeout("clear_data_button()",1000);						
		}
												
	}	
</script>