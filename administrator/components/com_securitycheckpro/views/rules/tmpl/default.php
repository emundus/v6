<?php
/**
 * Vista Rules para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
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

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$chosen = "media/com_securitycheckpro/new/vendor/chosen/chosen.css";
JHTML::stylesheet($chosen);

?>

<?php 
// Cargamos el contenido común
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/rules.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=rules&view=rules&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'); ?></li>
            </ol>
            
            <div class="card mb-6">
                <div class="card-body">
                    <div>
                        <div id="filter-bar" class="btn-toolbar">
                            <div class="filter-search btn-group pull-left">
                                <input type="text" name="filter_acl_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_acl_search" value="<?php echo $this->escape($this->state->get('filter.acl_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
                            </div>
                            <div class="btn-group pull-left" class="margin-left-10">
                                <button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                                <button class="btn tip" type="button" id="filter_acl_search_button" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info" class="margin-top-10" role="alert">
        <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_GUEST_USERS'); ?>
                        </div>
        
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="1%" class="nowrap center rules">
                                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                                    </th>
                                    <th class="left rules">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_GROUP_TITLE'); ?>
                                    </th>
                                    <th width="20%" class="rules">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_RULES_APPLIED'); ?>
                                    </th>
                                    <th width="5%" class="rules">
            <?php echo JText::_('JGRID_HEADING_ID'); ?>
                                    </th>
                                    <th width="20%" class="rules">
            <?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LAST_CHANGE'); ?>
                                    </th>
                                </tr>
                            </thead>
        <?php
        $k = 0;
        if (!empty($this->items) ) {    
            foreach ($this->items as &$row) {
                ?>

                                <tr class="row<?php echo $k % 2; ?>">
                                    <td class="center">
                <?php echo JHtml::_('grid.id', $k, $row->id); ?>
                                    </td>
                                    <td>
                <?php echo str_repeat('<span class="gi">|&mdash;</span>', $row->level) ?>
                <?php echo $this->escape($row->title); ?> 
                                    </td>
                                    <td class="rules-logs">
                <?php echo JHtml::_(
                    'jgrid.state', $states = array(
                    0 => array(
                    'task'                => 'apply_rules',
                    'text'                => '',
                    'active_title'        => 'COM_SECURITYCHECKPRO_RULES_NOT_APPLIED_AND_TOGGLE',
                    'inactive_title'    => '',
                    'tip'                => true,
                    'active_class'        => 'unpublish',
                    'inactive_class'    => 'unpublish'
                    ),
                    1 => array(
                    'task'                => 'not_apply_rules',
                    'text'                => '',
                    'active_title'        => 'COM_SECURITYCHECKPRO_RULES_APPLIED_AND_TOGGLE',
                    'inactive_title'    => '',
                    'tip'                => true,
                    'active_class'        => 'publish',
                    'inactive_class'    => 'publish'
                    )
                    ), $row->rules_applied, $k
                ); ?>
                                    </td>
                                    <td class="rules-logs">
                <?php echo (int) $row->id; ?>
                                    </td>
                                    <td class="rules-logs">
                <?php echo $row->last_change; ?>
                                    </td>
                                </tr>

                <?php
                $k = $k+1;
            }
        }
        ?>
                        </table>
        <?php
        if (!empty($this->items) ) {        
            ?>
                        <div>
            <?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
                        </div>
            <?php
        }
        ?>
                    </div>                    
                </div>
            </div>            
        </div>
</div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
</form>