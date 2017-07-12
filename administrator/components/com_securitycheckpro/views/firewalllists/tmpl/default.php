<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

function prioritylist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  'Blacklist', JText::_( 'PLG_SECURITYCHECKPRO_BLACKLIST' ) ),
		JHTML::_('select.option',  'Whitelist', JText::_( 'PLG_SECURITYCHECKPRO_WHITELIST' ) ),
		JHTML::_('select.option',  'DynamicBlacklist', JText::_( 'PLG_SECURITYCHECKPRO_DYNAMICBLACKLIST' ) ),
		JHTML::_('select.option',  'Geoblock', JText::_( 'PLG_SECURITYCHECKPRO_GEOBLOCK' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', $selected, $id );
}

//JHTML::_( 'behavior.framework', true );


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
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/charisma.js');
// Char libraries
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/excanvas.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.pie.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.stack.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.resize.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/bootstrap-tab.js');

//JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('jquery.framework');

?>

<?php
	$current_ip = "";
	$range_example = "";
	if ( isset($_SERVER["REMOTE_ADDR"]) ) {
		$current_ip = $this->escape($_SERVER["REMOTE_ADDR"]);
	} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {
		$current_ip = $this->escape($_SERVER["HTTP_X_FORWARDED_FOR"]);
	} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) ) {
		$current_ip = $this->escape($_SERVER["HTTP_CLIENT_IP"]);
	} 
	$range_example = explode('.',$current_ip);
	$range_example[2] = "*";
	$range_example[3] = "*";
	$range_example = implode('.',$range_example);
	$cidr_v4_example = $current_ip . "/20";
?>

<script type="text/javascript">
	var ActiveTab = "blacklist"; 
	
	function SetActiveTab($value) {
		ActiveTab = $value;
		storeValue('active', ActiveTab);
	}
	
	function storeValue(key, value) {
		if (localStorage) {
			localStorage.setItem(key, value);
		} else {
			$.cookies.set(key, value);
		}
	}
	
	function getStoredValue(key) {
		if (localStorage) {
			return localStorage.getItem(key);
		} else {
			return $.cookies.get(key);
		}
	}
	
	function hideElement(Id) {
		document.getElementById(Id).innerHTML = '';
	}
	
	window.onload = function() {
		ActiveTab = getStoredValue('active');
		$('.nav-tabs a[href=#'+ActiveTab+']').tab('show');
	};
	
	function setOwnIP() {
		var ownip = '<?php echo $current_ip; ?>';
		$("#whitelist_add_ip").val(ownip);
		
	}
	
	function muestra_progreso(){
		jQuery("#select_blacklist_file_to_upload").show();
	}
</script>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {
		jQuery("#dynamic_blacklist_time").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		jQuery("#dynamic_blacklist_counter").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
	});
		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewalllists&'. JSession::getFormToken() .'=1');?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_CONFIGURATION'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL') ?></legend>
				<div class="control-group">
					<label for="dynamic_blacklist_parameters" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('dynamic_blacklist', array(), $this->dynamic_blacklist) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION') ?></small></p></blockquote>
				</div>
				<div class="control-group">
					<label for="dynamic_blacklist_time" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="5" maxlength="5" id="dynamic_blacklist_time" name="dynamic_blacklist_time" value="<?php echo $this->dynamic_blacklist_time ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_DESCRIPTION') ?></small></p></blockquote>
				</div>
				<div class="control-group">
					<label for="dynamic_blacklist_counter" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="3" maxlength="3" id="dynamic_blacklist_counter" name="dynamic_blacklist_counter" value="<?php echo $this->dynamic_blacklist_counter ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?></small></p></blockquote>
				</div>					
		</div>
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_LABEL') ?></legend>
				<div class="control-group">
					<label for="blacklist_email" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('blacklist_email', array(), $this->blacklist_email) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL') ?></small></p></blockquote>
				</div>				
		</div>
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="priority" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_LABEL'); ?></label>
					<label for="priority" class="control-label" title="<?php echo JText::_('First'); ?>"><?php echo JText::_('First'); ?></label>
					<div class="controls">
						<?php echo prioritylist('priority1', array(), $this->priority1) ?>
					</div>
					<label for="priority" class="control-label" title="<?php echo JText::_('Second'); ?>"><?php echo JText::_('Second'); ?></label>
					<div class="controls">
						<?php echo prioritylist('priority2', array(), $this->priority2) ?>
					</div>
					<label for="priority" class="control-label" title="<?php echo JText::_('Third'); ?>"><?php echo JText::_('Third'); ?></label>
					<div class="controls">
						<?php echo prioritylist('priority3', array(), $this->priority3) ?>
					</div>
					<label for="priority" class="control-label" title="<?php echo JText::_('Fourth'); ?>"><?php echo JText::_('Fourth'); ?></label>
					<div class="controls">
						<?php echo prioritylist('priority4', array(), $this->priority4) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_LABEL') ?></small></p></blockquote>
				</div>					
		</div>
		
	</div>
</div>
</div>


