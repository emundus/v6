<?php
/**
* Protection Controller para Securitycheck Pro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
* Securitycheckpros  Controller
*
*/
class SecuritycheckprosControllerFirewallConfig extends SecuritycheckproController
{

/* Borra IPs de la lista negra */
function deleteip_blacklist()
{
	$model = $this->getModel("firewallconfig");
	$model->manage_list('blacklist','delete');
			
	parent::display();	
}

/* Añade un IP a la lista negra */
function addip_blacklist()
{
	$model = $this->getModel("firewallconfig");
	$model->manage_list('blacklist','add');
			
	parent::display();	
}

/* Borra IPs de la lista blanca */
function deleteip_whitelist()
{
	$model = $this->getModel("firewallconfig");
	$model->manage_list('whitelist','delete');
			
	parent::display();	
}

/* Añade un IP a la lista blanca */
function addip_whitelist()
{
	$model = $this->getModel("firewallconfig");
	$model->manage_list('whitelist','add');
			
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
	$custom_code = $jinput->get("custom_code",null,'raw');
	
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
	$query = "SELECT id from `#__usergroups` where `title`='Super Users'" ;			
	$db->setQuery($query);
	$super_user_group = $db->loadResult();
		
	// Establecemos el grupo "Super users" por defecto para aplicar la protección de sesión
	if ((!array_key_exists("session_protection_groups",$data)) || (is_null($data['session_protection_groups'])))
	{
		$data['session_protection_groups'] = array('0' => $super_user_group);
	}
	
	/* Continentes seleccionados */
	if (array_key_exists('continent',$data))
	{
		$continents = $data['continent'];		
		$continents = array_keys($continents);
		$continents = implode(',', $continents);
	} else
	{
		$continents = '';
	}
	

	/* Países seleccionados */
	if (array_key_exists('country',$data))
	{
		$countries = $data['country'];		
		$countries = array_keys($countries);
		$countries = implode(',', $countries);
	} else
	{
		$countries = '';
	}

	$config = array('geoblockcountries' => $countries, 'geoblockcontinents' => $continents);
	
	/* Guardamos los datos del geobloqueo en la BBDD */
	$model->save_geoblock($config,'geoblock');
	
	/* Variable que indicará si los emails introducidos en el campo 'email to' son válidos */
	$emails_valid = true;
	
	/* Obtenemos un array con todos los emails introducidos (separados con comas) */
	$emails_array = explode(",",$data['email_to']);
	
	/* Chequeamos si los emails introducidos son válidos */
	foreach($emails_array as $email)
	{
		$valid = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
		if (!$valid)
		{
			$emails_valid = false;
			break;
		}
	}
	
	$data['inspector_forbidden_words'] = $model->clearstring($data['inspector_forbidden_words'], 1);
	
	if (!array_key_exists('loggable_extensions',$data))
	{
		$data['loggable_extensions'] = explode(',',"com_banners,com_cache,com_categories,com_config,com_contact,com_content,com_installer,com_media,com_menus,com_messages,com_modules,com_newsfeeds,com_plugins,com_redirect,com_tags,com_templates,com_users");
	}
	
	if ((!$emails_valid) || (!filter_var($data['email_from_domain'], FILTER_VALIDATE_EMAIL)) || (!is_numeric($data['email_max_number'])))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_EMAIL_FORMAT'),'error');
	} else
	{
		if ((array_key_exists('spammer_limit',$data)) && (!is_numeric($data['spammer_limit'])) || (array_key_exists('delete_period',$data) && !is_numeric($data['delete_period'])))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_VALUE'),'error');
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

/* Función para descargar la bbdd de Maxmind 2 */
public function update_geoblock_database()
{
	$model = $this->getModel('firewallconfig');
	$msg = $model->update_geoblock_database();
	
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1',$msg);
}

/* Importa un fichero de ips a la lista pasada como argumento */
public function import_blacklist()
{
	$model = $this->getModel("firewallconfig");
	$model->import_blacklist();
			
	parent::display();	
}

/* Acciones al pulsar el botón para exportar las Ips en la lista negra */
function Export_blacklist()
{
	$db = JFactory::getDBO();
		
	// Obtenemos los valores de las distintas opciones del Firewall Web
	$query = $db->getQuery(true)
		->select(array('*'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where("storage_key = 'pro_plugin'");
	$db->setQuery($query);
	$params = $db->loadAssocList();
	
	if (empty($params))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'),error);
	} else
	{
			
		// Extraemos los valores de los array...
		$json_string = array_values($params);
			
		// ... y ahora todos los valores del parámetro como array también
		$blacklist_array = json_decode($json_string[0]['storage_value'],true);
		
		// Extraemos la lista en forma ip,ip,ip (texto plano)
		$blacklist = $blacklist_array['blacklist'];
									
		// Mandamos el contenido al navegador
		$config = JFactory::getConfig();
		$sitename = $config->get('sitename');
		// Remove whitespaces of sitename
		$sitename = str_replace(' ', '', $sitename);
		$timestamp = date('mdy_his');
		$filename = "securitycheckpro_blacklist_" . $sitename . "_" . $timestamp . ".txt";
		@ob_end_clean();	
		ob_start();	
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename=' . $filename);
		print $blacklist;
		exit();
	}
	
	parent::display();	
}

/* Importa un fichero de ips a la lista pasada como argumento */
public function import_whitelist()
{
	$model = $this->getModel("firewallconfig");
	$model->import_whitelist();
			
	parent::display();	
}

/* Acciones al pulsar el botón para exportar las Ips en la lista negra */
function Export_whitelist()
{
	$db = JFactory::getDBO();
		
	// Obtenemos los valores de las distintas opciones del Firewall Web
	$query = $db->getQuery(true)
		->select(array('*'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where("storage_key = 'pro_plugin'");
	$db->setQuery($query);
	$params = $db->loadAssocList();
	
	if (empty($params))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'),error);
	} else
	{
			
		// Extraemos los valores de los array...
		$json_string = array_values($params);
			
		// ... y ahora todos los valores del parámetro como array también
		$blacklist_array = json_decode($json_string[0]['storage_value'],true);
		
		// Extraemos la lista en forma ip,ip,ip (texto plano)
		$blacklist = $blacklist_array['whitelist'];
									
		// Mandamos el contenido al navegador
		$config = JFactory::getConfig();
		$sitename = $config->get('sitename');
		// Remove whitespaces of sitename
		$sitename = str_replace(' ', '', $sitename);
		$timestamp = date('mdy_his');
		$filename = "securitycheckpro_whitelist_" . $sitename . "_" . $timestamp . ".txt";
		@ob_end_clean();	
		ob_start();	
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename=' . $filename);
		print $blacklist;
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
		
	require_once JPATH_ROOT. DIRECTORY_SEPARATOR .'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
	$cpanelmodel = new SecuritycheckprosModelCpanel();
	$cpanelmodel->enable_plugin('url_inspector');
	
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallconfig&view=firewallconfig&'. JSession::getFormToken() .'=1');
		
}

}