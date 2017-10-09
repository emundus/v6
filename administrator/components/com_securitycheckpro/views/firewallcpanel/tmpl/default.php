<?php
/**
* Securitycheck Pro WAF Control Panel View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');

JHTML::_('behavior.framework');
JHtml::_('behavior.modal');

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallcpanel&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

	<div class="row-fluid" id="cpanel">
		<div class="box span12">
			<div class="box-header well" data-original-title>
				<i class="icon-list-alt"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_OPTIONS'); ?>
			</div>
		<div class="box-content">
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewalllists&view=firewalllists&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-waf_lists">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_LISTS_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallmethods&view=firewallmethods&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-methods">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallmode&view=firewallmode&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-mode">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_MODE_FIELDSET_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewalllogs&view=firewalllogs&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-logs">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallredirection&view=firewallredirection&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-redirection">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallsecond&view=firewallsecond&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-second">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallemail&view=firewallemail&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-email">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallexceptions&view=firewallexceptions&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-exceptions">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallsessionprotection&view=firewallsessionprotection&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-user_session_protection">&nbsp;</div>
				<span><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'); ?></span>
				</a>
			</div>		

			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=geoblock&view=geoblock&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-geoblock">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=uploadscanner&view=uploadscanner&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-uploadscanner">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallspam&view=firewallspam&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-spamprotection">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_SPAM_PROTECTION'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewallinspector&view=firewallinspector&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-url_inspector">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro&controller=firewalltrackactions&view=firewalltrackactions&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-firewalltrackactions">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_TRACK_ACTIONS'); ?></span>
				</a>
			</div>
	</div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallcpanel" />
</form>