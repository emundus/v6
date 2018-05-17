<?php 
/*
* Track Actions
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access'); 

$font_awesome = "media/com_securitycheckpro/stylesheets/font-awesome.min.css";
JHTML::stylesheet($font_awesome);

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
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

<script type="text/javascript" language="javascript">
	jQuery(document).ready(function() {		
		//Tooltips
		jQuery("#show_tables").tooltip();
	});	
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=trackactions_logs');?>" style="margin-top: -18px;" method="post" name="adminForm" id="adminForm">

			<?php 
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
						
			<!-- Breadcrumb-->
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
				</li>				
				<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_TRACKACTIONS_LOGS'); ?></li>
			</ol>
							
			<!-- Contenido principal -->
			<div class="row">
			
				<div class="col-lg-12">
					<div class="card mb-3">						
						<div class="card-body">
							<div id="j-main-container">
								<div id="editcell">
								<div class="accordion-group">
								<div style="font-weight:bold; font-size:10pt; text-align:center;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_COLOR_CODE' ); ?></div>
								<table class="table table-striped">								
								<thead>
									<tr>
										<td><span class="badge badge-warning"> </span>
										</td>
										<td>
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_ADMINISTRATOR_GROUP' ); ?>
										</td>
										<td><span class="badge badge-danger"> </span>
										</td>
										<td>
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_SUPER_USERS_GROUP' ); ?>
										</td>
										<td><span class="badge badge-default"> </span>
										</td>
										<td>
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_OTHER_GROUPS' ); ?>
										</td>
									</tr>
								</thead>
								</table>
								</div>
								<br />
								<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
								<?php if (empty($this->items)) : ?>
									<div class="alert alert-no-items">
										<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
									</div>
								<?php else : ?>
									<table class="table table-striped table-hover" id="logsList">
										<thead>
											<th width="2%">
												<?php echo JHtml::_('searchtools.sort', '', 'a.id', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
											</th>
											<th width="1%">
												<input type="checkbox" name="checkall-toggle" value=""
													title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
													onclick="Joomla.checkAll(this)" />
											</th>
											<th>
												<?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_MESSAGE', 'a.message', $listDirn, $listOrder); ?>
											</th>
											<th>
												<?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_DATE', 'a.log_date', $listDirn, $listOrder); ?>
											</th>
											<th>
												<?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_EXTENSION', 'a.extension', $listDirn, $listOrder); ?>
											</th>
											<th>
												<?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_USER', 'a.user_id', $listDirn, $listOrder); ?>
											</th>
											<th>
												<?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_IP_ADDRESS', 'a.ip_address', $listDirn, $listOrder); ?>
											</th>
										</thead>
										<tfoot>
											<tr>
												<td colspan="7">
													<?php echo $this->pagination->getListFooter(); ?>
												</td>
											</tr>
										</tfoot>
										<tbody>
											<?php foreach ($this->items as $i => $item) : ?>
												<tr class="row<?php echo $i % 2; ?>">
													<td>
														<span class="sortable-handler inactive tip-top hasTooltip">
															<i class="icon-menu"></i>
														</span>
													</td>
													<td class="center">
														<?php echo JHtml::_('grid.id', $i, $item->id); ?>
													</td>
													<td>
														<?php 
														$icono = null;
														 \JFactory::getApplication()->triggerEvent('onLogMessagePrepare', array (&$item->message, $item->extension, &$icono)); 
														echo $icono;
														echo $this->escape($item->message); ?>
													</td>
													<td>
														<?php echo $this->escape($item->log_date); ?>
													</td>
													<td>
														<?php echo TrackActionsHelper::translateExtensionName(strtoupper(strtok($this->escape($item->extension), '.'))); ?>
													</td>
													<td>
														<?php 
														$user_object = JUser::getInstance($item->user_id);
														// El usuario pertenece al grupo Super users
														if ( array_search(8,$user_object->groups) !== false ) {									
															$span = '<span class="badge badge-danger">';
														// El usuario pertenece al grupo Administrators
														} else if ( array_search(7,$user_object->groups) !== false ) {
															$span = '<span class="badge badge-warning">';
														} else {
															$span = '<span class="badge badge-default">';
														}
														echo $span . $user_object->name . "</span>";
														?>
													</td>
													<td>
														<?php echo JText::_($this->escape($item->ip_address)); ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								<?php endif;?>		
							</div>
						</div>
					</div>
				</div>
			<!-- End contenido principal -->
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
<input type="hidden" name="controller" value="trackactions_logs" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
</form>