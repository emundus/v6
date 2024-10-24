﻿<?php 

/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JSession::checkToken('get') or die('Invalid Token');

$kind_array = array(JHtml::_('select.option', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE')),
            JHtml::_('select.option', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER')));

$status_array = array(JHtml::_('select.option', '0', JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_TITLE_COMPROMISED')),
            JHtml::_('select.option', '1', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_OK')),
            JHtml::_('select.option', '2', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_EXCEPTIONS')));

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

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);
?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/fileintegrity.php';
?>

<?php
if (empty($this->last_check_integrity) ) {
    $this->last_check_integrity = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_NEVER');
}
if (empty($this->files_status) ) {
    $this->files_status = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_NOT_DEFINED');
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filesintegrity&'. JSession::getFormToken() .'=1');?>" method="post" class="margin-top-minus18" name="adminForm" id="adminForm">

<?php 
        
        // Cargamos la navegación
        require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
?>
        
          <!-- Breadcrumb-->
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
            </li>            
            <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_INTEGRITY_TEXT'); ?></li>
          </ol>
            
            <!-- Contenido principal -->            
            <div class="row">
            
                <div class="col-xl-6 col-sm-6 mb-6 margin-bottom-3rem">
                    <div class="card text-center">    
                        <div class="card-header">
        <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STATUS'); ?>                            
                        </div>
                        <div class="row card-body justify-content-center">                                
                            <div class="margin-right-10">
                                <ul class="list-group text-center">
                                    <li class="list-group-item active font-size-13"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_STARTTIME'); ?></li>
                                    <li class="list-group-item"><span id="start_time" class="badge badge-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_NEVER'); ?></span></li>
                                </ul>
                            </div>
                            <div class="margin-right-10">
                                <ul class="list-group text-center">
                                    <li class="list-group-item active font-size-13"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_ENDTIME'); ?></li>
                                    <li class="list-group-item"><span id="end_time" class="badge badge-dark"><?php echo $this->files_status; ?></span></li>
                                </ul>                                
                            </div>
                            <div class="margin-right-10">
                                <ul class="list-group text-center">
                                    <li class="list-group-item active font-size-13"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_TASK'); ?></li>
                                    <li class="list-group-item">
                                        <span id="task_status" class="badge badge-info"><?php echo $this->files_status; ?></span>
                                        <span id="task_error" class="badge badge-danger display-none">Error</span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <ul class="list-group text-center">
                                    <li class="list-group-item active font-size-13"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_HASH_ALG'); ?></li>
                                    <li class="list-group-item"><span id="end_time" class="badge badge-dark"><?php echo $this->hash_alg; ?></span></li>
                                </ul>                                
                            </div>                            
                        </div>                        
                        <div id="button_start_scan" class="card-footer">
                            <button class="btn btn-primary" type="button" id="button_start_scan"><i class="fapro fa-fw fa-fire"></i><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON'); ?></button>
                        </div>                        
                    </div>
                </div>
                
                <div class="col-xl-5 col-sm-5 mb-5">
                    <div class="card text-center">    
                        <div class="card-header">
        <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_CHECK_INTEGRITY_RESULT_HEADER'); ?>
                        </div>
                        <div class="row card-body justify-content-center">
                            <div class="margin-right-10">
                                <ul class="list-group text-center">
                                    <li class="list-group-item text-white bg-success"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_LAST_CHECK'); ?></li>
                                    <li class="list-group-item"><span class="badge badge-dark"><?php echo $this->last_check_integrity; ?></span></li>
                                </ul>
                            </div>
                            <div class="margin-right-10">
                                <ul class="list-group text-center">
                                    <li class="list-group-item text-white bg-success"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_FILES_SCANNED'); ?></li>
                                    <li class="list-group-item"><span class="badge badge-dark"><?php echo $this->files_scanned_integrity; ?></span></li>
                                </ul>                                
                            </div>
                            <div>
                                <ul class="list-group text-center">
                                    <li class="list-group-item text-white bg-success font-size-13"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_FILES_MODIFIED'); ?></li>
                                    <li class="list-group-item">
                                        <span class="badge badge-dark"><?php echo $this->files_with_bad_integrity; ?></span>
                                    </li>
                                </ul>
                            </div>                        
                        </div>    
                        <div id="button_show_log" class="card-footer">    
        <?php	                            
        if (!empty($this->log_filename) ) { ?>
                                    <button class="btn btn-success" type="button" id="view_modal_log_button"><i class="fapro fa-fw fa-eye"></i><?php echo substr(JText::_('COM_SECURITYCHECKPRO_ACTION_VIEWLOGS'), 0, -1); ?></button>
        <?php }    ?>                            
                        </div>    
                    </div>                    
                </div>
                
                 <div id="scandata" class="col-lg-12">
                    <div class="card mb-3">                        
                        <div class="card-body margin-left-10">
                            <div id="container_repair">
                                <div id="log-container_remember_text" class="centrado margen texto_14">
                                </div>
                                <div id="div_view_log_button" class="buttonwrapper">    
                                </div>                            
                                <div id="log-container_header" class="centrado margen texto_20">    
                                </div>
                            </div>
                            
                            <div id="error_message_container" class="securitycheck-bootstrap centrado margen-container">
                                <div id="error_message">
                                </div>    
                            </div>

                            <div id="error_button" class="securitycheck-bootstrap centrado margen-container">    
                            </div>
                            
                            <div id="memory_limit_message" class="centrado margen-loading">
                                <?php 
                                    // Extract 'memory_limit' value cutting the last character
                                    $memory_limit = ini_get('memory_limit');
                                    $memory_limit = (int) substr($memory_limit, 0, -1);
                                            
                                    // If $memory_limit value is less or equal than 128, shows a warning if no previous scans have finished
                                if (($memory_limit <= 128) && ($this->last_check_integrity == JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_NEVER')) ) {
                                    $span = "<div class=\"alert alert-warning\">";
                                    echo $span . JText::_('COM_SECURITYCHECKPRO_MEMORY_LIMIT_LOW') . "</div>";
                                }
                                ?>
                            </div>

                            <div id="scan_only_executable_message" class="centrado margen-loading">
                                <?php 
                                if ($this->scan_executables_only ) {
                                    $span = "<div class=\"alert alert-warning\">";
                                    echo $span . JText::_('COM_SECURITYCHECKPRO_SCAN_ONLY_EXECUTABLES_WARNING') . "</div>";
                                }
                                ?>
                            </div>

                            <div id="completed_message2" class="centrado margen-loading color_verde">    
                            </div>
                                                        
                            <div id="warning_message2" class="centrado margen-loading">                                
                            </div>
                                                                            
                            <div id="backup-progress" class="progress">
                                <div id="bar" class="progress-bar bg-success width-0" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>    
                        <div id="container_resultado">
                            <div id="filter-bar" class="btn-toolbar">
                                <div class="filter-search btn-group pull-left">
                                    <input type="text" name="filter_fileintegrity_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_fileintegrity_search" value="<?php echo $this->escape($this->state->get('filter.fileintegrity_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
                                </div>
                                <div class="btn-group pull-left">
                                    <button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                                    <button class="btn tip" type="button" id="filter_fileintegrity_search_clear" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                                </div>
                                
                                <div class="btn-group pull-left margin-left-10">
                                    <select name="filter_fileintegrity_status" class="custom-select" onchange="this.form.submit()">
                                        <option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_STATUS_DESCRIPTION');?></option>
            <?php echo JHtml::_('select.options', $status_array, 'value', 'text', $this->state->get('filter.fileintegrity_status'));?>
                                    </select>                
                                </div>    
                            </div>
                                            
        <?php if (!$this->items == null) { ?>
            <?php if (($this->files_with_bad_integrity > 0 ) && ( empty($this->items) ) ) { ?>
                            <div class="alert alert-danger">
                <?php echo JText::_('COM_SECURITYCHECKPRO_EMPTY_ITEMS'); ?>
                            </div>                            
            <?php } ?>

            <?php if ($this->database_error == "DATABASE_ERROR" ) { ?>
                            <div class="alert alert-danger">
                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_DATABASE_ERROR'); ?>
                            </div>                            
            <?php } ?>

            <?php if ($this->files_with_bad_integrity >3000 ) { ?>
                            <div class="alert alert-danger">
                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ALERT') . "."; ?>
                                <br/>
                <?php echo JText::_('COM_SECURITYCHECKPRO_EMAIL_ALERT_BODY_ALERT'); ?>                                
                            </div>                            
            <?php } ?>

            <?php if ($this->show_all == 1 ) { ?>
                            <div class="alert alert-info">
                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_INFO'); ?>
                            </div>                            
            <?php } ?>
                            
                            <div class="card margin-top-30 margin-bottom-20">
                                <div class="card-header text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_COLOR_CODE'); ?>
                                </div>
                                <div class="card-block">                                    
                                    <table class="table table-striped margin-top-30">
                                        <thead>
                                            <tr>
                                                <td><span class="badge badge-success"> </span>
                                                </td>
                                                <td class="left">
                                                    <?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_GREEN_COLOR'); ?>
                                                </td>
                                                <td><span class="badge badge-warning"> </span>
                                                </td>
                                                <td class="left">
                                                    <?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_YELLOW_COLOR'); ?>
                                                </td>
                                                <td><span class="badge badge-danger"> </span>
                                                </td>
                                                <td class="left">
                                                    <?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_RED_COLOR'); ?>
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>                                
                                </div>                            
                            </div>                        
                            
            <?php
            if ((!empty($this->items)) && (!$this->state->get('filter.fileintegrity_status')) ) {            
                ?>
                                <div id="permissions_buttons" class="btn-toolbar">
                                    <div class="pull-right">
                                        <button class="btn btn-success margin-right-5" id="add_exception_button" href="#">
                                            <i class="fapro fa-fw fa-plus"> </i>
                <?php echo JText::_('COM_SECURITYCHECKPRO_ADD_AS_EXCEPTION'); ?>
                                        </button>                                        
                                    </div>
                                </div>
                <?php
            } else if ($this->state->get('filter.fileintegrity_status') == 2 ) { ?>
                                    <div id="permissions_buttons" class="btn-toolbar">
                                        <div class="btn-group pull-right">
                                            <button class="btn btn-danger" id="delete_exception_button" href="#">
                                                <i class="icon-trash icon-white"> </i>
                <?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_EXCEPTION'); ?>
                                            </button>
                                        </div>
                                </div>

            <?php } ?>

                                <div>
                                    <span class="badge integrity-files padding-10-10-10-10"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_CHECKED_FILES');?></span>
            <?php
            if (!empty($this->items) ) {    
                $extensions_updated_tooltip = JText::_('COM_SECURITYCHECKPRO_EXTENSIONS_UPDATED_INSTALLED_DESC') . "<br /><br />";
                if (is_array($this->installs)) {
                    $extensions_updated = count($this->installs);                                            
                    foreach($this->installs as $extension)
                    {
                        $extensions_updated_tooltip .= htmlentities($extension['name'], ENT_QUOTES) . " (" . htmlentities($extension['type'], ENT_QUOTES) . ")<br />";
                    }
                } else
                {
                    $extensions_updated = 0;
                }
                ?>
                                    <span class="badge extensions-updated padding-10-10-10-10" data-html="true" data-toggle="tooltip" title="<?php echo $extensions_updated_tooltip; ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSIONS_UPDATED_INSTALLED') . $extensions_updated; ?></span>
                <?php
            }        
            ?>
                                </div>
                                
                                <div class="table-responsive overflow-x-auto margin-top-30">
                                    <table id="filesintegritystatus_table" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
											<?php 
												if ($this->checkbox_position == 1) {
											?>
											<th class="filesintegrity-table width-5">
                                                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                                            </th> 
											<?php 
												}
											?>
                                            <th class="filesintegrity-table">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_NAME'); ?>
                                            </th>
                                            <th class="filesintegrity-table ruta-style">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_RUTA'); ?>                
                                            </th>
                                            <th class="filesintegrity-table">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TAMANNO'); ?>                
                                            </th>
                                            <th class="filesintegrity-table">
                                                <?php echo JText::_('Info'); ?>            
                                            </th>
                                            <th class="filesintegrity-table">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_LAST_MODIFIED'); ?>
                                            </th>
                                            <?php 
												if ($this->checkbox_position == 0) {
											?>
											<th class="filesintegrity-table width-5">
                                                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                                            </th> 
											<?php 
												}
											?>
                                        </tr>
                                    </thead>
            <?php
            $k = 0;
            if (!empty($this->items) ) {    
                foreach ($this->items as &$row) {
                    $safe_integrity = $row['safe_integrity'];
                    ?>
					<?php 
					if ($this->checkbox_position == 1) {
						echo '<td class="centrado">' . JHtml::_('grid.id', $k, $row['path'], '', 'filesintegritystatus_table') . '</td>'; 
					}
					?>
                    <td class="centrado">
                    <?php 
                    $last_part = explode(DIRECTORY_SEPARATOR, $row['path']);
					$end = end($last_part);
                    echo filter_var($end, FILTER_SANITIZE_STRING); ?>
                    </td>
                    <td class="centrado malwarescan-table-info">
                    <?php echo filter_var($row['path'], FILTER_SANITIZE_STRING); ?>
                    </td>
                    <td class="centrado">
                    <?php 
                    if (file_exists($row['path']) ) {
						$size = filesize($row['path']);
                        echo filter_var($size, FILTER_SANITIZE_STRING);
                    } 
                    ?>
                    </td>
                    <?php 
                    if ($safe_integrity == '0' ) {
                        echo "<td class=\"centrado;\"><span class=\"badge badge-danger\">";
                    } else {
                        echo "<td class=\"centrado;\">";
                    } 
                    if (file_exists($row['path']) ) {
                        echo date('Y-m-d H:i:s', filemtime($row['path']));
                    }
                    ?>
                    </span>
                    </td>
                    <?php 
                    if ($safe_integrity == '0' ) {
                        echo "<td class=\"centrado;\"><span class=\"badge badge-danger\">";
                    } else if ($safe_integrity == '1' ) {
                        echo "<td class=\"centrado;\"><span class=\"badge badge-success\">";
                    } else if ($safe_integrity == '2' ) {
                        echo "<td class=\"centrado;\"><span class=\"badge badge-warning\">";
                    } ?>
                    <?php echo filter_var($row['notes'], FILTER_SANITIZE_STRING); ?>
                    </span>
                    </td>
                    <?php 
					if ($this->checkbox_position == 0) {
						echo '<td class="centrado">' . JHtml::_('grid.id', $k, $row['path'], '', 'filesintegritystatus_table') . '</td>'; 
					}
					?>
                    </tr>
                    <?php
                    $k = $k+1;
                }
            }  ?>
                                    </table>
                                    </div>

            <?php
            if (!empty($this->items) ) {        
                ?>
                                    <div class="margen">
                                        <div>
                <?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
                                        </div>
                                    </div>
            <?php } ?>    
                                </div>
        <?php } else {
			if ($this->state->get('filter.malwarescan_status') == 2 ) {
				if ($this->file_manager_include_exceptions_in_database == 0 ) { 
					echo '<div class="alert alert-info">' . JText::_('COM_SECURITYCHECKPRO_EXCEPTIONS_NOT_INCLUDED_IN_DATABASE'). '</div>';                            
				} 
			}
		} ?>
                    </div>        
                </div>
            </div>
            
        </div>
</div>        

<input type="hidden" name="controller" value="filemanager" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="table" value="integrity" />
</form>
