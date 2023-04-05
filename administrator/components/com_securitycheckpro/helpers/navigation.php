<?php
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
?>

    <!-- Modal view file -->
        <div class="modal" id="view_logfile" tabindex="-1" role="dialog" aria-labelledby="viewlogfileLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header alert alert-info">
                <h2 class="modal-title" id="viewlogfileLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_MESSAGE'); ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <? ob_start(); ?>              
                <textarea rows="10" class="table">        
                <?php 
                $contenido = "There is no log info";
                if (!empty($this->log_filename)) {
                    if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR.$this->log_filename)) {
                        $contenido = file_get_contents(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR.$this->log_filename);        
                        $contenido = filter_var($contenido, FILTER_SANITIZE_SPECIAL_CHARS);
                    }            
                }        
                echo $contenido;                
                ?></textarea>
                <? echo ob_get_clean(); ?>
              </div>
                <div class="modal-footer">                    
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                </div>              
            </div>
          </div>
        </div>
        
<!-- Modal purgesessions -->
        <div class="modal fade" id="purgesessions" tabindex="-1" role="dialog" aria-labelledby="purgesessionsLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header alert alert-info">
                <h2 class="modal-title" id="purgesessionsLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_PURGE_SESSIONS'); ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">    
                <div id="div_messages">
                    <h5><?php echo JText::_('COM_SECURITYCHECKPRO_PURGE_SESSIONS_MESSAGE'); ?></h5>                        
                    <h5><?php echo JText::_('COM_SECURITYCHECKPRO_PURGE_SESSIONS_MESSAGE_EXPLAINED'); ?></h5>
                </div>
                <div id="div_loading" style="text-align:center; display:none;">
                    <span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_PURGING'); ?></span><br/>
                    <img src="<?php echo JURI::root(); ?>media/com_securitycheckpro/images/loading.gif" width="30" height="30" />
                </div>        
              </div>
                <div class="modal-footer" id="div_boton_subida">
                    <input class="btn btn-primary" type="button" id="boton_subida" value="<?php echo JText::_('COM_SECURITYCHECKPRO_YES'); ?>" onclick= "muestra_progreso_purge(); Joomla.submitbutton('purge_sessions');"  />
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_NO'); ?></button>
                </div>              
            </div>
          </div>
        </div>
        
        <!-- Modal initialize_data -->
        <div class="modal fade" id="initialize_data" tabindex="-1" role="dialog" aria-labelledby="initializedataLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header alert alert-info">
                <h2 class="modal-title" id="initializedataLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_INITIALIZE_DATA'); ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center">    
                <div id="warning_message" class="margen-loading texto_14">
        <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CLEAR_DATA_WARNING_START_MESSAGE'); ?>
                </div>
                <div id="completed_message" class="margen-loading texto_14 color_verde">    
                </div>
                <div id="loading-container" class="text-center margen">    
                </div>        
              </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="buttonwrapper" type="button" onclick="hideElement('buttonwrapper'); hideElement('buttonclose'); clear_data_button();"><i class="fapro fa-fw fa-fire"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CLEAR_DATA_CLEAR_BUTTON'); ?></button>
                    <button type="button" id="buttonclose" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                </div>              
            </div>
          </div>
        </div>
        
        <!-- Modal clean tmp dir -->
        <div class="modal fade" id="cleantmpdir" tabindex="-1" role="dialog" aria-labelledby="cleantmpdirLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header alert alert-info">
                <h2 class="modal-title" id="cleantmpdirLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_CLEAN_TMP_DIR'); ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center">    
                <div id="warning_message_tmpdir" class="margen-loading texto_14">
        <?php echo JText::_('COM_SECURITYCHECKPRO_CLEAN_TMP_DIR_MESSAGE'); ?>
                </div>
                <div id="completed_message_tmpdir" class="margen-loading texto_14">    
                </div>
                <div id="tmpdir-container" class="text-center margen">    
                </div>    
                <div id="container_result" class="text-center margen hide">    
                    <textarea id="container_result_area" rows="10" class="table" readonly>                        
                    </textarea>                
                </div>
              </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="buttonwrapper_tmpdir" type="button" onclick="hideElement('buttonwrapper_tmpdir'); hideElement('buttonclose_tmpdir'); clean_tmp_dir();"><i class="fapro fa-fw fa-fire"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CLEAR_DATA_CLEAR_BUTTON'); ?></button>
                    <button type="button" id="buttonclose_tmpdir" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                </div>              
            </div>
          </div>
        </div>

  <div class="fixed2-nav" id="page-top">
  <!-- Navigation-->
  <nav class="navbar2 navbar2-expand-lg navbar2-dark fixed-top" id="mainNav">
     <!--button class="navbar2-toggler bg-dark navbar2-toggler-right" type="button" data-toggle="collapse2" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar2-toggler-icon"></span>
    </button-->
    <div class="collapse2 navbar2-collapse2" id="navbarResponsive">
      <ul class="navbar2-nav navbar2-sidenav" id="contentbar">
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?>">
          <a class="nav2-link" href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>">
            <i class="fapro fa-fw fa-home"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></span>
          </a>
        </li>
        
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_SYSINFO_TEXT'); ?>">
          <a class="nav2-link" href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1');
            ?>">
            <i class="fapro fa-fw fa-info-square"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_SYSINFO_TEXT'); ?></span>
          </a>
        </li>
        
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CHECK_VULNERABILITIES_TEXT'); ?>">
          <a class="nav2-link" href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1');?>">
            <i class="fapro fa-fw fa-check-circle"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CHECK_VULNERABILITIES_TEXT'); ?></span>
          </a>
        </li>
        
         <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_FIREWALL_LOGS'); ?>">
          <a class="nav2-link" href="<?php echo 'index.php?option=com_securitycheckpro&controller=securitycheckpro&view=logs'?>">
            <i class="fapro fa-fw fa-eye"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_FIREWALL_LOGS'); ?></span>
    <?php	
    if ($this->logs_pending >= 99) {
        $this->logs_pending = "+99";
    }
    if ($this->logs_pending == 0) { ?>
                <span class="badge badge-success">
    <?php     } else
                    { ?>
                <span class="badge badge-warning">
    <?php	}
                echo $this->logs_pending;
    ?>
            </span>
          </a>          
        </li>
        
    <?php 
    if ($this->trackactions_plugin_exists) {                 
        ?>
             <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_TRACKACTIONS_LOGS'); ?>">
              <a class="nav2-link" href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&view=trackactions_logs');?>">
                <i class="fapro fa-fw fa-binoculars"></i>
                <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_TRACKACTIONS_LOGS'); ?></span>
              </a>
            </li>
        <?php
    }
    ?>
        
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_OPTIONS'); ?>">
          <a class="nav2-link nav2-link-collapse2 collapsed" data-toggle="collapse2" href="#cpaneloptions" data-parent="#contentbar">
            <i class="fapro fa-fw fa-list"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_OPTIONS'); ?></span>
          </a>
          <ul class="sidenav2-second-level collapse2" id="cpaneloptions">
    <?php 
                // Chequeamos si existe el fichero filemanager, necesario para lanzar las tareas de integridad y permisos
                $mainframe =JFactory::getApplication();
                $exists_filemanager = $mainframe->getUserState("exists_filemanager", true);
                    
    if ($exists_filemanager) {                        
        ?>    
                 <li>
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1');
                    ?>"><i class="fapro fa-fw fa-circle"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_MANAGER_TEXT'); ?></a>
                </li>
    <?php } ?>
    <?php 
                    // Chequeamos si existe el fichero filemanager, necesario para lanzar las tareas de integridad y permisos
                    $mainframe =JFactory::getApplication();
                    $exists_filemanager = $mainframe->getUserState("exists_filemanager", true);
                    
    if ($exists_filemanager) {                        
        ?>    
                <li>
                  <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filesintegrity&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-file-check"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_INTEGRITY_TEXT'); ?></a>
                </li>
    <?php } ?>        
                    
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=protection&view=protection&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-file-alt"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HTACCESS_PROTECTION_TEXT'); ?></a>
            </li>
            
    <?php 
                // Chequeamos si existe el fichero filemanager, necesario para lanzar las tareas de integridad y permisos
                $mainframe =JFactory::getApplication();
                $exists_filemanager = $mainframe->getUserState("exists_filemanager", true);
                    
    if ($exists_filemanager) {                        
        ?>
                <li>
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=malwarescan&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-bug"></i><?php echo JText::_('COM_SECURITYCHECKPRO_MALWARESCAN'); ?></a>
                </li>                
    <?php } ?>            
          </ul>
        </li>
        
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONFIGURATION'); ?>">
          <a class="nav2-link nav2-link-collapse2 collapsed" data-toggle="collapse2" href="#cpanelconfiguration" data-parent="#contentbar">
            <i class="fapro fa-fw fa-wrench"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONFIGURATION'); ?></span>
          </a>
          <ul class="sidenav2-second-level collapse2" id="cpanelconfiguration">    
            <li>
              <a href="index.php?option=com_config&view=component&component=com_securitycheckpro&path=&return=<?php echo base64_encode(JURI::getInstance()->toString()) ?>"><i class="fapro fa-fw fa-wrench"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_GLOBAL_CONFIGURATION'); ?></a>
            </li>
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-wrench"></i><?php echo JText::_('COM_SECURITYCHECKPRO_WAF_CONFIG'); ?></a>
            </li>
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=cron&view=cron&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-wrench"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CRON_CONFIGURATION'); ?></a>
            </li>
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=rules&view=rules&'. JSession::getFormToken() .'=1');
                ?>"><i class="fapro fa-fw fa-wrench"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'); ?></a>
            </li>
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=controlcenter&view=controlcenter&'. JSession::getFormToken() .'=1');
                ?>"><i class="fapro fa-fw fa-wrench"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONTROLCENTER_TEXT'); ?></a>
            </li>
          </ul>         
        </li>
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_TASKS'); ?>">
          <a class="nav2-link nav2-link-collapse2 collapsed" data-toggle="collapse2" href="#cpaneltasks" data-parent="#contentbar">
            <i class="fapro fa-fw fa-tasks"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_TASKS'); ?></span>
          </a>
          <ul class="sidenav2-second-level collapse2" id="cpaneltasks">             
            <li>
                <a href="#initialize_data" data-toggle="modal" data-target="#initialize_data"><i class="fapro fa-fw fa-undo"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_INITIALIZE_DATA'); ?></a>
            </li>
            <li>
              <a href="#" onclick="Joomla.submitbutton('Export_config');"><i class="fapro fa-fw fa-download"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_EXPORT_CONFIG'); ?></a>
            </li>
            <li>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=upload&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-upload"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_IMPORT_CONFIG'); ?></a>
            </li>
          </ul>
        </li>
            
        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_PERFORMANCE'); ?>">
          <a class="nav2-link nav2-link-collapse2 collapsed" data-toggle="collapse2" href="#performance" data-parent="#contentbar">
            <i class="fapro fa-fw fa-cogs"></i>
            <span class="nav2-link-text"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_PERFORMANCE'); ?></span>
          </a>
          <ul class="sidenav2-second-level collapse2" id="performance">
            <li>
				<?php
					$config = JFactory::getConfig();
					$dbtype = $config->get('dbtype');
					if (strstr($dbtype,"mysql")) {
				?>
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=dbcheck&view=dbcheck&'. JSession::getFormToken() .'=1');?>"><i class="fapro fa-fw fa-database"></i><?php echo JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'); ?></a>
				<?php
					}
				?>
            </li>
            <li>
              <a href="#purge_sessions" data-toggle="modal" data-target="#purgesessions"><i class="fapro fa-fw fa-user-times"></i><?php echo JText::_('COM_SECURITYCHECKPRO_PURGE_SESSIONS'); ?></a>
            </li> 
            <li>
              <a href="#clean_tmp_dir" data-toggle="modal" data-target="#cleantmpdir"><i class="fapro fa-fw fa-recycle"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CLEAN_TMP_DIR'); ?></a>
            </li>
          </ul>
        </li>  

        <li class="nav2-item" data-toggle="tooltip" data-placement="right" title="<?php JText::_('OTP'); ?>">
            <a class="nav2-link" href="#" onclick="get_otp_status();"><i class="fapro fa-fw fa-sign-in"></i><?php echo JText::_('OTP'); ?></a>      
        </li>
            
      </ul>
      <ul class="navbar2-nav sidenav2-toggler">
        <li class="nav2-item">
          <a class="nav2-link text-center" id="sidenavToggler">
            <i class="fapro fa-fw fa-angle-left"></i>
          </a>
        </li>
      </ul>     
    </div>
  </nav>
  
<!-- Main panel -->
<div class="content-wrapper">
        <div class="container-fluid" style="margin-left: 5px;">