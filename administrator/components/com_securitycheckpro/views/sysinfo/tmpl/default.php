<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

// Load Javascript
$document = JFactory::getDocument();
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/bootstrap-modal.js');

JHTML::_( 'behavior.framework', true );
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

	<div id="editcell">
		<div class="accordion-group">
	</div>
	
	<div>
		<span class="badge" style="background-color: #C993FF; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSTEM_INFORMATION' ); ?></span>
	</div>
	
	<table id="report" width="100%">
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        	
		<tr>
            <td><h5><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY' ); ?></h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
			<td colspan="5">
                <ul>	
					<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_OVERALL_STATUS' ); ?>
					<?php 
						if ( $this->system_info['overall_joomla_configuration'] <=50 ) {
							$div = "<div class=\"progress progress-danger\">";
						} else if ( ($this->system_info['overall_joomla_configuration'] >50) && ($this->system_info['overall_joomla_configuration'] <=70) ) {
							$div = "<div class=\"progress progress-warning\">";
						} else {
							$div = "<div class=\"progress progress-success\">";
						}
					?>					
					<?php echo $div . "<div class=\"bar\" style=\"width: " . $this->system_info['overall_joomla_configuration'] ."%\">" . $this->system_info['overall_joomla_configuration']; ?>
						</div>						
					</div>			
								
					<li>					
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_UP_TO_DATE' ); ?></strong>
						<br/>
						<?php 
							if ( version_compare($this->system_info['coreinstalled'],$this->system_info['corelatest'],'==') ) {
								$span = "<span class=\"label label-success\">";
							} else {
								$span = "<span class=\"label label-important\">";
							}
						?>
						<?php echo $span . $this->system_info['coreinstalled']; ?>
						</span>
						<div class="securitycheck-bootstrap">							
							<?php 
								if ( version_compare($this->system_info['coreinstalled'],$this->system_info['corelatest'],'==') ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="GoToJoomlaUpdate();"><i class="icon-wrench icon-white"></i></button>
							<?php }	?>														
						</div>																	
					</li>                    					
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_VULNERABLE_EXTENSIONS' ); ?>
						</strong>						
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['vuln_extensions'] == 0 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',$this->system_info['vuln_extensions'] ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToVuln')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_vuln_extensions" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_VULN_EXTENSIONS_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_vuln_extensions" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_MALWARE_FOUND' ); ?>
						</strong>						
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['suspicious_files'] == 0 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',$this->system_info['suspicious_files'] ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToMalware')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_malware_found" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_MALWARE_FOUND_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_malware_found" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_NO_FILES_MODIFIED' ); ?></strong>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['files_with_bad_integrity'] == 0 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',$this->system_info['files_with_bad_integrity']) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToIntegrity')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_files_with_bad_integrity" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILES_BAD_INTEGRITY_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_files_with_bad_integrity" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_PERMISSIONS' ); ?></strong>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['files_with_incorrect_permissions'] == 0 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',$this->system_info['files_with_incorrect_permissions'] ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToPermissions')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_file_permissions" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILE_PERMISSIONS_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_file_permissions" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_HIDE_BACKEND' ); ?></strong>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['backend_protection'] ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_hide_backend" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_HIDE_BACKEND_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_hide_backend" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL' ); ?>
						</strong>						
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['firewall_options']['forbid_new_admins'] == 1 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToUserSessionProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_forbid_new_admins" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_forbid_new_admins" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>					
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_TWO_FACTOR_ENABLED_LABEL' ); ?>
						</strong>						
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['twofactor_enabled'] == 1 ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="GoToJoomlaPlugins();" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_two_factor_enabled" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_TWO_FACTOR_ENABLED_LABEL_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_two_factor_enabled" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>											
					</li>	
                 </ul>   
            </td>
        </tr>
		
		<tr>
            <td><h5><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS' ); ?></h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
                <ul>
					<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_OVERALL_STATUS' ); ?>
					<?php 
						if ( $this->system_info['overall_web_firewall'] <=50 ) {
							$div = "<div class=\"progress progress-danger\">";
						} else if ( ($this->system_info['overall_web_firewall'] >50) && ($this->system_info['overall_web_firewall'] <=70) ) {
							$div = "<div class=\"progress progress-warning\">";
						} else {
							$div = "<div class=\"progress progress-success\">";
						}
					?>				
					<?php echo $div . "<div class=\"bar\" style=\"width: " . $this->system_info['overall_web_firewall'] ."%\">" . $this->system_info['overall_web_firewall']; ?>
						</div>
					</div>	
					<li style="margin-top: 10px;">
						<strong><span class="columna"><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_WEB_FIREWALL_ACTIVE' ); ?></strong></span>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['firewall_plugin_enabled'] ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToCpanel')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_firewall_plugin_enabled" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_FIREWALL_ENABLED_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_firewall_plugin_enabled" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>	
						<ul>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_DYNAMIC_BLACKLIST' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( $this->system_info['firewall_options']['dynamic_blacklist'] ) {
											echo "<span class=\"label label-success\">OK</span>";
										
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallLists')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_dynamic_blacklist" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_dynamic_blacklist" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_LOGS' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else 	if ( $this->system_info['firewall_options']['logs_attacks'] ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallLogs')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_logs_attacks" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_LOG_ATTACKS_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_logs_attacks" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_SECOND_LEVEL' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else 	if ( $this->system_info['firewall_options']['second_level'] ) {
											echo "<span class=\"label label-success\">OK</span>";
										
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallSecondLevel')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_second_level" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECOND_LEVEL_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_second_level" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_EXCLUDE_EXCEPTIONS' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( $this->system_info['firewall_options']['exclude_exceptions_if_vulnerable'] ) {
											echo "<span class=\"label label-success\">OK</span>";										
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallExceptions')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_exclude_exceptions_if_vulnerable" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_exclude_exceptions_if_vulnerable" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_XSS_FILTER' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else 	if ( !(strstr($this->system_info['firewall_options']['strip_tags_exceptions'],'*')) ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallExceptions')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_strip_tags_exceptions" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_XSS_FILTER_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_strip_tags_exceptions" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_SQL_FILTER' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( !(strstr($this->system_info['firewall_options']['sql_pattern_exceptions'],'*')) ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallExceptions')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_sql_pattern_exceptions" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SQL_FILTER_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_sql_pattern_exceptions" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_LFI_FILTER' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( !(strstr($this->system_info['firewall_options']['lfi_exceptions'],'*')) ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToFirewallExceptions')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_lfi_exceptions" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_LFI_FILTER_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_lfi_exceptions" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_SESSION_PROTECTION' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										// Chequeamos si la opción de compartir sesiones está activa; en este caso no aplicaremos esta opción para evitar una denegación de entrada
										$params          = JFactory::getConfig();		
										$shared_session_enabled = $params->get('shared_session');
		
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( ($this->system_info['firewall_options']['session_protection_active']) && (!$shared_session_enabled) ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToUserSessionProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_session_protection_active" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SESSION_PROTECTION_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_session_protection_active" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_SESSION_HIJACK_PROTECTION' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										// Chequeamos si la opción de compartir sesiones está activa; en este caso no aplicaremos esta opción para evitar una denegación de entrada
										$params          = JFactory::getConfig();		
										$shared_session_enabled = $params->get('shared_session');
										
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( ($this->system_info['firewall_options']['session_hijack_protection']) && (!$shared_session_enabled) ) {
											echo "<span class=\"label label-success\">OK</span>";
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToUserSessionProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_session_hijack_protection" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_INFO' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_session_hijack_protection" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_UPLOAD_SCANNER' ); ?></strong>
								<br/>
								<div class="securitycheck-bootstrap">
									<?php 
										if ( !$this->system_info['firewall_plugin_enabled'] ) {	
											echo "<span class=\"label label-warning\">" . JText::_( 'COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
										} else if ( $this->system_info['firewall_options']['upload_scanner_enabled'] ) {
											echo "<span class=\"label label-success\">OK</span>";
										
										} else {
											echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
									?>
										<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToUploadScanner')" href="#"><i class="icon-wrench icon-white"></i></button>
										<div id="modal_upload_scanner_enabled" class="modal hide fade">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
												<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
											</div>
											<div class="modal-body">
												<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_UPLOADSCANNER_DESCRIPTION' ); ?></p>
											</div>
											<div class="modal-footer">
												<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
											</div>
										</div>
										<a href="#modal_upload_scanner_enabled" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
									<?php }	?>														
								</div>
							</li>
						</ul>
					</li>
					<li style="margin-top: 10px;">
						<strong><span class="columna"><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_ENABLED' ); ?></strong></span>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['cron_plugin_enabled'] ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToCpanel')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_cron_enabled" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_CRON_ENABLED_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_cron_enabled" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>
						<ul>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_LAST_FILEMANAGER_CHECK' ); ?></strong>
								<br/>
								<?php 
									$last_check = new DateTime(date('Y-m-d',strtotime($this->system_info['last_check'])));
									$now = new DateTime(date('Y-m-d',strtotime(date('Y-m-d H:i:s'))));
					
									// Extraemos los días que han pasado desde el último chequeo
									(int) $interval = $now->diff($last_check)->format("%a");
																		
									if ( $interval < 2 ) {
										$span = "<span class=\"label label-success\">";
									} else {
										$span = "<span class=\"label label-warning\">";
									}
								?>
									<?php echo $span . $this->system_info['last_check']; ?>
									</span>
									<div class="securitycheck-bootstrap">							
										<?php 
											if ( $interval < 2 ) {
												echo "<span class=\"label label-success\">OK</span>";
											} else {
												echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
										?>
											<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToPermissions');"><i class="icon-wrench icon-white"></i></button>
											<div id="modal_last_check" class="modal hide fade">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
													<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
												</div>
												<div class="modal-body">
													<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_CHECK_INFO' ); ?></p>
												</div>
												<div class="modal-footer">
													<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
												</div>
											</div>
											<a href="#modal_last_check" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
										<?php }	?>														
									</div>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_LAST_FILEINTEGRITY_CHECK' ); ?></strong>
								<br/>
								<?php 
									$last_check_integrity = new DateTime(date('Y-m-d',strtotime($this->system_info['last_check_integrity'])));
									$now = new DateTime(date('Y-m-d',strtotime(date('Y-m-d H:i:s'))));
					
									// Extraemos los días que han pasado desde el último chequeo
									(int) $interval = $now->diff($last_check_integrity)->format("%a");
																		
									if ( $interval < 2 ) {
										$span = "<span class=\"label label-success\">";
									} else {
										$span = "<span class=\"label label-warning\">";
									}
								?>
									<?php echo $span . $this->system_info['last_check_integrity']; ?>
									</span>
									<div class="securitycheck-bootstrap">							
										<?php 
											if ( $interval < 2 ) {
												echo "<span class=\"label label-success\">OK</span>";
											} else {
												echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
										?>
											<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToIntegrity');"><i class="icon-wrench icon-white"></i></button>
											<div id="modal_last_check_integrity" class="modal hide fade">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
													<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
												</div>
												<div class="modal-body">
													<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_CHECK_INTEGRITY_INFO' ); ?></p>
												</div>
												<div class="modal-footer">
													<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
												</div>
											</div>
											<a href="#modal_last_check_integrity" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
										<?php }	?>														
									</div>
							</li>						
						</ul>
					</li>
					<li style="margin-top: 10px;">
						<strong><span class="columna"><?php echo JText::_( 'COM_SECURITYCHECKPRO_EXTENSION_STATUS_SPAM_PROTECTION_ENABLED' ); ?></strong></span>
						<br/>
						<div class="securitycheck-bootstrap">
							<?php 
								if ( $this->system_info['spam_protection_plugin_enabled'] ) {
									echo "<span class=\"label label-success\">OK</span>";
								} else {
									echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
							?>
								<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToCpanel')" href="#"><i class="icon-wrench icon-white"></i></button>
								<div id="modal_spam_protection_enabled" class="modal hide fade">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
									</div>
									<div class="modal-body">
										<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SPAM_PROTECTION_ENABLED_INFO' ); ?></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
									</div>
								</div>
								<a href="#modal_spam_protection_enabled" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
							<?php }	?>														
						</div>
					</li>
					<li style="margin-top: 10px;">
						<strong><span class="columna"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_HTACCESS_PROTECTION_TEXT' ); ?></strong></span>
						<br/>						
						<ul>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_ACCESS_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['prevent_access'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_prevent_access" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_prevent_access" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['prevent_unauthorized_browsing'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_prevent_unauthorized_browsing" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_prevent_unauthorized_browsing" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['file_injection_protection'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_file_injection_protection" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_file_injection_protection" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['self_environ'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_self_environ" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_self_environ" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_XFRAME_OPTIONS_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['xframe_options'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_xframe_options" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_xframe_options" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['prevent_mime_attacks'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_prevent_mime_attacks" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_prevent_mime_attacks" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['default_banned_list'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_default_banned_list" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_INFO' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_default_banned_list" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['disable_server_signature'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_disable_server_signature" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_disable_server_signature" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['disallow_php_eggs'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_disallow_php_eggs" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_disallow_php_eggs" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>
								<?php }	?>
							</li>
							<li style="margin-top: 10px;">
								<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_TEXT' ); ?></strong>
								<br/>
								<?php 
									if ( $this->system_info['htaccess_protection']['disallow_sensible_files_access'] ) {
										echo "<span class=\"label label-success\">OK</span>";
									} else {
										echo "<span class=\"label label-important\">" . JText::sprintf( 'COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND',1 ) . "</span>";
								?>
									<button class="btn btn-info btn-mini" type="button" onclick="Joomla.submitbutton('GoToHtaccessProtection')" href="#"><i class="icon-wrench icon-white"></i></button>
									<div id="modal_disallow_sensible_files_access" class="modal hide fade">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 style="color: #3986AC;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT' ); ?></</h3>
										</div>
										<div class="modal-body">
											<p><?php echo JText::_( 'COM_SECURITYCHECKPRO_DISALLOW_ACCESS_SENSIBLE_FILES_INFO' ); ?></p>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CLOSE' ); ?></button>
										</div>
									</div>
									<a href="#modal_disallow_sensible_files_access" role="button" class="btn btn-inverse btn-mini" data-toggle="modal"><?php echo JText::_( 'COM_SECURITYCHECKPRO_MORE_INFO' ); ?></a>									
								<?php }	?>
							</li>
						
						</ul>
					</li>
                 </ul>   
            </td>
        </tr>
		<tr>
            <td><h5><?php echo JText::_( 'COM_SECURITYCHECKPRO_GLOBAL_CONFIGURATION' ); ?></h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
                <ul>
					<li>
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSINFO_JOOMLAVERSION' ); ?></strong>
						<br/>
						<?php echo $this->system_info['version']; ?>											
					</li>                    					
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSINFO_JOOMLAPLATFORM' ); ?></strong>
						<br/>
						<?php echo $this->system_info['platform']; ?>										
					</li>
                 </ul>   
            </td>
        </tr>        
		
		<tr>
            <td><h5><?php echo JText::_( 'COM_SECURITYCHECKPRO_MYSQL_CONFIGURATION' ); ?></h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
                <ul>				
                    <li>
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSINFO_MAX_ALLOWED_PACKET' ); ?></strong>
						<br/>
						<?php echo $this->system_info['max_allowed_packet']; ?>M										
					</li>                                       
                 </ul>   
            </td>
        </tr>
		
		<tr>
            <td><h5><?php echo JText::_( 'COM_SECURITYCHECKPRO_PHP_CONFIGURATION' ); ?></h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
                <ul>
					<li>
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSINFO_PHPVERSION' ); ?></strong>
						<br/>
						<?php echo $this->system_info['phpversion']; ?>											
					</li>                    					
					<li style="margin-top: 10px;">
						<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SYSINFO_MEMORY_LIMIT' ); ?></strong>
						<br/>
						<?php echo $this->system_info['memory_limit']; ?>											
					</li>
                 </ul>   
            </td>
        </tr>
    </table>	
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="filemanager" />
</form>

<style type="text/css">
        body { font-family:Arial, Helvetica, Sans-Serif; font-size:0.8em;}
        #report { border-collapse:collapse;}
        #report h4 { margin:0px; padding:0px;}
		#report h5 { margin:0px; padding:0px;}
        #report img { float:right;}
        #report ul { margin:10px 0 10px 40px; padding:0px;}
        #report th { background:#7CB8E2 url(/media/com_securitycheckpro/images/header_bkg.png) repeat-x scroll center left; color:#fff; padding:7px 15px; text-align:left;}
        #report td { background:#C7DDEE none repeat-x scroll center left; color:#000; padding:7px 15px; }
        #report tr.odd td { background:#fff url(/media/com_securitycheckpro/images/row_bkg.png) repeat-x scroll center left; cursor:pointer; }
        #report div.arrow { background:transparent url(/media/com_securitycheckpro/images/arrows.png) no-repeat scroll 0px -16px; width:16px; height:16px; display:block;}
        #report div.up { background-position:0px 0px;}		
</style>
<script type="text/javascript">  
        $(document).ready(function(){
            $("#report tr:odd").addClass("odd");
           // $("#report tr:not(.odd)").hide();
            $("#report tr:first-child").show();
			            
            $("#report tr.odd").click(function(){
                $(this).next("tr").toggle();
                $(this).find(".arrow").toggleClass("up");
            });
            //$("#report").jExpand();
        });			
</script>
	
<script type="text/javascript" language="javascript">
		
		// Go to Joomla Update page
		function GoToJoomlaUpdate() {
			window.location.href="index.php?option=com_joomlaupdate";			
		}				
		
		// Go to Joomla Plugins page
		function GoToJoomlaPlugins() {
			window.location.href="index.php?option=com_plugins&view=plugins";			
		}	
</script>