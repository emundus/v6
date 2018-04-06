<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

$kind_array = array(JHtml::_('select.option',JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE')),
			JHtml::_('select.option',JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER')));

$status_array = array(JHtml::_('select.option','0', JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_TITLE_COMPROMISED')),
			JHtml::_('select.option','1', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_OK')),
			JHtml::_('select.option','2', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_EXCEPTIONS')));

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

?>

  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery/jquery.min.js"></script>

<?php 
// Cargamos el contenido común
include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<!-- Bootstrap core CSS-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
<!-- Custom fonts for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fa-solid.css" rel="stylesheet" type="text/css">
 <!-- Custom styles for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/css/sb-admin.css" rel="stylesheet">

<script type="text/javascript" language="javascript">
	function get_percent() {
		url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent_integrity';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			success: function(responseText){					
				if ( responseText < 100 ) {
					document.getElementById('current_task').innerHTML = in_progress_string;
					document.getElementById('warning_message2').innerHTML = '';
					document.getElementById('error_message').className = 'alert alert-info';
					document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ACTIVE_TASK' ); ?>';					
					hideElement('button_start_scan');
					cont = 3;					
					runButton();
				}					
			}
		});
	}
	
	function estado_integrity_timediff() {		
		url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=getEstadoIntegrity_Timediff';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			dataType: 'json',
			success: function(response){				
				var json = Object.keys(response).map(function(k) {return response[k] });
				var estado_integrity = json[0];
				var timediff = json[1];
											
				if ( ((estado_integrity != 'ENDED') && (estado_integrity != error_string)) && (timediff < 3) ) {
					get_percent();
				} else if ( ((estado_integrity != 'ENDED') && (estado_integrity != error_string)) && (timediff > 3) ) {					
					hideElement('button_start_scan');
					hideElement('task_status');
					document.getElementById('task_error').style.display = "block";					
					document.getElementById('error_message').className = 'alert alert-error';
					document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_TASK_FAILURE' ); ?>';			
				}						
			},
			error: function(xhr, status) {				
			}
		});
	}
	
	jQuery(document).ready(function() {	
		hideElement('backup-progress');
		estado_integrity_timediff();
				
		// Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
		$(function() {
			$("#buttonclose").click(function() {
				setTimeout(function () {window.location.reload()},1000);				
			});
		});		
	});		
</script>
	
<script type="text/javascript" language="javascript">
	var cont = 0;
	var etiqueta = '';
	var url = '';
	var percent = 0;
	var ended_string2 = '<span class="badge badge-success"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ENDED' ); ?></span>';
	var in_progress_string = '<span class="badge badge-info"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS' ); ?></span>';
	var error_string = '<span class="badge badge-danger"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_ERROR' ); ?>';
	var now = '';
	var respuesta_reparar = '';
		
	function date_time(id) {
		date = new Date();
		year = date.getFullYear();
		month = date.getMonth()+1;
		if (month<10) {
			month = "0"+month;
		}
		day = date.getDate();
		if (day<10) {
			day = "0"+day;
		}
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
			document.getElementById('backup-progress').style.display = "block";
			document.getElementById('warning_message2').innerHTML = '';			
			date_time('start_time');								
			percent = 0;
		} else if ( cont == 1 ){			
			document.getElementById('task_status').innerHTML = in_progress_string;
			url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=acciones_integrity';
			jQuery.ajax({
				url: url,							
				method: 'GET',
				success: function(responseText){													
				}
			});							
		} else {
			url = 'index.php?option=com_securitycheckpro&controller=filemanager&format=raw&task=get_percent_integrity';
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
						document.getElementById('completed_message2').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PROCESS_COMPLETED' ); ?>';
						document.getElementById('warning_message2').innerHTML = "<?php echo JText::_( 'COM_SECURITYCHECKPRO_UPDATING_STATS' ); ?><br/><br/><img src=\"<?php echo JURI::root(); ?>media/com_securitycheckpro/images/loading.gif\" width=\"30\" height=\"30\" />";												
						//setTimeout(function () {window.location.reload()},2000);							
						var url_to_redirect = '<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filesintegrity&'. JSession::getFormToken() .'=1',false);?>';
						window.location.href = url_to_redirect;
					}
				},
				error: function(responseText) {
					document.getElementById('task_error').style.display = "block";
					hideElement('backup-progress');
					hideElement('task_status');	
					document.getElementById('warning_message2').innerHTML = '';
					document.getElementById('error_message').className = 'alert alert-error';
					document.getElementById('error_message').innerHTML = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_FAILURE' ); ?>';
					document.getElementById('error_button').innerHTML = '<?php echo ('<button class="btn btn-primary" type="button" onclick="window.location.reload();">' . JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_REFRESH_BUTTON' ) . '</button>');?>';
				}
			});
		}
						
		cont = cont + 1;
		
		if ( percent == 100) {
		
		} else if  ( (cont > 40) && (percent < 90) ) {
			var t = setTimeout("runButton()",75000);
		} else {								
			var t = setTimeout("runButton()",1000);
		}
													
	}	
