<?php
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
?>

<script type="text/javascript" language="javascript">
    
    function get_percent() {
        url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent';
        jQuery.ajax({
            url: url,                            
            method: 'GET',
            success: function(responseText){                    
                if ( responseText < 100 ) {
                    document.getElementById('current_task').innerHTML = in_progress_string;
                    document.getElementById('warning_message2').innerHTML = '';
                    document.getElementById('error_message').className = 'alert alert-info';
                    document.getElementById('error_message').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ACTIVE_TASK'); ?>';                    
                    hideElement('button_start_scan');
                    cont = 3;                    
                    boton_filenamager();
                }                    
            }
        });
    }
    
    function estado_timediff() {        
        url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=getEstado_Timediff';
        jQuery.ajax({
            url: url,                            
            method: 'GET',
            dataType: 'json',
            success: function(response){                
                var json = Object.keys(response).map(function(k) {return response[k] });
                var estado = json[0];
                var timediff = json[1];
                                            
                if ( ((estado != 'ENDED') && (estado != error_string)) && (timediff < 3) ) {
                    get_percent();
                } else if ( ((estado != 'ENDED') && (estado != error_string)) && (timediff > 3) ) {                    
                    hideElement('button_start_scan');
                    hideElement('task_status');
                    document.getElementById('task_error').style.display = "block";                    
                    document.getElementById('error_message').className = 'alert alert-error';
                    document.getElementById('error_message').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TASK_FAILURE'); ?>';            
                }                        
            },
            error: function(xhr, status) {                
            }
        });
    }
    
        
    function showLog() {
        document.getElementById('completed_message2').innerHTML = '';
        document.getElementById('div_view_log_button').innerHTML = '';
        document.getElementById('log-container_header').innerHTML = '<div class="alert alert-info" role="alert"><?php echo JText::_('COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_HEADER'); ?></div>';
        document.getElementById('log-text').style.display = "block";
    }
    
    jQuery(document).ready(function() {    
    
        jQuery( "#button_start_scan" ).click(function() {
            hideElement('button_start_scan');
            hideElement('container_resultado'); 
            hideElement('container_repair'); 
            hideElement('completed_message2'); 
            boton_filenamager();
        });
        
        jQuery( "#view_modal_log_button" ).click(function() {
            view_modal_log();
        });
        
        jQuery( "#filter_filemanager_search_clear_button" ).click(function() {
            document.getElementById('filter_filemanager_search').value=''; 
            jQuery("#adminForm").submit();
        });
        
        jQuery( "#add_exception_button" ).click(function() {
            Joomla.submitbutton('addfile_exception');
        });
        
        jQuery( "#repair_button" ).click(function() {
            Joomla.submitbutton('repair');
        });
        
        jQuery( "#delete_exception_button" ).click(function() {
            Joomla.submitbutton('deletefile_exception');
        });
        
        hideElement('container_repair');
        var repair_launched = '<?php echo $this->repair_launched; ?>';
        if ( repair_launched ) {
            hideElement('container_resultado');
            document.getElementById('container_repair').style.display = "block";
            document.getElementById('completed_message2').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED'); ?>';
            document.getElementById('log-container_remember_text').innerHTML = '<div class="alert alert-warning" role="alert"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_LAUNCH_NEW_TASK'); ?></div>';
            document.getElementById('div_view_log_button').innerHTML = '<?php echo ('<button class="btn btn-primary" onclick="showLog();">' . JText::_('COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_MESSAGE') . '</button>');?>';
            hideElement('log-text');                        
        }        
        hideElement('backup-progress');
        estado_timediff();
                
        // Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
        $(function() {
            $("#buttonclose").click(function() {
                setTimeout(function () {window.location.reload()},1000);                
            });
        });        
    });        

    var cont = 0;
    var etiqueta = '';
    var url = '';
    var percent = 0;
    var ended_string2 = '<span class="badge badge-success"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ENDED'); ?></span>';
    var in_progress_string = '<span class="badge badge-info"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS'); ?></span>';
    var error_string = '<span class="badge badge-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ERROR'); ?>';
    var now = '';
    var respuesta_reparar = '';
        
    function date_time(id) {
        url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=currentDateTime';
		jQuery.ajax({
			url: url,                            
			method: 'GET',
			success: function(responseText){	
				document.getElementById(id).innerHTML = responseText;
			},
			error: function(responseText) { 
				
			}
		});	        
    }
    
    function boton_filenamager() {
        if ( cont == 0 ){
            document.getElementById('backup-progress').style.display = "block";
            document.getElementById('warning_message2').innerHTML = '';            
            date_time('start_time');                                
            percent = 0;
        } else if ( cont == 1 ){            
            document.getElementById('task_status').innerHTML = in_progress_string;
            url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=acciones';
            jQuery.ajax({
                url: url,                            
                method: 'GET',
                success: function(responseText){                                                    
                }
            });                            
        } else {
            url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent';
            jQuery.ajax({
                url: url,                            
                method: 'GET',
                success: function(responseText){
                    percent = responseText;                    
                    document.getElementById('bar').style.width = percent + "%";
                    if (percent == 100) {                        
                        date_time('end_time');
                        hideElement('error_message');
                        document.getElementById('task_status').innerHTML = ended_string2;
                        document.getElementById('bar').style.width = 100 + "%";
                        document.getElementById('completed_message2').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED'); ?>';
                        document.getElementById('warning_message2').innerHTML = "<?php echo JText::_('COM_SECURITYCHECKPRO_UPDATING_STATS'); ?><br/><br/><img src=\"<?php echo JURI::root(); ?>media/com_securitycheckpro/images/loading.gif\" width=\"30\" height=\"30\" />";                                                
                        //setTimeout(function () {window.location.reload()},2000);                            
                        var url_to_redirect = '<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1', false);?>';
                        window.location.href = url_to_redirect;
                    }
                },
                error: function(responseText) {
                    document.getElementById('task_error').style.display = "block";
                    hideElement('backup-progress');
                    hideElement('task_status');    
                    document.getElementById('warning_message2').innerHTML = '';
                    document.getElementById('error_message').className = 'alert alert-error';
                    document.getElementById('error_message').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_FAILURE'); ?>';
                    document.getElementById('error_button').innerHTML = '<?php echo ('<button class="btn btn-primary" type="button" onclick="window.location.reload();">' . JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_REFRESH_BUTTON') . '</button>');?>';
                }
            });
        }
                        
        cont = cont + 1;
        
        if ( percent == 100) {
        
        } else if  ( (cont > 40) && (percent < 90) ) {
            var t = setTimeout("boton_filenamager()",75000);
        } else {                                
            var t = setTimeout("boton_filenamager()",1000);
        }
                                                    
    }
    
    function repair() {
        hideElement('container_resultado');
        document.getElementById('backup-progress').style.display = "block";
        document.getElementById('bar').style.width = 100 + "%";
        document.getElementById('completed_message2').innerHTML = '<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED'); ?>';
        document.getElementById('warning_message2').innerHTML = "<?php echo JText::_('COM_SECURITYCHECKPRO_UPDATING_STATS'); ?><br/><br/><img src=\"<?php echo JURI::root(); ?>media/com_securitycheckpro/images/loading.gif\" width=\"30\" height=\"30\" />";    
    }
</script>