<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-pin"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_LISTS_MANAGEMENT'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	
	<div id="filter-bar" class="btn-toolbar" style="margin-left: 10px;">
		<div class="filter-search btn-group pull-left">
			<input type="text" name="filter_lists_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.lists_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn tip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
		</div>
	</div>
	
	<div class="box-content">
		<ul class="nav nav-tabs" id="myTab">
			<li id="blacklist_li" onclick="SetActiveTab('blacklist');"><a href="#blacklist"><?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST') ?></a></li>
			<li id="dynamic_blacklist_li" onclick="SetActiveTab('dynamic_blacklist_tab');"><a href="#dynamic_blacklist_tab"><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST') ?></a></li>
			<li id="whitelist_li" onclick="SetActiveTab('whitelist');"><a href="#whitelist"><?php echo JText::_('COM_SECURITYCHECKPRO_WHITELIST') ?></a></li>
		</ul>
		
		<div id="pagination">
			<?php				
				if ( isset($this->pagination) ) {									
			?>
			<div class="btn-group pull-right">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>			
		<?php echo $this->pagination->getListFooter(); ?>			
		<?php
			}
		?>
		</div>
						
		<div id="myTabContent" class="tab-content">	

			<div id="select_blacklist_file_to_upload" class="modal hide fade">
				<fieldset class="uploadform" style="margin-left: 15px;">
					<legend><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></legend>
						<label style="color: red;"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
						<div class="control-group">
							<label for="install_package" class="control-label"><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></label>
							<div class="controls">
								<input class="input_box" id="file_to_import" name="file_to_import" type="file" size="57" />
							</div>
						</div>
						<div class="form-actions">
							<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('import_blacklist');" />
						</div>
				</fieldset>								
			</div>
			
			<div class="tab-pane" id="blacklist">
				<div class="box-content">
					<div class="alert alert-info">
						<p><?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST_DESCRIPTION'); ?></p>
					</div>
									
					<div class="alert alert-info">
						<a class="close" href="#" data-dismiss="alert">×</a>
							<p><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_HEADER'); ?></p>
							<ol>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_IPV4'); ?></b>							
								<li>
									<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_SINGLE'); ?></b>
										, i.e.
									<var><?php echo $current_ip; ?></var>
								</li>
								<li>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_RANGE'); ?></b>
								, i.e.
								<var><?php echo $range_example; ?></var>
								</li>
								<li>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_CIDR'); ?></b>
								, i.e.
								<var><?php echo $cidr_v4_example; ?></var>								
								</li>							
							</ol>
							<ol>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_IPV6'); ?></b>							
								<li>
									<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_SINGLE'); ?></b><?php echo ", i.e. 2001:13d0::1"; ?>
								</li>
								<li>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_CIDR'); ?></b>	<?php echo ", i.e. 2001:13d0::/29"; ?>
								</li>							
							</ol>
							<p>
							<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_CURRENT'); ?>
							<code><?php echo $current_ip; ?></code>	
							<button class="btn-mini btn-success" onclick="setOwnIP(); Joomla.submitbutton('addip_whitelist');" href="#">
								<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
							</button>
							</p>
						</div>

					<div id="blacklist_buttons" class="btn-toolbar">
						<div class="btn-group pull-left">
							<input type="text" name="blacklist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="blacklist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
						</div>
						<div class="btn-group pull-left">
							<button class="btn btn-success" onclick="Joomla.submitbutton('addip_blacklist')" href="#">
								<i class="icon-new icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
							</button>
						</div>
						<div class="btn-group pull-left">
							<a href="#select_blacklist_file_to_upload" role="button" class="btn" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_IMPORT_IPS' ); ?></a>								
						</div>
						<div class="btn-group pull-left">
							<button class="btn btn-inverse" onclick="Joomla.submitbutton('Export_blacklist');" href="#">
								<i class="icon-new icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_EXPORT_IPS'); ?>
							</button>
						</div>
						<div class="btn-group pull-right">
							<button class="btn btn-danger" onclick="Joomla.submitbutton('deleteip_blacklist');" href="#">
								<i class="icon-trash icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
							</button>
						</div>
					</div>
					<table class="table table-striped table-bordered bootstrap-datatable datatable">
							<thead>
								<tr>
									<th class="center"><?php echo JText::_( "Ip" ); ?></th>
									<th class="center"><?php echo JText::_( 'COM_SECURITYCHECKPRO_GEOLOCATION_LABEL' ); ?></th>
									<th class="center">
										<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
									</th>
								</tr>
							</thead>   
						<tbody>
							<?php
							if ( count($this->blacklist_elements)>0 ) {
								$k = 0;
								foreach ($this->blacklist_elements as &$row) { 
							?>
							<tr>
								<td class="center"><?php echo $row; ?></td>
								<td class="center"><?php echo ($this->blacklist_elements_geolocation[$k]); ?></td>
								<td class="center">
									<?php echo JHtml::_('grid.id', $k, $row); ?>
								</td>
							</tr>
							<?php 
								$k++;
								} 
							}	?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane" id="dynamic_blacklist_tab">
				<div class="box-content">
					<div class="alert alert-info">
						<p><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION'); ?></p>
					</div>

					<div id="dynamic_blacklist_buttons" class="btn-toolbar">
						<div class="btn-group pull-right" style="margin-bottom: 5px;">
							<button class="btn btn-danger" onclick="Joomla.submitbutton('deleteip_dynamic_blacklist')" href="#">
								<i class="icon-trash icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
							</button>
						</div>						
					</div>
					<table id="dynamic_blacklist_table" class="table table-striped table-bordered bootstrap-datatable datatable">
							<thead>
								<tr>
									<th class="center"><?php echo JText::_( "Ip" ); ?></th>
									<th class="center"><?php echo JText::_( 'COM_SECURITYCHECKPRO_GEOLOCATION_LABEL' ); ?></th>
									<th class="center">
										<input type="checkbox" id="toggle_dynamic_blacklist" name="toggle_dynamic_blacklist" value="" onclick="Joomla.checkAll(this)" />
									</th>
								</tr>
							</thead>   
						<tbody>
							<?php
							if ( count($this->dynamic_blacklist_elements)>0 ) {
								$k = 0;
								foreach ($this->dynamic_blacklist_elements as &$row_dynamic) { 				
							?>
							<tr>
								<td class="center"><?php echo $row_dynamic; ?></td>
								<td class="center"><?php echo ($this->dynamic_elements_geolocation[$k]); ?></td>
								<td class="center">
									<?php echo JHtml::_('grid.id', $k, $row_dynamic, '', 'dynamic_blacklist_table'); ?>
								</td>
							</tr>
							<?php 
								$k++;
								} 
							}	?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane" id="whitelist">
			
				<div id="select_whitelist_file_to_upload" class="modal hide fade" style="float: right; margin-bottom: 5px;">
					<fieldset class="uploadform" style="margin-left: 15px;">
						<legend><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></legend>
							<label style="color: red;"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
							<div class="control-group">
								<label for="install_package" class="control-label"><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></label>
								<div class="controls">
									<input class="input_box" id="file_to_import_whitelist" name="file_to_import_whitelist" type="file" size="57" />
								</div>
							</div>
							<div class="form-actions">
								<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('import_whitelist');" />
							</div>
					</fieldset>								
				</div>
				
				<div class="box-content">
					<div class="alert alert-info">
						<p><?php echo JText::_('COM_SECURITYCHECKPRO_WHITELIST_DESCRIPTION'); ?></p>
					</div>
										
					<div class="alert alert-info">
						<a class="close" href="#" data-dismiss="alert">×</a>
							<p><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_HEADER'); ?></p>
							<ol>
								<li>
									<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_SINGLE'); ?></b>
										, i.e.
									<var><?php echo $current_ip; ?></var>
								</li>
								<li>
								<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_RANGE'); ?></b>
								, i.e.
								<var><?php echo $range_example; ?></var>
								</li>
							</ol>
							<p>
							<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_CURRENT'); ?>
							<code><?php echo $current_ip; ?></code>							
							<button class="btn-mini btn-success" onclick="setOwnIP(); Joomla.submitbutton('addip_whitelist');" href="#">
								<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
							</button>
							</p>
						</div>

					<div id="blacklist_buttons" class="btn-toolbar">
						<div class="btn-group pull-left">
							<input type="text" name="whitelist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="whitelist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
						</div>
						<div class="btn-group pull-left">
							<button class="btn btn-success" onclick="Joomla.submitbutton('addip_whitelist')" href="#">
								<i class="icon-new icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
							</button>
						</div>
						<div class="btn-group pull-left">
							<a href="#select_whitelist_file_to_upload" role="button" class="btn" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_IMPORT_IPS' ); ?></a>								
						</div>
						<div class="btn-group pull-left">
							<button class="btn btn-inverse" onclick="Joomla.submitbutton('Export_whitelist');" href="#">
								<i class="icon-new icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_EXPORT_IPS'); ?>
							</button>
						</div>
						<div class="btn-group pull-right">
							<button class="btn btn-danger" onclick="Joomla.submitbutton('deleteip_whitelist')" href="#">
								<i class="icon-trash icon-white"> </i>
									<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
							</button>
						</div>						
					</div>
					<table class="table table-striped table-bordered bootstrap-datatable datatable">
							<thead>
								<tr>
									<th class="center"><?php echo JText::_( "Ip" ); ?></th>
									<th class="center"><?php echo JText::_( 'COM_SECURITYCHECKPRO_GEOLOCATION_LABEL' ); ?></th>
									<th class="center">
										<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
									</th>
								</tr>
							</thead>   
						<tbody>
							<?php
							if ( count($this->whitelist_elements)>0 ) {
								$k = 0;
								foreach ($this->whitelist_elements as &$row) { 
							?>
							<tr>
								<td class="center"><?php echo $row; ?></td>
								<td class="center"><?php echo ($this->whitelist_elements_geolocation[$k]); ?></td>
								<td class="center">
									<?php echo JHtml::_('grid.id', $k, $row, '', 'whitelist_cid'); ?>
								</td>
							</tr>
							<?php 
								$k++;
								} 
							}	?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewalllists" />
</form>