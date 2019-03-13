<?php
defined('_JEXEC') or die();

echo '<script src="' . JURI::root() . 'media/com_securitycheckpro/new/vendor/chart.js/Chart.min.js"></script>';
?>

<script type="text/javascript" language="javascript">
	
	jQuery(document).ready(function() {
		
		jQuery( "#geoblock_button" ).click(function() {
			Joomla.submitbutton('go_to_geoblock');
		});
		
		jQuery( "#automatic_updates_geoblock_button" ).click(function() {
			oculta_popup(); 
			Joomla.submitbutton('automatic_updates_geoblock');
		});
		
		jQuery( "#disable_firewall_button" ).click(function() {
			Joomla.submitbutton('disable_firewall');
		});
		
		jQuery( "#enable_firewall_button" ).click(function() {
			Joomla.submitbutton('enable_firewall');
		});
		
		jQuery( "#disable_cron_button" ).click(function() {
			Joomla.submitbutton('disable_cron');
		});
		
		jQuery( "#enable_cron_button" ).click(function() {
			Joomla.submitbutton('enable_cron');
		});
		
		jQuery( "#disable_update_database_button" ).click(function() {
			Joomla.submitbutton('disable_update_database');
		});
		
		jQuery( "#enable_update_database_button" ).click(function() {
			Joomla.submitbutton('enable_update_database');
		});
		
		jQuery( "#disable_spam_protection_button" ).click(function() {
			Joomla.submitbutton('disable_spam_protection');
		});
		
		jQuery( "#enable_spam_protection_button" ).click(function() {
			Joomla.submitbutton('enable_spam_protection');
		});
		
		jQuery( "#manage_lists_button" ).click(function() {
			SetActiveTab('lists');
			Joomla.submitbutton('manage_lists');
		});
		
		jQuery( "#go_system_info_buton" ).click(function() {
			Joomla.submitbutton('Go_system_info');
		});
		
		jQuery( "#unlock_tables_button" ).click(function() {
			Joomla.submitbutton('unlock_tables');
		});
		
		jQuery( "#lock_tables_button" ).click(function() {
			Joomla.submitbutton('lock_tables');
		});
		
		jQuery( "#apply_default_config_button" ).click(function() {
			Set_Default_Config();
		});
		
		jQuery( "#apply_easy_config_button" ).click(function() {
			Set_Easy_Config();
		});
		
		
		// Actualizamos los datos del gráfico 'pie'
		Chart.defaults.global.defaultFontFamily='-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif',Chart.defaults.global.defaultFontColor="#292b2c";var ctx=document.getElementById("piechart"),piechart=new Chart(ctx,{type:"pie",data:{labels:['<?php echo JText::_('COM_SECURITYCHECKPRO_BLOCKED_ACCESS'); ?>','<?php echo JText::_('COM_SECURITYCHECKPRO_USER_AND_SESSION_PROTECTION'); ?>','<?php echo JText::_('COM_SECURITYCHECKPRO_FIREWALL_RULES_APLIED'); ?>'],datasets:[{data:['<?php echo $this->total_blocked_access; ?>','<?php echo $this->total_user_session_protection; ?>','<?php echo $this->total_firewall_rules; ?>'],backgroundColor:["#007bff","#dc3545","#ffc107"]}]}});
			
		<?php 
			// Actualizamos la variable de estado para no mostrar más el popup
			$mainframe = JFactory::getApplication();						
		?>
		
		// Mostramos el aviso de actualizaciones si hay que actualizar
		dias_ultima_actualizacion = '<?php echo $this->geoip_database_update; ?>';
		testigo_update = '<?php 
			$existe = JFile::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR .'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'scans' . DIRECTORY_SEPARATOR . 'maxmind_update.php');
			echo $existe;
		?>';
		if (dias_ultima_actualizacion > 30 && testigo_update == false ) {
			jQuery("#div_update_geoblock_database").modal('show');		
			jQuery("#div_refresh").show();		
		}
		
		//Tooltip subscripcion
		jQuery("#subscriptions_status").tooltip();
		jQuery("#scp_version").tooltip();
		jQuery("#update_database_version").tooltip();
		
		if( Cookies.get('SCPInfoMessage') ){
            //it is still within the day          		  
        } else {
            //either cookie already expired, or user never visit the site
            //create the cookie			
            Cookies.set('SCPInfoMessage', 'SCPInfoMessage', { expires: 1 });

            //and display the div
           jQuery("#mensaje_informativo").show();
		   window.setTimeout(function () {
				jQuery("#mensaje_informativo").fadeTo(500, 0).slideUp(500, function () {
					jQuery(this).remove();
				});
			}, 5000);
        }
		
	});
	
	function oculta_popup(){
		jQuery("#div_update_geoblock_database").modal('hide');		
		jQuery("#div_refresh").hide();
	}
	function muestra_progreso(){
		jQuery("#div_boton_subida").hide();
		jQuery("#div_loading").show();
	}

	function Set_Easy_Config() {
		url = 'index.php?option=com_securitycheckpro&controller=cpanel&format=raw&task=Set_Easy_Config';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			success: function(data){
				location.reload();				
			}
		});
	}
	
	function Set_Default_Config() {
		url = 'index.php?option=com_securitycheckpro&controller=cpanel&format=raw&task=Set_Default_Config';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			success: function(data){
				location.reload();				
			}
		});
	}
	var ActiveTab = "lists"; 
		
	function SetActiveTab($value) {
		ActiveTab = $value;
		storeValue('active', ActiveTab);
	}
	
	function storeValue(key, value) {
		if (localStorage) {
			localStorage.setItem(key, value);
		} else {
			$.cookies.set(key, value);
		}
	}		
</script>