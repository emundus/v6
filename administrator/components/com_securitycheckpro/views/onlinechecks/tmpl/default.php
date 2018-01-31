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

$status_array = array(JHtml::_('select.option','0', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_WRONG')),
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

	jQuery(document).ready(function() {	
					
		// Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
		$(function() {
			$("#buttonclose").click(function() {
				setTimeout(function () {window.location.reload()},1000);				
			});
		});		
		
		contenido = '<?php 
			$mainframe = JFactory::getApplication();
			$contenido = $mainframe->getUserState('contenido', "vacio");			
			if ($contenido != "vacio") {
				echo "no vacio";
			} else {
				echo "vacio";								
			}
		?>';	
		
		if (contenido != "vacio") {	
			jQuery("#view_file").modal('show');
		} 
	});		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=onlinechecks&'. JSession::getFormToken() .'=1');?>" method="post" style="margin-top: -18px;" name="adminForm" id="adminForm">

	<!-- Modal view file -->
		<div class="modal" id="view_file" tabindex="-1" role="dialog" aria-labelledby="viewfileLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header alert alert-info">
				<h2 class="modal-title" id="viewfileLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_CONTENT'); ?></h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body" style="overflow-y: scroll;">	
				<?php 
					$mainframe = JFactory::getApplication();
					$contenido = $mainframe->getUserState('contenido', "vacio");
					echo $contenido;
					?>				
			  </div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
				</div>			  
			</div>
		  </div>
		</div>

<?php 
		
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
		
		  <!-- Breadcrumb-->
		  <ol class="breadcrumb">
			<li class="breadcrumb-item">
			  <a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
			</li>
			<li class="breadcrumb-item">
			  <a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=filemanager&view=malwarescan&'. JSession::getFormToken() .'=1' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_MALWARESCAN'); ?></a>
			</li>
			<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_ONLINE_CHECK_LOGS'); ?></li>
		  </ol>
		  
			<div class="alert alert-warn">
				<?php echo JText::_('COM_SECURITYCHECKPRO_PROFESSIONAL_HELP'); ?>
				<p>	<a href="https://securitycheck.protegetuordenador.com/index.php/contact-us" target="_blank" class="btn btn-primary btn-success btn-large">
					<?php echo JText::_('COM_SECURITYCHECKPRO_CONTACT_US'); ?></a>
				</p>
			</div>
			
			<!-- Contenido principal -->			
			<div>			
							<div id="filter-bar" class="btn-toolbar">
								<div class="filter-search btn-group pull-left">
									<input type="text" name="filter_onlinechecks_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_onlinechecks_search" value="<?php echo $this->escape($this->state->get('filter_onlinechecks_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
								</div>
								<div class="btn-group pull-left">
									<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
									<button class="btn tip" type="button" onclick="document.getElementById('filter_onlinechecks_search').value=''; this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
								</div>								
							</div>
											
							<div style="text-align: right;">
								<span class="badge" style="background-color: #19AAFF; padding: 10px 10px 10px 10px;"><?php echo JText::_('COM_SECURITYCHECKPRO_ONLINE_CHECK_LOGS');?></span>
							</div>
							<div class="table-responsive">
									<table id="onlinechecks_logs_table" class="table table-bordered table-hover">
									<thead>
										<tr>
											<th class="onlinelogs-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_FILES_SCANNED' ); ?>
											</th>
											<th class="onlinelogs-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_THREATS_FOUND' ); ?>				
											</th>
											<th class="onlinelogs-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_INFECTED_FILES' ); ?>				
											</th>
											<th class="onlinelogs-table">
												<?php echo JText::_( 'COM_SECURITYCHECKPRO_CREATION_DATE' ); ?>
											</th>											
											<th class="onlinelogs-table">
												<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
											</th>		
										</tr>
									</thead>
									<?php
									$k = 0;
									if ( !empty($this->items) ) {	
										foreach ($this->items as &$row) {		
									?>
										<tr>
										<td class="center">
										<?php
											$span = "<span class=\"badge badge-inverse\">";
											echo $span . $row[2]; ?>
											</span>					
										</td>
										<td class="center">
											<?php 
												if ( $row[3] == 0 ) {
													$span = "<span class=\"badge badge-success\">";
													echo $span . $row[3];
												} else  {
													$span = "<span class=\"badge badge-important\">";
													echo $span . $row[3];
												}
											?>
											</span>					
										</td>
										<td class="center">
										<?php
										if ( empty($row[5]) ) {
											$span = "<span class=\"badge badge-success\">";
											echo $span . JText::_('COM_SECURITYCHECKPRO_NONE') . "</span>";
										} else {
											// Decodificamos los nombres, que vendrán en formato json
											$infected_files = json_decode($row[5],true);
											// Contamos los elementos, puesto que vamos a mostrar sólo 3 nombres en la tabla por motivos de claridad.
											$elements = count($infected_files);
											$cont = 0;
											while ( ($cont <=2) && ($cont < $elements) ) {
												$span = "<span class=\"badge badge-warning\">";
												echo $span . $infected_files[$cont] . "</span><br/>";
												$cont++;
											}
											// Si hay más elementos, lo indicamos
											if ( $cont < $elements ) {
												$span = "<span class=\"badge\">";
												echo $span . JText::sprintf('COM_SECURITYCHECKPRO_MORE_FILES',$elements - $cont) . "</span><br/>";
											}					
										}				
										?></td>				
										<td class="center" style="font-size:14px"><?php echo $row[4]; ?></td>
										</td>										
										<td class="center">
											<?php echo JHtml::_('grid.id', $k, $row[1], '', 'onlinechecks_logs_table'); ?>
										</td>
									</tr>
									<?php 
										$k++;
										} 
									}	?>
									</table>
							</div>

								<?php
								if ( !empty($this->items) ) {		
								?>
									<div>										
										<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
									</div>
								<?php } ?>
			
			</div>
			
		</div>
</div>		

<!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Custom scripts for all pages -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sb-admin.js"></script>   

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="onlinechecks" />
</form>