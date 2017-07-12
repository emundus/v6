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

function email_actions( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_BOTH_INCORRECT' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_FRONTEND' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_BACKEND' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

function actions_failed_login( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

JHTML::_( 'behavior.framework', true );
JHtml::_('behavior.multiselect');

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

JHtml::_('formbehavior.chosen', 'select');
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallsessionprotection&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<?php
					$params          = JFactory::getConfig();		
					$shared_session_enabled = $params->get('shared_session');
					
					if ( !$shared_session_enabled ) {
				?>
					
				<div class="control-group">
					<label for="session_protection_active" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_ACTIVE_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_ACTIVE_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('session_protection_active', array(), $this->session_protection_active) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_ACTIVE_LABEL') ?></small></p></blockquote>
				</div>	
				
				<div class="control-group">
					<label for="session_hijack_protection" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('session_hijack_protection', array(), $this->session_hijack_protection) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_DESCRIPTION') ?></small></p></blockquote>
				</div>			
				
				<div class="control-group">
					<label for="session_protection_groups" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_LABEL'); ?></label>
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
						echo JHTML::_('select.genericlist', $options, 'session_protection_groups[]', 'class="inputbox" multiple="multiple"', 'value', 'text',  $this->session_protection_groups); 												
						?>					
					</div>
					
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_DESCRIPTION') ?></small></p></blockquote>
				</div>	
				
				<?php
					} else {
				?>
						<blockquote id="launch_time_alert"><p class="text-info"><small><span style="color: red;"><?php echo JText::_('PLG_SECURITYCHECKPRO_SHARED_SESSIONS_EANBLED') ?></span></small></p></blockquote>
				<?php		
					}
				?>
		</div>	
	
		<div class="well span4 top-block">
			<legend><?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS') ?></legend>
				<div class="control-group">
					<label for="track_failed_logins" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('track_failed_logins', array(), $this->track_failed_logins) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TRACK_FAILED_LOGINS_LABEL') ?></small></p></blockquote>
				</div>
				
				<div class="control-group">
					<label for="logins_to_monitorize" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_LOGINS_TO_MONITORIZE_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGINS_TO_MONITORIZE_LABEL'); ?></label>
					<div class="controls">
						<?php echo email_actions('logins_to_monitorize', array(), $this->logins_to_monitorize) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGINS_TO_MONITORIZE_DESCRIPTION') ?></small></p></blockquote>
				</div>

				<div class="control-group">
					<label for="write_log" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_WRITE_LOG_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_WRITE_LOG_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('write_log', array(), $this->write_log) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_WRITE_LOG_DESCRIPTION') ?></small></p></blockquote>
				</div>
												
				<div class="control-group">
					<label for="include_password_in_log" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_INCLUDE_PASSWORD_IN_LOG_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_INCLUDE_PASSWORD_IN_LOG_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('include_password_in_log', array(), $this->include_password_in_log) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_INCLUDE_PASSWORD_IN_LOG_DESCRIPTION') ?></small></p></blockquote>
				</div>	

				<div class="control-group">
					<label for="actions_failed_login" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></label>
					<div class="controls">
						<?php echo actions_failed_login('actions_failed_login', array(), $this->actions_failed_login) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_DESCRIPTION') ?></small></p></blockquote>
				</div>			
				
		</div>
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('PLG_SECURITYCHECKPRO_ADMIN_LOGINS') ?></legend>
			<div class="control-group">
				<label for="email_on_admin_login" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ON_BACKEND_LOGIN_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ON_BACKEND_LOGIN_LABEL'); ?></label>
				<div class="controls">
					<?php echo booleanlist('email_on_admin_login', array(), $this->email_on_admin_login) ?>
				</div>
				<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ON_BACKEND_LOGIN_DESCRIPTION') ?></small></p></blockquote>
			</div>
			
			<div class="control-group">
				<label for="email_on_admin_login" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_ADMIN_FRONTEND_LOGIN_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_ADMIN_FRONTEND_LOGIN_LABEL'); ?></label>
				<div class="controls">
					<?php echo booleanlist('forbid_admin_frontend_login', array(), $this->forbid_admin_frontend_login) ?>
				</div>
				<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_ADMIN_FRONTEND_LOGIN_DESCRIPTION') ?></small></p></blockquote>
			</div>
			
			<div class="control-group">
				<label for="forbid_new_admins" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_NEW_ADMINS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL'); ?></label>
				<div class="controls">
					<?php echo booleanlist('forbid_new_admins', array(), $this->forbid_new_admins) ?>
				</div>
				<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_FORBID_NEW_ADMINS_DESCRIPTION') ?></small></p></blockquote>
			</div>
		</div>
	</div>

</div>

</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallsessionprotection" />
</form>