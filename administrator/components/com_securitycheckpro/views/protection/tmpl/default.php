<?php 

/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JSession::checkToken('get') or die('Invalid Token');

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_NO')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_YES'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function xframeoptions( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  'NO', JText::_('COM_SECURITYCHECKPRO_NO')),
    JHTML::_('select.option',  'DENY', JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_DENY')),
    JHTML::_('select.option',  'SAMEORIGIN', JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_SAMEORIGIN'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id);
}

// Cargamos los archivos javascript necesarios
$document = JFactory::getDocument();
if ( version_compare(JVERSION, '3.20', 'lt') )
{	
	$document->addScript(JURI::root().'media/system/js/core.js');
}

$document->addScript(JURI::root().'media/com_securitycheckpro/new/js/sweetalert.min.js');
// Bootstrap core JavaScript
// Inline javascript to avoid deferring in Joomla 4
echo '<script src="' . JURI::root(). '/media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>';
//$document->addScript(JURI::root().'media/com_securitycheckpro/new/vendor/popper/popper.min.js');


// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$site_url = JURI::base();

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$chosen = "media/com_securitycheckpro/new/vendor/chosen/chosen.css";
JHTML::stylesheet($chosen);
?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/protection.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=protection&view=protection&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<?php 
        
        // Cargamos la navegación
        require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
?>
        
          <!-- Breadcrumb-->
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
            </li>            
            <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HTACCESS_PROTECTION_TEXT'); ?></li>
          </ol>
          
    <?php
    if (($this->server == 'apache') || ($this->server == 'iis') ) {
        ?>
            <div class="alert alert-warning">
        <?php echo JText::_('COM_SECURITYCHECKPRO_USER_AGENT_INTRO'); ?>
            </div>
            <div class="alert alert-danger">
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
    } else if ($this->server == 'nginx') {
        ?>
            <div class="alert alert-danger">
        <?php echo JText::_('COM_SECURITYCHECKPRO_NGINX_SERVER'); ?>    
            </div>
        <?php
    }
    ?>
            
            <!-- Contenido principal -->            
            <div class="overflow-x-auto">    
                <ul class="nav nav-tabs" role="tablist" id="protectionTab">
                    <li class="nav-item" id="li_autoprotection_tab">
                        <a class="nav-link active" href="#autoprotection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_PROTECTION_AUTOPROTECTION_TEXT'); ?></a>
                    </li>
                    <li class="nav-item" id="li_headers_protection_tab">
                        <a class="nav-link" href="#headers_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_PROTECTION_TEXT'); ?></a>
                    </li>
                    <li class="nav-item" id="li_user_agents_protection_tab">
                        <a class="nav-link" href="#user_agents_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_PROTECTION_USER_AGENTS_TEXT'); ?></a>
                    </li>
                    <li class="nav-item" id="li_fingerprinting_tab">
                        <a class="nav-link" href="#fingerprinting" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_FINGERPRINTING_PROTECTION_TEXT'); ?></a>
                    </li>
                    <li class="nav-item" id="li_backend_protection_tab">
                        <a class="nav-link" href="#backend_protection" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_BACKEND_PROTECTION_TEXT'); ?></a>
                    </li>
                    <li class="nav-item" id="li_performance_tab_tab">
                        <a class="nav-link" href="#performance_tab" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_PERFORMANCE'); ?></a>
                    </li>                    
                </ul>
                
                <div class="tab-content" class="overflow-auto">
                    <div class="tab-pane show active" id="autoprotection" role="tabpanel">
                        <div class="control-group">
                            <label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('prevent_access', array(), $this->protection_config['prevent_access']) ?>
                                <?php if ($this->config_applied['prevent_access'] ) {?>
                                    <span class="help-inline">
                                    <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN') ?></footer></blockquote>
                        
                        
                        <div class="control-group">
                            <label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('prevent_unauthorized_browsing', array(), $this->protection_config['prevent_unauthorized_browsing']) ?>
                                <?php if ($this->config_applied['prevent_unauthorized_browsing'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('file_injection_protection', array(), $this->protection_config['file_injection_protection']) ?>
                                <?php if ($this->config_applied['file_injection_protection'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('self_environ', array(), $this->protection_config['self_environ']) ?>
                                <?php if ($this->config_applied['self_environ'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>    
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN') ?></footer></blockquote>
                    <!-- autoprotection tab end -->
                    </div>
                    
                    <div class="tab-pane" id="headers_protection" role="tabpanel">
                        <div class="alert alert-danger">
        <?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_EXPLAIN'); ?>    
                        </div>
                        
                        <div class="control-group">
                            <label for="xframe_options" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo xframeoptions('xframe_options', array(), $this->protection_config['xframe_options']) ?>
                                <?php if ($this->config_applied['xframe_options'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="prevent_mime_attacks" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('prevent_mime_attacks', array(), $this->protection_config['prevent_mime_attacks']) ?>
                                <?php if ($this->config_applied['prevent_mime_attacks'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="sts_options" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_STS_OPTIONS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_STS_OPTIONS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('sts_options', array(), $this->protection_config['sts_options']) ?>
                                <?php if ($this->config_applied['sts_options'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_STS_OPTIONS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="xss_options" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_XSS_OPTIONS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_XSS_OPTIONS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('xss_options', array(), $this->protection_config['xss_options']) ?>
                                <?php if ($this->config_applied['xss_options'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_XSS_OPTIONS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="csp_policy" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CSP_OPTIONS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CSP_OPTIONS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <input type="text" class="form-control width_560" id="csp_policy" name="csp_policy" aria-describedby="csp_policy" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_ENTER_POLICY') ?>" value="<?php echo htmlentities($this->protection_config['csp_policy']); ?>">            
                                <?php if ($this->config_applied['csp_policy'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_CSP_OPTIONS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="referrer_policy" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_REFERRER_POLICY_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_REFERRER_POLICY_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <input type="text" class="form-control width_560" id="referrer_policy" name="referrer_policy" aria-describedby="referrer_policy" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_ENTER_POLICY') ?>" value="<?php echo htmlentities($this->protection_config['referrer_policy']); ?>">            
                                <?php if ($this->config_applied['referrer_policy'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_REFERRER_POLICY_EXPLAIN') ?></footer></blockquote>
						
						<div class="control-group">
                            <label for="permissions_policy" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_PERMISSIONS_POLICY_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_PERMISSIONS_POLICY_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <input type="text" class="form-control width_560" id="permissions_policy" name="permissions_policy" aria-describedby="feture_policy" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_ENTER_POLICY') ?>" value="<?php echo htmlentities($this->protection_config['permissions_policy']); ?>">            
                                <?php if ($this->config_applied['permissions_policy'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_PERMISSIONS_POLICY_EXPLAIN') ?></footer></blockquote>
                    <!-- headers_protection tab end -->
                    </div>
                        
                    <div class="tab-pane" id="user_agents_protection" role="tabpanel">
                    
                        <!-- View default user agent list -->
        <?php $default = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'user_agent_blacklist.inc'); ?>
                        <div class="modal" id="div_default_user_agents" tabindex="-1" role="dialog" aria-labelledby="defaultuseragentsLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header alert alert-info">
                                        <h2 class="modal-title" id="defaultuseragentsLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_CONTENT'); ?></h2>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" class="overflow-y-scroll">
                                        <div class="color_rojo">
            <?php echo JText::_('COM_SECURITYCHECKPRO_WARNING_CHANGES_USER_AGENTS'); ?>
                                        </div>
                                        <br/>
                                        <textarea id="file_info" name="file_info" rows="20" class="width-750 height-300"><?php echo $default; ?></textarea>                                
                                    </div>
                                    <div class="modal-footer">                    
                                        <input class="btn btn-success" id="save_default_user_agent_button" type="button" id="boton_guardar" value="<?php echo JText::_('COM_SECURITYCHECKPRO_SAVE_CLOSE'); ?>" />
                                        <button type="button" class="btn dtn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                    </div>              
                                </div>
                            </div>
                        </div>                        
                        
                        <div class="control-group">
                            <label for="default_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('default_banned_list', array(), $this->protection_config['default_banned_list']) ?>
                                <?php if ($this->config_applied['default_banned_list'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <input class="btn btn-primary" class="margin-bottom-10" type="button" id="boton_default_user_agent" value="<?php echo JText::_('COM_SECURITYCHECKPRO_EDIT_DEFAULT_USER_AGENTS'); ?>" />
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="own_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <textarea rows="5" cols="55" name="own_banned_list" id="own_banned_list"><?php echo $this->protection_config['own_banned_list'] ?></textarea>
                                <?php if ($this->config_applied['own_banned_list'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_BANNED_LIST_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="own_code" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <textarea rows="5" cols="60" name="own_code" id="own_code"><?php echo $this->protection_config['own_code'] ?></textarea>
                                <?php if ($this->config_applied['own_code'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_OWN_CODE_EXPLAIN') ?></footer></blockquote>
                    <!-- headers_protection tab end -->
                    </div>                        
        
                    <div class="tab-pane" id="fingerprinting" role="tabpanel">
                        <div class="alert alert-danger">
        <?php echo JText::_('COM_SECURITYCHECKPRO_FINGERPRINTING_EXPLAIN'); ?>    
                        </div>
                        <div class="control-group">
                            <label for="default_banned_list" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('disable_server_signature', array(), $this->protection_config['disable_server_signature']) ?>
                                <?php if ($this->config_applied['disable_server_signature'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="disallow_php_eggs" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('disallow_php_eggs', array(), $this->protection_config['disallow_php_eggs']) ?>
                                <?php if ($this->config_applied['disallow_php_eggs'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>                
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="disallow_sensible_files_access" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_TEXT'); ?></label>
                            <div class="controls controls-row">
        <?php if (empty($this->protection_config['disallow_sensible_files_access']) ) {
            $this->protection_config['disallow_sensible_files_access'] = "htaccess.txt" . PHP_EOL . "configuration.php(-dist)?" . PHP_EOL . "joomla.xml" . PHP_EOL . "README.txt" . PHP_EOL . "web.config.txt" . PHP_EOL . "CONTRIBUTING.md" . PHP_EOL . "phpunit.xml.dist" . PHP_EOL . "plugin_googlemap2_proxy.php";
        }?>
                                <textarea rows="5" cols="110" name="disallow_sensible_files_access" id="disallow_sensible_files_access"><?php echo $this->protection_config['disallow_sensible_files_access'] ?></textarea>
                                <?php if ($this->config_applied['disallow_sensible_files_access'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>    
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_EXPLAIN') ?></footer></blockquote>
                    <!-- fingerprinting tab end -->
                    </div>
                                        
                    <div class="tab-pane" id="backend_protection" role="tabpanel">
                        <div class="control-group">
                        
                            <label for="hide_backend_url" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <input id="backend_protection_applied" name="backend_protection_applied" type="checkbox" onchange="hideIt();" <?php if ($this->protection_config['backend_protection_applied']) { ?> checked <?php 
                                                                                                                                              } ?> />
                            </div>
                        </div>
                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_FEATURE_APPLIED_EXPLAIN') ?></footer></blockquote>
                        
                        <div id="menu_hide_backend_1" class="alert alert-danger">
        <?php echo JText::_('COM_SECURITYCHECKPRO_BACKEND_PROTECTION_EXPLAIN'); ?>    
                        </div>
                        
                        <div id="menu_hide_backend_2" class="control-group">
                            <label for="hide_backend_url" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php 
                                if (version_compare(JVERSION, '3.20', 'lt') ) {                                        
                                    ?>
                                    <div class="input-prepend">
                                        <span class="add-on" class="background-FFBF60""><?php echo $site_url ?>?</span>
                                        <input class="input-large" type="text" name="hide_backend_url" id="hide_backend_url" value="<?php echo $this->protection_config['hide_backend_url']?>" placeholder="<?php echo $this->protection_config['hide_backend_url'] ?>">
                                <?php } else {    ?>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text background-FFBF60" id="inputGroup-sizing-lg"><?php echo $site_url ?>?</span>
                                        </div>
                                        <input type="text" class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm" name="hide_backend_url" id="hide_backend_url" value="<?php echo $this->protection_config['hide_backend_url']?>" placeholder="<?php echo $this->protection_config['hide_backend_url'] ?>">                        
                                                                                                    
                                <?php } ?>
                                    <input type='button' id="hide_backend_url_button" class="btn btn-primary" class="margin-left-10" value='<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?>' />
                                </div>
                                <?php if ($this->config_applied['hide_backend_url'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>                
                            </div>
                        </div>
                        <blockquote class="blockquote" id="block2"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_EXPLAIN') ?></footer></blockquote>
                        
                        <div id="menu_hide_backend_3" class="control-group">
                            <label for="hide_backend_url_redirection" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php 
                                if (version_compare(JVERSION, '3.20', 'lt') ) {                                        
                                    ?>
                                    <div class="input-prepend">
                                        <span class="add-on" class="background-D0F5A9""><?php echo "/" ?></span>
                                        <input class="input-large" type="text" name="hide_backend_url_redirection" id="hide_backend_url_redirection" value="<?php echo $this->protection_config['hide_backend_url_redirection']?>" placeholder="not_found">
                                <?php } else {    ?>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text background-D0F5A9" id="inputGroup-sizing-lg"><?php echo "/" ?></span>
                                        </div>
                                        <input type="text" class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm" name="hide_backend_url_redirection" id="hide_backend_url_redirection" value="<?php echo $this->protection_config['hide_backend_url_redirection']?>" placeholder="not_found">
                                <?php } ?>
                                </div>                            
                                <?php if ($this->config_applied['hide_backend_url_redirection'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>                
                            </div>
                        </div>
                        <blockquote class="blockquote" id="block3"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_URL_REDIRECTION_EXPLAIN') ?></footer></blockquote>
                        
                        <div id="menu_hide_backend_4" class="control-group">
                            <label for="add_exception" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_EXCEPTIONS') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_EXCEPTIONS'); ?></label>
                            <div class="row">
                                <div class="col-lg-3">
                                    <textarea readonly rows="5" cols="30" name="backend_exceptions" id="backend_exceptions"><?php echo $this->protection_config['backend_exceptions'] ?></textarea>                            
                                </div>
                                <div class="col-lg-6 margin-left-60 margin-top-50">
                                    <div class="input-group">
            <?php 
            if (version_compare(JVERSION, '3.20', 'lt') ) {
                ?>
                                        <input class="span8" type="text" name="exception" id="exception" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_YOUR_EXCEPTION_HERE') ?>">
            <?php }     ?>
                                        <div class="input-group-btn">
            <?php 
            if (version_compare(JVERSION, '3.20', 'lt') ) {
                ?>
                                                <div class="btn-group">
                                                    <button class="btn dropdown-toggle" data-toggle="dropdown">
                <?php echo JText::_('COM_SECURITYCHECKPRO_ACTIONS') ?>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a href="#backend_exceptions" id="add_exception_button"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_ADD_EXCEPTION_TEXT') ?></a>
                                                        </li>                            
                                                        <li>
                                                            <a href="#backend_exceptions" id="delete_exception_button"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_DELETE_EXCEPTION_TEXT') ?></a>
                                                        </li>
                                                        <li>
                                                            <a href="#backend_exceptions" id="delete_all_button"><?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_ALL') ?></a>
                                                        </li>
                                                    </ul>                        
                                                </div>
            <?php } else {    ?>
                                                
                                                    <div class="input-group margin-left-60">
                                                        <input type="text" class="form-control span8" aria-label="exception" name="exception" id="exception" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_YOUR_EXCEPTION_HERE') ?>">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo JText::_('COM_SECURITYCHECKPRO_ACTIONS') ?></button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" id="add_exception_button" href="#backend_exceptions"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_ADD_EXCEPTION_TEXT') ?></a>
                                                                <a class="dropdown-item" id="delete_exception_button" href="#backend_exceptions"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_DELETE_EXCEPTION_TEXT') ?></a>
                                                                <a class="dropdown-item" id="delete_all_button" href="#backend_exceptions"><?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_ALL') ?></a>            
                                                            </div>
                                                        </div>
                                                    </div>
            <?php	}     ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <blockquote class="blockquote" id="block4"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_ADD_EXCEPTION_EXPLAIN') ?></footer></blockquote>
                    <!-- backend_protection tab end -->
                    </div>
                    
                    <div class="tab-pane" id="performance_tab" role="tabpanel">
                        <div class="control-group">
                            <label for="optimal_expiration_time" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('optimal_expiration_time', array(), $this->protection_config['optimal_expiration_time']) ?>
                                <?php if ($this->config_applied['optimal_expiration_time'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_OPTIMAL_EXPIRATION_TIME_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="compress_content" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('compress_content', array(), $this->protection_config['compress_content']) ?>
                                <?php if ($this->config_applied['compress_content'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_COMPRESS_CONTENT_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="redirect_to_www" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('redirect_to_www', array(), $this->protection_config['redirect_to_www']) ?>
                                <?php if ($this->config_applied['redirect_to_www'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_WWW_EXPLAIN') ?></footer></blockquote>
                        
                        <div class="control-group">
                            <label for="redirect_to_non_www" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_NON_WWW_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_NON_WWW_TEXT'); ?></label>
                            <div class="controls controls-row">
                                <?php echo booleanlist('redirect_to_non_www', array(), $this->protection_config['redirect_to_non_www']) ?>
                                <?php if ($this->config_applied['redirect_to_non_www'] ) {?>
                                    <span class="help-inline">
                                        <span class="badge badge-success"><i class="fapro fa-check"></i>&nbsp;&nbsp;<?php echo JText::_('COM_SECURITYCHECKPRO_APPLIED') ?></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                        <blockquote class="blockquote"><footer class="blockquote-footer"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECT_TO_NON_WWW_EXPLAIN') ?></footer></blockquote>
                    <!-- performance tab end -->
                    </div>
                <!-- tab-content end -->
                </div>                
            
            </div>
            
        </div>
</div>        

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/end.php';
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" id="task" value="save" />
</form>