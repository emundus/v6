<?php
/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

// Load library
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php';

class plgSystemSecuritycheckpro_cron extends JPlugin
{
    private $cron_plugin = null;
	
	var $global_model = null;

    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);        
    
        // Cargamos los par�metros del componente
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/protection.php';
        if(interface_exists('JModel')) {
            $this->cron_plugin = JModelLegacy::getInstance('Protection', 'SecuritycheckProsModel');
        } else {
            $this->cron_plugin = JModel::getInstance('Protection', 'SecuritycheckProsModel');
        }        
		require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
		$this->global_model = new SecuritycheckproModel();
    }
    
    /* Acciones para chequear los permisos de los archivos autom�ticamente*/
    function acciones($opcion,$launch_time)
    {
        
        // Import Securitycheckpros model
        JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        $model = JModelLegacy::getInstance('filemanager', 'SecuritycheckprosModel');
    
        if ($opcion == 'launch') {
            $model->set_campo_filemanager('last_check', date('Y-m-d ' . $launch_time . ':00:00'));
        } else
        {
			$timestamp = $this->global_model->get_Joomla_timestamp();
            $model->set_campo_filemanager('last_check', $timestamp);
        }
    
        $message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS');
        $model->set_campo_filemanager('estado', 'IN_PROGRESS'); 
        $model->scan("permissions");    
    }

    /* Acciones para chequear la integridad del sistema de ficheros autom�ticamente */
    function acciones_integrity($opcion,$launch_time)
    {
        // Inicializamos las variables
        $number_of_files = array();
    
        // Import Securitycheckpros model
        JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        $model = JModelLegacy::getInstance('filemanager', 'SecuritycheckprosModel');
    
        if ($opcion == 'launch') {
            $model->set_campo_filemanager('last_check_integrity', date('Y-m-d ' . $launch_time . ':00:00'));
        } else
        {
			$timestamp = $this->global_model->get_Joomla_timestamp();
            $model->set_campo_filemanager('last_check_integrity', $timestamp);
        }
    
        $message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS');
        $model->set_campo_filemanager('estado_integrity', 'IN_PROGRESS'); 
        $model->scan("integrity");
        $files_with_bad_integrity = $model->loadStack("fileintegrity_resume", "files_with_bad_integrity");
    
        // �Hemos de analizar los ficheros con integridad modificada en busca de malware?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $look_for_malware = $params->get('look_for_malware', 0);
        if ($look_for_malware) {
            $model->scan("malwarescan_modified");
        }
    
        // Consultamos si hay que mandar un correo cuando se encuentran archivos con integridad incorrecta
        $number_of_files = $this->consulta_resultado_scan();
        $send_email = $params->get('send_email_on_wrong_integrity', 1);
        $email_subject = $params->get('email_subject_on_wrong_integrity', "");
        if ($send_email && ($number_of_files[0] > 0)) {
            $this-> mandar_correo($number_of_files[0], $number_of_files[1], $look_for_malware, $email_subject);
        }
    
    
    }

    /* Acciones para chequear si el sitio web no ha lanzado la(s) tarea(s) del cron en el horario especificado (esto puede pasar en sitios web con poco tr�fico o tr�fico principal en horas diferentes a las que se ha establecido el cron) */
    private function check_timestamp()
    {
        // Inicializamos las variables
        $last_check = null;
        $launch = false;
    
        // Consultamos la �ltima tarea lanzada
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select($db->quoteName('last_task'))
            ->from($db->quoteName('#__securitycheckpro_file_manager'));
        $db->setQuery($query);
        $task = $db->loadResult();
		
		$timestamp = $this->global_model->get_Joomla_timestamp();
    
        switch ($task)
        {
        case "INTEGRITY":
            $query = $db->getQuery(true)
                ->select($db->quoteName('last_check_integrity'))
                ->from($db->quoteName('#__securitycheckpro_file_manager'));
            $db->setQuery($query);
            $task_time = $db->loadResult();
			
            
            if((isset($task_time)) && (!empty($task_time))) {
                $last_check = $task_time;
            } else
            {
                $last_check = $timestamp;
            }    
            break;
        case "PERMISSIONS":
            $query = $db->getQuery(true)
                ->select($db->quoteName('last_check'))
                ->from($db->quoteName('#__securitycheckpro_file_manager'));
            $db->setQuery($query);
            $task_time = $db->loadResult();
			            
            if((isset($task_time)) && (!empty($task_time))) {
                $last_check = $task_time;
            } else
            {
                $last_check = $timestamp;
            }
            break;
        }
				
		$seconds = strtotime($timestamp) - strtotime($last_check);
		// Extraemos las horas que han pasado desde el �ltimo chequeo
		$interval = intval($seconds/3600);	
					
		if ($interval >= 24) {
            $launch = true;
            // Actualizamos el campo 'cron_tasks_launched' de la tabla 'file_manager' para asegurarnos que la(s) tarea(s) se lanza(n) siempre.
            $query = 'UPDATE #__securitycheckpro_file_manager SET cron_tasks_launched=0 WHERE id=1';
            $db->setQuery($query);
            $db->execute();
        }
    
        return $launch;
    }
			
	// Lanzamos la tarea pendiente 
    private function launch_task($task_pending)
    {
		// Load library
		require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'json.php';
		$model = new SecuritycheckProsModelJson();
		$model->execute($task_pending);
		
	}
	
	// Buscamos nuevas tareas pendientes de lanzar remitidas por el control center
    private function look_for_remote_tasks()
    {
		$db = JFactory::getDBO();
        $query = 'SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key="remote_task"';
        $db->setQuery($query);
        $db->execute();
        $task_pending = $db->loadResult();
		
		if (!empty($task_pending))
		{
			$this->launch_task($task_pending);
		}		
	}
	
	function getBetween($string, $start = "", $end = ""){
		if (strpos($string, $start)) { // required if $start not exist in $string
			$startCharCount = strpos($string, $start) + strlen($start);
			$firstSubStr = substr($string, $startCharCount, strlen($string));
			$endCharCount = strpos($firstSubStr, $end);
			if ($endCharCount == 0) {
				$endCharCount = strlen($firstSubStr);
			}
			return substr($firstSubStr, 0, $endCharCount);
		} else {
			return '';
		}
	}
    
    function onAfterInitialise()
    {
		// Look for remote tasks pending
		$this->look_for_remote_tasks();
		
        // Import Securitycheckpros model
        JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        $model = JModelLegacy::getInstance('filemanager', 'SecuritycheckprosModel');
    
        if (empty($model)) {
            $mainframe =JFactory::getApplication();
            $mainframe->setUserState("exists_filemanager", false);
            return;
        }
        
        $tasks = $this->cron_plugin->getValue('tasks', 'alternate', 'cron_plugin');
        $launch_time = $this->cron_plugin->getValue('launch_time', 2, 'cron_plugin');
        $periodicity = $this->cron_plugin->getValue('periodicity', 24, 'cron_plugin');
            
        // Comprobamos si es necesario lanzar la tarea porque no se ha recibido ninguna petici�n en el horario establecido en el cron
        $launch_task = $this->check_timestamp();    
            
        // Hora local del servidor
        $now = $this->global_model->get_Joomla_timestamp();
		$hour = $this->getBetween($now," ",":");
		
		
        // Si la  hora local coincide con la establecida para lanzar las tareas, no se han recibido peticiones en el horario fijado o el horario de lanzamiento es cada X horas
        if ((($hour == $launch_time) || ($launch_task)) || ($periodicity < 24)) { 
            // Creamos un nuevo objeto query ...
            $db = $model->getDbo();
            // y consultamos si se est� ejecutando una tarea por el plugin
            $query = 'SELECT cron_tasks_launched FROM #__securitycheckpro_file_manager WHERE id=1';
            $db->setQuery($query);
            $launched = $db->loadResult();
			
            if ($launched == 0) {  // No hay ninguna tarea ejecut�ndose
                // Actualizamos el campo 'cron_tasks_launched' de la tabla 'file_manager' para indicar que las tareas se est�n ejecutando
                $query = 'UPDATE #__securitycheckpro_file_manager SET cron_tasks_launched=1 WHERE id=1';
                $db->setQuery($query);
                $db->execute();
                switch ($tasks)
                {
                case "alternate":
                    $last_task = $model->get_campo_filemanager('last_task');
                    if ($last_task == "INTEGRITY") {
                          $last_check = $model->loadStack("fileintegrity_resume", "last_check_integrity");
                    } else if ($last_task == "PERMISSIONS") {
                        $last_check = $model->loadStack("filemanager_resume", "last_check");
                    }
					$now = $this->global_model->get_Joomla_timestamp();
					$seconds = strtotime($now) - strtotime($last_check);
					$interval = intval($seconds/3600);                    
                    
                    if ($interval >= $periodicity) {  // Hay que lanzar la tarea
                        if ($last_task == "PERMISSIONS") {
                            if (($launch_task) && ($hour != $launch_time)) {
                                $this->acciones_integrity('launch', $launch_time);
                            } else
                            {
                                $this->acciones_integrity('normal', $launch_time);
                            }
                            // Actualizamos el campo 'last_task' de la tabla 'file_manager' para reflejar la �ltima tarea lanzada
                            $model->set_campo_filemanager("last_task", 'INTEGRITY');
                        } else if ($last_task == "INTEGRITY") {
                            if (($launch_task) && ($hour != $launch_time)) {
                                $this->acciones('launch', $launch_time);
                            } else
                            {
                                $this->acciones('normal', $launch_time);
                            }                            
                            // Actualizamos el campo 'last_task' de la tabla 'file_manager' para reflejar la �ltima tarea lanzada
                            $model->set_campo_filemanager("last_task", 'PERMISSIONS');
                        }
                    }
                    break;
                case "permissions":
                    $last_check = $model->loadStack("filemanager_resume", "last_check");
					$now = $this->global_model->get_Joomla_timestamp();
					$seconds = strtotime($now) - strtotime($last_check);
					$permissions_interval = intval($seconds/3600);					
					                    
                    if ($permissions_interval >= $periodicity) {  // Hay que lanzar la tarea
                        if (($launch_task) && ($hour != $launch_time)) {
                            $this->acciones('launch', $launch_time);
                        } else
                        {
                            $this->acciones('normal', $launch_time);
                        }
                        // Actualizamos el campo 'last_task' de la tabla 'file_manager' para reflejar la �ltima tarea lanzada
                        $model->set_campo_filemanager("last_task", 'PERMISSIONS');
                    }
                    break;
                case "integrity":
                    $last_check_integrity = $model->loadStack("fileintegrity_resume", "last_check_integrity");
					$now = $this->global_model->get_Joomla_timestamp();
					$seconds = strtotime($now) - strtotime($last_check_integrity);
					$integrity_interval = intval($seconds/3600);
					                    
                    if ($integrity_interval >= $periodicity) {  // Hay que lanzar la tarea
                        if (($launch_task) && ($hour != $launch_time)) {
                            $this->acciones_integrity('launch', $launch_time);                            
                        } else
                        {
                            $this->acciones_integrity('normal', $launch_time);                            
                        }
                        
                        // Actualizamos el campo 'last_task' de la tabla 'file_manager' para reflejar la �ltima tarea lanzada
                        $model->set_campo_filemanager("last_task", 'INTEGRITY');
                    }
                    break;
                case "both":
                    $last_check_integrity = $model->loadStack("fileintegrity_resume", "last_check_integrity");
                    $last_check = $model->loadStack("filemanager_resume", "last_check");
					
					$now = $this->global_model->get_Joomla_timestamp();					
					$seconds_permissions = strtotime($now) - strtotime($last_check);
					$seconds_integrity = strtotime($now) - strtotime($last_check_integrity);
					$interval_permissions = intval($seconds_permissions/3600);
					$interval_integrity = intval($seconds_integrity/3600);
					
                    if (($interval_permissions >= $periodicity) && ($interval_integrity >= $periodicity)) {  // Hay que lanzar la tarea
                        if (($launch_task) && ($hour != $launch_time)) {
                            $this->acciones_integrity('launch', $launch_time);
                            $this->acciones('launch', $launch_time);
                        } else
                        {
                            $this->acciones_integrity('normal', $launch_time);
                            $this->acciones('normal', $launch_time);
                        }
                        
                        // Actualizamos el campo 'last_task' de la tabla 'file_manager' para reflejar la �ltima tarea lanzada
                        $model->set_campo_filemanager("last_task", 'PERMISSIONS');
                    }
                    break;
                }
                // Actualizamos el campo 'cron_tasks_launched' de la tabla 'file_manager' para indicar que las tareas ya han terminado de ejecutarse
                $query = 'UPDATE #__securitycheckpro_file_manager SET cron_tasks_launched=0 WHERE id=1';
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /*  Funci�n para mandar correos electr�nicos */
    function mandar_correo($with_bad_integrity, $with_suspicious_patterns,$look_for_malware,$subject)
    {
        // Cargamos los par�metros del componente
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/protection.php';
        if(interface_exists('JModel')) {
            $this->pro_plugin = JModelLegacy::getInstance('Protection', 'SecuritycheckProsModel');
        } else {
            $this->pro_plugin = JModel::getInstance('Protection', 'SecuritycheckProsModel');
        }        
        
        // Variables del correo electr�nico
        $email_to = $this->pro_plugin->getValue('email_to', '', 'pro_plugin');
        $to = explode(',', $email_to);
        $email_from_domain = $this->pro_plugin->getValue('email_from_domain', '', 'pro_plugin');
        $email_from_name = $this->pro_plugin->getValue('email_from_name', '', 'pro_plugin');
        $from = array($email_from_domain,$email_from_name);
    
        // Obtenemos el nombre del sitio, que ser� usado en el asunto del correo
        $config = JFactory::getConfig();
        $sitename = $config->get('sitename');
    
        // Chequeamos si se han establecido los valores para mandar el correo
        if (!empty($email_to)) {        
        
            /* Cargamos el lenguaje del sitio */
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                                
            // Creamos el asunto y el cuerpo del mensaje
            if (empty($subject)) {
                $subject = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_EMAIL_SITENAME'), $sitename);
            }        
            if ($look_for_malware) {
                $body = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_EMAIL_ALERT_BODY'), $with_bad_integrity, $with_suspicious_patterns);
            } else
            {
                $body = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_EMAIL_ALERT_BODY_NO_MALWARE_SCAN'), $with_bad_integrity);            
            }
        
            $body .= '</br>' . '</br>' . JText::_($lang->_('COM_SECURITYCHECKPRO_EMAIL_ALERT_BODY_ALERT'));
                    
            // Invocamos la clase JMail
            $mailer = JFactory::getMailer();
            // Emisor
            $mailer->setSender($from);
            // Destinatario -- es un array de direcciones
            $mailer->addRecipient($to);
            // Asunto
            $mailer->setSubject($subject);
            // Cuerpo
            $mailer->setBody($body);
            // Opciones del correo
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            // Enviamos el mensaje
            $send = $mailer->Send();
        }
            
    }

    /* Funci�n que devuelve el n�mero de archivos con integridad o permisos incorrectos y con patrones sospechosos */
    private function consulta_resultado_scan()
    {
        
        // Inicializamos las variables
        $result = array();
    
        // Cargamos los par�metros del componente
        // Import Securitycheckpros model
        JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        if(interface_exists('JModel')) {
            $model = JModelLegacy::getInstance('filemanager', 'SecuritycheckProsModel');
        } else
        {
            $model = JModel::getInstance('filemanager', 'SecuritycheckProsModel');
        }    
    
        $files_with_bad_integrity = $model->loadStack("fileintegrity_resume", "files_with_bad_integrity");
        $files_with_suspicious_patterns = $model->loadStack("malwarescan_resume", "suspicious_files");
    
        // A�adimos los resultados a la variable que ser� devuelta
        array_push($result, $files_with_bad_integrity);
        array_push($result, $files_with_suspicious_patterns);
        
        return $result;        
    
    }

}