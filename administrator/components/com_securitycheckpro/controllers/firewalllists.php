<?php
/**
* Protection Controller para Securitycheck Pro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load framework base classes
jimport('joomla.application.component.controller');

/**
* Securitycheckpros  Controller
*
*/
class SecuritycheckprosControllerFirewallLists extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewalllists&view=firewallcpanel' );
}

/* Borra IPs de la lista negra */
function deleteip_blacklist()
{
	$model = $this->getModel("firewalllists");
	$model->manage_list('blacklist','delete');
			
	parent::display();	
}

/* Añade un IP a la lista negra */
function addip_blacklist()
{
	$model = $this->getModel("firewalllists");
	$model->manage_list('blacklist','add');
			
	parent::display();	
}

/* Borra IPs de la lista blanca */
function deleteip_whitelist()
{
	$model = $this->getModel("firewalllists");
	$model->manage_list('whitelist','delete');
			
	parent::display();	
}

/* Añade un IP a la lista blanca */
function addip_whitelist()
{
	$model = $this->getModel("firewalllists");
	$model->manage_list('whitelist','add');
			
	parent::display();	
}

/* Borra IPs de la lista negra dinámica */
function deleteip_dynamic_blacklist()
{
	$model = $this->getModel("firewalllists");
	$model->deleteip_dynamic_blacklist();
			
	parent::display();	
}

/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewalllists');
	$data = JRequest::get('post');
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewalllists&'. JSession::getFormToken() .'=1');	
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewalllists&view=firewalllists&'. JSession::getFormToken() .'=1');
}

/* Importa un fichero de ips a la lista pasada como argumento */
public function import_blacklist()
{
	$model = $this->getModel("firewalllists");
	$model->import_blacklist();
			
	parent::display();	
}

/* Acciones al pulsar el botón para exportar las Ips en la lista negra */
function Export_blacklist(){
	$db = JFactory::getDBO();
		
	// Obtenemos los valores de las distintas opciones del Firewall Web
	$query = $db->getQuery(true)
		->select(array('*'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where("storage_key = 'pro_plugin'");
	$db->setQuery($query);
	$params = $db->loadAssocList();
	
	if ( empty($params) ) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'),error);
	} else {
			
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
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=' . $filename );
		print $blacklist;
		exit();
	}
	
	parent::display();	
}

/* Importa un fichero de ips a la lista pasada como argumento */
public function import_whitelist()
{
	$model = $this->getModel("firewalllists");
	$model->import_whitelist();
			
	parent::display();	
}

/* Acciones al pulsar el botón para exportar las Ips en la lista negra */
function Export_whitelist(){
	$db = JFactory::getDBO();
		
	// Obtenemos los valores de las distintas opciones del Firewall Web
	$query = $db->getQuery(true)
		->select(array('*'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where("storage_key = 'pro_plugin'");
	$db->setQuery($query);
	$params = $db->loadAssocList();
	
	if ( empty($params) ) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'),error);
	} else {
			
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
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=' . $filename );
		print $blacklist;
		exit();
	}
	
	parent::display();	
}

}