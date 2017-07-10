<?php
/**
* @ Copyright (c) 2011 - Jose A. Luque
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die();

class SecuritycheckprosModelLogView extends SecuritycheckproModel
{

	public function  __construct($config = array()) {
		parent::__construct($config);
				
	}

	/* Función que carga el contenido del fichero de log en in iframe */
	public function view_log(){
	
		// Inicializamos las variables
		$stack = "<h2>" . "Error" . "</h2>";
		
		/* Extraemos información de las variables de estado */
		$mainframe = JFactory::getApplication();
		$filename = $mainframe->getUserState("name",'error');
		
		// Establecemos la ruta donde se almacenarán los escaneos
		$this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
		
		if ( JFile::exists($this->folder_path.$filename) ) {
			$stack = JFile::read($this->folder_path.$filename);
			// Eliminamos la parte del fichero que evita su lectura al acceder directamente
			$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
		}
		
		return $stack;
		
		
	}
	
				
}