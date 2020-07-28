<?php
defined('_JEXEC') or die();

use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Language\Text as JText;

echo '<script src="' . JURI::root() . 'media/com_securitycheckpro/new/vendor/chart.js/Chart.min.js"></script>';
?>

<script type="text/javascript" language="javascript">
    
    jQuery(document).ready(function() {
        
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
    
        
        //Tooltip subscripcion
        jQuery("#subscriptions_status").tooltip();
        jQuery("#scp_version").tooltip();
        jQuery("#update_database_version").tooltip();
		
		// Si existe el mensaje informativo lo ocultamos en 5 segundos
		var element =  document.getElementById('mensaje_informativo');
		if (typeof(element) != 'undefined' && element != null)
		{
		  window.setTimeout(function () {
                jQuery("#mensaje_informativo").fadeTo(500, 0).slideUp(500, function () {
                    jQuery(this).remove();
                });
            }, 5000);
		}        
        
    });
    
    
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
		var message = '<?php echo JText::_('COM_SECURITYCHECKPRO_SET_DEFAULT_CONFIG_CONFIRM'); ?>'
		var answer = confirm(message);
        if (!answer) {
            e.preventDefault();
        }
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
