<?php
/**
 * Securitycheck Pro ControlCenter View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Session\Session as JSession;
use Joomla\CMS\Router\Route as JRoute;

JSession::checkToken('get') or die('Invalid Token');

function booleanlist($name, $attribs = null, $selected = null, $id=false)
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('COM_SECURITYCHECKPRO_NO')),
    JHTML::_('select.option',  '1', JText::_('COM_SECURITYCHECKPRO_YES'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
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

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/controlcenter.php';
?>


<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=controlcenter&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONTROLCENTER_TEXT'); ?></li>
            </ol>
            
    <?php if (function_exists('openssl_encrypt')) { ?>
            
            <div class="card mb-6">
                <div class="card-body">
                    <div class="row">
                        <div class="alert alert-info">
        <?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_EXPLAIN'); ?>    
                        </div>
        
                        <div class="col-xl-6 mb-6">
                            <div class="card-header text-white bg-primary">
                                <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                            </div>
                            <div class="card-body">
                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_TEXT'); ?></h4>                                        
                                <div class="controls controls-row">
                                    <?php echo booleanlist('control_center_enabled', array(), $this->control_center_enabled) ?>                
                                </div>
                                <blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_EXPLAIN') ?></small></p></blockquote>                                        
                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_TEXT'); ?></h4>
                                <?php 
                                if (version_compare(JVERSION, '3.20', 'lt')) {                                        
                                    ?>
                                    <div class="input-prepend">
                                        <input class="input-xlarge" type="text" name="secret_key" id="secret_key" value="<?php echo $this->secret_key ?>" readonly>
                                    </div>
                                                    
                                    <div class="input-append">
                                        <input type='button' class="btn btn-primary" value='<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?>' onclick='document.getElementById("secret_key").value = Password.generate(32)' />
                                    </div>					
                                <?php } else {    ?>                                    
                                    <div class="input-group">
                                      <input type="text" class="form-control input-xlarge" name="secret_key" id="secret_key" value="<?php echo $this->secret_key ?>" readonly>
                                      <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick='document.getElementById("secret_key").value = Password.generate(32)'><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?></button>
                                      </span>
                                    </div>                                                                                                
                                <?php } ?>                                
                                <blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_EXPLAIN') ?></small></p></blockquote>
								
								<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_URL'); ?></h4>
								<div class="input-prepend">
									<input class="input-xxlarge" type="text" name="control_center_url" id="control_center_url" value="<?php echo $this->control_center_url ?>" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_URL_PLACEHOLDER') ?>">
                                </div>
								<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_URL_EXPLAIN') ?></small></p></blockquote>
								
								<?php	 
									$mainframe = JFactory::getApplication();
									$cc_status = $mainframe->getUserState('download_controlcenter_log', null);									
									if ( (!empty($cc_status)) || ($this->error_file_exists == 1) ) { 							
								?>
								<div id="button_show_log" class="card-footer">
									<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_CONFIG_FILE_MANAGER_LOG_PATH_LABEL'); ?></h4>
									<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_LOG_FILE_EXPLAIN') ?></small></p></blockquote>
									<?php
									 if (!empty($cc_status)) {
									?>
									<button class="btn btn-success" type="button" onclick="Joomla.submitbutton('download_controlcenter_log');"><i class="fapro fa-fw fa-download"></i><?php echo JText::_('COM_SECURITYCHECKPRO_DOWNLOAD_LOG'); ?></button>
									<?php
									}
									if ($this->error_file_exists == 1) {
									?>
									<button class="btn btn-danger" type="button" onclick="add_element_to_form('error_log','1'); Joomla.submitbutton('download_controlcenter_log');"><i class="fapro fa-fw fa-download"></i><?php echo JText::_('COM_SECURITYCHECKPRO_DOWNLOAD_ERROR_LOG'); ?></button>
									<?php
									 }
									 ?>
									<button class="btn btn-warning" type="button" onclick="Joomla.submitbutton('delete_controlcenter_log');"><i class="fapro fa-fw fa-trash"></i><?php echo JText::_('COM_SECURITYCHECKPRO_CONFIG_FILE_MANAGER_DELETE_LOG_FILE_LABEL'); ?></button>
								</div>  
								<?php }    ?>        							  
								
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
    <?php } else { ?>
                <div class="alert alert-error">
        <?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENCRYPT_LIBRARY_NOT_PRESENT'); ?>    
                </div>

    <?php } ?>
        </div>
</div>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/end.php';
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="view" value="controlcenter" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" id="task" value="save" />
<input type="hidden" name="controller" value="controlcenter" />    
</form>