<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 
JSession::checkToken('get') or die('Invalid Token');

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_NO')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_YES'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function prioritylist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  'Blacklist', JText::_('PLG_SECURITYCHECKPRO_BLACKLIST')),
    JHTML::_('select.option',  'Whitelist', JText::_('PLG_SECURITYCHECKPRO_WHITELIST')),
    JHTML::_('select.option',  'DynamicBlacklist', JText::_('PLG_SECURITYCHECKPRO_DYNAMICBLACKLIST'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id);
}

function methodslist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  'GET,POST,REQUEST', 'Get,Post,Request'),

    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id);
}

function mode( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('PLG_SECURITYCHECKPRO_ALERT_MODE')),
    JHTML::_('select.option',  '1', JText::_('PLG_SECURITYCHECKPRO_STRICT_MODE'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function redirectionlist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '1', JText::_('PLG_SECURITYCHECKPRO_JOOMLA_PATH_LABEL')),
    JHTML::_('select.option',  '2', JText::_('COM_SECURITYCHECKPRO_REDIRECTION_OWN_PAGE'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single" onchange="Disable()"', 'value', 'text', (int) $selected, $id);
}

function secondredirectlist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_YES'))
    );
    return JHTML::_('select.genericlist',  $arr, $name,  'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function booleanlist_js( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_NO')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_YES'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single" onchange="Disable()"', 'value', 'text', (int) $selected, $id);
}

