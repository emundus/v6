<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

function xframeoptions( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  'NO', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  'DENY', JText::_( 'COM_SECURITYCHECKPRO_XFRAME_OPTIONS_DENY' ) ),
		JHTML::_('select.option',  'SAMEORIGIN', JText::_( 'COM_SECURITYCHECKPRO_XFRAME_OPTIONS_SAMEORIGIN' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id );
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

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$site_url = JURI::base();

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
 <!-- Chosen styles -->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.css" rel="stylesheet">

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {	
	
		// Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
		$(function() {
			$("#buttonclose").click(function() {
				setTimeout(function () {window.location.reload()},1000);				
			});
		});			
		
	});		
</script>

<script type="text/javascript" language="javascript">

var Password = {
 
  _pattern : /[a-zA-Z0-9]/, 
  
  _getRandomByte : function()
  {
    // http://caniuse.com/#feat=getrandomvalues
    if(window.crypto && window.crypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.crypto.getRandomValues(result);
      return result[0];
    }
    else if(window.msCrypto && window.msCrypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.msCrypto.getRandomValues(result);
      return result[0];
    }
    else
    {
      return Math.floor(Math.random() * 256);
    }
  },
  
  generate : function(length)
  {
    return Array.apply(null, {'length': length})
      .map(function()
      {
        var result;
        while(true) 
        {
          result = String.fromCharCode(this._getRandomByte());
          if(this._pattern.test(result))
          {
            return result;
          }
        }        
      }, this)
      .join('');  
  }    
    
};
</script>

<script type="text/javascript" language="javascript">

/*window.addEvent('domready', function() {
	hideIt();
});*/

var ActiveTabHtaccess = "autoprotection";

function SetActiveTabHtaccess($value) {
	ActiveTabHtaccess = $value;
	storeValue('active_htaccess', ActiveTabHtaccess);
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
	ActiveTabHtaccess = getStoredValue('active_htaccess');
			
	if (ActiveTabHtaccess) {
		$('.nav-tabs a[href="#' + ActiveTabHtaccess + '"]').parent().addClass('active');
		$('.nav-tabs a[href="#' + ActiveTabHtaccess + '"]').tab('show');
	} else {
		$('.nav-tabs a[href="#autoprotection"]').parent().addClass('active');
	}			
};

function add_exception() {
	var exception = document.adminForm.exception.value;
	
	var previous_exceptions = (document.adminForm.backend_exceptions.value).length;
	
	if (previous_exceptions > 0 ) {
		document.adminForm.backend_exceptions.value += ',' + exception;
	} else {
		document.adminForm.backend_exceptions.value += exception;
	}
	document.adminForm.exception.value = "";
}

function delete_exception() {
	var exception = document.adminForm.exception.value;
	
	var textarea = document.getElementById("backend_exceptions");
	
	// Borramos todas las opciones posibles, comas delante y detrás y sin comas
	textarea.value = textarea.value.replace(',' + exception, "");	
	textarea.value = textarea.value.replace(exception + ',', "");	
	textarea.value = textarea.value.replace(exception, "");
	
	document.adminForm.exception.value = "";
}

function delete_all() {
	var exception = document.adminForm.exception.value;
	
	var textarea = document.getElementById("backend_exceptions");
	
	textarea.value = "";	
}

function muestra_default_user_agent(){
		jQuery("#div_default_user_agents").modal('show');			
}

