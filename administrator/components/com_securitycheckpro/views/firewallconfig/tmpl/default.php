<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 
JSession::checkToken( 'get' ) or die( 'Invalid Token' );

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function prioritylist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  'Blacklist', JText::_( 'PLG_SECURITYCHECKPRO_BLACKLIST' ) ),
		JHTML::_('select.option',  'Whitelist', JText::_( 'PLG_SECURITYCHECKPRO_WHITELIST' ) ),
		JHTML::_('select.option',  'DynamicBlacklist', JText::_( 'PLG_SECURITYCHECKPRO_DYNAMICBLACKLIST' ) ),
		JHTML::_('select.option',  'Geoblock', JText::_( 'PLG_SECURITYCHECKPRO_GEOBLOCK' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id );
}

function methodslist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  'GET,POST,REQUEST', 'Get,Post,Request' ),

	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id );
}

function mode( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'PLG_SECURITYCHECKPRO_ALERT_MODE' ) ),
		JHTML::_('select.option',  '1', JText::_( 'PLG_SECURITYCHECKPRO_STRICT_MODE' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function redirectionlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '1', JText::_( 'PLG_SECURITYCHECKPRO_JOOMLA_PATH_LABEL' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_REDIRECTION_OWN_PAGE' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single" onchange="Disable()"', 'value', 'text', (int) $selected, $id );
}

function secondredirectlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name,  'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function booleanlist_js( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single" onchange="Disable()"', 'value', 'text', (int) $selected, $id );
}