function email_actions( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_EMAIL_BOTH_INCORRECT')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_EMAIL_ONLY_FRONTEND')),
    JHTML::_('select.option',  '2', JText::_('COM_SECURITYCHECKPRO_EMAIL_ONLY_BACKEND'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function actions_failed_login( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_DO_NOTHING')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function actions( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_DO_NOTHING')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function spammer_action( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_DO_NOTHING')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function action( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_DO_NOTHING')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST')),
    JHTML::_('select.option',  '2', JText::_('COM_SECURITYCHECKPRO_ADD_IP_TO_BLACKLIST'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function what_to_check( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '1', JText::sprintf('PLG_SECURITYCHECKPRO_IP_USER_AGENT', "OR")),
    JHTML::_('select.option',  '2', JText::sprintf('PLG_SECURITYCHECKPRO_IP_USER_AGENT', "AND"))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

// Esta parte no se puede eliminar porque de lo contrario las pestañas y la navegación "desaparecen" al navegar por ellas
if (version_compare(JVERSION, "3.20", "lt")) {
	JHtml::_('behavior.modal');
}

$document = JFactory::getDocument();
$document->addScript(JURI::root().'media/com_securitycheckpro/new/js/sweetalert.min.js');
// Bootstrap core JavaScript
// Inline javascript to avoid deferring in Joomla 4
echo '<script src="' . JURI::root(). '/media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>';
//$document->addScript(JURI::root().'media/com_securitycheckpro/new/vendor/popper/popper.min.js');

$site_url = JURI::root();

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$chosen = "media/com_securitycheckpro/new/vendor/chosen/chosen.css";
JHTML::stylesheet($chosen);

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);
?>

<?php
    $current_ip = "";
    $range_example = "";
	// Contribution of George Acu - thanks!
	if (isset($_SERVER['HTTP_TRUE_CLIENT_IP']))
	{
		# CloudFlare specific header for enterprise paid plan, compatible with other vendors
		$current_ip = $_SERVER['HTTP_TRUE_CLIENT_IP']; 
	} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
	{
		# another CloudFlare specific header available in all plans, including the free one
		$current_ip = $_SERVER['HTTP_CF_CONNECTING_IP']; 
	} elseif (isset($_SERVER['HTTP_INCAP_CLIENT_IP'])) 
	{
		// Users of Incapsula CDN
		$current_ip = $_SERVER['HTTP_INCAP_CLIENT_IP']; 
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
	{
		# specific header for proxies
		$current_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		$result_ip_address = explode(', ', $clientIpAddress);
        $current_ip = $result_ip_address[0];
	} elseif (isset($_SERVER['REMOTE_ADDR']))
	{
		# this one would be used, if no header of the above is present
		$current_ip = $_SERVER['REMOTE_ADDR']; 
	}

    $range_example = explode('.', $current_ip);
    $range_example[2] = "*";
    $range_example[3] = "*";
    $range_example = implode('.', $range_example);
    $cidr_v4_example = $current_ip . "/20";
?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/j3_firewallconfig.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallconfig&'. JSession::getFormToken() .'=1');?>" class="margin-top-minus18" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">       
        
    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
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
                      <li class="nav-item" id="li_lists_tab">
                        <a class="nav-link active" href="#lists" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LISTS_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_methods_tab">
                        <a class="nav-link" href="#methods" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_mode_tab">
                        <a class="nav-link" data-toggle="tab" href="#mode" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_FIELDSET_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_logs_tab">
                        <a class="nav-link" data-toggle="tab" href="#logs" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_redirection_tab">
                        <a class="nav-link" data-toggle="tab" href="#redirection" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_second_tab">
                        <a class="nav-link" data-toggle="tab" href="#second" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_email_notifications_tab">
                        <a class="nav-link" data-toggle="tab" href="#email_notifications" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_exceptions_tab">
                        <a class="nav-link" data-toggle="tab" href="#exceptions" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_session_protection_tab">
                        <a class="nav-link" data-toggle="tab" href="#session_protection" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'); ?></a>
                      </li>                      
                      <li class="nav-item" id="li_upload_scanner_tab">
                        <a class="nav-link" data-toggle="tab" href="#upload_scanner" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL'); ?></a>
                      </li>
                      <li class="nav-item" id="li_spam_protection_tab">
                        <a class="nav-link" data-toggle="tab" href="#spam_protection" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_SPAM_PROTECTION'); ?></a>
                      </li>
                      <li class="nav-item" id="li_url_inspector_tab">
                        <a class="nav-link" data-toggle="tab" href="#url_inspector" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT'); ?></a>
                      </li>
                      <li class="nav-item" id="li_track_actions_tab">
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
                                                            <input type="number" size="5" maxlength="5" id="dynamic_blacklist_time" name="dynamic_blacklist_time" value="<?php echo $this->dynamic_blacklist_time ?>" title="" />        
                                                        </div>
                                                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_DESCRIPTION') ?></small></p></blockquote>
                                                        
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <input type="number" size="3" maxlength="3" id="dynamic_blacklist_counter" name="dynamic_blacklist_counter" value="<?php echo $this->dynamic_blacklist_counter ?>" title="" />        
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
            <?php echo JText::_('COM_SECURITYCHECKPRO_LISTS_MANAGEMENT'); ?>
                                        </div>
                                        <div class="card-body">
                                                <div id="filter-bar" class="btn-toolbar" class="margin-left-10">
                                                    <div class="filter-search btn-group pull-left">
                                                        <input type="text" name="filter_lists_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.lists_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
                                                    </div>
                                                    <div class="btn-group pull-left" class="margin-left-10">
                                                        <button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                                                        <button id="search_button" class="btn tip" type="button" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                                                    </div>
                                                </div>
                                                <br/>
                                                <div class="box-content">
                                                    <ul class="nav nav-tabs" role="tablist" id="ListsTabs">
                                                      <li class="nav-item" id="li_blacklist_tab">
                                                        <a class="nav-link active" href="#blacklist" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST'); ?></a>
                                                      </li>
                                                      <li class="nav-item" id="li_dynamic_blacklist_tab">
                                                        <a class="nav-link" href="#dynamic_blacklist_tab" data-toggle="tab" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST'); ?></a>
                                                      </li>
                                                      <li class="nav-item" id="li_whitelist_tab">
                                                        <a class="nav-link" data-toggle="tab" href="#whitelist" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_WHITELIST'); ?></a>
                                                      </li>
                                                    </ul>
                                                                                                
                                                    <div id="pagination" class="margin-bottom-30">
                <?php	            
                if (isset($this->pagination) ) {                                    
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
                                                                            <label class="red"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
                                                                            <h5><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></h5>                        
                                                                            <div class="controls">
                                                                                <input class="input_box" id="file_to_import_blacklist" name="file_to_import_blacklist" type="file" size="57" />
                                                                            </div>
                                                                        </div>                                                                                
                                                                      </div>
                                                                        <div class="modal-footer" id="div_boton_subida_blacklist">
                                                                            <input class="btn btn-primary" id="upload_import_button" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" />								
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
                                                                            <b><?php echo JText::_('COM_SECURITYCHECKPRO_CIDR'); ?></b>    <?php echo ", i.e. 2001:13d0::/29"; ?>
                                                                            </li>                            
                                                                        </ol>
                                                                        <p>
                    <?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_CURRENT'); ?>
                                                                        <code><?php echo $current_ip; ?></code>    
                                                                        <button type="button" id="add_ip_whitelist_button2" class="btn btn-sm btn-success" href="#">
                    <?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
                                                                        </button>
                                                                        </p>
                                                                </div>
                                                                
                                                                <div id="blacklist_buttons">
                                                                    <div class="btn-group pull-left">
                                                                        <input type="text" name="blacklist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="blacklist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10 margin-bottom-20">
                                                                        <button class="btn btn-success" id="add_ip_blacklist_button" href="#">
                                                                            <i class="fapro fa-plus-octagon"> </i>
                                                                                <?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
                                                                        </button>
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10">
                                                                        <a href="#select_blacklist_file_to_upload" id="select_blacklist_file_to_upload" role="button" class="btn btn-secondary" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_IPS'); ?></a>                                
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10">
                                                                        <button class="btn btn-info" id="export_blacklist_button" href="#">
                                                                            <i class="icon-new icon-white"> </i>
                                                                                <?php echo JText::_('COM_SECURITYCHECKPRO_EXPORT_IPS'); ?>
                                                                        </button>
                                                                    </div>
                    <?php
                    if (count($this->blacklist_elements)>0 ) {                                                                            
                        ?>
                                                                    <div class="btn-group pull-right">
                                                                        <button class="btn btn-danger" id="delete_ip_blacklist_button" href="#">
                                                                            <i class="icon-trash icon-white"> </i>
                        <?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
                                                                        </button>
                                                                    </div>
                        <?php
                    }                                                                    
                    ?>
                                                                </div>
                                                                
                                                                <table class="table table-striped table-bordered bootstrap-datatable datatable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="center"><?php echo JText::_("Ip"); ?></th>                                                                    
                                                                            <th class="center">
                                                                                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                                                                            </th>
                                                                        </tr>
                                                                    </thead>   
                                                                    <tbody>
                    <?php
                    if (count($this->blacklist_elements)>0 ) {
                        $k = 0;
                        foreach ($this->blacklist_elements as &$row) { 
                            ?>
                                                                        <tr>
                                                                            <td class="center"><?php echo $row; ?></td>                                                                            
                                                                            <td class="center">
                            <?php echo JHtml::_('grid.id', $k, $row); ?>
                                                                            </td>
                                                                        </tr>
                            <?php 
                            $k++;
                        } 
                    }    ?>
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
                                                                
                                                                <?php
                                                                if (count($this->dynamic_blacklist_elements)>0 ) {                                                                            
                                                                    ?>

                                                                <div id="dynamic_blacklist_buttons">
                                                                    <div class="btn-group pull-right" class="margin-bottom-5">
                                                                        <button class="btn btn-danger" id="deleteip_dynamic_blacklist_button" href="#">
                                                                            <i class="icon-trash icon-white"> </i>
                                                                                <?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
                                                                        </button>
                                                                    </div>                        
                                                                </div>
                                                                
                                                                    <?php
                                                                }                                                                    
                                                                ?>
                                                                <table id="dynamic_blacklist_table" class="table table-striped table-bordered bootstrap-datatable datatable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="center"><?php echo JText::_("Ip"); ?></th>                                                                
                                                                                <th class="center">
                                                                                    <input type="checkbox" id="toggle_dynamic_blacklist" name="toggle_dynamic_blacklist" value="" />
                                                                                </th>
                                                                            </tr>
                                                                        </thead>   
                                                                    <tbody>
                    <?php
                    if (count($this->dynamic_blacklist_elements)>0 ) {
                        $k = 0;
                        foreach ($this->dynamic_blacklist_elements as &$row_dynamic) {                 
                            ?>
                                                                        <tr>
                                                                            <td class="center"><?php echo $row_dynamic; ?></td>                                                                     
                                                                            <td class="center">
                            <?php echo JHtml::_('grid.id', $k, $row_dynamic, '', 'dynamic_blacklist_table'); ?>
                                                                            </td>
                                                                        </tr>
                            <?php 
                            $k++;
                        } 
                    }    ?>
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
                                                                            <label class="red"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
                                                                            <h5><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></h5>                        
                                                                            <div class="controls">
                                                                                <input class="input_box" id="file_to_import_whitelist" name="file_to_import_whitelist" type="file" size="57" />
                                                                            </div>
                                                                        </div>                                                                                
                                                                      </div>
                                                                        <div class="modal-footer" id="div_boton_subida_whitelist">
                                                                            <input class="btn btn-primary" id="import_whitelist_button" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" />												
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
                                                                        <button type="button" id="add_ip_whitelist_button" class="btn btn-sm btn-success" href="#">
                    <?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
                                                                        </button>
                                                                        </p>
                                                                    </div>

                                                                <div id="blacklist_buttons">
                                                                    <div class="btn-group pull-left">
                                                                        <input type="text" name="whitelist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="whitelist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10 margin-bottom-20">
                                                                        <button class="btn btn-success" id="addip_whitelist_button" href="#">
                                                                            <i class="fapro fa-plus-octagon"> </i>
                                                                                <?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
                                                                        </button>
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10">
                                                                        <a href="#select_whitelist_file_to_upload" role="button" class="btn btn-secondary" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_IPS'); ?></a>                                
                                                                    </div>
                                                                    <div class="btn-group pull-left" class="margin-left-10">
                                                                        <button class="btn btn-info" id="export_whitelist_button" href="#">
                                                                            <i class="icon-new icon-white"> </i>
                                                                                <?php echo JText::_('COM_SECURITYCHECKPRO_EXPORT_IPS'); ?>
                                                                        </button>
                                                                    </div>
                    <?php
                    if (count($this->whitelist_elements)>0 ) {                                                                        
                        ?>
                                                                    <div class="btn-group pull-right">
                                                                        <button class="btn btn-danger" id="deleteip_whitelist_button" href="#">
                                                                            <i class="icon-trash icon-white"> </i>
                        <?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
                                                                        </button>
                                                                    </div>    
                        <?php
                    }                                                                    
                    ?>
                                                                </div>
                                                                
                                                                <table class="table table-striped table-bordered bootstrap-datatable datatable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="center"><?php echo JText::_("Ip"); ?></th>                                                                              
                                                                                <th class="center">
                                                                                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                                                                                </th>
                                                                            </tr>
                                                                        </thead>   
                                                                    <tbody>
                    <?php
                    if (count($this->whitelist_elements)>0 ) {
                        $k = 0;
                        foreach ($this->whitelist_elements as &$row) { 
                            ?>
                                                                        <tr>
                                                                            <td class="center"><?php echo $row; ?></td>                                                                            
                                                                            <td class="center">
                            <?php echo JHtml::_('grid.id', $k, $row, '', 'whitelist_cid'); ?>
                                                                            </td>
                                                                        </tr>
                            <?php 
                            $k++;
                        } 
                    }    ?>
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
                                                    <input type="number" size="4" maxlength="4" id="log_limits_per_ip_and_day" name="log_limits_per_ip_and_day" value="<?php echo $this->log_limits_per_ip_and_day ?>" title="" />        
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?></small></footer></blockquote>
                                                                                 
                                                                                                                                               
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
                                                if (version_compare(JVERSION, '3.20', 'lt') ) {                                        
                                                    ?>
                                                    <div class="controls controls-row">
                                                        <div class="input-prepend">
                                                            <span class="add-on" class="background-8EBBFF"><?php echo $site_url ?></span>
                                                            <input class="input-large" type="text" id="redirect_url" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
                                                        </div>                        
                                                    </div>
                                                <?php } else {    ?>
                                                    <div class="input-group">
                                                        <span class="input-group-text" class="background-8EBBFF"><?php echo $site_url ?></span>
                                                        <input type="text" class="form-control" id="redirect_url" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
                                                    </div>                                            
                                                <?php } ?>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_EXPLAIN') ?></small></footer></blockquote>
                                                                                                
                                                <div class="control-group">
                                                    <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_TEXT'); ?></h4>
                                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_EXPLAIN') ?></small></footer></blockquote>                                                    
                <?php 
                // IMPORT EDITOR CLASS
                jimport('joomla.html.editor');

                // GET EDITOR SELECTED IN GLOBAL SETTINGS
                $config = JFactory::getConfig();
                $global_editor = $config->get('editor');

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
                                                    <input type="number" size="2" maxlength="2" id="second_level_limit_words" name="second_level_limit_words" value="<?php echo $this->second_level_limit_words ?>" title="" />        
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
                                                    <textarea cols="35" rows="3" id="second_level_words" name="second_level_words" class="width_560_height_340"><?php echo $this->second_level_words ?></textarea>
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
                                                    <input class="btn btn-primary" type="button" id="boton_test_email" value="<?php echo JText::_('COM_SECURITYCHECKPRO_SEND_EMAIL_TEST'); ?>" />        
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
                                                    <input type="number" size="3" maxlength="3" id="email_max_number" name="email_max_number" value="<?php echo $this->email_max_number ?>" title="" />        
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
                                                    <li class="nav-item" id="li_header_referer_tab">
                                                        <a class="nav-link active" href="#header_referer" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL'); ?></a>
                                                    </li>
                                                    <li class="nav-item" id="li_base64_tab">
                                                        <a class="nav-link" href="#base64" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL'); ?></a>
                                                    </li>
                                                    <li class="nav-item" id="li_xss_tab">
                                                        <a class="nav-link" href="#xss" data-toggle="tab" role="tab"><?php echo JText::_('XSS'); ?></a>
                                                    </li>
                                                    <li class="nav-item" id="li_sql_tab">
                                                        <a class="nav-link" href="#sql" data-toggle="tab" role="tab"><?php echo JText::_('SQL Injection'); ?></a>
                                                    </li>
                                                    <li class="nav-item" id="li_lfi_tab">
                                                        <a class="nav-link" href="#lfi" data-toggle="tab" role="tab"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></a>
                                                    </li>
                                                    <li class="nav-item" id="li_secondlevel_tab">
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
                                                            <textarea cols="35" rows="3" name="base64_exceptions" class="firewall-config-style"><?php echo $this->base64_exceptions ?></textarea>                                
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
                                                                <textarea cols="35" rows="3" name="tags_to_filter" class="firewall-config-style"><?php echo $this->tags_to_filter ?></textarea>                                
                                                            </div>
                                                            <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_DESCRIPTION') ?></small></footer></blockquote>
                                                            
                                                        </div>
                                                        
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="strip_tags_exceptions" class="firewall-config-style"><?php echo $this->strip_tags_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                        
                                                    </div>
                                                    <div class="tab-pane" id="sql" role="tabpanel">
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="duplicate_backslashes_exceptions" class="firewall-config-style"><?php echo $this->duplicate_backslashes_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                            
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="line_comments_exceptions" class="firewall-config-style"><?php echo $this->line_comments_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="sql_pattern_exceptions" class="firewall-config-style"><?php echo $this->sql_pattern_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="if_statement_exceptions" class="firewall-config-style"><?php echo $this->if_statement_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="using_integers_exceptions" class="firewall-config-style"><?php echo $this->using_integers_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="escape_strings_exceptions" class="firewall-config-style"><?php echo $this->escape_strings_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                        
                                                    </div>
                                                    <div class="tab-pane" id="lfi" role="tabpanel">
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="lfi_exceptions" class="firewall-config-style"><?php echo $this->lfi_exceptions ?></textarea>                                
                                                        </div>
                                                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                            
                                                    </div>
                                                    <div class="tab-pane" id="secondlevel" role="tabpanel">
                                                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL'); ?></h4>
                                                        <div class="controls">
                                                            <textarea cols="35" rows="3" name="second_level_exceptions" class="firewall-config-style"><?php echo $this->second_level_exceptions ?></textarea>                                
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
                                                    
                                                if (!$shared_session_enabled ) {
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
												
												<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_WHAT_TO_CHECK_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <?php echo what_to_check('session_hijack_protection_what_to_check', array(), $this->session_hijack_protection_what_to_check) ?>
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_WHAT_TO_CHECK_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                
                                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <?php
                                                    // Listamos todos los grupos presentes en el sistema excepto el grupo 'Guest'
                                                    $db = JFactory::getDBO();
                                                    $query = "SELECT id,title from #__usergroups WHERE title != 'Guest'";            
                                                    $db->setQuery($query);
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
                                                        <blockquote class="blockquote" id="launch_time_alert"><footer class="blockquote-footer"><span class="red"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SHARED_SESSIONS_EANBLED') ?></span></small></footer></blockquote>                                                        
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
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_MIMETYPES_BLACKLIST_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <textarea cols="35" rows="3" name="mimetypes_blacklist" class="mimetypes-blacklist"><?php echo $this->mimetypes_blacklist ?></textarea>                                
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_MIMETYPES_BLACKLIST_DESCRIPTION') ?></small></footer></blockquote>
												
												
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_EXTENSIONS_BLACKLIST_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <textarea cols="35" rows="3" name="extensions_blacklist" class="extensions-blacklist"><?php echo $this->extensions_blacklist ?></textarea>                                
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
        <?php if ($this->plugin_installed ) { ?>                            
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
                                                        if (!is_array($this->spammer_what_to_check) ) {                            
                                                            $this->spammer_what_to_check = array('Email','IP','Username');
                                                        }                        
                                                        echo JHTML::_('select.genericlist', $options_spam, 'spammer_what_to_check[]', 'class="chosen-select-no-single" multiple="multiple"', 'text', 'text',  $this->spammer_what_to_check);                                                
                                                        ?>                    
                                                    </div>
                                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WHAT_TO_CHECK_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                        
                                                    <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_LABEL'); ?></h4>
                                                    <div class="controls">
                                                        <input type="number" size="3" maxlength="3" id="spammer_limit" name="spammer_limit" value="<?php echo $this->spammer_limit ?>" title="" />    
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
                                <h4><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INPECTOR_DISABLED'); ?></h4>
                                <button id="enable_url_inspector_button" class="btn btn-success" href="#">
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
                                                    <textarea cols="35" rows="3" name="inspector_forbidden_words" class="width_560_height_340"><?php echo $this->inspector_forbidden_words ?></textarea>
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
        <?php if ($this->plugin_trackactions_installed) { ?>
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
                                                        <input type="number" size="3" maxlength="3" id="delete_period" name="delete_period" value="<?php echo $this->delete_period ?>" title="" />    
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
                                                        $query = "SELECT extension from #__securitycheckpro_trackactions_extensions" ;            
                                                        $db->setQuery($query);
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

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/end.php';
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallconfig" />
</form>
