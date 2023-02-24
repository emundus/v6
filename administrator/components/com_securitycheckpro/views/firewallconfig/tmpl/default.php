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
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/firewallconfig.php';
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
					<?php echo JHtml::_('bootstrap.startTabSet', 'WafConfigurationTabs'); ?>
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_lists_tab', JText::_('PLG_SECURITYCHECKPRO_LISTS_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_list_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_methods_tab', JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_methods_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_mode_tab', JText::_('PLG_SECURITYCHECKPRO_MODE_FIELDSET_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_mode_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_logs_tab', JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_logs_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_redirection_tab', JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_redirection_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_second_tab', JText::_('PLG_SECURITYCHECKPRO_SECOND_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_second_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_email_notifications_tab', JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_notification_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_exceptions_tab', JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_exceptions_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_session_protection_tab', JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_session_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_upload_scanner_tab', JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_upload_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_spam_protection_tab', JText::_('COM_SECURITYCHECKPRO_SPAM_PROTECTION')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_spam_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_url_inspector_tab', JText::_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_url_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.addTab', 'WafConfigurationTabs', 'li_track_actions_tab', JText::_('COM_SECURITYCHECKPRO_TRACK_ACTIONS')); ?>
							<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_track_tab.php'; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					
					<?php echo JHtml::_('bootstrap.endTabSet'); ?> 
					
				</div>
            </div>
        <!-- End container fluid -->        
             

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/end.php';
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallconfig" />
</form>
