<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {    
        
        jQuery( "#GoToJoomlaUpdate_button" ).click(function() {
            GoToJoomlaUpdate();            
        });
        jQuery( "#GoToVuln_button" ).click(function() {
            Joomla.submitbutton('GoToVuln');
        });
        jQuery( "#GoToMalware_button" ).click(function() {
            Joomla.submitbutton('GoToMalware');
        });
        jQuery( "#GoToIntegrity_button" ).click(function() {
            Joomla.submitbutton('GoToIntegrity');
        });
        jQuery( "#GoToPermissions_button" ).click(function() {
            Joomla.submitbutton('GoToPermissions');
        });
        jQuery( "#GoToHtaccessProtection_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_session_protection_button" ).click(function() {
            SetActiveTab('session_protection'); 
            Joomla.submitbutton('GoToUserSessionProtection');
        });
        jQuery( "#li_joomla_plugins_button" ).click(function() {
            GoToJoomlaPlugins();
        });
        jQuery( "#li_headers_button" ).click(function() {
            storeValue('active_htaccess', 'headers_protection');
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_twofactor_button" ).click(function() {
            Joomla.submitbutton('GoToCpanel');
        });
        jQuery( "#li_security_status_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallLists');
        });
        jQuery( "#li_security_status_logs_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallLogs');
        });
        jQuery( "#li_extension_status_second_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallSecondLevel');
        });
        jQuery( "#li_extension_status_exclude_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallExceptions');
        });
        jQuery( "#li_extension_status_xss_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallExceptions');
        });
        jQuery( "#li_extension_status_sql_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallExceptions');
        });
        jQuery( "#li_extension_status_lfi_button" ).click(function() {
            Joomla.submitbutton('GoToFirewallExceptions');
        });
        jQuery( "#li_extension_status_session_button" ).click(function() {
            Joomla.submitbutton('GoToUserSessionProtection');
        });
        jQuery( "#li_extension_status_session_hijack_button" ).click(function() {
            Joomla.submitbutton('GoToUserSessionProtection');
        });
        jQuery( "#li_extension_status_upload_button" ).click(function() {
            Joomla.submitbutton('GoToUploadScanner');
        });
        jQuery( "#li_extension_status_cron_button" ).click(function() {
            Joomla.submitbutton('GoToCpanel');
        });
        jQuery( "#li_extension_status_filemanager_check_button" ).click(function() {
            Joomla.submitbutton('GoToPermissions');
        });
        jQuery( "#li_extension_status_fileintegrity_check_button" ).click(function() {
            Joomla.submitbutton('GoToIntegrity');
        });
        jQuery( "#li_extension_status_spam_button" ).click(function() {
            Joomla.submitbutton('GoToCpanel');
        });
        jQuery( "#li_extension_status_htaccess_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_browsing_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_file_injection_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_self_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_xframe_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_mime_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_default_banned_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_signature_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_eggs_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        jQuery( "#li_extension_status_sensible_button" ).click(function() {
            Joomla.submitbutton('GoToHtaccessProtection');
        });
        
        // Go to Joomla Update page
        function GoToJoomlaUpdate() {
            window.location.href="index.php?option=com_joomlaupdate";            
        }                
        
        // Go to Joomla Plugins page
        function GoToJoomlaPlugins() {
            window.location.href="index.php?option=com_plugins&view=plugins";            
        }    
        
    });        

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
    
    // Set active tab
    window.onload = function() {
        $('.nav-tabs a[href="#overall_status"]').parent().addClass('active');                
    };        
</script>
