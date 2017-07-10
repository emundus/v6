<?php
/**
* Securitycheck Pro FileManager Controller
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
class SecuritycheckprosControllerFileManager extends JControllerLegacy
{

public function  __construct() {
		parent::__construct();
}

/* Mostramos el Panel de Control del Gestor de archivos */
public function display($cachable = false, $urlparams = Array())
{
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}

/* Función que nos permite establecer la vista 'filesintegrity' y usar el modelo 'filemanager' conjuntamente */
public function files_integrity_panel()
{
$view = $this->getView( 'filesintegrity', 'html' );
$view->setModel( $this->getModel( 'filemanager' ) );
$view->display();

}

/* Función que nos permite establecer la vista 'malwarescan' y usar el modelo 'filemanager' conjuntamente */
public function malwarescan_panel()
{
$view = $this->getView( 'malwarescan', 'html' );
$view->setModel( $this->getModel( 'filemanager' ) );
$view->display();

}
	
/* Redirecciona las peticiones al Panel de Control */
function redireccion_control_panel()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );
}

/* Redirecciona las peticiones al Panel de Control de la Gestión de Archivos  y borra el fichero de logs*/
function redireccion_control_panel_y_borra_log()
{
	jimport('joomla.filesystem.file');
	jimport( 'joomla.application.component.helper' );

	// Obtenemos la ruta al fichero de logs, que vendrá marcada por la entrada 'log_path' del fichero 'configuration.php'
	$app = JFactory::getApplication();
	$logName = $app->getCfg('log_path');
	$filename = $logName . DIRECTORY_SEPARATOR ."change_permissions.log.php";
	
	// ¿ Debemos borrar el archivo de logs?
	$params = JComponentHelper::getParams('com_securitycheckpro');
	$delete_log_file = $params->get('delete_log_file',1);
	if ( $delete_log_file == 1 ) {
		// Si no puede borrar el archivo, Joomla muestra un error indicándolo a través de JERROR
		$result = JFile::delete($filename);
	}
	
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' );
}

/* Mostramos información de la integridad de los archivos analizados */
public function view_files_integrity()
{
	JRequest::setVar( 'view', 'filesintegritystatus' );
	
	parent::display();
} 

/* Mostramos los permisos de los archivos analizados */
public function view_file_permissions()
{
	JRequest::setVar( 'view', 'filesstatus' );
	
	parent::display();
}

/* Mostramos información sobre los archivos sospechosos de contener malware */
public function view_files_malwarescan()
{
	JRequest::setVar( 'view', 'malwarescanstatus' );
	
	parent::display();
} 

/* Mostramos el Panel para borrar los datos de la BBDD  */
public function initialize_data()
{
	JRequest::setVar( 'view', 'initialize_data' );
	
	parent::display();
}

/* Acciones al pulsar el escaneo de archivos manual */
function acciones(){
	$model = $this->getModel("filemanager");
	
	$model->set_campo_filemanager('files_scanned',0);
	$model->set_campo_filemanager('last_check',date('Y-m-d H:i:s'));
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS');
	echo $message; 
	$model->set_campo_filemanager('estado','IN_PROGRESS'); 
	$model->scan("permissions");
}

/* Acciones al pulsar el chequeo manual de integridad */
function acciones_integrity(){
	$model = $this->getModel("filemanager");
	
	$model->set_campo_filemanager('files_scanned_integrity',0);
	$model->set_campo_filemanager('last_check_integrity',date('Y-m-d H:i:s'));
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS');
	echo $message; 
	$model->set_campo_filemanager('estado_integrity','IN_PROGRESS'); 
	$model->scan("integrity");
}

/* Acciones al pulsar el chequeo manual de malware */
function acciones_malwarescan(){
	$model = $this->getModel("filemanager");
	
	$model->set_campo_filemanager('files_scanned_malwarescan',0);
	$model->set_campo_filemanager('last_check_malwarescan',date('Y-m-d H:i:s'));
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_IN_PROGRESS');
	echo $message; 
	$model->set_campo_filemanager('estado_malwarescan','IN_PROGRESS'); 
	$model->scan("malwarescan");	
}

/* Acciones al pulsar el borrado de la información de la BBDD */
function acciones_clear_data(){
	
	$message = JText::_('COM_SECURITYCHECKPRO_CLEAR_DATA_DELETING_ENTRIES');
	echo $message; 
	$this->initialize_database();
	$model = $this->getModel("filemanager");
	$model->set_campo_filemanager('estado_clear_data','ENDED');
}

/* Borra los datos de la tabla '#__securitycheckpro_file_permissions' */
function initialize_database()
{
	$model = $this->getModel("filemanager");
	$model->initialize_database();
	
}

/* Obtiene el estado del proceso de análisis de permisos de archivos consultando la tabla '#__securitycheckpro_file_manager'*/
public function getEstado() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('estado');
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_' .$message);
	echo $message;
}

