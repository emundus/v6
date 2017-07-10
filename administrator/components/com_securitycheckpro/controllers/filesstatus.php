<?php
/**
* Securitycheck Pro FileStatus Controller
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protección frente a accesos no autorizados
defined('_JEXEC') or die('Restricted Access');

// Cargamos las clases base
jimport('joomla.application.component.controller');

/**
 * Controlador de la clase FileManager
 *
 */
class SecuritycheckprosControllerFilesStatus extends JControllerLegacy
{

public function  __construct() {
		parent::__construct();
}

/* Mostramos el Panel de Control del Gestor de archivos */
public function display($cachable = false, $urlparams = Array())
{
	JRequest::setVar( 'view', 'filesstatus' );
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}
	

/* Redirecciona las peticiones al Panel de Control de la Gestión de Archivos */
function redireccion_file_manager_control_panel()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' );
}

/* Establece correctamente los permisos de archivos y/o carpetas */
function repair()
{
	$model = $this->getModel("filesstatus");
	$model->repair();
	JRequest::setVar( 'view', 'LogsFilesstatus' );
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}

// Renderiza el contenido del iframe en el que se verán los logs de cambiar los permisos de los archivos
public function iframe()
{
	
	parent::display();
	
	flush();
	JFactory::getApplication()->close();
}

public function getEstado() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('estado_cambio_permisos');
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_' .$message);
	echo $message;
}

/* Añade ruta(s) a la lista de excepciones */
function addfile_exception()
{
	$model = $this->getModel("filesstatus");
	$model->addfile_exception('permissions');
	
	JRequest::setVar( 'view', 'filesstatus' );
	
	parent::display();
}

/* Borra ruta(s) de la lista de excepciones */
function deletefile_exception()
{
	$model = $this->getModel("filesstatus");
	$model->deletefile_exception('permissions');
	
	JRequest::setVar( 'view', 'filesstatus' );
	
	parent::display();
}

}