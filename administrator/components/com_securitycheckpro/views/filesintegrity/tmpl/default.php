<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

$kind_array = array(JHtml::_('select.option','File', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE')),
			JHtml::_('select.option','Folder', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER')));

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHTML::_( 'behavior.framework', true );

?>
<script type="text/javascript" language="javascript">
	function get_percent() {
		url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent_integrity';
		new Request({
				url: url,							
				method: 'GET',
				onSuccess: function(responseText){
					if ( responseText < 100 ) {
						document.getElementById('current_task').innerHTML = in_progress_string;
						document.getElementById('warning_message').innerHTML = '';
						document.getElementById('error_message').className = 'alert alert-info';
						document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ACTIVE_TASK' ); ?>';
						document.getElementById('backup-progress').className="progress progress-success";
						hideElement('buttonwrapper');
						cont = 3;
						runButton();
					}
				}
		}).send(); 
	}
	
	function estado_integrity_timediff() {
		url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=getEstadoIntegrity_Timediff';
		new Request({
				url: url,							
				method: 'GET',
				dataType: 'json',
				onSuccess: function(responseText){
					var json = JSON.parse(responseText);
					var estado_integrity = json['estado_integrity'];
					var timediff = json['timediff'];
					if ( ((estado_integrity != 'ENDED') && (estado_integrity != error_string)) && (timediff < 3) ) {
						get_percent();
					} else if ( ((estado_integrity != 'ENDED') && (estado_integrity != error_string)) && (timediff > 3) ) {
						hideElement('buttonwrapper');
						document.getElementById('current_task').innerHTML = '<?php echo ('<font color="red">Error</font>');?>';
						document.getElementById('warning_message').innerHTML = '';
						document.getElementById('error_message').className = 'alert alert-error';
						document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_TASK_FAILURE' ); ?>';			
					}
				}
		}).send(); 
	}

	window.addEvent('domready', function() {
		estado_integrity_timediff();
	});
</script>
	
<script type="text/javascript" language="javascript">
	var cont = 0;
	var etiqueta = '';
	var url = '';
	var percent = 0;
	var ended_string = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ENDED' ); ?>';
	var in_progress_string = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS' ); ?>';
	var error_string = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ERROR' ); ?>';
	var now = '';
		
	function date_time(id) {
		date = new Date();
		year = date.getFullYear();
		month = date.getMonth()+1;
		if (month<10) {
			month = "0"+month;
		}
		day = date.getDate();
		h = date.getHours();
		if (h<10) {
			h = "0"+h;
		}
		m = date.getMinutes();
		if (m<10) {
			m = "0"+m;
		}
		s = date.getSeconds();
		if (s<10) {
			s = "0"+s;
		}
		now = year+'-'+month+'-'+day+' '+h+':'+m+':'+s
		document.getElementById(id).innerHTML = now;
	}
		
	function runButton() {
						if ( cont == 0 ){							
							document.getElementById('warning_message').innerHTML = '';
							document.getElementById('backup-progress').className="progress progress-success";
							date_time('start_time');		
							percent = 0;
						} else if ( cont == 1 ){
							document.getElementById('current_task').innerHTML = in_progress_string;
							url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=acciones_integrity';
							new Request({
								url: url,							
								method: 'GET',
								onSuccess: function(responseText){									
								}
								}).send(); 
						} else {
							url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent_integrity';					
							new Request({
								url: url,							
								method: 'GET',
								onSuccess: function(responseText){
									percent = responseText;
									document.getElementById('bar').style.width = percent + "%";
									if (percent == 100) {
										date_time('end_time');
										hideElement('error_message');
										document.getElementById('current_task').innerHTML = ended_string;
										document.getElementById('bar').style.width = 100 + "%";
										document.getElementById('completed_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED' ); ?>';
										document.getElementById('warning_message').innerHTML = "<?php echo JText::_( 'COM_SECURITYCHECKPRO_UPDATING_STATS' ); ?> <br/><img src=\"/media/com_securitycheckpro/images/loading.gif\" width=\"30\" height=\"30\" />";
										setTimeout(function () {window.location.reload()},2000);
									}
								},
								onFailure: function(responseText) {
									document.getElementById('warning_message').innerHTML = '';
									document.getElementById('current_task').innerHTML = '<?php echo ('<font color="red">Error</font>');?>';
									document.getElementById('error_message').className = 'alert alert-error';
									document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_FAILURE' ); ?>';
									document.getElementById('error_button').innerHTML = '<?php echo ('<button class="btn btn-primary" type="button" onclick="window.location.reload();">' . JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_REFRESH_BUTTON' ) . '</button>');?>';
								}
								}).send(); 
						}
						
						cont = cont + 1;
						
						if ( percent == 100) {
						
						} else if  ( (cont > 40) && (percent < 90) ) {
							var t = setTimeout("runButton()",75000);
						} else {							
							var t = setTimeout("runButton()",1000);
						}
												
	}
	
	function hideElement(Id) {
		document.getElementById(Id).innerHTML = '';
	}
	
</script>

<?php
if ( empty($this->last_check_integrity) ) {
	$this->last_check_integrity = JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' );
?>
	<div class="securitycheck-bootstrap centrado margen-container">	
		<span class="label label-info"><?php echo JText::_('COM_SECURITYCHECKPRO_NO_PREVIOUS_SCAN'); ?></span>
	</div>
	
<?php } 
if ( empty($this->files_status) ) {
	$this->files_status = JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NOT_DEFINED' );
} ?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager');?>" method="post" name="adminForm" id="adminForm">
<?php echo JHTML::_( 'form.token' ); ?>

<div id="header_manual_check" class="securitycheck-bootstrap-content-box-header">
	<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_MANUAL_INTEGRITY_CHECK_HEADER' ); ?></strong>
</div>

<div id="error_message_container" class="securitycheck-bootstrap centrado margen-container">
	<div id="error_message">
	</div>	
</div>

<div id="error_button" class="securitycheck-bootstrap centrado margen-container">	
</div>

<div id="memory_limit_message" class="securitycheck-bootstrap centrado margen-loading texto_14">
	<?php 
		// Extract 'memory_limit' value cutting the last character
		$memory_limit = ini_get('memory_limit');
		$memory_limit = (int) substr($memory_limit,0,-1);
				
		// If $memory_limit value is less or equal than 128, shows a warning if no previous scans have finished
		if ( ($memory_limit <= 128) && ($this->last_check_integrity == JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' )) ) {
			$span = "<span class=\"label label-warning\">";
			echo $span . JText::_('COM_SECURITYCHECKPRO_MEMORY_LIMIT_LOW') . "</span>";
		}
	?>
</div>

<div id="scan_only_executable_message" class="securitycheck-bootstrap centrado margen-loading texto_14">
	<?php 
		if ( $this->scan_executables_only ) {
			$span = "<span class=\"label label-warning\">";
			echo $span . JText::_('COM_SECURITYCHECKPRO_SCAN_ONLY_EXECUTABLES_WARNING') . "</span>";
		}
	?>
</div>

<div id="completed_message" class="centrado margen-loading texto_14 color_verde">	
</div>

<div id="warning_message" class="centrado margen-loading texto_14">
	<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_INTEGRITY_WARNING_START_MESSAGE' ); ?>
</div>

<div class="securitycheck-bootstrap">
	<div id="buttonwrapper" class="buttonwrapper">
		<button class="btn btn-primary" type="button" onclick="hideElement('buttonwrapper'); runButton();"><i class="icon-fire icon-white"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON' ); ?></button>
	</div>
</div>
<div id="info-container" class="centrado margen">
	<table summary="File check status" class="sofT_green margen" cellspacing="0">
	<tr>
		<td colspan="4" class="helpHed_green"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STATUS' ); ?></td>
	</tr>
	<tr>
		<td class="helpHed_green"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STARTTIME' ); ?></td>
		<td class="helpHed_green"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_ENDTIME' ); ?></td>	
		<td class="helpHed_green"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_TASK' ); ?></td>
		<td class="helpHed_green"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_HASH_ALG' ); ?></td>
	</tr>
	<tr>
		<td id="start_time"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' ); ?></td>
		<td id="end_time"><?php echo $this->files_status; ?></td>
		<td id="current_task"><?php echo $this->files_status; ?></td>
		<td id="hash_alg"><?php echo $this->hash_alg; ?></td>
	</tr>
	</table>
	<table summary="Loading gif" class="sofT_green margen centrado" cellspacing="0">
	<tr>
		<td></td>		
	</tr>
	<tr>
		<td>
			<div class="securitycheck-bootstrap margen-container">
				<div id="backup-progress">
					<div id="bar" class="bar" style="width: 0%"></div>
				</div>
			</div>
		</td>
	</tr>
	</table>	
</div>

<div id="header_check_result" class="header_check_result margen">
	<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_INTEGRITY_RESULT_HEADER' ); ?></strong>
</div>
<div id="summarytable" class="centrado margen">
<table summary="File check summary" class="sofT_brown margen" cellspacing="0">
<tr>
	<td colspan="3" class="helpHed_brown"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILE_CHECK_INTEGRITY_RESUME' ); ?></td>
</tr>
<tr>
	<td class="helpHed_brown"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_LAST_CHECK' ); ?></td>
	<td class="helpHed_brown"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_FILES_SCANNED' ); ?></td>
	<td class="helpHed_brown"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEINTEGRITY_FILES_MODIFIED' ); ?></td>
</tr>
<tr>
	<td><?php echo $this->last_check_integrity; ?></td>
	<td><?php echo $this->files_scanned_integrity; ?></td>	
	<td><?php echo $this->files_with_bad_integrity; ?></td>
</tr>  
</table>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="filemanager" />
</form>