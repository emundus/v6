<?php
/**
 * ControlCenter Controller para Securitycheck Pro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Session\Session as JSession;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerControlCenter extends SecuritycheckproController
{

    /* Redirecciona las peticiones al componente */
    function redireccion()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }


    /* Guarda los cambios y redirige al cPanel */
    public function save()
    {
        $model = $this->getModel('cron');
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getArray($_POST);
		$model->saveConfig($data, 'controlcenter');

        $this->setRedirect('index.php?option=com_securitycheckpro&view=controlcenter&'. JSession::getFormToken() .'=1', JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
    }

    /* Guarda los cambios */
    public function apply()
    {
        $this->save('cron_plugin');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=controlcenter&view=controlcenter&'. JSession::getFormToken() .'=1', JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
    }
	
	/* Download log file */
    function download_controlcenter_log($log_name=null)
    {
				
		$mainframe = JFactory::getApplication();
		
		$is_error_log = $mainframe->input->get('error_log', null);
		
		if ($is_error_log) {
			$filename = "error.php";
		} else {
			$filename = $mainframe->getUserState('download_controlcenter_log', null);
		}	
											
        if (!empty($filename)) {  
			// Establecemos la ruta donde se almacenan los escaneos
			$folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;			
			if (file_exists($folder_path.$filename)) {				

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment;filename=' . $filename);
				header('Expirer: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Lenght: ' . filesize($folder_path.$filename));
				ob_clean();
				flush();
				readfile($folder_path.$filename);
				exit;
			} else {
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_LOGFILENOTEXISTS'), 'error');
			}
            
        }else
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ERROR_RETRIEVING_FILE'), 'error');    
        } 
        
        parent::display();    
        
    }
	
	/* Delete log file */
    function delete_controlcenter_log()
    {
		$mainframe = JFactory::getApplication();
		$filename = $mainframe->getUserState('download_controlcenter_log', null);
		// Establecemos la ruta donde se almacenan los escaneos
		$folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
											
        if (!empty($filename)) {  
			
			if (file_exists($folder_path.$filename)) {
				$res = JFile::delete($folder_path.$filename);
				// Let's delete the error.log if exists
				if (file_exists($folder_path."error.php")) {
					JFile::delete($folder_path."error.php");
				}
			} else {
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_LOGFILENOTEXISTS'), 'error');
			}
            
        }else
        {
			// Let's delete the error.log if exists
			if (file_exists($folder_path."error.php")) {
				$res = JFile::delete($folder_path."error.php");
			} else {
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ERROR_RETRIEVING_FILE'), 'error'); 
			}
               
        } 
		if ($res) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_FILE_DELETED'));
			$db = JFactory::getDbo();		
			$sql = "DELETE FROM #__securitycheckpro_storage WHERE storage_key='controlcenter_log'";
			$db->setQuery($sql);
			$db->execute();  
		}
        
        parent::display();    
        
    }

}
