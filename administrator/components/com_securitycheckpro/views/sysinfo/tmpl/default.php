<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JSession::checkToken('get') or die('Invalid Token');

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

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

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

?>
<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/sysinfo.php';
?>


<form action="index.php" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

<?php 
        // Cargamos la navegación
        require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
?>    
        
          <!-- Breadcrumb-->
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
             <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
            </li>
            <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSTEM_INFORMATION'); ?></li>
          </ol>
                        
            <!-- Contenido principal -->
            
            <div class="card mb-3" class="margin-left-10">
                <div class="card-header">
                  <i class="fapro fa-table"></i>
        <?php echo JText::_('COM_SECURITYCHECKPRO_SYSTEM_INFORMATION'); ?>
                </div>
                <div class="card-body">
                                    
                    <ul class="nav nav-tabs" role="tablist" id="sysinfoTab">
                      <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#overall_status" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_OVERALL_STATUS'); ?></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#extension_status" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS'); ?></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#global_configuration" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_CONFIGURATION'); ?></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#mysql_configuration" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_MYSQL_CONFIGURATION'); ?></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#php_configuration" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_PHP_CONFIGURATION'); ?></a>
                      </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane show active" id="overall_status" role="tabpanel">
                            <!-- Overall status -->
                            <div class="card mb-3">
                                    <div class="card-header">
            <?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_OVERALL_STATUS'); ?>
                                    </div>
                                <div class="card-body">
                                    <div class="progress">
            <?php 
            if ($this->system_info['overall_joomla_configuration'] <=50 ) {
                $div = "<div class=\"progress-bar bg-danger\"";
            } else if (($this->system_info['overall_joomla_configuration'] >50) && ($this->system_info['overall_joomla_configuration'] <=70) ) {
                $div = "<div class=\"progress-bar bg-warning\"";
            } else {
                $div = "<div class=\"progress-bar bg-success\"";
            }
            ?>                    
            <?php echo $div . " role=\"progressbar\" style=\"width: " . $this->system_info['overall_joomla_configuration'] ."%\">" . $this->system_info['overall_joomla_configuration']; ?>
                                        </div>                        
                                    </div>
                                    <br/>
                                    
                                     <div class="row">
                                    
                                        <!-- Akeeba files -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_AKEEBA_RESTORATION_FILES_FOUND'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['kickstart_exists'] ) {
                                                    $span = "<span class=\"badge badge-danger\">" . JText::_("COM_SECURITYCHECKPRO_YES");
                                                } else {                                
                                                    $span = "<span class=\"badge badge-success\">" . JText::_("COM_SECURITYCHECKPRO_NO");
                                                }
                                                ?>                        
                                                </span>
                                                <div>                            
                <?php 
                if (!$this->system_info['kickstart_exists'] ) {
                    echo "<span class=\"badge badge-success\">OK</span>";
                } else {
                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                    ?>
                                                        
                                                    <!-- Modal Akeeba restoration -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_akeeba_restoration" tabindex="-1" role="dialog" aria-labelledby="modal_akeeba_restorationLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_AKEEBA_RESTORATION_FILES_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                
                                                        <a href="#modal_akeeba_restoration" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                <?php }    ?>                                                        
                                                </div>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Up to date -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_UP_TO_DATE'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (version_compare($this->system_info['coreinstalled'], $this->system_info['corelatest'], '==') ) {
                                                    $span = "<span class=\"badge badge-success\">";
                                                } else {
                                                    $span = "<span class=\"badge badge-danger\">";
                                                }
                                                ?>
                                                <?php echo $span . $this->system_info['coreinstalled']; ?>
                                                </span>
                                                <div>                            
                <?php 
                if (version_compare($this->system_info['coreinstalled'], $this->system_info['corelatest'], '==') ) {
                    echo "<span class=\"badge badge-success\">OK</span>";
                } else {
                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                    ?>
                                                        <button class="btn btn-info btn-sm" id="GoToJoomlaUpdate_button" type="button"><i class="icon-wrench icon-white"></i></button>
                <?php }    ?>                                                        
                                                </div>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Vulnerable extensions -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_VULNERABLE_EXTENSIONS'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['vuln_extensions'] == 0 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', $this->system_info['vuln_extensions']) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" id="GoToVuln_button" type="button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal vuln extensions -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_vuln_extensions" tabindex="-1" role="dialog" aria-labelledby="modal_vuln_extensionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_VULN_EXTENSIONS_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>
                                                    <a href="#modal_vuln_extensions" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Malware -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_MALWARE_FOUND'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['suspicious_files'] == 0 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', $this->system_info['suspicious_files']) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" id="GoToMalware_button" type="button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal malware found -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_malware_found" tabindex="-1" role="dialog" aria-labelledby="modal_malware_foundLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_MALWARE_FOUND_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_malware_found" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- File integrity -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_NO_FILES_MODIFIED'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['files_with_bad_integrity'] == 0 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', $this->system_info['files_with_bad_integrity']) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" id="GoToIntegrity_button" type="button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal file integrity -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_files_with_bad_integrity" tabindex="-1" role="dialog" aria-labelledby="modal_files_with_bad_integrityLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FILES_BAD_INTEGRITY_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_files_with_bad_integrity" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- File permissions -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_PERMISSIONS'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['files_with_incorrect_permissions'] == 0 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', $this->system_info['files_with_incorrect_permissions']) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" id="GoToPermissions_button" type="button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal file permissions -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_file_permissions" tabindex="-1" role="dialog" aria-labelledby="modal_file_permissionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_PERMISSIONS_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                                
                                                    <a href="#modal_file_permissions" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Hide backend -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_SECURITY_HIDE_BACKEND'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['backend_protection'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" id="GoToHtaccessProtection_button" type="button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal Hide backend -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_hide_backend" tabindex="-1" role="dialog" aria-labelledby="modal_hide_backendLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_hide_backend" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- New admins -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['firewall_options']['forbid_new_admins'] == 1 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_session_protection_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal forbid new admins -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_forbid_new_admins" tabindex="-1" role="dialog" aria-labelledby="modal_forbid_new_adminsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_forbid_new_admins" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Two factor -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_TWO_FACTOR_ENABLED_LABEL'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['twofactor_enabled'] >= 1 ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_joomla_plugins_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal two factor -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_two_factor_enabled" tabindex="-1" role="dialog" aria-labelledby="modal_two_factor_enabledLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_TWO_FACTOR_ENABLED_LABEL_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_two_factor_enabled" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Http headers -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-primary"><?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_LABEL'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (($this->system_info['htaccess_protection']['xframe_options'] > 0) && ($this->system_info['htaccess_protection']['sts_options'] > 0) && ($this->system_info['htaccess_protection']['xss_options'] > 0) && ($this->system_info['htaccess_protection']['csp_policy'] > 0) && ($this->system_info['htaccess_protection']['referrer_policy'] > 0) && ($this->system_info['htaccess_protection']['prevent_mime_attacks'] > 0) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_headers_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal http headers -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_http_headers" tabindex="-1" role="dialog" aria-labelledby="modal_http_headersLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_HTTP_HEADERS_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_http_headers" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                    <!-- row -->
                                    </div>                            
                                </div> 
                            </div>                                                
                        </div>    

                        <div class="tab-pane" id="extension_status" role="tabpanel">
                            <!-- Extension status -->
                            <div class="card mb-3">
                                    <div class="card-header">
            <?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS'); ?>
                                    </div>
                                <div class="card-body">
                                    <div class="progress">
            <?php 
            if ($this->system_info['overall_web_firewall'] <=50 ) {
                $div = "<div class=\"progress-bar bg-danger\"";
            } else if (($this->system_info['overall_web_firewall'] >50) && ($this->system_info['overall_web_firewall'] <=70) ) {
                $div = "<div class=\"progress-bar bg-warning\"";
            } else {
                $div = "<div class=\"progress-bar bg-success\"";
            }
            ?>    
            <?php echo $div . " role=\"progressbar\" style=\"width: " . $this->system_info['overall_web_firewall'] ."%\">" . $this->system_info['overall_web_firewall']; ?>
                                        </div>                        
                                    </div>
                                    <br/>
                                    
                                    <div class="row">
                                    
                                        <!-- Firewall enabled -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_TWO_FACTOR_ENABLED_LABEL'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['firewall_plugin_enabled'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_twofactor_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal firewall enabled -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_firewall_plugin_enabled" tabindex="-1" role="dialog" aria-labelledby="modal_firewall_plugin_enabledLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FIREWALL_ENABLED_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_firewall_plugin_enabled" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Dynamic blacklist -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_DYNAMIC_BLACKLIST'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if ($this->system_info['firewall_options']['dynamic_blacklist'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                    
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_security_status_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal firewall enabled -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_dynamic_blacklist" tabindex="-1" role="dialog" aria-labelledby="modal_dynamic_blacklistLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FIREWALL_ENABLED_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_dynamic_blacklist" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Dynamic blacklist -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_LOGS'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else     if ($this->system_info['firewall_options']['logs_attacks'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_security_status_logs_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal firewall enabled -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_logs_attacks" tabindex="-1" role="dialog" aria-labelledby="modal_logs_attacksLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_LOG_ATTACKS_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_logs_attacks" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Second level -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_SECOND_LEVEL'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else     if ($this->system_info['firewall_options']['second_level'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                    
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_second_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal firewall enabled -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_second_level" tabindex="-1" role="dialog" aria-labelledby="modal_second_levelLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SECOND_LEVEL_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_second_level" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Exceptions -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_EXCLUDE_EXCEPTIONS'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if ($this->system_info['firewall_options']['exclude_exceptions_if_vulnerable'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";                                        
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_exclude_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal exceptions -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_exclude_exceptions_if_vulnerable" tabindex="-1" role="dialog" aria-labelledby="modal_second_levelLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_exclude_exceptions_if_vulnerable" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Xss filter -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_XSS_FILTER'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else     if (!(strstr($this->system_info['firewall_options']['strip_tags_exceptions'], '*')) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_xss_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal xss filter -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_strip_tags_exceptions" tabindex="-1" role="dialog" aria-labelledby="modal_strip_tags_exceptionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_XSS_FILTER_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_strip_tags_exceptions" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- SQL filter -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_SQL_FILTER'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if (!(strstr($this->system_info['firewall_options']['sql_pattern_exceptions'], '*')) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_sql_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal SQL filter -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_sql_pattern_exceptions" tabindex="-1" role="dialog" aria-labelledby="modal_sql_pattern_exceptionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SQL_FILTER_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_sql_pattern_exceptions" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- LFI filter -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_LFI_FILTER'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if (!(strstr($this->system_info['firewall_options']['lfi_exceptions'], '*')) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_lfi_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal LFI filter -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_lfi_exceptions" tabindex="-1" role="dialog" aria-labelledby="modal_lfi_exceptionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SQL_FILTER_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_lfi_exceptions" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Session protection -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_SESSION_PROTECTION'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                    // Chequeamos si la opción de compartir sesiones está activa; en este caso no aplicaremos esta opción para evitar una denegación de entrada
                                                    $params          = JFactory::getConfig();        
                                                    $shared_session_enabled = $params->get('shared_session');
                    
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if (($this->system_info['firewall_options']['session_protection_active']) && (!$shared_session_enabled) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_session_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal Session protection -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_session_protection_active" tabindex="-1" role="dialog" aria-labelledby="modal_session_protection_activeLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SESSION_PROTECTION_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_session_protection_active" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Session hijack -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_SESSION_HIJACK_PROTECTION'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                    // Chequeamos si la opción de compartir sesiones está activa; en este caso no aplicaremos esta opción para evitar una denegación de entrada
                                                    $params          = JFactory::getConfig();        
                                                    $shared_session_enabled = $params->get('shared_session');
                                                    
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if (($this->system_info['firewall_options']['session_hijack_protection']) && (!$shared_session_enabled) ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_session_hijack_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal Session hijack -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_session_hijack_protection" tabindex="-1" role="dialog" aria-labelledby="modal_session_hijack_protectionLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SESSION_HIJACK_PROTECTION_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_session_hijack_protection" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Upload scanner -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_UPLOAD_SCANNER'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if ($this->system_info['firewall_options']['upload_scanner_enabled'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                    
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_upload_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal upload scanner -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_upload_scanner_enabled" tabindex="-1" role="dialog" aria-labelledby="modal_upload_scanner_enabledLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DESCRIPTION'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_upload_scanner_enabled" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Cron enabled -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_ENABLED'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if (!$this->system_info['firewall_plugin_enabled'] ) {    
                                                    echo "<span class=\"badge badge-warning\">" . JText::_('COM_SECURITYCHECKPRO_ENABLE_FIREWALL_TO_APPLY') . "</span>";
                                                } else if ($this->system_info['firewall_options']['upload_scanner_enabled'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                    
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_cron_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal cron enabled -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_cron_enabled" tabindex="-1" role="dialog" aria-labelledby="modal_cron_enabledLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_CRON_ENABLED_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_cron_enabled" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Last filemanager -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_LAST_FILEMANAGER_CHECK'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                    $last_check = $this->system_info['last_check'];
													require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
													$global_model = new SecuritycheckproModel();
                                                    $now = $global_model->get_Joomla_timestamp();
													
													$seconds = strtotime($now) - strtotime($last_check);
													// Extraemos los días que han pasado desde el último chequeo
													$interval = intval($seconds/86400);	                                            
                                                                                        
                                                if ($interval < 2 ) {
                                                    $span = "<span class=\"badge badge-success\">";
                                                } else {
                                                    $span = "<span class=\"badge badge-warning\">";
                                                }
                                                ?>
												<?php echo $span . $this->system_info['last_check']; ?>
                                                    </span>
                <?php 
                if ($interval < 2 ) {
                    echo "<span class=\"badge badge-success\">OK</span>";
                } else {
                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                    ?>                                            
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_filemanager_check_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal last filemanager -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_last_check" tabindex="-1" role="dialog" aria-labelledby="modal_last_checkLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_LAST_CHECK_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_last_check" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Last fileintegrity -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_CRON_LAST_FILEINTEGRITY_CHECK'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                    $last_check_integrity = $this->system_info['last_check_integrity'];
                                                    require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
													$global_model = new SecuritycheckproModel();
                                                    $now = $global_model->get_Joomla_timestamp();
													
													$seconds = strtotime($now) - strtotime($last_check_integrity);
													// Extraemos los días que han pasado desde el último chequeo
													$interval = intval($seconds/86400);	            
                                                                                        
                                                if ($interval < 2 ) {
                                                    $span = "<span class=\"badge badge-success\">";
                                                } else {
                                                    $span = "<span class=\"badge badge-warning\">";
                                                }
                                                ?>
                <?php echo $span . $this->system_info['last_check_integrity']; ?>
                                                    </span>
                <?php 
                if ($interval < 2 ) {
                    echo "<span class=\"badge badge-success\">OK</span>";
                } else {
                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                    ?>                                            
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_fileintegrity_check_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal last fileintegrity -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_last_check_integrity" tabindex="-1" role="dialog" aria-labelledby="modal_last_check_integrityLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_LAST_CHECK_INTEGRITY_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_last_check_integrity" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Spam protection -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION_STATUS_SPAM_PROTECTION_ENABLED'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['spam_protection_plugin_enabled'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_spam_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal spam protection -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_spam_protection_enabled" tabindex="-1" role="dialog" aria-labelledby="modal_spam_protection_enabledLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SPAM_PROTECTION_ENABLED_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_spam_protection_enabled" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- htaccess protection -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HTACCESS_PROTECTION_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['prevent_access'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_htaccess_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal htaccess protection -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_prevent_access" tabindex="-1" role="dialog" aria-labelledby="modal_prevent_accessLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_ACCESS_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_prevent_access" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- unauthorized browsing -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['prevent_unauthorized_browsing'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_browsing_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal unauthorized browsing -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_prevent_unauthorized_browsing" tabindex="-1" role="dialog" aria-labelledby="modal_prevent_unauthorized_browsingLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_UNAUTHORIZED_BROWSING_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_prevent_unauthorized_browsing" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- File injection -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['file_injection_protection'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_file_injection_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal file injection -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_file_injection_protection" tabindex="-1" role="dialog" aria-labelledby="modal_file_injection_protectionLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_FILE_INJECTION_PROTECTION_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_file_injection_protection" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Self environ -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['self_environ'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_self_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal self environ -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_self_environ" tabindex="-1" role="dialog" aria-labelledby="modal_self_environLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_SELF_ENVIRON_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_self_environ" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Xframe options -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['xframe_options'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_xframe_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal xframe options -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_xframe_options" tabindex="-1" role="dialog" aria-labelledby="modal_xframe_optionsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_XFRAME_OPTIONS_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_xframe_options" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Mime attacks -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['prevent_mime_attacks'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_mime_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal mime attacks -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_prevent_mime_attacks" tabindex="-1" role="dialog" aria-labelledby="modal_prevent_mime_attacksLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_PREVENT_MIME_ATTACKS_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_prevent_mime_attacks" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Default banned list -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['default_banned_list'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_default_banned_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal default banned list -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_default_banned_list" tabindex="-1" role="dialog" aria-labelledby="modal_default_banned_listLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_DEFAULT_BANNED_LIST_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_default_banned_list" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Disable server signature -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['disable_server_signature'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_signature_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal disable server signature -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_disable_server_signature" tabindex="-1" role="dialog" aria-labelledby="modal_disable_server_signatureLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE_SERVER_SIGNATURE_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_disable_server_signature" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Disallow php eggs -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['disallow_php_eggs'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_eggs_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal disallow php eggs -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_disallow_php_eggs" tabindex="-1" role="dialog" aria-labelledby="modal_disallow_php_eggsLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_PHP_EGGS_EXPLAIN'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_disallow_php_eggs" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>
                                        
                                        <!-- Disallow sensible files -->
                                        <div class="col-xl-3 mb-3">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_SENSIBLE_FILES_ACCESS_TEXT'); ?></li>
                                            <li class="list-group-item">
                                                <?php 
                                                if ($this->system_info['htaccess_protection']['disallow_php_eggs'] ) {
                                                    echo "<span class=\"badge badge-success\">OK</span>";
                                                } else {
                                                    echo "<span class=\"badge badge-danger\">" . JText::sprintf('COM_SECURITYCHECKPRO_SECURITY_PROBLEM_FOUND', 1) . "</span>";
                                                    ?>
                                                    <button class="btn btn-info btn-sm" type="button" id="li_extension_status_sensible_button" href="#"><i class="icon-wrench icon-white"></i></button>
                                                    <!-- Modal disallow sensible files -->
                                                    <div class="modal hide bd-example-modal-lg" id="modal_disallow_sensible_files_access" tabindex="-1" role="dialog" aria-labelledby="modal_disallow_sensible_files_accessLabel" aria-hidden="true">
                                                          <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                              <div class="modal-header alert alert-info">
                                                                <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_WHY_IS_THIS_IMPORTANT'); ?></h2>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>                
                                                              </div>
                                                              <div class="modal-body">    
                                                                <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_DISALLOW_ACCESS_SENSIBLE_FILES_INFO'); ?></span><br/><br/>    
                                                              </div>
                                                                <div class="modal-footer">
                                                                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                                                                </div>              
                                                            </div>
                                                          </div>
                                                        </div>                                            
                                                    <a href="#modal_disallow_sensible_files_access" role="button" class="btn btn-secondary btn-sm" data-toggle="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                                                <?php }    ?>                            
                                            </li>
                                        </ul>
                                        </div>                                
                                    </div>
                                </div>
                            </div>                        
                        </div>
                        
                        <div class="tab-pane" id="global_configuration" role="tabpanel">
                            <!-- Global configuration -->
                            <div class="card mb-3">
                                    <div class="card-header">
            <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_CONFIGURATION'); ?>
                                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Joomla version -->
                                        <div class="col-xl-3 mb-3">
                                            <ul class="list-group">                                
                                                <li class="list-group-item list-group-item-success"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSINFO_JOOMLAVERSION'); ?></li>
                                                <li class="list-group-item"><?php echo $this->system_info['version']; ?></li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Joomla platform -->
                                        <div class="col-xl-3 mb-3">
                                            <ul class="list-group">                                
                                                <li class="list-group-item list-group-item-success"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSINFO_JOOMLAPLATFORM'); ?></li>
                                                <li class="list-group-item"><?php echo $this->system_info['platform']; ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="mysql_configuration" role="tabpanel">
                            <!-- Mysql configuration -->
                            <div class="card mb-3">
                                    <div class="card-header">
            <?php echo JText::_('COM_SECURITYCHECKPRO_MYSQL_CONFIGURATION'); ?>
                                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <ul class="list-group">                                
                                            <li class="list-group-item list-group-item-warning"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSINFO_MAX_ALLOWED_PACKET'); ?></li>
                                            <li class="list-group-item"><?php echo $this->system_info['max_allowed_packet']; ?>M</li>
                                        </ul>                                                
                                    </div>
                                </div> 
                            </div>                        
                        </div>
                        
                        <div class="tab-pane" id="php_configuration" role="tabpanel">
                            <!-- PHP configuration -->
                            <div class="card mb-3">
                                    <div class="card-header">
            <?php echo JText::_('COM_SECURITYCHECKPRO_PHP_CONFIGURATION'); ?>
                                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Phpversion -->
                                        <div class="col-xl-3 mb-3">
                                            <ul class="list-group">                                
                                                <li class="list-group-item list-group-item-secondary"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSINFO_PHPVERSION'); ?></li>
                                                <li class="list-group-item"><?php echo $this->system_info['phpversion']; ?></li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Memory limit -->
                                        <div class="col-xl-3 mb-3">
                                            <ul class="list-group">                                
                                                <li class="list-group-item list-group-item-secondary"><?php echo JText::_('COM_SECURITYCHECKPRO_SYSINFO_MEMORY_LIMIT'); ?></li>
                                                <li class="list-group-item"><?php echo $this->system_info['memory_limit']; ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        
                    <!-- Tab content -->
                    </div>                    
                </div>        
            </div>    
        </div>    
</div>


<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="filemanager" />
</form>