function email_actions( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_BOTH_INCORRECT' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_FRONTEND' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_BACKEND' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function actions_failed_login( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function actions( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function spammer_action( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function action( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

$site_url = JURI::root();

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
	var ActiveTab = "lists"; 
	var ActiveTabLists = "blacklist";
	var ExceptionsActiveTab = "header_referer";
	var ActiveTabContinent = "europe";
	
	function SetActiveTab($value) {
		ActiveTab = $value;
		storeValue('active', ActiveTab);
	}
	
	function SetActiveTabLists($value) {
		ActiveTabLists = $value;
		storeValue('activelists', ActiveTabLists);
	}
	
	function SetActiveTabContinent($value) {
		ActiveTabContinent = $value;
		storeValue('activecontinent', ActiveTabContinent);
	}
	
	function SetActiveTabExceptions($value) {
		ExceptionsActiveTab = $value;
		storeValue('exceptions_active', ExceptionsActiveTab);
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
	
	window.onload = function() {
		ActiveTab = getStoredValue('active');
		if (ActiveTab) {
			$('.nav-tabs a[href="#' + ActiveTab + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTab + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#lists"]').parent().addClass('active');
		}
		
		ActiveTablists = getStoredValue('activelists');
		if (ActiveTablists) {
			$('.nav-tabs a[href="#' + ActiveTablists + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTablists + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#blacklist"]').parent().addClass('active');
		}
		
		ExceptionsActiveTab = getStoredValue('exceptions_active');
		if (ExceptionsActiveTab) {
			$('.nav-tabs a[href="#' + ExceptionsActiveTab + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ExceptionsActiveTab + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#header_referer"]').parent().addClass('active');
		}
		
		ActiveTabContinent = getStoredValue('activecontinent');
		//ActiveTabContinent = "northAmerica";
		if (ActiveTabContinent) {			
			$('.nav-tabs a[href="#' + ActiveTabContinent + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTabContinent + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#europe"]').parent().addClass('active');
		}
				
	};
	
		
	function setOwnIP() {
		var ownip = '<?php echo $current_ip; ?>';
		$("#whitelist_add_ip").val(ownip);
		
	}
	
	function muestra_progreso(){
		jQuery("#select_blacklist_file_to_upload").show();
	}
	
	function muestra_progreso_geoblock(){
		jQuery("#div_update_geoblock_database").show();		
		//jQuery("#div_refresh_geoblock").show();
	}
	
	function Disable() {
		//Obtenemos el índice las opciones de redirección
		var element = adminForm.elements["redirect_options"].selectedIndex;
						
		// Si está establecida la opción de la propia página, habilitamos el campo redirect_url para escritura. Si no, lo deshabilitamos
		if ( element==0 ) {
			document.getElementById('redirect_url').readOnly = true;
		} else {			
			document.getElementById('redirect_url').readOnly = false;
		}
		
		//Obtenemos el índice de la opción 'strip all tags'
		var element = adminForm.elements["strip_all_tags"].selectedIndex;
				
		// Ocultamos o mostramos la caja de texto según la elección anterior
		if ( element==1 ) {
			$("#tags_to_filter_div").hide();			
		} else {
			$("#tags_to_filter_div").show();			
		}
		
	}
	
	function CheckAll(idname, checktoggle, continentname) {
		var checkboxes = new Array();
		checkboxes = document.getElementById(idname).getElementsByTagName('input');
		document.getElementById(continentname).checked = checktoggle;
		
		for (var i=0; i<checkboxes.length; i++) {
			if (checkboxes[i].type == 'checkbox') {
				checkboxes[i].checked = checktoggle;
			}			
		}
		
    }	 
	
	function disable_continent_checkbox(continentname, name) {
		var checkbox = document.getElementById(name);
		if (checkbox.checked != true) {
			document.getElementById(continentname).checked = false;
		}		
	}
</script>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {
		Disable();
		
		jQuery("#dynamic_blacklist_time").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		jQuery("#dynamic_blacklist_counter").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		
		jQuery("#email_max_number").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		
		jQuery("#log_limits_per_ip_and_day").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		
		jQuery("#second_level_limit_words").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		
		jQuery("#spammer_limit").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
		
		jQuery("#delete_period").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});		
	
	});
		
</script>

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

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallconfig&'. JSession::getFormToken() .'=1');?>" style="margin-top: -18px;" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">

		<!-- Modal update geoblock -->
		<div class="modal fade" id="div_update_geoblock_database" tabindex="-1" role="dialog" aria-labelledby="updategeoblockLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">					
					<div class="modal-body">	
						<fieldset class="uploadform" style="margin-left: 10px;">
							<legend><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_TEXT'); ?></legend>
							<div class="form-actions center" id="div_refresh_geoblock">
								<span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATING'); ?></span><br/>					
							</div>						
						</fieldset>			
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
					<a href="#"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
				</li>
				<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_WAF_CONFIG'); ?></li>
			</ol>
			
			<div class="card mb-3">
				<div class="card-body">
									
					<ul class="nav nav-tabs" role="tablist" id="WafConfigurationTabs">
					  <li class="nav-item" onclick="SetActiveTab('lists');">
						<a class="nav-link active" href="#lists" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LISTS_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('methods');">
						<a class="nav-link" href="#methods" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('mode');">
						<a class="nav-link" data-toggle="tab" href="#mode" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_FIELDSET_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('logs');">
						<a class="nav-link" data-toggle="tab" href="#logs" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('redirection');">
						<a class="nav-link" data-toggle="tab" href="#redirection" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('second');">
						<a class="nav-link" data-toggle="tab" href="#second" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('email_notifications');">
						<a class="nav-link" data-toggle="tab" href="#email_notifications" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('exceptions');">
						<a class="nav-link" data-toggle="tab" href="#exceptions" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('session_protection');">
						<a class="nav-link" data-toggle="tab" href="#session_protection" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('geoblock');">
						<a class="nav-link" data-toggle="tab" href="#geoblock" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('upload_scanner');">
						<a class="nav-link" data-toggle="tab" href="#upload_scanner" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('spam_protection');">
						<a class="nav-link" data-toggle="tab" href="#spam_protection" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_SPAM_PROTECTION'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('url_inspector');">
						<a class="nav-link" data-toggle="tab" href="#url_inspector" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT'); ?></a>
					  </li>
					  <li class="nav-item" onclick="SetActiveTab('track_actions');">
						<a class="nav-link" data-toggle="tab" href="#track_actions" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_TRACK_ACTIONS'); ?></a>
					  </li>
					</ul>
					
					<div class="tab-content">
						<div class="tab-pane show active" id="lists" role="tabpanel">
								<!-- Lists -->
									<div class="card mb-3">											
										<div class="card-body">
											<div class="row">
												<div class="col-xl-3 mb-3">
													<div class="card-header text-white bg-primary">
														<?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL') ?>
													</div>
													<div class="card-body">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL'); ?></h4>
														<div class="controls">
															<?php echo booleanlist('dynamic_blacklist', array(), $this->dynamic_blacklist) ?>
														</div>
														<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION') ?></small></p></blockquote>
														
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_LABEL'); ?></h4>
														<div class="controls">
															<input type="text" size="5" maxlength="5" id="dynamic_blacklist_time" name="dynamic_blacklist_time" value="<?php echo $this->dynamic_blacklist_time ?>" title="" />		
														</div>
														<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_DESCRIPTION') ?></small></p></blockquote>
														
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_LABEL'); ?></h4>
														<div class="controls">
															<input type="text" size="3" maxlength="3" id="dynamic_blacklist_counter" name="dynamic_blacklist_counter" value="<?php echo $this->dynamic_blacklist_counter ?>" title="" />		
														</div>
														<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?></small></p></blockquote>														
													</div>													
												</div>
												
												<div class="col-xl-3 mb-3">
													<div class="card-header text-white bg-primary">
														<?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_LABEL') ?>
													</div>
													<div class="card-body">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL'); ?></h4>
														<div class="controls">
															<?php echo booleanlist('blacklist_email', array(), $this->blacklist_email) ?>
														</div>
														<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL') ?></small></p></blockquote>														
													</div>													
												</div>											
												
												<div class="col-xl-3 mb-3">
													<div class="card-header text-white bg-primary">
														<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
													</div>
													<div class="card-body">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_LABEL'); ?></h4>
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
									
									<!-- Lists tab -->
									<div class="card mb-3">	
										<div class="card-header">
											<i class="fapro fa-bars"></i>
											<?php echo JText::_( 'COM_SECURITYCHECKPRO_LISTS_MANAGEMENT' ); ?>
										</div>
										<div class="card-body">
												<div id="filter-bar" class="btn-toolbar" style="margin-left: 10px;">
													<div class="filter-search btn-group pull-left">
														<input type="text" name="filter_lists_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.lists_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
													</div>
													<div class="btn-group pull-left" style="margin-left: 10px;">
														<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
														<button class="btn tip" type="button" onclick="document.getElementById('filter_search').value=''; this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
													</div>
												</div>
												<br/>
												<div class="box-content">
													<ul class="nav nav-tabs" role="tablist" id="ListsTabs">
													  <li class="nav-item" onclick="SetActiveTabLists('blacklist');">
														<a class="nav-link active" href="#blacklist" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST'); ?></a>
													  </li>
													  <li class="nav-item" onclick="SetActiveTabLists('dynamic_blacklist_tab');">
														<a class="nav-link" href="#dynamic_blacklist_tab" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST'); ?></a>
													  </li>
													  <li class="nav-item" onclick="SetActiveTabLists('whitelist');">
														<a class="nav-link" data-toggle="tab" href="#whitelist" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_WHITELIST'); ?></a>
													  </li>
													</ul>
																								
													<div id="pagination" style="margin-bottom: 30px;">
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
													
													<div class="tab-content">
														<!-- Blacklist tab -->
														<div class="tab-pane show active" id="blacklist" role="tabpanel">
															<!-- Blacklist Import file modal -->
															<div class="modal fade" id="select_blacklist_file_to_upload" tabindex="-1" role="dialog" aria-labelledby="blacklistfileuploadLabel" aria-hidden="true">
																<div class="modal-dialog" role="document">
																	<div class="modal-content">
																	  <div class="modal-header alert alert-info">
																		<h2 class="modal-title" id="blacklistfileuploadLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></h2>
																		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	  </div>
																	  <div class="modal-body">	
																		<div id="div_messages">
																			<label style="color: red;"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
																			<h5><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></h5>						
																			<div class="controls">
																				<input class="input_box" id="file_to_import" name="file_to_import" type="file" size="57" />
																			</div>
																		</div>																				
																	  </div>
																		<div class="modal-footer" id="div_boton_subida">
																			<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('import_blacklist');" />
																			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
																		</div>			  
																	</div>
																  </div>
															</div>
															
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
																		<button type="button" class="btn btn-sm btn-success" onclick="setOwnIP(); Joomla.submitbutton('addip_whitelist');" href="#">
																			<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
																		</button>
																		</p>
																</div>
																
																<div id="blacklist_buttons">
																	<div class="btn-group pull-left">
																		<input type="text" name="blacklist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="blacklist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px; margin-bottom: 20px;">
																		<button class="btn btn-success" onclick="Joomla.submitbutton('addip_blacklist')" href="#">
																			<i class="fapro fa-plus-octagon"> </i>
																				<?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
																		</button>
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px;">
																		<a href="#select_blacklist_file_to_upload" role="button" class="btn btn-secondary" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_IMPORT_IPS' ); ?></a>								
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px;">
																		<button class="btn btn-info" onclick="Joomla.submitbutton('Export_blacklist');" href="#">
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
														<!-- End Blacklist tab -->															
														</div>
														
														<!-- Dynamic blacklist tab -->
														<div class="tab-pane" id="dynamic_blacklist_tab" role="tabpanel">
															<div class="box-content">
																<div class="alert alert-info">
																	<p><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION'); ?></p>
																</div>

																<div id="dynamic_blacklist_buttons">
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
														<!-- Dynamic blacklist tab -->
														</div>
														
														<!-- Whitelist tab -->
														<div class="tab-pane" id="whitelist" role="tabpanel">
														
															<!-- Whitelist Import file modal -->
															<div class="modal fade" id="select_whitelist_file_to_upload" tabindex="-1" role="dialog" aria-labelledby="whitelistfileuploadLabel" aria-hidden="true">
																<div class="modal-dialog" role="document">
																	<div class="modal-content">
																	  <div class="modal-header alert alert-info">
																		<h2 class="modal-title" id="whitelistfileuploadLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></h2>
																		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	  </div>
																	  <div class="modal-body">	
																		<div id="div_messages">
																			<label style="color: red;"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
																			<h5><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></h5>						
																			<div class="controls">
																				<input class="input_box" id="file_to_import_whitelist" name="file_to_import_whitelist" type="file" size="57" />
																			</div>
																		</div>																				
																	  </div>
																		<div class="modal-footer" id="div_boton_subida">
																			<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('import_whitelist');" />
																			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
																		</div>			  
																	</div>
																  </div>
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
																		<button type="button" class="btn btn-sm btn-success" onclick="setOwnIP(); Joomla.submitbutton('addip_whitelist');" href="#">
																			<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
																		</button>
																		</p>
																	</div>

																<div id="blacklist_buttons">
																	<div class="btn-group pull-left">
																		<input type="text" name="whitelist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="whitelist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px; margin-bottom: 20px;">
																		<button class="btn btn-success" onclick="Joomla.submitbutton('addip_whitelist')" href="#">
																			<i class="fapro fa-plus-octagon"> </i>
																				<?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
																		</button>
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px;">
																		<a href="#select_whitelist_file_to_upload" role="button" class="btn btn-secondary" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_( 'COM_SECURITYCHECKPRO_IMPORT_IPS' ); ?></a>								
																	</div>
																	<div class="btn-group pull-left" style="margin-left: 10px;">
																		<button class="btn btn-info" onclick="Joomla.submitbutton('Export_whitelist');" href="#">
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
														<!-- End Whitelist tab -->
														</div>														
													</div>													
												</div>
										</div>
									</div>
						<!-- End lists -->
						</div>
						
						<!-- Methods -->
						<div class="tab-pane" id="methods" role="tabpanel">
							<!-- Methods -->
							<div class="card mb-6">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-6 mb-6">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo methodslist('methods', array(), $this->methods); ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_DESCRIPTION') ?></small></footer></blockquote>												
											</div>
										</div>										
									</div>
								</div> 
							</div>
						<!-- End Methods -->
						</div>
						
						<!-- Mode -->
						<div class="tab-pane" id="mode" role="tabpanel">
							<!-- Methods -->
							<div class="card mb-6">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-6 mb-6">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_FIELDSET_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_LABEL'); ?></h4>										
												<div class="controls">
													<?php echo mode('mode', array(), $this->mode) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_DESCRIPTION') ?></small></footer></blockquote>												
											</div>
										</div>										
									</div>
								</div> 
							</div>
						<!-- End Mode -->
						</div>
						
						<!-- Logs -->
						<div class="tab-pane" id="logs" role="tabpanel">
							<!-- Methods -->
							<div class="card mb-6">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-6 mb-6">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOG_ATTACKS_DESCRIPTION'); ?></h4>						
												<div class="controls">
													<?php echo booleanlist('logs_attacks', array(), $this->logs_attacks) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LOG_ATTACKS_DESCRIPTION') ?></small></footer></blockquote>
												
												<h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD'); ?></h4>						
												<div class="controls">
													<input type="text" size="4" maxlength="4" id="scp_delete_period" name="scp_delete_period" value="<?php echo $this->scp_delete_period ?>" title="" />	
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD_DESC') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOG_LIMITS_PER_IP_AND_DAY_LABEL'); ?></h4>					
												<div class="controls">
													<input type="text" size="4" maxlength="4" id="log_limits_per_ip_and_day" name="log_limits_per_ip_and_day" value="<?php echo $this->log_limits_per_ip_and_day ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_ADD_GEOBLOCK_LOGS_LABEL'); ?></h4>					
												<div class="controls">
													<?php echo booleanlist('add_geoblock_logs', array(), $this->add_geoblock_logs) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ADD_GEOBLOCK_LOGS_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_ADD_ACCESS_ATTEMPTS_LOGS_LABEL'); ?></h4>					
												<div class="controls">
													<?php echo booleanlist('add_access_attempts_logs', array(), $this->add_access_attempts_logs) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ADD_ACCESS_ATTEMPTS_LOGS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>										
									</div>
								</div> 
							</div>
						<!-- End Logs -->
						</div>
						
						<!-- Redirection -->
						<div class="tab-pane" id="redirection" role="tabpanel">
							<!-- Methods -->
							<div class="card mb-6">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-6 mb-6">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('redirect_after_attack', array(), $this->redirect_after_attack) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_LABEL'); ?></h4>
												<div class="controls" id="redirect_options">
													<?php echo redirectionlist('redirect_options', array(), $this->redirect_options) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_DESCRIPTION') ?></small></footer></blockquote>
																																				
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_TEXT'); ?></h4>
												<?php 
													if ( version_compare(JVERSION, '3.20', 'lt') ) {										
												?>
													<div class="controls controls-row">
														<div class="input-prepend">
															<span class="add-on" style="background-color: #8EBBFF;"><?php echo $site_url ?></span>
															<input class="input-large" type="text" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
														</div>						
													</div>
												<?php } else {	?>
													<div class="input-group">
														<span class="input-group-addon" style="background-color: #8EBBFF;"><?php echo $site_url ?></span>
														<input type="text" class="form-control" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
													</div>											
												<?php } ?>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_EXPLAIN') ?></small></footer></blockquote>
																								
												<div class="control-group">
													<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_TEXT'); ?></h4>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_EXPLAIN') ?></small></footer></blockquote>													
													<?php 
													// IMPORT EDITOR CLASS
													jimport( 'joomla.html.editor' );

													// GET EDITOR SELECTED IN GLOBAL SETTINGS
													$config = JFactory::getConfig();
													$global_editor = $config->get( 'editor' );

													// GET USER'S DEFAULT EDITOR
													$user_editor = JFactory::getUser()->getParam("editor");

													if($user_editor && $user_editor !== 'JEditor') {
														$selected_editor = $user_editor;
													} else {
														$selected_editor = $global_editor;
													}

													// INSTANTIATE THE EDITOR
													$editor = JEditor::getInstance($selected_editor);
													
													// SET EDITOR PARAMS
													$params = array( 'smilies'=> '0' ,
														'style'  => '1' ,
														'layer'  => '0' ,
														'table'  => '0' ,
														'clear_entities'=>'0'
													);

													// DISPLAY THE EDITOR (name, html, width, height, columns, rows, bottom buttons, id, asset, author, params)
													echo $editor->display('custom_code', $this->custom_code, '600', '200', '10', '10', true, null, null, null, $params);
													?>													
												</div>
											</div>
										</div>										
									</div>
								</div> 
							</div>
						<!-- End Redirection -->
						</div>
						
						<!-- Second -->
						<div class="tab-pane" id="second" role="tabpanel">							
							<div class="card mb-3">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-4 mb-4">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('second_level', array(), $this->second_level) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_IF_PATTERN_LABEL'); ?></h4>
												<div class="controls">
													<?php echo secondredirectlist('second_level_redirect', array(), $this->second_level_redirect) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_IF_PATTERN_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LIMIT_WORDS_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="2" maxlength="2" id="second_level_limit_words" name="second_level_limit_words" value="<?php echo $this->second_level_limit_words ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LIMIT_WORDS_DESCRIPTION') ?></small></footer></blockquote>
																							
											</div>
										</div>										
										<div class="col-xl-6 mb-6">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_LABEL'); ?></h4>
												<div class="controls">
													<textarea cols="35" rows="3" name="second_level_words" style="width: 560px; height: 340px;"><?php echo $this->second_level_words ?></textarea>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_DESCRIPTION') ?></small></footer></blockquote>
																							
											</div>
										</div>
									</div>
								</div> 
							</div>
						<!-- End Second Redirection -->
						</div>
						
						<!-- Email notification -->
						<div class="tab-pane" id="email_notifications" role="tabpanel">							
							<div class="card mb-3">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ACTIVE_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('email_active', array(), $this->email_active) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ACTIVE_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_SUBJECT_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="30" name="email_subject" value="<?php echo $this->email_subject ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_SUBJECT_DESCRIPTION') ?></small></footer></blockquote>
																															
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_BODY_LABEL'); ?></h4>
												<div class="controls">
													<textarea cols="35" rows="3" name="email_body" ><?php echo $this->email_body ?></textarea>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_BODY_DESCRIPTION') ?></small></footer></blockquote>
																						
											</div>
										</div>	
											
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_TO_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="30" id="email_to" name="email_to" value="<?php echo $this->email_to ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_TO_DESCRIPTION') ?></small></footer></blockquote>											
												
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_DOMAIN_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="30" id="email_from_domain" name="email_from_domain" value="<?php echo $this->email_from_domain ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_DOMAIN_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_NAME_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="30" name="email_from_name" value="<?php echo $this->email_from_name ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_NAME_DESCRIPTION') ?></small></footer></blockquote>
																								
												<div class="controls">
													<input class="btn btn-primary" type="button" id="boton_test_email" value="<?php echo JText::_('COM_SECURITYCHECKPRO_SEND_EMAIL_TEST'); ?>" onclick= "Joomla.submitbutton('send_email_test');" />		
												</div>												
											</div>
										</div>
										
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ADD_APPLIED_RULE_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('email_add_applied_rule', array(), $this->email_add_applied_rule) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ADD_APPLIED_RULE_DESCRIPTION') ?></small></footer></blockquote>
																							
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_MAX_NUMBER_LABEL'); ?></h4>
												<div class="controls">
													<input type="text" size="3" maxlength="3" id="email_max_number" name="email_max_number" value="<?php echo $this->email_max_number ?>" title="" />		
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_MAX_NUMBER_DESCRIPTION') ?></small></footer></blockquote>
											</div>
										</div>
									</div>
								</div> 
							</div>
						<!-- End Email notification -->
						</div>
						
						<!-- Filter exceptions -->
						<div class="tab-pane" id="exceptions" role="tabpanel">							
							<div class="card mb-12">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-12 mb-12">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('exclude_exceptions_if_vulnerable', array(), $this->exclude_exceptions_if_vulnerable) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION') ?></small></footer></blockquote>
												
												<ul class="nav nav-tabs" role="tablist" id="ExceptionsTabs">
													<li class="nav-item" onclick="SetActiveTabExceptions('header_referer');">
														<a class="nav-link active" href="#header_referer" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL'); ?></a>
													</li>
													<li class="nav-item" onclick="SetActiveTabExceptions('base64');">
														<a class="nav-link" href="#base64" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL'); ?></a>
													</li>
													<li class="nav-item" onclick="SetActiveTabExceptions('xss');">
														<a class="nav-link" href="#xss" data-toggle="tab" role="tab"><?php echo JText::_('XSS'); ?></a>
													</li>
													<li class="nav-item" onclick="SetActiveTabExceptions('sql');">
														<a class="nav-link" href="#sql" data-toggle="tab" role="tab"><?php echo JText::_('SQL Injection'); ?></a>
													</li>
													<li class="nav-item" onclick="SetActiveTabExceptions('lfi');">
														<a class="nav-link" href="#lfi" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></a>
													</li>
													<li class="nav-item" onclick="SetActiveTabExceptions('secondlevel');">
														<a class="nav-link" href="#secondlevel" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL'); ?></a>
													</li>
												</ul>

												<div class="tab-content">
													<div class="tab-pane show active" id="header_referer" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL'); ?></h4>
														<div class="controls">
															<?php echo booleanlist('check_header_referer', array(), $this->check_header_referer) ?>
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_DESCRIPTION') ?></small></footer></blockquote>												
													</div>
													<div class="tab-pane" id="base64" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL'); ?></h4>
														<div class="controls">
															<?php echo booleanlist('check_base_64', array(), $this->check_base_64) ?>
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_DESCRIPTION') ?></small></footer></blockquote>
																												
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="base64_exceptions" style="width: 560px; height: 140px;"><?php echo $this->base64_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>											
													</div>
													<div class="tab-pane" id="xss" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_LABEL'); ?></h4>
														<div class="controls" id="strip_all_tags">
															<?php echo booleanlist_js('strip_all_tags', array(), $this->strip_all_tags) ?>
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_DESCRIPTION') ?></small></footer></blockquote>
																												
														<div class="control-group" id="tags_to_filter_div">
															<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_LABEL'); ?></h4>
															<div class="controls">
																<textarea cols="35" rows="3" name="tags_to_filter" style="width: 560px; height: 140px;"><?php echo $this->tags_to_filter ?></textarea>								
															</div>
															<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_DESCRIPTION') ?></small></footer></blockquote>
															
														</div>
														
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="strip_tags_exceptions" style="width: 560px; height: 140px;"><?php echo $this->strip_tags_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
														
													</div>
													<div class="tab-pane" id="sql" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="duplicate_backslashes_exceptions" style="width: 560px; height: 140px;"><?php echo $this->duplicate_backslashes_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																											
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="line_comments_exceptions" style="width: 560px; height: 140px;"><?php echo $this->line_comments_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																												
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="sql_pattern_exceptions" style="width: 560px; height: 140px;"><?php echo $this->sql_pattern_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																												
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="if_statement_exceptions" style="width: 560px; height: 140px;"><?php echo $this->if_statement_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																												
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="using_integers_exceptions" style="width: 560px; height: 140px;"><?php echo $this->using_integers_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																												
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="escape_strings_exceptions" style="width: 560px; height: 140px;"><?php echo $this->escape_strings_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
														
													</div>
													<div class="tab-pane" id="lfi" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="lfi_exceptions" style="width: 560px; height: 140px;"><?php echo $this->lfi_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
																											
													</div>
													<div class="tab-pane" id="secondlevel" role="tabpanel">
														<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL'); ?></h4>
														<div class="controls">
															<textarea cols="35" rows="3" name="second_level_exceptions" style="width: 560px; height: 140px;"><?php echo $this->second_level_exceptions ?></textarea>								
														</div>
														<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
														
													</div>
												</div>												
											</div>									
										</div>									
									</div>
								</div> 
							</div>
						<!-- End Filter exceptions -->
						</div>
						
						<!-- User session protection -->
						<div class="tab-pane" id="session_protection" role="tabpanel">							
							<div class="card mb-3">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL') ?>
											</div>
											<div class="card-body">
												<?php
													$params          = JFactory::getConfig();		
													$shared_session_enabled = $params->get('shared_session');
													
													if ( !$shared_session_enabled ) {
												?>
												
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_ACTIVE_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('session_protection_active', array(), $this->session_protection_active) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_ACTIVE_LABEL') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('session_hijack_protection', array(), $this->session_hijack_protection) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_LABEL'); ?></h4>
												<div class="controls">
													<?php
													// Listamos todos los grupos presentes en el sistema excepto el grupo 'Guest'
													$db = JFactory::getDBO();
													$query = "SELECT id,title from `#__usergroups` WHERE title != 'Guest'" ;			
													$db->setQuery( $query );
													$groups = $db->loadRowList();						
													foreach ($groups as $key=>$value) {							
														$options[] = JHTML::_('select.option', $value[0], $value[1]);							
													}
													echo JHTML::_('select.genericlist', $options, 'session_protection_groups[]', 'class="chosen-select-no-single" multiple="multiple"', 'value', 'text',  $this->session_protection_groups); 												
													?>					
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_DESCRIPTION') ?></small></footer></blockquote>
																								
												<?php
													} else {
												?>	
														<blockquote class="blockquote" id="launch_time_alert"><footer class="blockquote-footer"><span style="color: red;"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SHARED_SESSIONS_EANBLED') ?></span></small></footer></blockquote>														
												<?php		
													}
												?>
											</div>
										</div>	
										
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('track_failed_logins', array(), $this->track_failed_logins) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS_LABEL') ?></small></footer></blockquote>
																							
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGINS_TO_MONITORIZE_LABEL'); ?></h4>
												<div class="controls">
													<?php echo email_actions('logins_to_monitorize', array(), $this->logins_to_monitorize) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGINS_TO_MONITORIZE_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_WRITE_LOG_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('write_log', array(), $this->write_log) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_WRITE_LOG_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo actions_failed_login('actions_failed_login', array(), $this->actions_failed_login) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
										
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('PLG_SECURITYCHECKPRO_ADMIN_LOGINS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ON_BACKEND_LOGIN_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('email_on_admin_login', array(), $this->email_on_admin_login) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ON_BACKEND_LOGIN_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_ADMIN_FRONTEND_LOGIN_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('forbid_admin_frontend_login', array(), $this->forbid_admin_frontend_login) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_ADMIN_FRONTEND_LOGIN_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('forbid_new_admins', array(), $this->forbid_new_admins) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_NEW_ADMINS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
									</div>
								</div> 
							</div>
						<!-- End User session protection -->
						</div>
						
						<!-- Geoblock -->
						<div class="tab-pane" id="geoblock" role="tabpanel">
						
							<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.3/d3.min.js"></script>
							<script src="//cdnjs.cloudflare.com/ajax/libs/topojson/1.6.9/topojson.min.js"></script>
							<script src="/media/com_securitycheckpro/new/js/datamaps.world.min.js"></script>
							<div class="alert alert-info" style="text-align: center;">
								<strong><?php echo JText::_('COM_SECURITYCHECKPRO_MAP_ATTACKS_TEXT'); ?></strong>
							</div>
							
							<div class="card-block">	
								<div class="card-header text-center">
									<?php echo JText::_( 'COM_SECURITYCHECKPRO_COLOR_CODE' ) . " - " . JText::_( 'COM_SECURITYCHECKPRO_NUMBER_OF_ATTACKS' ); ?>
									<div style="margin-top: 20px;">
										<span class="label" style="background-color: green; color: black; padding: 4px 8px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_NOATTACKS' ); ?></span>
										<span class="label" style="background-color: #F3F781; color: black; padding: 4px 8px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_LOW' ); ?></span>
										<span class="label" style="background-color: #FF8000; color: black; padding: 4px 8px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MEDIUM' ); ?></span>
										<span class="label" style="background-color: #FF0000; color: black; padding: 4px 8px;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_HIGH' ); ?></span>
									</div>
								</div>														
							</div>	
													
							<div id="container" style="position: relative; width: 800px; height: 600px;"></div>
					
							<div class="card mb-3">															
								<div class="alert alert-info" style="text-align: center;">
									<strong><?php echo JText::_('COM_SECURITYCHECKPRO_COUNTRIES_BLOCKED'); ?></strong>									
								</div>
								
								<ul class="nav nav-tabs" role="tablist" id="ContinentTabs">
									<li class="nav-item" onclick="SetActiveTabContinent('europe');">
										<a class="nav-link active" href="#europe" data-anchor="europe" data-toggle="tab" role="tab">Europe</a>
									</li>
									<li class="nav-item" onclick="SetActiveTabContinent('northAmerica');">
										<a class="nav-link" href="#northAmerica" data-anchor="northAmerica" data-toggle="tab" role="tab">North America</a>
									</li>
									<li class="nav-item" onclick="SetActiveTabContinent('southAmerica');">
										<a class="nav-link" href="#southAmerica" data-anchor="southAmerica" data-toggle="tab" role="tab">South America</a>
									</li>
									<li class="nav-item" onclick="SetActiveTabContinent('africa');">
										<a class="nav-link" href="#africa" data-anchor="africa" data-toggle="tab" role="tab">Africa</a>
									</li>
									<li class="nav-item" onclick="SetActiveTabContinent('asia');">
										<a class="nav-link"href="#asia" data-anchor="asia" data-toggle="tab" role="tab">Asia</a>
									</li>
									<li class="nav-item" onclick="SetActiveTabContinent('oceania');">
										<a class="nav-link" href="#oceania" data-anchor="oceania" data-toggle="tab" role="tab">Oceania</a>
									</li>
								</ul>
								<div class="tab-content">
									<div id="europe" class="tab-pane show active">
										<?php																		
											$checked = in_array("EU", $this->continents) ? 'checked="$checked"' : '';						
										?>
										<input type="checkbox" style="visibility: hidden;" name="continent[EU]" id="continentEU" <?php echo $checked?> />
																
										<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('europe_table',true,'continentEU')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
										<button class="btn btn-small" type="button" onclick="CheckAll('europe_table',false,'continentEU')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
										<table id="europe_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
											<tbody>
												<?php
													$i = 0;
																	
													foreach($this->europe as $code => $name) {
														$i++;
														if(empty($name)) continue;
														$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
												?>
												<?php 
													if ( ($i % 4 == 0) && ($i<4) ) {
														echo '<tr>';
													}
													if ( $checked ) {		
												?>	
													<td class="marcado">
												<?php 
													} else {
												?>
												<td>
												<?php
													}
												?>							
												<?php echo $name; ?>
												<input type="checkbox" onclick="disable_continent_checkbox('continentEU','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
												</td>								
												<?php 
													if($i % 4 == 0) {
														echo '</tr>';
													}
												} 
												?>
											</tbody>
										</table>
									</div>
																	
																	<div id="northAmerica" class="tab-pane">
																		<?php																		
																		$checked = in_array("NA", $this->continents) ? 'checked="$checked"' : '';						
																		?>
																		<input type="checkbox" style="visibility: hidden;" name="continent[NA]" id="continentNA" <?php echo $checked?> />
																	
																		<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('northamerica_table',true,'continentNA')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
																		<button class="btn btn-small" type="button" onclick="CheckAll('northamerica_table',false,'continentNA')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
																		<table id="northamerica_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
																			<tbody>
																				<?php
																				$i = 0;
																				
																				foreach($this->northamerica as $code => $name) {
																					$i++;
																					if(empty($name)) continue;
																					$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
																				?>
																				<?php 
																					if ( ($i % 4 == 0) && ($i<4) ) {
																						echo '<tr>';
																					}
																					if ( $checked ) {		
																				?>	
																						<td class="marcado">
																				<?php 
																					} else {
																				?>
																						<td>
																				<?php
																					}
																				?>							
																						<?php echo $name; ?>
																						<input type="checkbox" onclick="disable_continent_checkbox('continentNA','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
																					</td>								
																				<?php 
																					if($i % 4 == 0) {
																						echo '</tr>';
																					}
																				} 
																				?>
																			</tbody>
																		</table>
																	</div>
																	
																	<div id="southAmerica" class="tab-pane">
																		<?php																		
																		$checked = in_array("SA", $this->continents) ? 'checked="$checked"' : '';						
																		?>
																		<input type="checkbox" style="visibility: hidden;" name="continent[SA]" id="continentSA" <?php echo $checked?> />
																	
																		<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('southamerica_table',true,'continentSA')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
																		<button class="btn btn-small" type="button" onclick="CheckAll('southamerica_table',false,'continentSA')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
																		<table id="southamerica_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
																			<tbody>
																				<?php
																				$i = 0;
																				
																				foreach($this->southamerica as $code => $name) {
																					$i++;
																					if(empty($name)) continue;
																					$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
																				?>
																				<?php 
																					if ( ($i % 4 == 0) && ($i<4) ) {
																						echo '<tr>';
																					}
																					if ( $checked ) {		
																				?>	
																						<td class="marcado">
																				<?php 
																					} else {
																				?>
																						<td>
																				<?php
																					}
																				?>							
																						<?php echo $name; ?>
																						<input type="checkbox" onclick="disable_continent_checkbox('continentSA','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
																					</td>								
																				<?php 
																					if($i % 4 == 0) {
																						echo '</tr>';
																					}
																				} 
																				?>
																			</tbody>
																		</table>
																	</div>
																	
																	<div id="africa" class="tab-pane">
																		<?php																		
																		$checked = in_array("AF", $this->continents) ? 'checked="$checked"' : '';						
																		?>
																		<input type="checkbox" style="visibility: hidden;" name="continent[AF]" id="continentAF" <?php echo $checked?> />
																	
																		<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('africa_table',true,'continentAF')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
																		<button class="btn btn-small" type="button" onclick="CheckAll('africa_table',false,'continentAF')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
																		<table id="africa_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
																			<tbody>
																				<?php
																				$i = 0;
																				
																				foreach($this->africa as $code => $name) {
																					$i++;
																					if(empty($name)) continue;
																					$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
																				?>
																				<?php 
																					if ( ($i % 4 == 0) && ($i<4) ) {
																						echo '<tr>';
																					}
																					if ( $checked ) {		
																				?>	
																						<td class="marcado">
																				<?php 
																					} else {
																				?>
																						<td>
																				<?php
																					}
																				?>							
																						<?php echo $name; ?>
																						<input type="checkbox" onclick="disable_continent_checkbox('continentAF','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
																					</td>								
																				<?php 
																					if($i % 4 == 0) {
																						echo '</tr>';
																					}
																				} 
																				?>
																			</tbody>
																		</table>
																	</div>
																	
																	<div id="asia" class="tab-pane">
																		<?php																		
																		$checked = in_array("AS", $this->continents) ? 'checked="$checked"' : '';						
																		?>
																		<input type="checkbox" style="visibility: hidden;" name="continent[AS]" id="continentAS" <?php echo $checked?> />
																	
																		<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('asia_table',true,'continentAS')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
																		<button class="btn btn-small" type="button" onclick="CheckAll('asia_table',false,'continentAS')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
																		<table id="asia_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
																			<tbody>
																				<?php
																				$i = 0;
																				
																				foreach($this->asia as $code => $name) {
																					$i++;
																					if(empty($name)) continue;
																					$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
																				?>
																				<?php 
																					if ( ($i % 4 == 0) && ($i<4) ) {
																						echo '<tr>';
																					}
																					if ( $checked ) {		
																				?>	
																						<td class="marcado">
																				<?php 
																					} else {
																				?>
																						<td>
																				<?php
																					}
																				?>							
																						<?php echo $name; ?>
																						<input type="checkbox" onclick="disable_continent_checkbox('continentAS','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
																					</td>								
																				<?php 
																					if($i % 4 == 0) {
																						echo '</tr>';
																					}
																				} 
																				?>
																			</tbody>
																		</table>
																	</div>
																	
																	<div id="oceania" class="tab-pane">
																		<?php																		
																		$checked = in_array("OC", $this->continents) ? 'checked="$checked"' : '';						
																		?>
																		<input type="checkbox" style="visibility: hidden;" name="continent[OC]" id="continentOC" <?php echo $checked?> />
																	
																		<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('oceania_table',true,'continentOC')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
																		<button class="btn btn-small" type="button" onclick="CheckAll('oceania_table',false,'continentOC')"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
													
																		<table id="oceania_table" class="table table-striped table-bordered bootstrap-datatable datatable" style="margin-top: 10px;">
																			<tbody>
																				<?php
																				$i = 0;
																				
																				foreach($this->oceania as $code => $name) {
																					$i++;
																					if(empty($name)) continue;
																					$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
																				?>
																				<?php 
																					if ( ($i % 4 == 0) && ($i<4) ) {
																						echo '<tr>';
																					}
																					if ( $checked ) {		
																				?>	
																						<td class="marcado">
																				<?php 
																					} else {
																				?>
																						<td>
																				<?php
																					}
																				?>							
																						<?php echo $name; ?>
																						<input type="checkbox" onclick="disable_continent_checkbox('continentOC','country<?php echo $code?>')" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
																					</td>								
																				<?php 
																					if($i % 4 == 0) {
																						echo '</tr>';
																					}
																				} 
																				?>
																			</tbody>
																		</table>
																	</div>
								</div>
							</div>
																
						<!-- End Geoblock -->
						</div>
						
						<!-- Upload scanner -->
						<div class="tab-pane" id="upload_scanner" role="tabpanel">							
							<div class="card mb-3">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('upload_scanner_enabled', array(), $this->upload_scanner_enabled) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_CHECK_MULTIPLE_EXTENSIONS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('check_multiple_extensions', array(), $this->check_multiple_extensions) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_CHECK_MULTIPLE_EXTENSIONS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
										
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_EXTENSIONS_BLACKLIST_LABEL'); ?></h4>
												<div class="controls">
													<textarea cols="35" rows="3" name="extensions_blacklist" style="width: 260px; height: 140px;"><?php echo $this->extensions_blacklist ?></textarea>								
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_EXTENSIONS_BLACKLIST_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DELETE_FILES_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('delete_files', array(), $this->delete_files) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DELETE_FILES_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
										
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo actions('actions_upload_scanner', array(), $this->actions_upload_scanner) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
									</div>
								</div>
							</div>
						<!-- End Upload scanner -->
						</div>
						
						<!-- Spam protection -->
						<div class="tab-pane" id="spam_protection" role="tabpanel">
							<?php if ( $this->plugin_installed ) { ?>							
								<div class="card mb-3">
									<div class="card-body">
										<div class="row">
											<div class="col-xl-3 mb-3">
												<div class="card-header text-white bg-primary">
													<?php echo JText::_('COM_SECURITYCHECKPRO_CHECK_USERS') ?>
												</div>
												<div class="card-body">
															
													<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_IF_USER_IS_SPAMMER_LABEL'); ?></h4>
													<div class="controls">
														<?php echo booleanlist('check_if_user_is_spammer', array(), $this->check_if_user_is_spammer) ?>
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_IF_USER_IS_SPAMMER_DESCRIPTION') ?></small></footer></blockquote>
																										
													<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_ACTION_LABEL'); ?></h4>
													<div class="controls">
														<?php echo spammer_action('spammer_action', array(), $this->spammer_action) ?>
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_ACTION_DESCRIPTION') ?></small></footer></blockquote>
																										
													<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WRITE_LOG_LABEL'); ?></h4>
													<div class="controls">
														<?php echo booleanlist('spammer_write_log', array(), $this->spammer_write_log) ?>
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WRITE_LOG_DESCRIPTION') ?></small></footer></blockquote>
													
												</div>
											</div>
											
											<div class="col-xl-3 mb-3">
												<div class="card-header text-white bg-primary">
													<?php echo JText::_('COM_SECURITYCHECKPRO_CHECK_USERS') ?>
												</div>
												<div class="card-body">
													<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WHAT_TO_CHECK_LABEL'); ?></h4>
													<div class="controls">
														<?php
														$options_spam[] = JHTML::_('select.option', 0, JText::_('PLG_SECURITYCHECKPRO_EMAIL'));							
														$options_spam[] = JHTML::_('select.option', 1, JText::_('PLG_SECURITYCHECKPRO_IP'));
														$options_spam[] = JHTML::_('select.option', 2, JText::_('PLG_SECURITYCHECKPRO_USERNAME'));
														if ( !is_array($this->spammer_what_to_check) ) {							
															$this->spammer_what_to_check = array('Email','IP','Username');
														}						
														echo JHTML::_('select.genericlist', $options_spam, 'spammer_what_to_check[]', 'class="chosen-select-no-single" multiple="multiple"', 'text', 'text',  $this->spammer_what_to_check);												
														?>					
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WHAT_TO_CHECK_DESCRIPTION') ?></small></footer></blockquote>
																										
													<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_LABEL'); ?></h4>
													<div class="controls">
														<input type="text" size="3" maxlength="3" id="spammer_limit" name="spammer_limit" value="<?php echo $this->spammer_limit ?>" title="" />	
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_DESCRIPTION') ?></small></footer></blockquote>
													
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="alert alert-warning centrado">
									<?php echo JText::_('COM_SECURITYCHECK_SPAM_PROTECTION_NOT_INSTALLED'); ?>	
								</div>
								<div class="alert alert-info centrado">
									<?php echo JText::_('COM_SECURITYCHECK_WHY_IS_NOT_INCLUDED'); ?>	
								</div>
							<?php }  ?>
						<!-- End spam protection -->
						</div>
						
						<!-- Url inspector -->
						<div class="tab-pane" id="url_inspector" role="tabpanel">
							<?php if ($this->url_inspector_enabled == 0) { ?>
							<div class="alert alert-warning centrado">
								<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_URL_INPECTOR_DISABLED' ); ?></h4>
								<button class="btn btn-success" onclick="Joomla.submitbutton('enable_url_inspector')" href="#">
									<i class="icon-ok icon-white"> </i>
										<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
								</button>			
							</div>
							<?php } ?>
							<div class="card mb-3">
								<div class="card-body">
									<div class="row">
										<div class="col-xl-3 mb-3">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
																									
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_WRITE_LOG_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('write_log_inspector', array(), $this->write_log_inspector) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_WRITE_LOG_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></h4>
												<div class="controls">
													<?php echo action('action_inspector', array(), $this->action_inspector) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_ACTION_DESCRIPTION') ?></small></footer></blockquote>
																								
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_SEND_EMAIL_LABEL'); ?></h4>
												<div class="controls">
													<?php echo booleanlist('send_email_inspector', array(), $this->send_email_inspector) ?>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_SEND_EMAIL_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
										
										<div class="col-xl-8 mb-8">
											<div class="card-header text-white bg-primary">
												<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
											</div>
											<div class="card-body">
																									
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_LABEL'); ?></h4>
												<div class="controls">
													<textarea cols="35" rows="3" name="inspector_forbidden_words" style="width: 560px; height: 340px;"><?php echo $this->inspector_forbidden_words ?></textarea>
												</div>
												<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_DESCRIPTION') ?></small></footer></blockquote>
												
											</div>
										</div>
									</div>
								</div>
							</div>							
						<!-- End Url inspector -->
						</div>
						
						<!-- Track actions -->
						<div class="tab-pane" id="track_actions" role="tabpanel">
							<?php if ( $this->plugin_trackactions_installed) { ?>
								<div class="card mb-3">
									<div class="card-body">
										<div class="row">
											<div class="col-xl-3 mb-3">
												<div class="card-header text-white bg-primary">
													<?php echo JText::_('PLG_TRACKACTIONS_LABEL') ?>
												</div>
												<div class="card-body">
																									
													<h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD'); ?></h4>
													<div class="controls">
														<input type="text" size="3" maxlength="3" id="delete_period" name="delete_period" value="<?php echo $this->delete_period ?>" title="" />	
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD_DESC') ?></small></footer></blockquote>
																										
													<h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_IP_LOGGING'); ?></h4>
													<div class="controls">
														<?php echo booleanlist('ip_logging', array(), $this->ip_logging) ?>
													</div>
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_IP_LOGGING_DESC') ?></small></footer></blockquote>
												</div>
											</div>
											
											<div class="col-xl-3 mb-3">
												<div class="card-header text-white bg-primary">
													<?php echo JText::_('PLG_TRACKACTIONS_LABEL') ?>
												</div>
												<div class="card-body">
													<h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_EXTENSIONS'); ?></h4>
													<div class="controls">
														<?php
														// Listamos todas las extensiones 
														$db = JFactory::getDBO();
														$query = "SELECT extension from `#__securitycheckpro_trackactions_extensions`" ;			
														$db->setQuery( $query );
														$groups = $db->loadRowList();	
														foreach ($groups as $key=>$value) {								
															$options_trackactions[] = JHTML::_('select.option', $value[0], $value[0]);							
														}
														echo JHTML::_('select.genericlist', $options_trackactions, 'loggable_extensions[]', 'class="chosen-select-no-single" multiple="multiple"', 'value', 'text',  $this->loggable_extensions); 												
														?>					
													</div>	
													<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_EXTENSIONS_DESC') ?></small></footer></blockquote>												
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php } else { ?>
									<div class="alert alert-warning centrado">
										<?php echo JText::_('COM_SECURITYCHECKPRO_TRACKACTIONS_NOT_INSTALLED'); ?>	
									</div>	
								<?php }  ?>
							
						<!-- End Track actions -->
						</div>						
						
					<!-- End Tab content -->		
					</div>
				<!-- End card body -->	
				</div>
			</div>
		<!-- End container fluid -->		
		</div>
<!-- End Wrapper -->			
</div>		

 <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Custom scripts for all pages -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sb-admin.js"></script>
<!-- Chosen scripts -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.jquery.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/init.js"></script>

<script>
	var cont = 1;
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href") // activated tab
		
		var data_json = <?php echo $this->logs_by_country; ?>;
		
		if ( target == "#geoblock" ) {			
			if (cont == 1) {
				//var map = new Datamap({element: document.getElementById('container')});
				map = new Datamap({
					scope: 'world',
					element: document.getElementById('container'),
					projection: 'mercator',
					fills: {
						HIGH: '#FF0000',
						LOW: '#F3F781',
						MEDIUM: '#FF8000',
						UNKNOWN: 'rgb(0,0,0)',
						defaultFill: 'green'
					},
					data:	data_json
					,geographyConfig: {
						popupTemplate: function(geo, data) {
							return ['<div class="hoverinfo" style="text-align: center;"><strong>',
									geo.properties.name,
									'<br/>' + data.numberOfThings,
									'</strong></div>'].join('');
						},
						highlightFillColor: 'blue',
					}
				});
				cont++;
			}
		}
	});
</script>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallconfig" />
</form>