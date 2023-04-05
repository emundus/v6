<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 

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

$chosen = "media/com_securitycheckpro/new/vendor/chosen/chosen.css";
JHTML::stylesheet($chosen);

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);
?>

<?php 
// Cargamos el contenido común
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=ruleslogs');?>" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=rules&view=rules&'. JSession::getFormToken() .'=1');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LOGS'); ?></li>
            </ol>
            
            <div class="card mb-6">
                <div class="card-body">
                    <div>
                        <div id="filter-bar" class="btn-toolbar">
                            <div class="filter-search btn-group pull-left">
                                <input type="text" name="filter_rules_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_rules_search" value="<?php echo $this->escape($this->state->get('filter.rules_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
                            </div>
                            <div class="btn-group pull-left margin-left-10">
                                <button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                                <button class="btn tip" id="filter_rules_search_button" type="button" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                            </div>                            
                        </div>
                        
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="rules-logs">
            <?php echo JText::_("Ip"); ?>
                                    </th>
                                    <th class="rules-logs">
            <?php echo JText::_('COM_SECURITYCHECKPRO_USER'); ?>
                                    </th>
                                    <th class="rules-logs">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LOGS_LAST_ENTRY'); ?>
                                    </th>
                                    <th class="rules-logs">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LOGS_REASON_HEADER'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
        <?php
        $k = 0;
        foreach ($this->log_details as &$row) {    
            ?>
                        <tr class="row<?php echo $k % 2; ?>">
                            <td class="rules-logs">
            <?php echo $row->ip; ?>    
                            </td>
                            <td class="rules-logs">
            <?php echo $row->username; ?>    
                            </td>
                            <td class="rules-logs">
            <?php echo $row->last_entry; ?>    
                            </td>
                            <td class="rules-logs">
            <?php echo $row->reason; ?>    
                            </td>
                        </tr>
            <?php
            $k = $k+1;
        }
        ?>
                            </tbody>
                        </table>

        <?php
        if (!empty($this->log_details) ) {        
            ?>
                        <div class="margen">
                            <div>
            <?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
                            </div>
                        </div>
            <?php
        }
        ?>

                        </div>

                        <div class="card" class="margin-top-10 margin-left-10 width-40rem">
                            <div class="card-body card-header">
                                <?php echo JText::_('COM_SECURITYCHECKPRO_COPYRIGHT'); ?><br/>                                
                            </div>                                
                        </div>            
                </div>
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
<input type="hidden" name="controller" value="ruleslogs" />
</form>
