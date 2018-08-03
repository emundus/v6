<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);
?>

  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery/jquery.min.js"></script>

<?php 
// Cargamos el contenido común
include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sweetalert.min.js"></script>

<?php 
if ( version_compare(JVERSION, '3.20', 'lt') ) {
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
 <!-- Chosen styles -->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.css" rel="stylesheet">
 <!-- Cpanel styles -->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/stylesheets/cpanelui.css" rel="stylesheet">

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=ruleslogs');?>" style="margin-top: -18px;" method="post" name="adminForm" id="adminForm">

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
					<a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=rules&view=rules&'. JSession::getFormToken() .'=1');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'); ?></a>
				</li>
				<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LOGS'); ?></li>
			</ol>
			
			<div class="card mb-6">
				<div class="card-body">
					<div>
						<div id="filter-bar" class="btn-toolbar">
							<div class="filter-search btn-group pull-left">
								<input type="text" name="filter_rules_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_rules_search" value="<?php echo $this->escape($this->state->get('filter.rules_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
							</div>
							<div class="btn-group pull-left" style="margin-left: 10px;">
								<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
								<button class="btn tip" type="button" onclick="document.getElementById('filter_rules_search').value=''; this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
							</div>							
						</div>
						
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="rules-logs">
										<?php echo JText::_( "Ip" ); ?>
									</th>
									<th class="rules-logs">
										<?php echo JText::_( 'COM_SECURITYCHECKPRO_USER' ); ?>
									</th>
									<th class="rules-logs">
										<?php echo JText::_( 'COM_SECURITYCHECKPRO_RULES_LOGS_LAST_ENTRY' ); ?>
									</th>
									<th class="rules-logs">
										<?php echo JText::_( 'COM_SECURITYCHECKPRO_RULES_LOGS_REASON_HEADER' ); ?>
									</th>
								</tr>
							</thead>
							<tbody>
						<?php
						$k = 0;
						foreach ($this->log_details as &$row) {	
						?>
						<tr class="row<?php echo $k % 2; ?>">
							<td class="rules-logs">
									<?php echo $row->ip; ?>	
							</td>
							<td class="rules-logs">
									<?php echo $row->username; ?>	
							</td>
							<td class="rules-logs">
									<?php echo $row->last_entry; ?>	
							</td>
							<td class="rules-logs">
									<?php echo $row->reason; ?>	
							</td>
						</tr>
						<?php
						$k = $k+1;
						}
						?>
							</tbody>
						</table>

						<?php
						if ( !empty($this->log_details) ) {		
						?>
						<div class="margen">
							<div>
								<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
							</div>
						</div>
						<?php
						}
						?>

						</div>

						<div class="card" style="margin-top: 10px; margin-left: 10px; width: 40rem;">
							<div class="card-body card-header">
								<?php echo JText::_('COM_SECURITYCHECKPRO_COPYRIGHT'); ?><br/>								
							</div>								
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
<!-- Chosen scripts -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.jquery.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/init.js"></script>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="ruleslogs" />
</form>