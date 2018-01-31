<?php 

/**
* @package RSFirewall!
* @copyright (C) 2009-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
* @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);
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
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/stylesheets/cpanelui.css" rel="stylesheet">

<script type="text/javascript" language="javascript">
	jQuery(document).ready(function() {			
		//Tooltips
		jQuery("#show_tables").tooltip();
	});
</script>

<script type="text/javascript">
	requestTimeOut = {};
	requestTimeOut.Seconds = 60;

	
	Database = {};
	Database.Check = {
		unhide: function(item) {
			return $(item).removeClass('hidden');
		},
		tables: [],
		tablesNum: 0,
		table: '',
		content: '',
		prefix: '',
		startCheck: function() {
			this.table 	 = $('#' + this.prefix + '-table');
			this.content = $('#' + this.prefix);
			if (!this.tables.length) {
				return false;
			}
			
			this.unhide(this.content);
			this.content.hide().show('fast', function() {
				Database.Check.stepCheck(0);
			});
		},
		stopCheck: function() {
			
		},
		setProgress: function(index) {
			if ($('#' + this.prefix + '-progress .securitycheckpro-bar').length > 0) {
				var currentProgress = (100 / this.tablesNum) * index;
				$('#' + this.prefix + '-progress .securitycheckpro-bar').css('width', currentProgress + '%');				
			}
		},
		stepCheck: function(index) {
			this.setProgress(index);
			if (!this.tables || !this.tables.length) {
				this.stopCheck();
				return false;
			}
			
			this.unhide(this.table.find('tr')[index+1]);
			
						
			var jArray= <?php echo json_encode($this->tables ); ?>;
			var table = jArray[index]['Name'];
			var engine = jArray[index]['Engine'];
			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_securitycheckpro&controller=dbcheck',
				data: {
					task: 'optimize',
					table: table,
					engine: engine,
					sid: Math.random()
				},
				success: function(data) {
					$('#result' + index).html(data);
					if (requestTimeOut.Seconds != 0) {	
						setTimeout(function(){Database.Check.stepCheck(index+1)}, 60);						
					}
					else {						
						Database.Check.stepCheck(index+1);						
					}
				}
			});
		}
	}
	
	
	// DB Check
	function StartDbCheck() {
		hideElement('buttondatabase');
		
		Database.Check.unhide('#securitycheck-bootstrap-database');
				
		Database.Check.prefix = 'securitycheck-bootstrap-database';
		Database.Check.tables = [];
		<?php krsort($this->tables); ?>
		<?php foreach ($this->tables as $table) { ?>
		Database.Check.tables.push('<?php echo addslashes($table->Name); ?>');
		<?php } ?>
		Database.Check.tablesNum = Database.Check.tables.length;
		
		Database.Check.stopCheck = function() {
			$('#securitycheck-bootstrap-database-progress').fadeOut('fast', function(){$(this).remove()});			
		}
		
		Database.Check.startCheck();	
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=dbcheck');?>" style="margin-top: -18px;" method="post" name="adminForm" id="adminForm">

		<?php 
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
						
			<!-- Breadcrumb-->
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
				</li>				
				<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'); ?></li>
			</ol>
			
			<?php if ($this->supported) { ?>		
					
			<!-- Contenido principal -->
			<div class="row">
			
				<div class="col-xl-3 col-sm-6 mb-3">
					<div class="card text-center">						
						<div class="card-body">						
							<span class="sc-icon32 sc-icon-orange sc-icon-search"></span>
							<div style="margin-top: 5px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_SHOW_TABLES' ); ?></div>
							<div style="margin-top: 5px;"><span class="label label-info"><?php echo $this->show_tables; ?></span></div>													             
						</div>
						<div class="card-footer">
							<a href="#" id="show_tables" data-toggle="tooltip" title="<?php echo JText::_( 'COM_SECURITYCHECKPRO_DB_CONTENT' ); ?>"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
						</div>
					</div>
				</div>
				
				<div class="col-xl-3 col-sm-6 mb-3">
					<div class="card text-center">						
						<div class="card-body">						
							<span class="sc-icon32 sc-icon-orange sc-icon-date"></span>
							<div style="margin-top: 5px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_LABEL' ); ?></div>
							<div style="margin-top: 5px;"><span class="label label-info"><?php echo $this->last_check_database; ?></span></div>
						</div>
						<div class="card-footer">
							<a href="#" id="show_tables" data-toggle="tooltip" title="<?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_DESCRIPTION' ); ?>"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
						</div>						             						
					</div>
				</div>
				
				 <div class="col-lg-12">
					<div class="card mb-3">
						<div class="card-header">
							<i class="fa fa-database"></i>
							<?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'); ?>
						</div>
						<div class="card-body">
							<div id="buttondatabase" class="text-center">
								<button class="btn btn-primary" type="button" onclick="StartDbCheck();"><i class="fa fa-fw fa-fire"> </i><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON' ); ?></button>
							</div>
							
							<div id="securitycheck-bootstrap-main-content">		
								<div id="securitycheck-bootstrap-database" class="securitycheck-bootstrap-content-box hidden">
									<div class="securitycheck-bootstrap-content-box-content">
										<div class="securitycheck-bootstrap-progress" id="securitycheck-bootstrap-database-progress"><div class="securitycheckpro-bar" style="width: 0%;"></div></div>
										<table id="securitycheck-bootstrap-database-table">
											<thead>
												<tr>
													<th width="20%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_NAME'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_ENGINE'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_COLLATION'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_ROWS'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_DATA'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_INDEX'); ?></th>
													<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_OVERHEAD'); ?></th>
													<th><?php echo JText::_('COM_SECURITYCHECKPRO_RESULT'); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($this->tables as $i => $table) { ?>
												<tr class="securitycheck-bootstrap-table-row <?php if ($i % 2) { ?>alt-row<?php } ?> hidden">
													<td width="20%" nowrap="nowrap"><?php echo $this->escape($table->Name); ?></td>
													
													<?php if (strtolower($table->Engine) == 'myisam') { ?>
													<td width="1%" style="color:#00FF00;" nowrap="nowrap">
													<?php } else { ?>
													<td width="1%" nowrap="nowrap">
													<?php } ?>
													<?php echo $this->escape($table->Engine); ?></td>
													<td width="1%" nowrap="nowrap"><?php echo $this->escape($table->Collation); ?></td>
													<td width="1%" nowrap="nowrap"><?php echo (int) $table->Rows; ?></td>
													<td width="1%" nowrap="nowrap"><?php echo $this->bytes_to_kbytes($table->Data_length); ?></td>
													<td width="1%" nowrap="nowrap"><?php echo $this->bytes_to_kbytes($table->Index_length); ?></td>
													<td width="1%" nowrap="nowrap">
														<?php if ($table->Data_free > 0) { ?>
															<?php if (strtolower($table->Engine) == 'myisam') { ?>
															<b class="securitycheck-bootstrap-level-high"><?php echo $this->bytes_to_kbytes($table->Data_free); ?></b>
															<?php } else { ?>
															<em><?php echo JText::_('COM_SECURITYCHECKPRO_NOT_SUPPORTED'); ?></em>
															<?php } ?>
														<?php } else { ?>
															<?php echo $this->bytes_to_kbytes($table->Data_free); ?>
														<?php } ?>
													</td>
													<?php if (strtolower($table->Engine) == 'myisam') { ?>
													<td id="result<?php echo $i; ?>"></td>
													<?php } else { ?>
													<td id="result"><?php echo JText::_('COM_SECURITYCHECKPRO_NO_OPTIMIZATION_NEEDED'); ?></td>
													<?php } ?>
													
												</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<!-- End contenido principal -->
			</div>			
			<?php } else { ?>
				<div class="alert alert-error"><?php echo JText::_('COM_SECURITYCHECKPRO_DB_CHECK_UNSUPPORTED'); ?></div>
			<?php } ?>			
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
<input type="hidden" name="controller" value="dbcheck" />
</form>