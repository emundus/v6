<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JSession::checkToken( 'get' ) or die( 'Invalid Token' );

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

$type_array = array(JHtml::_('select.option','Component', JText::_('COM_SECURITYCHECKPRO_TITLE_COMPONENT')),
			JHtml::_('select.option','Plugin', JText::_('COM_SECURITYCHECKPRO_TITLE_PLUGIN')),
			JHtml::_('select.option','Module', JText::_('COM_SECURITYCHECKPRO_TITLE_MODULE')));
			
$vulnerable_array = array(JHtml::_('select.option','Si', JText::_('COM_SECURITYCHECKPRO_HEADING_VULNERABLE')),
			JHtml::_('select.option','No', JText::_('COM_SECURITYCHECKPRO_GREEN_COLOR')));

JHtml::_('formbehavior.chosen', 'select');

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

?>
  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery/jquery.min.js"></script>

<?php 
// Cargamos el contenido común
include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<?php 
if ( version_compare(JVERSION, '3.9.50', 'lt') ) {
?>
<!-- Bootstrap core CSS-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
<?php } else { ?>
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap_j4.css" rel="stylesheet">
<?php } ?>
<!-- Custom fonts for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fa-solid.css" rel="stylesheet" type="text/css">
 <!-- Custom styles for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/css/sb-admin.css" rel="stylesheet">

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1');?>" style="margin-top: -18px;" method="post" name="adminForm" id="adminForm">

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
			  <a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES'); ?></a>
			</li>
			<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_VULN_DATABASE_TEXT'); ?></li>
		  </ol>
			
			<!-- Contenido principal -->			
			<div class="card mb-3">
				<div style="margin-left: 10px; margin-right: 10px; margin-top: 10px;">
					<?php $local_joomla_branch = explode(".",JVERSION); 
					// Construimos la cabecera de la versión de Joomla para la que se muestran vulnerabilidades según la versión instalada
					if ( $local_joomla_branch[0] == "3" ) {
						$joomla_version_header = "<i class=\"fa fa-fw icon-joomla\"> 3</i>";
					} else {
						$joomla_version_header = "<i class=\"fa fa-fw icon-joomla\"> 4</i>";
					}
					?>
					<span class="badge" style="background-color: #C68C51; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_LIST' ); echo $joomla_version_header; ?></span>
				</div>
				<div class="card-body">						
						<div class="table-responsive">
							<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th width="5" class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_PRODUCT' ); ?>
										</th>
										<th class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_DETAILS' ); ?>
										</th>
										<th class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_CLASS' ); ?>
										</th>
										<th class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_PUBLISHED' ); ?>
										</th>
										<th class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_VULNERABLE' ); ?>
										</th>
										<th class="vulnerabilities-list text-center">
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_SOLUTION' ); ?>
										</th>
									</tr>
								</thead>
								<?php
								$k = 0;
								$local_joomla_branch = explode(".",JVERSION); // Versión de Joomla instalada
								foreach ($this->vuln_details as &$row) {
									// Variable que indica cuándo se ha de mostrar la información de cada elemento del array
									$to_list = false;
									/* Array con todas las versiones y modificadores para las que es vulnerable el producto */
									$vuln_joomla_version_array = explode(",",$row['Joomlaversion']);
									foreach ($vuln_joomla_version_array as $joomla_version) {
										$vulnerability_branch = explode(".",$joomla_version);
										if ( $vulnerability_branch[0] == $local_joomla_branch[0] ) {							
											$to_list = true;
											break;
										}
									}
									// Hemos de mostrar la información porque la vulnerabilidad es aplicable a nuestra versión de Joomla
									if ( $to_list ) {
								?>
								<tr class="<?php echo "row$k"; ?>">
									<td class="text-center">
										<?php echo $row['Product']; ?>	
									</td>
									<td class="text-center">
										<?php echo $row['description']; ?>
									</td>
									<td class="text-center">
										<?php echo $row['vuln_class']; ?>	
									</td>
									<td class="text-center">
										<?php echo $row['published']; ?>
									</td>
									<td class="text-center">
										<?php echo $row['vulnerable']; ?>	
									</td>
									<td class="text-center">
										<?php 
											$solution_type = $row['solution_type'];			
											if ( $solution_type == 'update' )
											{
												echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_' . $row['solution_type']) . ' ' . $row['solution'];
											}else if ( $solution_type == 'none' ){
												echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_NONE');
											}
											
										?>
									</td>							
								</tr>
								<?php
									$k = $k+1;
									}
								}
								?>							
							</table>						
						</div>	
						
						<div class="alert alert-success centrado">
							<?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITY_EXPLAIN_1'); ?>	
						</div>

						<?php
							if ( !empty($this->vuln_details) ) {		
						?>
						<div>
							<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
						</div>					
						<?php }	?>
					</div>		  					
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
<input type="hidden" name="view" value="vulninfo" />
<input type="hidden" name="controller" value="securitycheckpro" />
</form>
