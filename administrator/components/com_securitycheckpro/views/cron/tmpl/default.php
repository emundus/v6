<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Router\Route as JRoute;

JSession::checkToken('get') or die('Invalid Token');

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro_cron');


function taskslist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  'permissions', JText::_('PLG_SECURITYCHECKPRO_CRON_ONLY_PERMISSIONS')),
    JHTML::_('select.option',  'integrity', JText::_('PLG_SECURITYCHECKPRO_CRON_ONLY_INTEGRITY')),
    JHTML::_('select.option',  'both', JText::_('PLG_SECURITYCHECKPRO_CRON_BOTH_TASKS')),
    JHTML::_('select.option',  'alternate', JText::_('PLG_SECURITYCHECKPRO_CRON_ALTERNATE_TASKS'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', $selected, $id);    
}

function launchtimelist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '0', JText::_('00:00 - 01:00')),
    JHTML::_('select.option',  '1', JText::_('01:00 - 02:00')),
    JHTML::_('select.option',  '2', JText::_('02:00 - 03:00')),
    JHTML::_('select.option',  '3', JText::_('03:00 - 04:00')),
    JHTML::_('select.option',  '4', JText::_('04:00 - 05:00')),
    JHTML::_('select.option',  '5', JText::_('05:00 - 06:00')),
    JHTML::_('select.option',  '6', JText::_('06:00 - 07:00')),
    JHTML::_('select.option',  '7', JText::_('07:00 - 08:00')),
    JHTML::_('select.option',  '8', JText::_('08:00 - 09:00')),
    JHTML::_('select.option',  '9', JText::_('09:00 - 10:00')),
    JHTML::_('select.option',  '10', JText::_('10:00 - 11:00')),
    JHTML::_('select.option',  '11', JText::_('11:00 - 12:00')),
    JHTML::_('select.option',  '12', JText::_('12:00 - 13:00')),
    JHTML::_('select.option',  '13', JText::_('13:00 - 14:00')),
    JHTML::_('select.option',  '14', JText::_('14:00 - 15:00')),
    JHTML::_('select.option',  '15', JText::_('15:00 - 16:00')),
    JHTML::_('select.option',  '16', JText::_('16:00 - 17:00')),
    JHTML::_('select.option',  '17', JText::_('17:00 - 18:00')),
    JHTML::_('select.option',  '18', JText::_('18:00 - 19:00')),
    JHTML::_('select.option',  '19', JText::_('19:00 - 20:00')),
    JHTML::_('select.option',  '20', JText::_('20:00 - 21:00')),
    JHTML::_('select.option',  '21', JText::_('21:00 - 22:00')),
    JHTML::_('select.option',  '22', JText::_('22:00 - 23:00')),
    JHTML::_('select.option',  '23', JText::_('23:00 - 00:00'))        
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id);
}

function periodicitylist( $name, $attribs = null, $selected = null, $id=false )
{
    $arr = array(
    JHTML::_('select.option',  '1', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 1)),
    JHTML::_('select.option',  '2', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 2)),
    JHTML::_('select.option',  '4', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 4)),
    JHTML::_('select.option',  '6', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 6)),
    JHTML::_('select.option',  '8', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 8)),
    JHTML::_('select.option',  '12', JText::sprintf('PLG_SECURITYCHECKPRO_CRON_EVERY_X_HOUR', 12)),
    JHTML::_('select.option',  '24', JText::_('PLG_SECURITYCHECKPRO_CRON_EVERY_DAY')),
    JHTML::_('select.option',  '168', JText::_('PLG_SECURITYCHECKPRO_CRON_EVERY_WEEK'))
    );
    return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single" onchange="Disable()"', 'value', 'text', (int) $selected, $id);
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

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

?>


<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/cron.php';
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=cron&'. JSession::getFormToken() .'=1');?>" class="margin-top-minus18" method="post" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CRON_CONFIGURATION'); ?></li>
            </ol>
            
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 mb-3">
                            <div class="card-header text-white bg-primary">
                                <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                            </div>
                            <div class="card-body">
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_TASKS_LABEL'); ?></h4>                                        
                                <div class="controls">
            <?php echo taskslist('tasks', array(), $this->tasks) ?>
                                </div>
                                <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_TASKS_DESCRIPTION') ?></small></p></blockquote>        
                                
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_LAUNCH_TIME_LABEL'); ?></h4>                                        
                                <div class="controls" id="launch_time">
            <?php echo launchtimelist('launch_time', array(), $this->launch_time) ?>
                                </div>
                                <blockquote id="launch_time_description"><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_LAUNCH_TIME_DESCRIPTION') ?></small></p></blockquote>
                                <blockquote id="launch_time_alert"><p class="text-info"><small><span class="red"><?php echo JText::_('PLG_SECURITYCHECKPRO_LAUNCH_TIME_ALERT') ?></span></small></p></blockquote>
                                
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_PERIODICITY_LABEL'); ?></h4>                                        
                                <div class="controls" id="periodicity">
            <?php echo periodicitylist('periodicity', array(), $this->periodicity) ?>
                                </div>
                                <blockquote id="periodicity_description_normal"><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_PERIODICITY_DESCRIPTION') ?></small></p></blockquote>                
                                <blockquote id="periodicity_description_alert"><p class="text-info"><small><span class="red"><?php echo JText::_('PLG_SECURITYCHECKPRO_CRON_PERIODICITY_DESCRIPTION_ALERT') ?></span></small></p></blockquote>
                            </div>
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
<input type="hidden" name="controller" value="cron" />
</form>
