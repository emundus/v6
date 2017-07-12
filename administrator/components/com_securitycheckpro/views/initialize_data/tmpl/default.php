<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHTML::_( 'behavior.framework', true );

?>

<script>
	var cont = 0;
	var etiqueta = '';
	var url = '';
	var request = '';
	var ended_string = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ENDED' ); ?>';
	
	function runButton() {
						if ( cont == 0 ){							
							document.getElementById('loading-container').innerHTML = '<?php echo ('<img src="../media/com_securitycheckpro/images/loading.gif" title="' . JText::_( 'loading' ) .'" alt="' . JText::_( 'loading' ) .'">'); ?>';
							//document.getElementById('warning_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_WARNING_MESSAGE' ); ?>';
							document.getElementById('warning_message').innerHTML = '<?php echo addslashes(JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_WARNING_MESSAGE' )); ?>';
						} else if ( cont == 1 ){
							url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=acciones_clear_data';
							etiqueta = 'current_task';
							
						} else {
							url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=getEstadoClearData';
							etiqueta = 'warning_message';
						}
						new Request({
							url: url,							
	                        method: 'GET',
							onSuccess: function(responseText){
								document.getElementById(etiqueta).innerHTML = responseText;
								request = responseText;
	                        }
                        }).send();  
						cont = cont + 1;
						if ( request == ended_string ) {
							document.getElementById('loading-container').innerHTML = '';
							document.getElementById('warning_message').innerHTML = '';
							document.getElementById('completed_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED' ); ?>';							
						} else {
							var t = setTimeout("runButton()",1000);
						}
												
	}
	
	function hideElement(Id) {
		document.getElementById(Id).innerHTML = '';
	}
		
</script>


<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager');?>" method="post" name="adminForm" id="adminForm">
<div id="warning_message" class="centrado margen-loading texto_14">
	<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CLEAR_DATA_WARNING_START_MESSAGE' ); ?>
</div>
<div id="completed_message" class="centrado margen-loading texto_14 color_verde">	
</div>
<div class="securitycheck-bootstrap">
	<div id="buttonwrapper" class="buttonwrapper">
		<button class="btn btn-primary" type="button" onclick="hideElement('buttonwrapper'); runButton();"><i class="icon-fire icon-white"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLEAR_DATA_CLEAR_BUTTON' ); ?></button>
	</div>
</div>
<div id="loading-container" class="centrado margen">	
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="filemanager" />
</form>