/* Obtiene el estado del proceso de análisis de la integridad de los archivos consultando la tabla '#__securitycheckpro_file_manager'*/
public function getEstadoIntegrity() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('estado_integrity');
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_' .$message);
	echo $message;
}

/* Obtiene el estado del proceso de análisis de bús1queda de malware en los archivos consultando la tabla '#__securitycheckpro_file_manager'*/
public function getEstadoMalwareScan() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('estado_malwarescan');
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_' .$message);
	echo $message;
}

/* Obtiene el estado del proceso de hacer un drop y crear de nuevo la tabla '#__securitycheckpro_file_permissions'*/
public function getEstadoClearData() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('estado_clear_data');
	$message = JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_' .$message);
	echo $message;
}

public function currentDateTime() {
    echo date('Y-m-d H:i:s');
}

/* Obtiene el estado del proceso de análisis de la integridad de los archivos consultando los datos de sesión almacenados previamente */
public function get_percent_integrity() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('files_scanned_integrity');
	echo $message;
	
}

/* Obtiene el estado del proceso de análisis de permisos de los archivos consultando los datos de sesión almacenados previamente */
public function get_percent() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('files_scanned');
	echo $message;
	
}

/* Obtiene el estado del proceso de análisis de búsqueda de malware en los archivos consultando los datos de sesión almacenados previamente */
public function get_percent_malwarescan() {
	$model = $this->getModel("filemanager");
	$message = $model->get_campo_filemanager('files_scanned_malwarescan');
	echo $message;
	
}

/* Obtiene la diferencia, en horas, entre dos tareas de verificación de integridad. Si la diferencia es mayor de 3 horas, devuelve el valor 20000 */
public function getEstadoIntegrity_Timediff() {
	$model = $this->getModel("filemanager");
	$datos = null;
		
	(int) $timediff = $model->get_timediff("integrity");
	$estado_integrity = $model->get_campo_filemanager('estado_integrity');
	$datos = json_encode(array(
				'estado_integrity'	=> $estado_integrity,
				'timediff'		=> $timediff
			));
			
	echo $datos;		
}

/* Obtiene la diferencia, en horas, entre dos tareas de chequeo de permisos. Si la diferencia es mayor de 3 horas, devuelve el valor 20000 */
public function getEstado_Timediff() {
	$model = $this->getModel("filemanager");
	$datos = null;
		
	(int) $timediff = $model->get_timediff("permissions");
	$estado = $model->get_campo_filemanager('estado');
	$datos = json_encode(array(
				'estado'	=> $estado,
				'timediff'		=> $timediff
			));
			
	echo $datos;		
}

/* Obtiene la diferencia, en horas, entre dos tareas de búsqueda de malware. Si la diferencia es mayor de 3 horas, devuelve el valor 20000 */
public function getEstadoMalwarescan_Timediff() {
	$model = $this->getModel("filemanager");
	$datos = null;
		
	(int) $timediff = $model->get_timediff("malwarescan");
	$estado_malwarescan = $model->get_campo_filemanager('estado_malwarescan');
	$datos = json_encode(array(
				'estado_malwarescan'	=> $estado_malwarescan,
				'timediff'		=> $timediff
			));
			
	echo $datos;		
}

/* Redirecciona a la opción de mostrar las vulnerabilidades */
function GoToVuln()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1' );	
}

/* Redirecciona a la opción de mostrar la integridad de archivos */
function GoToIntegrity()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=files_integrity_panel&'. JSession::getFormToken() .'=1' );		
}

/* Redirecciona a la opción de mostrar los permisos de archivos/directorios */
function GoToPermissions()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' );	
}

/* Redirecciona a la opción htaccess protection */
function GoToHtaccessProtection()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=protection&view=protection&'. JSession::getFormToken() .'=1' );	
}

/* Redirecciona al Cponel */
function GoToCpanel()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro' );	
}

/* Redirecciona a las listas del firewall */
function GoToFirewallLists()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewalllists&view=firewalllists&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona a las listas del firewall */
function GoToFirewallLogs()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewalllogs&view=firewalllogs&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona al segundo nivel del firewall */
function GoToFirewallSecondLevel()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallsecond&view=firewallsecond&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona a las excepciones del firewall */
function GoToFirewallExceptions()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallexceptions&view=firewallexceptions&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona al escanér de archivos del firewall */
function GoToUploadScanner()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=uploadscanner&view=uploadscanner&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona a la opción User session del firewall */
function GoToUserSessionProtection()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallsessionprotection&view=firewallsessionprotection&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona a la opción User session del firewall */
function GoToMalware()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=malwarescan_panel&'. JSession::getFormToken() .'=1' );
}

/* Redirecciona las peticiones a System Info */
function redireccion_system_info()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1' );
}

}