function hideIt(){
	var selected = document.getElementById('backend_protection_applied');
	if (selected.checked) {
		jQuery("#menu_hide_backend_1").hide();
		jQuery("#menu_hide_backend_2").hide();
		jQuery("#menu_hide_backend_3").hide();
		jQuery("#menu_hide_backend_4").hide();
		document.getElementById("hide_backend_url").value = "";
		document.getElementById("backend_exceptions").value = "";		
	} else {
		jQuery("#menu_hide_backend_1").show();
		jQuery("#menu_hide_backend_2").show();
		jQuery("#menu_hide_backend_3").show();
		jQuery("#menu_hide_backend_4").show();
	}	
}
	
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=protection&view=protection&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<?php 
		
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
		
		  <!-- Breadcrumb-->
		  <ol class="breadcrumb">
			<li class="breadcrumb-item">
			  <a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
			</li>			
			<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HTACCESS_PROTECTION_TEXT'); ?></li>
		  </ol>
		  
			<?php
				if ( ($this->server == 'apache') || ($this->server == 'iis') ){
			?>
			<div class="alert alert-warn">
				<?php echo JText::_('COM_SECURITYCHECKPRO_USER_AGENT_INTRO'); ?>
			</div>
			<div class="alert alert-error">
				<?php echo JText::_('COM_SECURITYCHECKPRO_USER_AGENT_WARN'); ?>	
			</div>
			<div class="alert alert-info">
			<?php if($this->ExistsHtaccess) { 
					echo JText::_('COM_SECURITYCHECKPRO_USER_AGENT_HTACCESS');
				  } else { 
					echo JText::_('COM_SECURITYCHECKPRO_USER_AGENT_NO_HTACCESS');
			} ?>
			</div>
			<?php
				} else if ($this->server == 'nginx'){
			?>
			<div class="alert alert-error">
				<?php echo JText::_('COM_SECURITYCHECKPRO_NGINX_SERVER'); ?>	
			</div>
			<?php
				}
			?>
			
			<!-- Contenido principal -->			
			<div>	
				<ul class="nav nav-tabs" role="tablist" id="protectionTab">
					<li class="nav-item" onclick="SetActiveTabHtaccess('autoprotection');">
						<a class="nav-link active" href="#autoprotection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_PROTECTION_AUTOPROTECTION_TEXT'); ?></a>
					</li>
					<li class="nav-item" onclick="SetActiveTabHtaccess('headers_protection');">
						<a class="nav-link" href="#headers_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_PROTECTION_TEXT'); ?></a>
					</li>
					<li class="nav-item" onclick="SetActiveTabHtaccess('user_agents_protection');">
						<a class="nav-link" href="#user_agents_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_PROTECTION_USER_AGENTS_TEXT'); ?></a>
					</li>
					<li class="nav-item" onclick="SetActiveTabHtaccess('fingerprinting');">
						<a class="nav-link" href="#fingerprinting" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_FINGERPRINTING_PROTECTION_TEXT'); ?></a>
					</li>
					<li class="nav-item" onclick="SetActiveTabHtaccess('backend_protection');">
						<a class="nav-link" href="#backend_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_BACKEND_PROTECTION_TEXT'); ?></a>
					</li>
					<li class="nav-item" onclick="SetActiveTabHtaccess('performance_tab');">
						<a class="nav-link" href="#performance_tab" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_PERFORMANCE'); ?></a>
					</li>					
				</ul>
				
				<div class="tab-content">
					<div class="tab-pane show active" id="autoprotection" role="tabpanel">
						<div class="control-group">
							<label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('prevent_access', array(), $this->protection_config['prevent_access']) ?>
								<?php if ( $this->config_applied['prevent_access'] ) {?>
									<span class="help-inline">
									<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('prevent_unauthorized_browsing', array(), $this->protection_config['prevent_unauthorized_browsing']) ?>
								<?php if ( $this->config_applied['prevent_unauthorized_browsing'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('file_injection_protection', array(), $this->protection_config['file_injection_protection']) ?>
								<?php if ( $this->config_applied['file_injection_protection'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('self_environ', array(), $this->protection_config['self_environ']) ?>
								<?php if ( $this->config_applied['self_environ'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN') ?></small></p></blockquote>
						</div>	
					<!-- autoprotection tab end -->
					</div>
					
					<div class="tab-pane" id="headers_protection" role="tabpanel">
						<div class="alert alert-error">
							<?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_EXPLAIN'); ?>	
						</div>
						
						<div class="control-group">
							<label for="xframe_options" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo xframeoptions('xframe_options', array(), $this->protection_config['xframe_options']) ?>
								<?php if ( $this->config_applied['xframe_options'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN') ?></small></p></blockquote>
						</div>
						<div class="control-group">
							<label for="prevent_mime_attacks" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('prevent_mime_attacks', array(), $this->protection_config['prevent_mime_attacks']) ?>
								<?php if ( $this->config_applied['prevent_mime_attacks'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN') ?></small></p></blockquote>
						</div>
					<!-- headers_protection tab end -->
					</div>
						
					<div class="tab-pane" id="user_agents_protection" role="tabpanel">
					
						<!-- View default user agent list -->
						<?php $default = JFile::read(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'user_agent_blacklist.inc'); ?>
						<div class="modal" id="div_default_user_agents" tabindex="-1" role="dialog" aria-labelledby="defaultuseragentsLabel" aria-hidden="true">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header alert alert-info">
										<h2 class="modal-title" id="defaultuseragentsLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_CONTENT'); ?></h2>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body" style="overflow-y: scroll;">
										<div class="color_rojo">
											<?php echo JText::_('COM_SECURITYCHECKPRO_WARNING_CHANGES_USER_AGENTS'); ?>
										</div>
										<br/>
										<textarea id="file_info" name="file_info" rows="20" style="width: 800px;"><?php echo $default; ?></textarea>								
									</div>
									<div class="modal-footer">					
										<input class="btn btn-success" type="button" id="boton_guardar" value="<?php echo JText::_('COM_SECURITYCHECKPRO_SAVE_CLOSE'); ?>" onclick= " Joomla.submitbutton('save_default_user_agent');" />
										<button type="button" class="btn dtn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
									</div>			  
								</div>
							</div>
						</div>						
						
						<div class="control-group">
							<label for="default_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('default_banned_list', array(), $this->protection_config['default_banned_list']) ?>
								<?php if ( $this->config_applied['default_banned_list'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<input class="btn btn-primary" style="margin-bottom: 10px;" type="button" id="boton_deafult_user_agent" value="<?php echo JText::_('COM_SECURITYCHECKPRO_EDIT_DEFAULT_USER_AGENTS'); ?>" onclick= "muestra_default_user_agent();" />				
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_TEXT'); ?></label>
							<div class="controls controls-row">
								<textarea rows="5" cols="55" name="own_banned_list" id="own_banned_list"><?php echo $this->protection_config['own_banned_list'] ?></textarea>
								<?php if ( $this->config_applied['own_banned_list'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="own_code" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_TEXT'); ?></label>
							<div class="controls controls-row">
								<textarea rows="5" cols="110" name="own_code" id="own_code"><?php echo $this->protection_config['own_code'] ?></textarea>
								<?php if ( $this->config_applied['own_code'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_EXPLAIN') ?></small></p></blockquote>
						</div>
					<!-- headers_protection tab end -->
					</div>						
		
					<div class="tab-pane" id="fingerprinting" role="tabpanel">
						<div class="alert alert-error">
							<?php echo JText::_('COM_SECURITYCHECKPRO_FINGERPRINTING_EXPLAIN'); ?>	
						</div>
						<div class="control-group">
							<label for="default_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('disable_server_signature', array(), $this->protection_config['disable_server_signature']) ?>
								<?php if ( $this->config_applied['disable_server_signature'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN') ?></small></p></blockquote>
						</div>
						<div class="control-group">
							<label for="disallow_php_eggs" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('disallow_php_eggs', array(), $this->protection_config['disallow_php_eggs']) ?>
								<?php if ( $this->config_applied['disallow_php_eggs'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>				
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN') ?></small></p></blockquote>
						</div>
						<div class="control-group">
							<label for="disallow_sensible_files_access" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_TEXT'); ?></label>
							<div class="controls controls-row">
							<?php if ( empty($this->protection_config['disallow_sensible_files_access']) ) {
									$this->protection_config['disallow_sensible_files_access'] = "htaccess.txt" . PHP_EOL . "configuration.php(-dist)?" . PHP_EOL . "joomla.xml" . PHP_EOL . "README.txt" . PHP_EOL . "web.config.txt" . PHP_EOL . "CONTRIBUTING.md" . PHP_EOL . "phpunit.xml.dist" . PHP_EOL . "plugin_googlemap2_proxy.php";
							}?>
								<textarea rows="5" cols="110" name="disallow_sensible_files_access" id="disallow_sensible_files_access"><?php echo $this->protection_config['disallow_sensible_files_access'] ?></textarea>
								<?php if ( $this->config_applied['disallow_sensible_files_access'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_EXPLAIN') ?></small></p></blockquote>
						</div>	
					<!-- fingerprinting tab end -->
					</div>
					
					<div class="tab-pane" id="backend_protection" role="tabpanel">
						<div class="control-group">
							<label for="hide_backend_url" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_TEXT'); ?></label>
							<div class="controls controls-row">
								<input id="backend_protection_applied" name="backend_protection_applied" type="checkbox" onchange="hideIt();" <?php if ($this->protection_config['backend_protection_applied']) { ?> checked <?php } ?> />
							</div>
							<blockquote><p class="text-info" style="margin-bottom: 10px;"><small><?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div id="menu_hide_backend_1" class="alert alert-error">
							<?php echo JText::_('COM_SECURITYCHECKPRO_BACKEND_PROTECTION_EXPLAIN'); ?>	
						</div>
						
						<div id="menu_hide_backend_2" class="control-group">
							<label for="hide_backend_url" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_TEXT'); ?></label>
							<div class="controls controls-row">
								<div class="input-prepend">
									<span class="add-on" style="background-color: #FFBF60;"><?php echo $site_url ?>?</span>
									<input class="input-large" type="text" name="hide_backend_url" id="hide_backend_url" value="<?php echo $this->protection_config['hide_backend_url']?>" placeholder="<?php echo $this->protection_config['hide_backend_url'] ?>">
								</div>
								<?php
								// Obtenemos la longitud de la clave que tenemos que generar
								$params = JComponentHelper::getParams('com_securitycheckpro');
								$size = $params->get('secret_key_length',20);				
								?>
								<input type='button' class="btn btn-primary" value='<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?>' onclick='document.getElementById("hide_backend_url").value = Password.generate(<?php echo $size; ?>)' />				
								<?php if ( $this->config_applied['hide_backend_url'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>				
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_EXPLAIN') ?></small></p></blockquote>				
						</div>
						
						<div id="menu_hide_backend_3" class="control-group">
							<label for="hide_backend_url_redirection" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_TEXT'); ?></label>
							<div class="controls controls-row">
								<div class="input-prepend">
									<span class="add-on" style="background-color: #D0F5A9;"><?php echo "/" ?></span>
									<input class="input-large" type="text" name="hide_backend_url_redirection" id="hide_backend_url_redirection" value="<?php echo $this->protection_config['hide_backend_url_redirection']?>" placeholder="not_found">
								</div>							
								<?php if ( $this->config_applied['hide_backend_url_redirection'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>				
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_EXPLAIN') ?></small></p></blockquote>	
						</div>
						
						<div id="menu_hide_backend_4" class="control-group">
							<label for="add_exception" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_EXCEPTIONS') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_EXCEPTIONS'); ?></label>
							<div class="controls controls-row">
								<textarea readonly rows="5" cols="110" name="backend_exceptions" id="backend_exceptions"><?php echo $this->protection_config['backend_exceptions'] ?></textarea>
								<div class="input-append">
									<input class="span8" type="text" name="exception" id="exception" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_YOUR_EXCEPTION_HERE') ?>">
									<div class="btn-group">
										<button class="btn dropdown-toggle" data-toggle="dropdown">
											<?php echo JText::_('COM_SECURITYCHECKPRO_ACTIONS') ?>
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a href="#backend_exceptions" onclick="add_exception();"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_ADD_EXCEPTION_TEXT') ?></a>
											</li>							
											<li>
												<a href="#backend_exceptions" onclick="delete_exception();"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_DELETE_EXCEPTION_TEXT') ?></a>
											</li>
											<li>
												<a href="#backend_exceptions" onclick="delete_all();"><?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_ALL') ?></a>
											</li>
										</ul>						
									</div>
									<?php if ( $this->config_applied['backend_exceptions'] ) {?>
										<span class="help-inline">
											<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
										</span>
									<?php } ?>									
								</div>				
							</div>		
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_ADD_EXCEPTION_EXPLAIN') ?></small></p></blockquote>
						</div>
					<!-- backend_protection tab end -->
					</div>
					
					<div class="tab-pane" id="performance_tab" role="tabpanel">
						<div class="control-group">
							<label for="optimal_expiration_time" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('optimal_expiration_time', array(), $this->protection_config['optimal_expiration_time']) ?>
								<?php if ( $this->config_applied['optimal_expiration_time'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="compress_content" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('compress_content', array(), $this->protection_config['compress_content']) ?>
								<?php if ( $this->config_applied['compress_content'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_EXPLAIN') ?></small></p></blockquote>
						</div>
						
						<div class="control-group">
							<label for="redirect_to_www" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_TEXT'); ?></label>
							<div class="controls controls-row">
								<?php echo booleanlist('redirect_to_www', array(), $this->protection_config['redirect_to_www']) ?>
								<?php if ( $this->config_applied['redirect_to_www'] ) {?>
									<span class="help-inline">
										<span class="badge badge-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
									</span>
								<?php } ?>
							</div>
							<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_EXPLAIN') ?></small></p></blockquote>
						</div>					
					<!-- performance tab end -->
					</div>
				<!-- tab-content end -->
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
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" id="task" value="save" />
</form>