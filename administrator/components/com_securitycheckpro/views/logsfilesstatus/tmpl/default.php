<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

$kind_array = array(JHtml::_('select.option','File', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE')),
			JHtml::_('select.option','Folder', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER')));

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHTML::_( 'behavior.framework', true );

?>

<script>

	var url = '';
	var request = '';
	var cont = 0;
	var ended_string = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ENDED' ); ?>';
	
	window.addEvent('load', function create_loading(){
		document.getElementById('loading-container').innerHTML = '<?php echo ('<img src="../media/com_securitycheckpro/images/loading.gif" title="' . JText::_( 'loading' ) .'" alt="' . JText::_( 'loading' ) .'" onload="runButton();">'); ?>';
	});
	
	function runButton(){
		url = 'index.php?option=com_securitycheckpro&controller=filesstatus&format=raw&task=getEstado';
		new Request({
			url: url,							
	        method: 'GET',
			onSuccess: function(responseText){
				document.getElementById('warning_message').innerHTML = responseText;
				request = responseText;
	        }
        }).send(); 
		cont = cont + 1;
		if ( request == ended_string ) {
			document.getElementById('loading-container').innerHTML = '';
			document.getElementById('completed_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED' ); ?>';
			document.getElementById('buttonwrapper').innerHTML = '<?php echo ('<a class="ovalbutton_green" onClick="showIframe();"><span>' . JText::_( 'COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_MESSAGE' ) . '</span></a>');?>';
		} else {
			setTimeout("runButton()",1000);
		}
	}
	
	function hideElement(Id) {
		document.getElementById(Id).innerHTML = '';
	}
	
	function showIframe() {
		document.getElementById('warning_message').innerHTML = '';
		document.getElementById('completed_message').innerHTML = '';
		document.getElementById('buttonwrapper').innerHTML = '';
		document.getElementById('log-container_header').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_HEADER' ); ?>';
		document.getElementById('log-container_remember_text').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_REMEMBER_TEXT' ); ?>';
		ifrm = document.createElement("IFRAME");
		ifrm.setAttribute("src","<?php echo JURI::base(); ?>index.php?option=com_securitycheckpro&view=logsfilesstatus&controller=filesstatus&task=iframe&layout=raw");
		ifrm.style.width="99%";
		ifrm.style.height="400px";
		document.getElementById('log-container_header').appendChild(ifrm);		
	}
		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager');?>" method="post" name="adminForm" id="adminForm">
<div id="header_manual_check" class="header_manual_check">
	<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_REPAIR_HEADER' ); ?></strong>
</div>
<div id="warning_message" class="centrado margen-loading texto_14">
	<?php echo JText::_( 'COM_SECURITYCHECKPRO_REPAIR_START_MESSAGE' ); ?>
</div>
<div id="completed_message" class="centrado margen-loading texto_14 color_verde">	
</div>
<div id="buttonwrapper" class="buttonwrapper">
	
</div>
<div id="loading-container" class="centrado margen-loading">

</div>

<div id="log-container_remember_text" class="centrado margen color_rojo texto_14">

</div>
<div id="log-container_header" class="centrado margen texto_20">
	
</div>



<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="filemanager" />
</form>