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

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

$type_array = array(JHtml::_('select.option', 'Component', JText::_('COM_SECURITYCHECKPRO_TITLE_COMPONENT')),
            JHtml::_('select.option', 'Plugin', JText::_('COM_SECURITYCHECKPRO_TITLE_PLUGIN')),
            JHtml::_('select.option', 'Module', JText::_('COM_SECURITYCHECKPRO_TITLE_MODULE')));
            
$vulnerable_array = array(JHtml::_('select.option', 'Si', JText::_('COM_SECURITYCHECKPRO_HEADING_VULNERABLE')),
            JHtml::_('select.option', 'No', JText::_('COM_SECURITYCHECKPRO_GREEN_COLOR')));


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

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/securitycheckpros.php';
?>

    <!-- Modal vulnerable extension -->
    <div class="modal bd-example-modal-lg" id="modal_vuln_extension" tabindex="-1" role="dialog" aria-labelledby="modal_vuln_extensionLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" class="max-width-1200" role="document">
            <div class="modal-content">
                <div class="modal-header alert alert-info">
                    <h2 class="modal-title"><?php echo JText::_('COM_SECURITYCHECKPRO_VULN_INFO_TEXT'); ?></h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>                
                </div>
                <div class="modal-body" class="overflow-x-auto">
                    <div class="table-responsive" id="response_result">        
                    </div>                    
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
                </div>              
            </div>
        </div>
    </div>                                    

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1');?>" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
        
          <!-- Breadcrumb-->
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
            </li>
            <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES'); ?></li>
          </ol>
                        
            <!-- Contenido principal -->
            <!-- Update database plugin status -->
            <div class="card mb-3">
                    <div class="card-body">
        <?php if (($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") ) { ?>                        
                        <div class="badge badge-success">
                            <h4><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES'); ?></h4>
                            <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_DATABASE_VERSION'); ?></strong><?php echo($this->database_version); ?></p>
                            <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_LAST_CHECK'); ?></strong><?php echo($this->last_check); ?></p>
                        </div>
        <?php } else if (($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && (is_null($this->database_message)) ) { ?>
                            <div class="badge badge-success">
                                <h4><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES'); ?></h4>
                                <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_LAUNCHED'); ?></strong></p>                        
                            </div>
        <?php } else if (($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ( !($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") && !(is_null($this->database_message) )) ) { ?>                            
                            <div class="badge badge-danger">
                                <h4><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_PROBLEM'); ?></h4>
                                <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_DATABASE_MESSAGE'); ?></strong><?php echo JText::_($this->database_message); ?></p>
            <?php
            if ($this->database_message != "COM_SECURITYCHECKPRO_UPDATE_DATABASE_SUBSCRIPTION_EXPIRED" ) {
                ?>
                                <a href="<?php echo 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->plugin_id?>" class="btn btn-dark"><?php echo JText::_('COM_SECURITYCHECKPRO_CHECK_CONFIG'); ?></a>            
            <?php } else { ?>
                                    <a href="https://securitycheck.protegetuordenador.com/subscriptions" target="_blank"  rel="noopener noreferrer" class="btn"><?php echo JText::_('COM_SECURITYCHECKPRO_RENEW'); ?></a>
            <?php } ?>
                                        
                            </div>    
        <?php } else if (($this->update_database_plugin_exists) && (!$this->update_database_plugin_enabled) ) { ?>
                            <div class="badge badge-warning">
                                <h4><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME'); ?></h4>
                                <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_DISABLED'); ?></strong></p>                        
                            </div>
        <?php } else if (!($this->update_database_plugin_exists) ) { ?>
                            <div class="badge badge-info">
                                <h4><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_INSTALLED'); ?></h4>
                                <p><strong><?php echo JText::_('COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_RECEIVE'); ?></strong></p>            
                            </div>                        
        <?php } ?>                                                
                    </div>
                </div>
            
            <!-- Extensions table -->
            <div class="card mb-3">
                <div id="editcell">
                    <div class="card-header text-center">
        <?php echo JText::_('COM_SECURITYCHECKPRO_COLOR_CODE'); ?>
                    </div>
                    <table class="table table-striped">                        
                        <thead>
                            <tr>
                                <td><span class="badge badge-success"> </span>
                                </td>
                                <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_GREEN_COLOR'); ?>
                                </td>
                                <td><span class="badge badge-warning"> </span>
                                </td>
                                <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_YELLOW_COLOR'); ?>
                                </td>
                                <td><span class="badge badge-danger"> </span>
                                </td>
                                <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RED_COLOR'); ?>
                                </td>
                            </tr>
                        </thead>
                    </table>                    
                </div>
                
                <div class="margin-left-10 margin-right-10">
                    <select name="filter_extension_type" class="custom-select" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_TYPE_DESCRIPTION');?></option>
        <?php echo JHtml::_('select.options', $type_array, 'value', 'text', $this->state->get('filter.extension_type'));?>
                    </select>
                    <select name="filter_vulnerable" class="custom-select" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES');?></option>
        <?php echo JHtml::_('select.options', $vulnerable_array, 'value', 'text', $this->state->get('filter.vulnerable'));?>
                    </select>
                    <span class="badge badge-info padding-10-10-10-10 float-right"><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATE_DATE') . $this->last_update; ?></span>
                </div>
    
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="5" class="alert alert-info text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_HEADING_ID'); ?>
                                    </th>
                                    <th class="alert alert-info text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_HEADING_PRODUCT'); ?>
                                    </th>
                                    <th class="alert alert-info text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_HEADING_TYPE'); ?>
                                    </th>
                                    <th class="alert alert-info text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_HEADING_INSTALLED_VERSION'); ?>
                                    </th>
                                    <th class="alert alert-info text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_HEADING_VULNERABLE'); ?>
                                    </th>
                                </tr>
                            </thead>
        <?php
        $k = 0;
        if (!empty($this->items) ) {
            foreach ($this->items as &$row) {
                ?>
                            <tr class="<?php echo "row$k"; ?>">
                                <td class="text-center">
                <?php echo $row->id; ?>
                                </td>
                                <td class="text-center">
                <?php
                $vulnerable = $row->Vulnerable;
                if ($vulnerable <> 'No' ) {							
                    echo '<a href="#" onclick="filter_vulnerable_extension(\'' . $row->Product .'\');">' . $row->Product . '</a>';                        
                } else {
                    echo $row->Product; 
                }
                ?>    
                                </td>
                <?php 
                $type = $row->sc_type;
                ?>
                                    <td class="text-center">
                <?php
                if ($type == 'core' ) {
                    echo "<span class=\"badge\" class=\"background-FFADF5; \">";
                } else if ($type == 'component' ) {
                    echo "<span class=\"badge badge-info\">";
                } else if ($type == 'module' ) {
                    echo "<span class=\"badge\">";
                } else {
                    echo "<span class=\"badge badge-inverse\">";
                }
                ?>
                <?php echo JText::_('COM_SECURITYCHECKPRO_TYPE_' . $row->sc_type); ?>
                                </span>
                                </td>
                                <td class="text-center">
                <?php echo $row->Installedversion; ?>
                                </td>
                <?php 
                $vulnerable = $row->Vulnerable;
                ?>
                            <td class="text-center">
                <?php
                if ($vulnerable == 'Si' ) {
                    echo "<span class=\"badge badge-important\">";
                } else if ($vulnerable == 'Indefinido' ) {
                    echo "<span class=\"badge badge-warning\">";
                } else
                {
                    echo "<span class=\"badge badge-success\">";
                }
                ?>
                <?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABLE_' . $row->Vulnerable); ?>
                            </span>
                            </td>
                            </tr>
                <?php
                $k = 1 - $k;
            }
        }
        ?>                            
                        </table>
                    </div>    

        <?php
        if (!empty($this->items) ) {        
            ?>
                        <div>
            <?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
                        </div>                    
        <?php }    ?>
                </div>
                
            </div>        
                
</div>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/end.php';
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="securitycheckpro" />
</form>
