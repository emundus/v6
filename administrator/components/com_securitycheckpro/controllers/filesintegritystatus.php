<?php
/**
* Securitycheck Pro FilesIntegrityStatus Controller
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protección frente a accesos no autorizados
defined('_JEXEC') or die('Restricted Access');

// Cargamos las clases base
jimport('joomla.application.component.controller');

/**
 * Controlador de la clase FilesIntegrity
 *
 */
class SecuritycheckprosControllerFilesIntegrityStatus extends JControllerLegacy
{

public function  __construct() {
		parent::__construct();
}

/* Mostramos la pantalla Integridad de Archivos */
public function display($cachable = false, $urlparams = Array())
{
	JRequest::setVar( 'view', 'filesintegritystatus' );
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}
	

/* Redirecciona las peticiones al Panel de Control de Integridad de Archivos */
function redireccion_file_integrity_control_panel()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=files_integrity_panel&'. JSession::getFormToken() .'=1' );
}

/* Marca como seguros todos los archivos de la BBDD que aparecen como inseguros. Esto es útil cuando hay actualizaciones o la primera vez que lanzamos 'File Integrity' */
function mark_all_unsafe_files_as_safe() {
	
	$model = $this->getModel("filesintegritystatus");
	$model->mark_all_unsafe_files_as_safe();
	JRequest::setVar( 'view', 'filesintegritystatus' );
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}

/* Añade ruta(s) a la lista de excepciones */
function addfile_exception()
{
	$model = $this->getModel("filesintegritystatus");
	$model->addfile_exception('integrity');
	
	JRequest::setVar( 'view', 'filesintegritystatus' );
	
	parent::display();
}

/* Borra ruta(s) de la lista de excepciones */
function deletefile_exception()
{
	$model = $this->getModel("filesintegritystatus");
	$model->deletefile_exception('integrity');
	
	JRequest::setVar( 'view', 'filesintegritystatus' );
	
	parent::display();
}

/* Marca como seguros todos los archivos de la BBDD seleccionados */
function mark_checked_files_as_safe() {
	
	$model = $this->getModel("filesintegritystatus");
	$model->mark_checked_files_as_safe();
	JRequest::setVar( 'view', 'filesintegritystatus' );
	JRequest::setVar('hidemainmenu', 1);
		
	parent::display();
}

/* Acciones al pulsar el botón para exportar la información */
function export_logs_integrity(){
	
	/** @var string fileintegrity's name */
	$fileintegrity_name = '';
	
	// Establecemos la ruta donde se almacenaran los escaneos
	$this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
	
	// Obtenemos el nombre del escaneo actual
	$db = JFactory::getDBO();
	$query = $db->getQuery(true)
		->select(array($db->quoteName('storage_value')))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
	$db->setQuery($query);
	$stack_integrity = $db->loadResult();	
	$stack_integrity = json_decode($stack_integrity, true);
		
	if(!empty($stack_integrity)) {
		$fileintegrity_name = $stack_integrity['filename'];
				
		// Leemos el contenido del fichero
		if ( JFile::exists($this->folder_path.DIRECTORY_SEPARATOR.$fileintegrity_name) ) {
			$stack = JFile::read($this->folder_path.DIRECTORY_SEPARATOR.$fileintegrity_name);
			// Eliminamos la parte del fichero que evita su lectura al acceder directamente
			$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
		}
		
		$stack = json_decode($stack, true);
		$stack = $stack['files_folders'];
		
		$csv_export = "";
		
		// Cabecera del archivo
		$headers = array(JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_RUTA' ),JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TAMANNO'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_LAST_MODIFIED'), 'Info');
		$csv_export .= implode(",",$headers);
		
		for ($i = 0 , $n = count($stack) ; $i < $n ; $i++) {
			$csv_export .= "\n" .$stack[$i]['path'];
			$size = filesize($stack[$i]['path']);
			$csv_export .= "," .$size;		
			$last_modified = date('Y-m-d H:i:s',filemtime($stack[$i]['path']));
			$csv_export .= "," .$last_modified;	
			$csv_export .= "," .$stack[$i]['notes'];
		}
				
		// Mandamos el contenido al navegador
		$config = JFactory::getConfig();
		$sitename = $config->get('sitename');
		// Remove whitespaces of sitename
		$sitename = str_replace(' ', '', $sitename);
		$timestamp = date('mdy_his');
		$filename = "securitycheckpro_fileintegrity_" . $sitename . "_" . $timestamp . ".csv";
		@ob_end_clean();	
		ob_start();	
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=' . $filename );
		print $csv_export;
		exit();
	} else {
		Jerror::raiseWarning(null, JText::_('COM_SECURITYCHECKPRO_NO_DATA_TO_EXPORT'));
		$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&&task=files_integrity_panel&'. JSession::getFormToken() .'=1' );			
	}	
}

}