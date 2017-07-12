<?php
/**
* @ Copyright (c) 2011 - Jose A. Luque
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die();

class SecuritycheckprosControllerLogView extends SecuritycheckproController
{
	public function __construct($config = array()) {
		parent::__construct($config);
		
		/* Extraemos la web seleccionada */
		$jinput = JFactory::getApplication()->input;
		$name = $jinput->get('name',0,'');
		
		/* Guardamos el nombre del fichero seleccionado en una variable de estado */
		$mainframe = JFactory::getApplication();
		$mainframe->setUserState("name",$name);
	}
			
}
