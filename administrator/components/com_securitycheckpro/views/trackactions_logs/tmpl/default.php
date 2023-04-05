<?php 
/*
* Track Actions
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access'); 

$document = JFactory::getDocument();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$document->addScript(JURI::root().'media/com_securitycheckpro/new/js/sweetalert.min.js');
// Bootstrap core JavaScript
// Inline javascript to avoid deferring in Joomla 4
echo '<script src="' . JURI::root(). '/media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>';
//$document->addScript(JURI::root().'media/com_securitycheckpro/new/vendor/popper/popper.min.js');

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$font_awesome = "media/com_securitycheckpro/stylesheets/font-awesome.min.css";
JHTML::stylesheet($font_awesome);

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);
?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/trackactions_logs.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=trackactions_logs');?>" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>                
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_TRACKACTIONS_LOGS'); ?></li>
            </ol>
                            
            <!-- Contenido principal -->
            <div class="row">
            
                <div class="col-lg-12">
                    <div class="card mb-3">                        
                        <div class="card-body">
                            <div id="j-main-container">
                                <div id="editcell">
                                <div class="accordion-group">
                                <div class="card-header text-center">
            <?php echo JText::_('COM_SECURITYCHECKPRO_COLOR_CODE'); ?>
                                </div>
                                <table class="table table-striped">                                
                                <thead>
                                    <tr>
                                        <td><span class="badge badge-warning"> </span>
                                        </td>
                                        <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_ADMINISTRATOR_GROUP'); ?>
                                        </td>
                                        <td><span class="badge badge-danger"> </span>
                                        </td>
                                        <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_SUPER_USERS_GROUP'); ?>
                                        </td>
                                        <td><span class="badge badge-default"> </span>
                                        </td>
                                        <td class="left">
            <?php echo JText::_('COM_SECURITYCHECKPRO_OTHER_GROUPS'); ?>
                                        </td>
                                    </tr>
                                </thead>
                                </table>
                                </div>
                                <br />
                                <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                                <?php if (empty($this->items)) : ?>
                                    <div class="alert alert-no-items">
                                    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                                    </div>
                                <?php else : ?>
                                    <table class="table table-striped table-hover" id="logsList">
                                        <thead>
                                            <th width="2%">
                                                <?php echo JHtml::_('searchtools.sort', '', 'a.id', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                                            </th>
                                            <th width="1%">
                                                <input type="checkbox" name="checkall-toggle" value=""
                                                    title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                                                    onclick="Joomla.checkAll(this)" />
                                            </th>
                                            <th>
                                                <?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_MESSAGE', 'a.message', $listDirn, $listOrder); ?>
                                            </th>
                                            <th>
                                                <?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_DATE', 'a.log_date', $listDirn, $listOrder); ?>
                                            </th>
                                            <th>
                                                <?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_EXTENSION', 'a.extension', $listDirn, $listOrder); ?>
                                            </th>
                                            <th>
                                                <?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_USER', 'a.user_id', $listDirn, $listOrder); ?>
                                            </th>
                                            <th>
                                                <?php echo JHtml::_('searchtools.sort', 'COM_SECURITYCHECKPRO_IP_ADDRESS', 'a.ip_address', $listDirn, $listOrder); ?>
                                            </th>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <td colspan="7">
                                                    <?php echo $this->pagination->getListFooter(); ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                    <?php foreach ($this->items as $i => $item) : ?>
                                                <tr class="row<?php echo $i % 2; ?>">
                                                    <td>
                                                        <span class="sortable-handler inactive tip-top hasTooltip">
                                                            <i class="icon-menu"></i>
                                                        </span>
                                                    </td>
                                                    <td class="center">
                                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                                    </td>
                                                    <td>
                                        <?php 
                                        $icono = null;
                                        \JFactory::getApplication()->triggerEvent('onLogMessagePrepare', array (&$item->message, $item->extension, &$icono)); 
                                        echo $icono;
                                        echo $this->escape($item->message); ?>
                                                    </td>
                                                    <td>
                                        <?php echo $this->escape($item->log_date); ?>
                                                    </td>
                                                    <td>
                                        <?php echo TrackActionsHelper::translateExtensionName(strtoupper(strtok($this->escape($item->extension), '.'))); ?>
                                                    </td>
                                                    <td>
                                        <?php 
                                        $user_id = $item->user_id;
                                                        
                                        $db = JFactory::getDBO();
                                        $query = "SELECT COUNT(*) FROM #__users WHERE id={$user_id}";
                                        $db->setQuery($query);
                                        $db->execute();
                                        $existe_usuario = $db->loadResult();
                                                        
                                        if ($existe_usuario ) {
                                            $user_object = JUser::getInstance($user_id);
                                            // El usuario pertenece al grupo Super users
                                            if (array_search(8, $user_object->groups) !== false ) {                                    
                                                $span = '<span class="badge badge-danger">';
                                                // El usuario pertenece al grupo Administrators
                                            } else if (array_search(7, $user_object->groups) !== false ) {
                                                $span = '<span class="badge badge-warning">';
                                            } else {
                                                $span = '<span class="badge badge-default">';
                                            }
                                                            echo $span . $user_object->name . "</span>";
                                        } else {
                                            echo "<span class=\"badge badge-info\" data-toggle=\"tooltip\" title=\"" . JText::_('COM_SECURITYCHECKPRO_USER_DONT_EXISTS') . "\">---</span>";
                                                            
                                        }
                                        ?>
                                                    </td>
                                                    <td>
                                        <?php echo JText::_($this->escape($item->ip_address)); ?>
                                                    </td>
                                                </tr>
                                    <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif;?>        
                            </div>
                        </div>
                    </div>
                </div>
            <!-- End contenido principal -->
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
<input type="hidden" name="controller" value="trackactions_logs" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
</form>