</script>

<?php
if ( empty($this->last_check_integrity) ) {
	$this->last_check_integrity = JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' );
}
if ( empty($this->files_status) ) {
	$this->files_status = JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NOT_DEFINED' );
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filesintegrity&'. JSession::getFormToken() .'=1');?>" method="post" style="margin-top: -18px;" name="adminForm" id="adminForm">

<?php 
		
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
		
		  <!-- Breadcrumb-->
		  <ol class="breadcrumb">
			<li class="breadcrumb-item">
			  <a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
			</li>			
			<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_INTEGRITY_TEXT'); ?></li>
		  </ol>
			
			<!-- Contenido principal -->			
			<div class="row">
			
				<div class="col-xl-6 col-sm-6 mb-6" style= "margin-bottom: 3rem !important;">
					<div class="card text-center">	
						<div class="card-header">
							<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STATUS' ); ?>							
						</div>
						<div class="row card-body" style="justify-content: center;">								
							<div style="margin-right: 10px;">
								<ul class="list-group text-center">
									<li class="list-group-item active" style="font-size: 13px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STARTTIME' ); ?></li>
									<li class="list-group-item"><span id="start_time" class="badge badge-dark"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' ); ?></span></li>
								</ul>
							</div>
							<div style="margin-right: 10px;">
								<ul class="list-group text-center">
									<li class="list-group-item active" style="font-size: 13px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_ENDTIME' ); ?></li>
									<li class="list-group-item"><span id="end_time" class="badge badge-dark"><?php echo $this->files_status; ?></span></li>
								</ul>								
							</div>
							<div style="margin-right: 10px;">
								<ul class="list-group text-center">
									<li class="list-group-item active" style="font-size: 13px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_TASK' ); ?></li>
									<li class="list-group-item">
										<span id="task_status" class="badge badge-info"><?php echo $this->files_status; ?></span>
										<span id="task_error" class="badge badge-danger" style="display: none;">Error</span>
									</li>
								</ul>
							</div>
							<div>
								<ul class="list-group text-center">
									<li class="list-group-item active" style="font-size: 13px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_HASH_ALG' ); ?></li>
									<li class="list-group-item"><span id="end_time" class="badge badge-dark"><?php echo $this->hash_alg; ?></span></li>
								</ul>								
							</div>							
						</div>						
						<div id="button_start_scan" class="card-footer">
							<button class="btn btn-primary" type="button" onclick="hideElement('button_start_scan'); hideElement('container_resultado'); hideElement('container_repair'); hideElement('completed_message2'); runButton();"><i class="fa fa-fw fa-fire"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON' ); ?></button>
						</div>						
					</div>
				</div>
				
				<div class="col-xl-5 col-sm-5 mb-5">
					<div class="card text-center">	
						<div class="card-header">
							<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_INTEGRITY_RESULT_HEADER' ); ?>
						</div>
						<div class="row card-body" style="justify-content: center;">
							<div style="margin-right: 10px;">
								<ul class="list-group text-center">
									<li class="list-group-item text-white bg-success"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_LAST_CHECK' ); ?></li>
									<li class="list-group-item"><span class="badge badge-dark"><?php echo $this->last_check_integrity; ?></span></li>
								</ul>
							</div>
							<div style="margin-right: 10px;">
								<ul class="list-group text-center">
									<li class="list-group-item text-white bg-success"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_FILES_SCANNED' ); ?></li>
									<li class="list-group-item"><span class="badge badge-dark"><?php echo $this->files_scanned_integrity; ?></span></li>
								</ul>								
							</div>
							<div>
								<ul class="list-group text-center">
									<li class="list-group-item text-white bg-success" style="font-size: 13px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEINTEGRITY_FILES_MODIFIED' ); ?></li>
									<li class="list-group-item">
										<span class="badge badge-dark"><?php echo $this->files_with_bad_integrity; ?></span>
									</li>
								</ul>
							</div>						
						</div>					
					</div>					
				</div>
				
				 <div id="scandata" class="col-lg-12">
					<div class="card mb-3">						
						<div class="card-body" style="margin-left: 10px;">
							<div id="container_repair">
								<div id="log-container_remember_text" class="centrado margen texto_14">
								</div>
								<div id="div_view_log_button" class="buttonwrapper">	
								</div>							
								<div id="log-container_header" class="centrado margen texto_20">	
								</div>
							</div>
							
							<div id="error_message_container" class="securitycheck-bootstrap centrado margen-container">
								<div id="error_message">
								</div>	
							</div>

							<div id="error_button" class="securitycheck-bootstrap centrado margen-container">	
							</div>
							
							<div id="memory_limit_message" class="centrado margen-loading">
								<?php 
									// Extract 'memory_limit' value cutting the last character
									$memory_limit = ini_get('memory_limit');
									$memory_limit = (int) substr($memory_limit,0,-1);
											
									// If $memory_limit value is less or equal than 128, shows a warning if no previous scans have finished
									if ( ($memory_limit <= 128) && ($this->last_check_integrity == JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' )) ) {
										$span = "<div class=\"alert alert-warning\">";
										echo $span . JText::_('COM_SECURITYCHECKPRO_MEMORY_LIMIT_LOW') . "</div>";
									}
								?>
							</div>

							<div id="scan_only_executable_message" class="centrado margen-loading">
								<?php 
									if ( $this->scan_executables_only ) {
										$span = "<div class=\"alert alert-warning\">";
										echo $span . JText::_('COM_SECURITYCHECKPRO_SCAN_ONLY_EXECUTABLES_WARNING') . "</div>";
									}
								?>
							</div>

							<div id="completed_message2" class="centrado margen-loading color_verde">	
							</div>
														
							<div id="warning_message2" class="centrado margen-loading">								
							</div>
																			
							<div id="backup-progress" class="progress">
								<div id="bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
							</div>	
						<div id="container_resultado">
							<div id="filter-bar" class="btn-toolbar">
								<div class="filter-search btn-group pull-left">
									<input type="text" name="filter_fileintegrity_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_fileintegrity_search" value="<?php echo $this->escape($this->state->get('filter.fileintegrity_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
								</div>
								<div class="btn-group pull-left">
									<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
									<button class="btn tip" type="button" onclick="document.getElementById('filter_fileintegrity_search').value=''; this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
								</div>
								
								<div class="btn-group pull-left">
									<select name="filter_fileintegrity_status" class="inputbox" onchange="this.form.submit()">
										<option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_STATUS_DESCRIPTION');?></option>
										<?php echo JHtml::_('select.options', $status_array, 'value', 'text', $this->state->get('filter.fileintegrity_status'));?>
									</select>				
								</div>	
							</div>
											
						<?php if (!$this->items == null) { ?>
							<?php if ( ($this->files_with_bad_integrity > 0 ) && ( empty($this->items) ) ) { ?>
							<div class="alert alert-error">
								<?php echo JText::_('COM_SECURITYCHECKPRO_EMPTY_ITEMS'); ?>
							</div>							
							<?php } ?>

							<?php if ( $this->database_error == "DATABASE_ERROR" ) { ?>
							<div class="alert alert-error">
								<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_DATABASE_ERROR'); ?>
							</div>							
							<?php } ?>

							<?php if ( $this->files_with_bad_integrity >3000 ) { ?>
							<div class="alert alert-error">
								<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ALERT'); ?>
							</div>							
							<?php } ?>

							<?php if ( $this->show_all == 1 ) { ?>
							<div class="alert alert-info">
								<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_INFO'); ?>
							</div>							
							<?php } ?>
							
							<div class="card" style="margin-top: 30px; margin-bottom: 20px;">
								<div class="card-header text-center">
									<?php echo JText::_( 'COM_SECURITYCHECKPRO_COLOR_CODE' ); ?>
								</div>
								<div class="card-block">									
									<table class="table table-striped" style="margin-top: 30px;">
										<thead>
											<tr>
												<td><span class="label label-success"> </span>
												</td>
												<td>
													<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEINTEGRITY_GREEN_COLOR' ); ?>
												</td>
												<td><span class="label label-warning"> </span>
												</td>
												<td>
													<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEINTEGRITY_YELLOW_COLOR' ); ?>
												</td>
												<td><span class="label label-important"> </span>
												</td>
												<td>
													<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEINTEGRITY_RED_COLOR' ); ?>
												</td>
											</tr>
										</thead>
									</table>								
								</div>							
							</div>						
							
								<?php
								if ( (!empty($this->items)) && (!$this->state->get('filter.fileintegrity_status')) ) {			
								?>
								<div id="permissions_buttons" class="btn-toolbar">
									<div class="pull-right">
										<button class="btn btn-success" style="margin-right: 5px;" onclick="Joomla.submitbutton('addfile_exception');" href="#">
											<i class="fa fa-fw fa-plus"> </i>
											<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_AS_EXCEPTION'); ?>
										</button>										
									</div>
								</div>
								<?php
								} else if ( $this->state->get('filter.fileintegrity_status') == 2 ) { ?>
									<div id="permissions_buttons" class="btn-toolbar">
										<div class="btn-group pull-right">
											<button class="btn btn-danger" onclick="Joomla.submitbutton('deletefile_exception');" href="#">
												<i class="icon-trash icon-white"> </i>
												<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_EXCEPTION'); ?>
											</button>
										</div>
								</div>

								<?php } ?>

								<div>
									<span class="badge" style="background-color: #66ADDD; padding: 10px 10px 10px 10px;"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_CHECKED_FILES');?></span>
								</div>
								<div class="table-responsive" style="overflow-x: scroll;">
									<table id="filesintegritystatus_table" class="table table-bordered table-hover">
									<thead>
										<tr>
											<th class="filesintegrity-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NAME' ); ?>
											</th>
											<th class="filesintegrity-table" style="width: 35%;">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_RUTA' ); ?>				
											</th>
											<th class="filesintegrity-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_TAMANNO' ); ?>				
											</th>
											<th class="filesintegrity-table">
												<?php echo JText::_( 'Info' ); ?>			
											</th>
											<th class="filesintegrity-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_LAST_MODIFIED' ); ?>
											</th>
											<th class="filesintegrity-table">
												<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
											</th>
										</tr>
									</thead>
									<?php
									$k = 0;
									if ( !empty($this->items) ) {	
										foreach ($this->items as &$row) {
										$safe_integrity = $row['safe_integrity'];
									?>
										<td style="text-align: center;">
											<?php 
											$last_part = explode(DIRECTORY_SEPARATOR,$row['path']);
											echo end($last_part); ?>
										</td>
										<td style="text-align: center; font-size: 0.75em;">
											<?php echo $row['path']; ?>
										</td>
										<td style="text-align: center;">
											<?php 
											if ( JFile::exists($row['path']) ) {
												echo filesize($row['path']);
											} 
											?>
										</td>
										<?php 
											if ( $safe_integrity == '0' ) {
												echo "<td style=\"text-align: center;\"><span class=\"badge badge-important\">";
											} else {
												echo "<td style=\"text-align: center;\">";
											} 
											if ( JFile::exists($row['path']) ) {
												echo date('Y-m-d H:i:s',filemtime($row['path']));
											}
										?>
										</span>
										</td>
										<?php 
											if ( $safe_integrity == '0' ) {
												echo "<td style=\"text-align: center;\"><span class=\"label label-important\">";
											} else if ( $safe_integrity == '1' ) {
												echo "<td style=\"text-align: center;\"><span class=\"label label-success\">";
											} else if ( $safe_integrity == '2' ) {
												echo "<td style=\"text-align: center;\"><span class=\"label label-warning\">";
											} ?>
											<?php echo $row['notes']; ?>
										</span>
										</td>
										<td style="text-align: center;">
											<?php echo JHtml::_('grid.id', $k, $row['path'], '', 'filesintegritystatus_table'); ?>		
										</td>
									</tr>
									<?php
										$k = $k+1;
										}
									}  ?>
									</table>
									</div>

									<?php
									if ( !empty($this->items) ) {		
									?>
									<div class="margen">
										<div>
											<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
										</div>
									</div>
									<?php } ?>	
								</div>
						<?php } ?>
					</div>		
				</div>
			</div>
			
		</div>
</div>		

<!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Custom scripts for all pages -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sb-admin.js"></script>

<input type="hidden" name="controller" value="filemanager" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="table" value="integrity" />
</form>