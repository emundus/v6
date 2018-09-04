<?php
defined('_JEXEC') or die();
?>

<script type="text/javascript" language="javascript">	
	var cont_otp = 0;
	
	jQuery(document).ready(function() {	
			
		// Add timer to close system messages
		window.setTimeout(function () {
			jQuery("#system-message-container").fadeTo(500, 0).slideUp(500, function () {
				jQuery(this).remove();
			});
		}, 3000);
				
		$( "#toolbar" ).after( '<div style="margin-top: 10px; margin-left: 40px;"><button id="button_responsive" class="navbar2-toggler bg-dark navbar2-toggler-right" type="button" data-toggle="collapse2" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar2-toggler-icon"></span></button></div>');
		if ($(window).width() < 960) {
			jQuery("#button_responsive").show();
		  }
		 else {
			jQuery("#button_responsive").hide();
		 }
		
		$(window).resize(function() {
		  if ($(window).width() < 960) {
			jQuery("#button_responsive").show();
		  }
		 else {
			jQuery("#button_responsive").hide();
		 }
		});		
	});	
	
	function muestra_progreso_purge(){
		jQuery("#div_boton_subida").hide();
		jQuery("#div_loading").show();
	}
	
	function hideElement(Id) {
		document.getElementById(Id).style.display = "none";
	}
	
	function view_modal_log() {	
		jQuery("#view_logfile").modal('show');
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
	function get_otp_status() {
	
		
		<?php 
			// Obtenemos el valor de la variable de estado "resultado_scans", que indicará si el escaneo ha sido correcto o incorrecto
			require_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'json.php';
			$model = new SecuritycheckProsModelJson();
			$two_factor = $model->get_two_factor_status();
			
			$params = JComponentHelper::getParams('com_securitycheckpro');
			$otp_enabled = $params->get('otp',1);
		?>
		
		status = "<?php echo $two_factor; ?>";
		otp_enabled = "<?php echo $otp_enabled; ?>";
		
		if (otp_enabled == 1) {
			if (status >= 2) {
				type = "success";
				text = "<?php echo JText::_('COM_SECURITYCHECKPRO_PASSED'); ?>";
			} else {
				type = "error";
				text = "<?php echo JText::_('COM_SECURITYCHECKPRO_FAILED'); ?>";
			}
		} else {
			type = "error";
			text = "<?php echo JText::_('COM_SECURITYCHECKPRO_FAILED'); ?>";
		}		
		
		show_otp_status(text,type,status,otp_enabled);
	}
	
	function show_otp_status(otp_text,otp_type,status,otp_enabled) {
		swal({
		  title: "<?php echo JText::_('COM_SECURITYCHECKPRO_OTP_STATUS'); ?>",
		  text: otp_text,
		  type:	otp_type,
		  showCancelButton: true,
		  cancelButtonClass: "btn-success",
		  cancelButtonText: "<?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?>"
		},
		function(isConfirm) {
			if (isConfirm) {				
			} else {
				url = "https://scpdocs.securitycheckextensions.com/troubleshooting/otp";
				window.open(url);
			}
		});
		
		// Contenido extra que será mostrado en el pop-up con el resultado
		var extra_content= '<?php echo "<div class=\"card card-info bg-info h-100 text-center pt-2\" style=\"margin-bottom: 10px;\"><div class=\"card-block card-title\" style=\"color: #fff;\">" . JText::_('COM_SECURITYCHECKPRO_OTP_DESCRIPTION') . "</div></div>" ?>';
		
		if ( extra_content && (cont_otp < 1) ) {			
			jQuery( ".form-group" ).after( extra_content );			                                        
			cont_otp++;
		}
		
		if ( otp_enabled == 0 ) {
			var otp_enabled_content = '<?php echo "<div style=\"margin-top: 10px; margin-bottom: 10px;\"><span class=\"badge badge-danger\">" . JText::_('COM_SECURITYCHECKPRO_OTP_DISABLED') . "</span></div>"?>';
			if ( cont_otp < 2 ) {
				jQuery( ".form-group" ).after( otp_enabled_content );	
				cont_otp++;
			}
		} 
		
		if ( status == 0 ) {
			var status_content = '<?php echo "<div style=\"margin-top: 10px; margin-bottom: 10px;\"><span class=\"badge badge-danger\">" . JText::_('COM_SECURITYCHECKPRO_NO_2FA_ENABLED') . "</span></div>"?>';
			if ( cont_otp < 2 ) {
				jQuery( ".form-group" ).after( status_content );	
				cont_otp++;
			}
		} else if ( status == 1 ) {
			var status_content = '<?php echo "<div style=\"margin-top: 10px; margin-bottom: 10px;\"><span class=\"badge badge-danger\">" . JText::_('COM_SECURITYCHECKPRO_NO_2FA_USER_ENABLED') . "</span></div>"?>';
			if ( cont_otp < 2 ) {
				jQuery( ".form-group" ).after( status_content );	
				cont_otp++;
			}
		}
		
				
		
	}
	
	
</script>