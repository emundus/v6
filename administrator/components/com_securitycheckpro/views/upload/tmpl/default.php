<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
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

// Chosen scripts
$document->addScript(JURI::root().'media/com_securitycheckpro/new/vendor/chosen/chosen.jquery.js');
$document->addScript(JURI::root().'media/com_securitycheckpro/new/vendor/chosen/init.js');

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

$chosen = "media/com_securitycheckpro/new/vendor/chosen/chosen.css";
JHTML::stylesheet($chosen);

?>

<?php 
// Cargamos el contenido común...
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';

// ... y el contenido específico
require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/upload.php';
?>

<form enctype="multipart/form-data" method="post" class="margin-top-minus18" name="adminForm" id="adminForm">

    <?php 
    // Cargamos la navegación
    require JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
    ?>
                        
            <!-- Breadcrumb-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
                </li>                
                <li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_IMPORT_CONFIG'); ?></li>
            </ol>
            
            <div class="card mb-6">
                <div class="card-body">
                    <div class="alert alert-warning">
        <?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS_ALERT'); ?>
                    </div>
                    
                    <fieldset class="uploadform form-inline">
                        <legend><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></legend>
                        
                        <label class="custom-file">
                          <input type="file" id="file_to_import" name="file_to_import" class="custom-file-input" onchange="$(this).next().after().text($(this).val().split('\\').slice(-1)[0])">
                          <span class="custom-file-control"></span>
                        </label>
                        
                        <input class="btn btn-primary" class="margin-left-20" type="button" id="read_file_button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" />
                        
                    </fieldset>
                </div>
            </div>
        </div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="upload" />

</form>
