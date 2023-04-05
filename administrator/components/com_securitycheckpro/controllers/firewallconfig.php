<?php
/**
 * Protection Controller para Securitycheck Pro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Session\Session as JSession;
use Joomla\CMS\Language\Text as JText;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerFirewallConfig extends SecuritycheckproController
{

    /* Borra IPs de la lista negra */
    function deleteip_blacklist()
    {
        $model = $this->getModel("firewallconfig");
        $model->manage_list('blacklist', 'delete');
		            
        parent::display();    
    }

    /* Añade un IP a la lista negra */
    function addip_blacklist()
    {
        $model = $this->getModel("firewallconfig");	
		$model->manage_list('blacklist', 'add');
            
        parent::display();    
    }

    /* Borra IPs de la lista blanca */
    function deleteip_whitelist()
    {
        $model = $this->getModel("firewallconfig");
        $model->manage_list('whitelist', 'delete');
            
        parent::display();    
    }

    /* Añade un IP a la lista blanca */
    function addip_whitelist()
    {
        $model = $this->getModel("firewallconfig");
		$model->manage_list('whitelist', 'add');
            
        parent::display();    
    }

    /* Borra IPs de la lista negra dinámica */
    function deleteip_dynamic_blacklist()
    {
        $model = $this->getModel("firewallconfig");
        $model->deleteip_dynamic_blacklist();
            
        parent::display();    
    }

    /* Guarda los cambios y redirige al cPanel */
    public function save()
    {
		
        $model = $this->getModel('firewallconfig');
        $jinput = JFactory::getApplication()->input;
		    
        //El campo 'custom_code' tendrá un formato raw
        $custom_code = $jinput->get("custom_code", null, 'raw');
    
        $data = $jinput->getArray($_POST);
        
        $data['base64_exceptions'] = $model->clearstring($data['base64_exceptions'], 2);
        $data['strip_tags_exceptions'] = $model->clearstring($data['strip_tags_exceptions'], 2);
        $data['duplicate_backslashes_exceptions'] = $model->clearstring($data['duplicate_backslashes_exceptions'], 2);
        $data['line_comments_exceptions'] = $model->clearstring($data['line_comments_exceptions'], 2);
        $data['sql_pattern_exceptions'] = $model->clearstring($data['sql_pattern_exceptions'], 2);
        $data['if_statement_exceptions'] = $model->clearstring($data['if_statement_exceptions'], 2);
        $data['using_integers_exceptions'] = $model->clearstring($data['using_integers_exceptions'], 2);
        $data['escape_strings_exceptions'] = $model->clearstring($data['escape_strings_exceptions'], 2);    
        $data['lfi_exceptions'] = $model->clearstring($data['lfi_exceptions'], 2);
        $data['second_level_exceptions'] = $model->clearstring($data['second_level_exceptions'], 2);
        $data['custom_code'] = $custom_code;
        
        // Look for super users groups
        $db = JFactory::getDBO();
        $query = "SELECT id from #__usergroups where title='Super Users'" ;            
        $db->setQuery($query);
        $super_user_group = $db->loadResult();
        
        // Establecemos el grupo "Super users" por defecto para aplicar la protección de sesión
        if ((!array_key_exists("session_protection_groups", $data)) || (is_null($data['session_protection_groups']))) {
            $data['session_protection_groups'] = array('0' => $super_user_group);
        }       
    
        /* Variable que indicará si los emails introducidos en el campo 'email to' son válidos */
        $emails_valid = true;
    
        /* Obtenemos un array con todos los emails introducidos (separados con comas) */
        $emails_array = explode(",", $data['email_to']);
    
        /* Chequeamos si los emails introducidos son válidos */
        foreach($emails_array as $email)
        {
            $valid = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
            if (!$valid) {
                $emails_valid = false;
                break;
            }
        }
    
        $data['inspector_forbidden_words'] = $model->clearstring($data['inspector_forbidden_words'], 1);
    
        if (!array_key_exists('loggable_extensions', $data)) {
            $data['loggable_extensions'] = explode(',', "com_banners,com_cache,com_categories,com_config,com_contact,com_content,com_installer,com_media,com_menus,com_messages,com_modules,com_newsfeeds,com_plugins,com_redirect,com_tags,com_templates,com_users");
        }
    
        if ((!$emails_valid) || (!filter_var($data['email_from_domain'], FILTER_VALIDATE_EMAIL)) || (!is_numeric($data['email_max_number']))) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_EMAIL_FORMAT'), 'error');
        } else
        {
            if ((array_key_exists('spammer_limit', $data)) && (!is_numeric($data['spammer_limit'])) || (array_key_exists('delete_period', $data) && !is_numeric($data['delete_period']))) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_VALUE'), 'error');
            } else
            {
                $model->saveConfig($data, 'pro_plugin');
            }         
        }
            
        $this->setRedirect('index.php?option=com_securitycheckpro&view=firewallconfig&'. JSession::getFormToken() .'=1');    
    }

    /* Guarda los cambios */
    public function apply()
    {
        $this->save('pro_plugin');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1');
    }    

    /* Importa un fichero de ips a la lista pasada como argumento */
    public function import_list()
    {
        $model = $this->getModel("firewallconfig");
        $model->import_list();
            
        parent::display();    
    }
	
	/* Acciones al pulsar el botón para exportar las Ips en la lista negra */
    function export_list()
    {
		$jinput = JFactory::getApplication()->input;
		    
        $lista = $jinput->get("export", null);
		
		$db = JFactory::getDBO();
		$database = "#__securitycheckpro_" . $lista;
		
		try{
			$query = "SELECT * FROM " . $database;
			$db->setQuery($query);
			$db->execute();
			$array_ips = $db->loadColumn();		
		} catch (Exception $e)
        {    		
			JFactory::getApplication()->enqueueMessage($e->getMessage(), error);
        }
		
		if (empty($array_ips)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'), error);
        } else
        {
			// Extraemos la lista en forma ip,ip,ip (texto plano)
            $list = implode(",", $array_ips);
                                    
            // Mandamos el contenido al navegador
            $config = JFactory::getConfig();
            $sitename = $config->get('sitename');
            // Remove whitespaces of sitename
            $sitename = str_replace(' ', '', $sitename);
            $timestamp = date('mdy_his');
            $filename = "securitycheckpro_" . $lista . "_" . $sitename . "_" . $timestamp . ".txt";
            @ob_end_clean();    
            ob_start();    
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment;filename=' . $filename);
            print $list;
            exit();
		}
		 parent::display(); 
		
	}   
    
    /* Envía un correo de prueba */
    public function send_email_test()
    {
        $model = $this->getModel("firewallconfig");
        $model->send_email_test();
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1');
    }

    /* Acciones al pulsar el botón 'Enable' en la pestaña url inspector*/
    function enable_url_inspector()
    {
        
        include_once JPATH_ROOT. DIRECTORY_SEPARATOR .'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
        $cpanelmodel = new SecuritycheckprosModelCpanel();
        $cpanelmodel->enable_plugin('url_inspector');
    
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1');
        
    }

}
