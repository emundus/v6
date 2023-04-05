<?php 

/**
 * @package   RSFirewall!
 * @copyright (C) 2009-2014 www.rsjoomla.com
 * @license   GPL, http://www.gnu.org/licenses/gpl-2.0.html
 * @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JSession::checkToken('get') or die('Invalid Token');

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

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);
?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/dbcheck.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=dbcheck');?>" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>                
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'); ?></li>
            </ol>
            
    <?php if ($this->supported) { ?>        
                    
            <!-- Contenido principal -->
            <div class="row">
            
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-center">                        
                        <div class="card-body">                        
                            <span class="sc-icon32 sc-icon-orange sc-icon-search"></span>
                            <div class="margin-top-5"><?php echo JText::_('COM_SECURITYCHECKPRO_SHOW_TABLES'); ?></div>
                            <div class="margin-top-5"><span class="label label-info"><?php echo $this->show_tables; ?></span></div>                                                                 
                        </div>
                        <div class="card-footer">
                            <a href="#" id="show_tables" data-toggle="tooltip" title="<?php echo JText::_('COM_SECURITYCHECKPRO_DB_CONTENT'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-center">                        
                        <div class="card-body">                        
                            <span class="sc-icon32 sc-icon-orange sc-icon-date"></span>
                            <div class="margin-top-5"><?php echo JText::_('COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_LABEL'); ?></div>
                            <div class="margin-top-5"><span class="label label-info"><?php echo $this->last_check_database; ?></span></div>
                        </div>
                        <div class="card-footer">
                            <a href="#" id="show_tables" data-toggle="tooltip" title="<?php echo JText::_('COM_SECURITYCHECKPRO_LAST_OPTIMIZATION_DESCRIPTION'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
                        </div>                                                             
                    </div>
                </div>
                
                 <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fapro fa-database"></i>
        <?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'); ?>
                        </div>
                        <div class="card-body">
                            <div id="buttondatabase" class="text-center">
                                <button class="btn btn-primary" id="start_db_check" type="button"><i class="fapro fa-fw fa-fire"> </i><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_START_BUTTON'); ?></button>
                            </div>
                            
                            <div id="securitycheck-bootstrap-main-content">        
                                <div id="securitycheck-bootstrap-database" class="securitycheck-bootstrap-content-box hidden">
                                    <div class="securitycheck-bootstrap-content-box-content">
                                        <div class="securitycheck-bootstrap-progress" id="securitycheck-bootstrap-database-progress"><div class="securitycheckpro-bar" class="width-0"></div></div>
                                        <table id="securitycheck-bootstrap-database-table">
                                            <thead>
                                                <tr>
                                                    <th width="20%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_NAME'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_ENGINE'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_COLLATION'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_ROWS'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_DATA'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_INDEX'); ?></th>
                                                    <th width="1%" nowrap="nowrap"><?php echo JText::_('COM_SECURITYCHECKPRO_TABLE_OVERHEAD'); ?></th>
                                                    <th><?php echo JText::_('COM_SECURITYCHECKPRO_RESULT'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($this->tables as $i => $table) { ?>
                                                <tr class="securitycheck-bootstrap-table-row <?php if ($i % 2) { ?>alt-row<?php 
                                                                                             } ?> hidden">
                                                    <td width="20%" nowrap="nowrap"><?php echo $this->escape($table->Name); ?></td>
                                                    
                                                    <?php if (strtolower($table->Engine) == 'myisam') { ?>
                                                    <td width="1%" style="color:#00FF00;" nowrap="nowrap">
                                                    <?php } else { ?>
                                                    <td width="1%" nowrap="nowrap">
                                                    <?php } ?>
                                                    <?php echo $this->escape($table->Engine); ?></td>
                                                    <td width="1%" nowrap="nowrap"><?php echo $this->escape($table->Collation); ?></td>
                                                    <td width="1%" nowrap="nowrap"><?php echo (int) $table->Rows; ?></td>
                                                    <td width="1%" nowrap="nowrap"><?php echo $this->bytes_to_kbytes($table->Data_length); ?></td>
                                                    <td width="1%" nowrap="nowrap"><?php echo $this->bytes_to_kbytes($table->Index_length); ?></td>
                                                    <td width="1%" nowrap="nowrap">
                                                    <?php if ($table->Data_free > 0) { ?>
                                                        <?php if (strtolower($table->Engine) == 'myisam') { ?>
                                                            <b class="securitycheck-bootstrap-level-high"><?php echo $this->bytes_to_kbytes($table->Data_free); ?></b>
                                                        <?php } else { ?>
                                                            <em><?php echo JText::_('COM_SECURITYCHECKPRO_NOT_SUPPORTED'); ?></em>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php echo $this->bytes_to_kbytes($table->Data_free); ?>
                                                    <?php } ?>
                                                    </td>
                                                    <?php if (strtolower($table->Engine) == 'myisam') { ?>
                                                    <td id="result<?php echo $i; ?>"></td>
                                                    <?php } else { ?>
                                                    <td id="result"><?php echo JText::_('COM_SECURITYCHECKPRO_NO_OPTIMIZATION_NEEDED'); ?></td>
                                                    <?php } ?>
                                                    
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- End contenido principal -->
            </div>            
    <?php } else { ?>
                <div class="alert alert-error"><?php echo JText::_('COM_SECURITYCHECKPRO_DB_CHECK_UNSUPPORTED'); ?></div>
    <?php } ?>            
        </div>
</div>    

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="dbcheck" />
</form>
