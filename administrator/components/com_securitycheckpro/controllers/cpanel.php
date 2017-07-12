<?php
/**
* Securitycheck Pro Cpanel Controller
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * The Control Panel controller class
 *
 */
class SecuritycheckprosControllerCpanel extends JControllerLegacy
{
	public function  __construct() {
		parent::__construct();
		
	}

	/**
	 * Displays the Control Panel 
	 */
	public function display($cachable = false, $urlparams = Array())
	{
		JRequest::setVar( 'view', 'cpanel' );
		
		// Display the panel
		parent::display();
	}

	/* Acciones al pulsar el botón para establecer 'Easy Config' */
	function Set_Easy_Config(){
		$model = $this->getModel("cpanel");
	
		$applied = $model->Set_Easy_Config();
				
		echo $applied;
	}
	
	/* Acciones al pulsar el botón para establecer 'Default Config' */
	function Set_Default_Config(){
		$model = $this->getModel("cpanel");
	
		$applied = $model->Set_Default_Config();
		
		echo $applied;
	}
	
	/* Acciones al pulsar el botón 'Disable' del Firewall Web */
	function disable_firewall(){
		$model = $this->getModel("cpanel");
		$model->disable_plugin('firewall');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Acciones al pulsar el botón 'Enable' del Firewall Web */
	function enable_firewall(){
		$model = $this->getModel("cpanel");
		$model->enable_plugin('firewall');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Acciones al pulsar el botón 'Disable' del Cron */
	function disable_cron(){
		$model = $this->getModel("cpanel");
		$model->disable_plugin('cron');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Acciones al pulsar el botón 'Enable' del Cron */
	function enable_cron(){
		$model = $this->getModel("cpanel");
		$model->enable_plugin('cron');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Acciones al pulsar el botón 'Disable' de Update database */
	function disable_update_database(){
		$model = $this->getModel("cpanel");
		$model->disable_plugin('update_database');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Acciones al pulsar el botón 'Enable' de Update database */
	function enable_update_database(){
		$model = $this->getModel("cpanel");
		$model->enable_plugin('update_database');
		
		$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
	}
	
	/* Hace una consulta a la tabla especificada como parámetro */
	public function load($key_name)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query 
			->select($db->quoteName('storage_value'))
			->from($db->quoteName('#__securitycheckpro_storage'))
			->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
		$db->setQuery($query);
		$res = $db->loadResult();
			
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$this->config = new JRegistry();
		} else {
			$this->config = new JRegistry('securitycheckpro');
		}
		if(!empty($res)) {
			$res = json_decode($res, true);
			$this->config->loadArray($res);
		}
	}
	
	/* Acciones al pulsar el botón para exportar la configuración */
	function Export_config(){
		$db = JFactory::getDBO();
	
		// Obtenemos los valores de las distintas opciones del Firewall Web
		$query = $db->getQuery(true)
			->select(array('*'))
			->from($db->quoteName('#__securitycheckpro_storage'));
		$db->setQuery($query);
		$params = $db->loadAssocList();
			
		// Extraemos los valores de los array...
		$json_string = array_values($params);
		
		// Obtenemos los valores de configuración 
		$query = $db->getQuery(true)
			->select(array('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name').' = '.$db->quote('Securitycheck Pro'));
		$db->setQuery($query);
		$params = $db->loadAssocList();
		
		// Extraemos los valores de los array...
		$json_string_config = array_values($params);
		
		// Combinamos los arrays
		$json_string = array_merge($json_string,$json_string_config);
		
		// ...Y los codificamos en formato json
		$json_string = json_encode($json_string);
		
		// Cargamos los parámetros del Control Center porque necesitamos eliminar su clave secreta
		$this->load("controlcenter");
		
		// Buscamos si el campo ha sido configurado
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$secret_key = $this->config->get("secret_key", false);
		} else {
			$secret_key = $this->config->getValue("secret_key", false);
		}
				
		// Si ha sido configurado, buscamos su valor en el string_json y lo borramos
		if ( $secret_key ) {
			$json_string = str_replace($secret_key,"",$json_string);
		}
							
		// Mandamos el contenido al navegador
		$config = JFactory::getConfig();
		$sitename = $config->get('sitename');
		// Remove whitespaces of sitename
		$sitename = str_replace(' ', '', $sitename);
		$timestamp = date('mdy_his');
		$filename = "securitycheckpro_export_" . $sitename . "_" . $timestamp . ".txt";		
		@ob_end_clean();	
		ob_start();	
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=' . $filename );
		print $json_string;
		exit();
	}
	
/* Redirecciona las peticiones a System Info */
function Go_system_info()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona las peticiones a las listas del firewall */
function manage_lists()
{
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewalllists&view=firewalllists&'. JSession::getFormToken() .'=1');
}

/* Acciones a ejecutar cuando se pulsa el botón 'Purge sessions' */
function purge_sessions(){
	$model = $this->getModel("cpanel");
	$model->purge_sessions();
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
}

/* Acciones al pulsar el boton 'Enable' del Spam Protection */
function enable_spam_protection(){
	$model = $this->getModel("cpanel");
	$model->enable_plugin('spam_protection');
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
}
	
/* Acciones al pulsar el botn 'Disable' de Spam Protection */
function disable_spam_protection(){
	$model = $this->getModel("cpanel");
	$model->disable_plugin('spam_protection');
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );
		
}

/* Función para ir al menú de vulnerabilidades. Usada desde el submenú */
function go_to_vulnerabilities(){
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1' );		
}

/* Función para ir al menú de permisos. Usada desde el submenú */
function go_to_filemanager(){
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' );		
}

/* Función para ir al menú de integridad. Usada desde el submenú */
function go_to_fileintegrity(){
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=files_integrity_panel&'. JSession::getFormToken() .'=1' );		
}

/* Función para ir al menú de htaccess. Usada desde el submenú */
function go_to_htaccess(){
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=protection&view=protection&'. JSession::getFormToken() .'=1' );		
}

/* Función para ir al menú de malware. Usada desde el submenú */
function go_to_malware(){
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=malwarescan_panel&'. JSession::getFormToken() .'=1' );		
}

/* Redirecciona las peticiones a Geoblock */
function go_to_geoblock()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=geoblock&view=geoblock&'. JSession::getFormToken() .'=1' );
}

/* Función que establece las actualizaciones automáticas de Geolite2 */
function automatic_updates_geoblock() {
	$model = $this->getModel("cpanel");
	$model->enable_automatic_updates();
		
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );
	
}

}