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


// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

// Load Javascript
$document = JFactory::getDocument();
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/bootstrap.js');

?>
<?php
if ( empty($this->last_check_database) ) {
	$this->last_check_database = JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NEVER' );
}
?>

<div class="securitycheck-bootstrap">
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
			<i class="icon-tasks"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_DATABASE_OPTIMIZATION_OPTIONS'); ?>
		</div>
		<div class="box-content">
			
			<div class="well span3 top-block">
				<span class="sc-icon32 sc-icon-orange sc-icon-search"></span>

				<div><?php echo JText::_( 'COM_SECURITYCHECKPRO_SHOW_TABLES' ); ?></div>
				<div><span class="label label-info"><?php echo $this->show_tables; ?></span></div>
				<div style="margin-top: 10px;" class="centrado popover-info">
					<a data-content="<?php echo JText::_( 'COM_SECURITYCHECKPRO_DB_CONTENT' ); ?>" title="" data-toggle="popover" class="btn btn-mini btn-inverse" href="#" data-original-title="<?php echo JText::_( 'COM_SECURITYCHECKPRO_DB_TITLE' ); ?>"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>	
				</div>
			</div>		
			
			<div class="well span3 top-block">
				<span class="sc-icon32 sc-icon-orange sc-icon-date"></span>
				<div><?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_LABEL' ); ?></div>
				<div><span class="label label-info"><?php echo $this->last_check_database; ?></span></div>
				<div style="margin-top: 10px;" class="centrado popover-info">
					<a data-content="<?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_DESCRIPTION' ); ?>" title="" data-toggle="popover" class="btn btn-mini btn-inverse" href="#"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>	
				</div>
			</div>
		</div>
	</div>		
</div>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=dbcheck');?>" method="post" name="adminForm" id="adminForm">

<div id="header_db_optimization" class="securitycheck-bootstrap-content-box-header">
	<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DB_OPTIMIZATION' ); ?></strong>
</div>

<div id="error_button" class="securitycheck-bootstrap centrado margen-container">	
</div>

<?php if ($this->supported) { ?>						

<div class="securitycheck-bootstrap">
	<div id="buttonwrapper" class="buttonwrapper">
		<button class="btn btn-primary" type="button" onclick="StartDbCheck();"><i class="icon-fire icon-white"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON' ); ?></button>
	</div>
</div>
<div class="span10">
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
	
	<script type="text/javascript" language="javascript">
		var more_info_tag = '<?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?>';

		$(document).ready(function(){
			$(".popover-info a").popover({
				placement : 'bottom'
			});
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
		$('#buttonwrapper').remove();
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
	<?php } else { ?>
	<div class="alert alert-error"><?php echo JText::_('COM_SECURITYCHECKPRO_DB_CHECK_UNSUPPORTED'); ?></div>
	<?php } ?>
	</div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="dbcheck" />
</form>