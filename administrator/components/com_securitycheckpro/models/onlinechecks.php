<?php
/**
 * Modelo Onlinechecks para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo est� inclu�do en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Pagination\Pagination as JPagination;
use Joomla\Utilities\ArrayHelper as JArrayHelper;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelOnlineChecks extends SecuritycheckproModel
{

    /**
     * @var object Pagination 
     */
    var $_pagination = null;

    /**
     * @var int Total number of files of Pagination 
     */
    var $total = 0;

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $managewebsites_search = $app->getUserStateFromRequest('filter.onlinechecks_search', 'filter_onlinechecks_search');
        $this->setState('filter.onlinechecks_search', $managewebsites_search);
                
        parent::populateState();
    }

    /*  Funci�n para la paginaci�n */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todav�a
        if (empty($this->_pagination)) {            
            $this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /* Ver un fichero de log */
    public function view_log()
    {

        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $filename = $jinput->get('onlinechecks_logs_table', null, 'array');
        
        $mainframe = JFactory::getApplication();
    
        if (!empty($filename) && (count($filename) == 1)) {    
            // Establecemos la ruta donde est�n almacenados los escaneos
            $folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
            $file_content = file_get_contents($folder_path.$filename[0]);
            $contenido = $mainframe->setUserState('contenido', $file_content);
        
        }else
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_SELECT_ONLY_A_FILE'), 'error');
            $contenido = $mainframe->setUserState('contenido', "vacio");
        }

    }

    /* Borra ficheros de logs */
    public function delete_files()
    {

        // Inicializamos las variables
        $query = null;
        $deleted_elements = 0;
        
        $db = JFactory::getDBO();

        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $filenames = $jinput->get('onlinechecks_logs_table', null, 'array');
    
        // Establecemos la ruta donde est�n almacenados los escaneos
        $folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
    
        if (!empty($filenames)) {    
            foreach($filenames as $filename)
            {
                  $delete_result = JFile::delete($folder_path.$filename);
                if ($delete_result) {
                    $sql = "DELETE FROM #__securitycheckpro_online_checks WHERE filename='{$filename}'";
                    $db->setQuery($sql);
                    $result = $db->execute();
                
                    if ($result) {
                               $deleted_elements++;
                    }
                }else
                {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_DELETE_FILE_ERROR', $folder_path.$filename), 'error');    
                }
            
            }
        
            if ($deleted_elements > 0) {
                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_DELETED_FROM_LIST', $deleted_elements));
            }
        
        }else
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_FILES_SELECTED'), 'error');    
        }

        // Inicializamos las variables
        $query = null;
        $deleted_elements = 0;
        
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las webs que ser�n borradas de la BBDD
		$input = JFactory::getApplication()->input;
		$uids = $input->get('cid', null, 'array');	
        JArrayHelper::toInteger($uids, array());
    
        foreach($uids as $uid)
        {                
            $sql = "DELETE FROM #__securitycheckprocontrolcenter_websites WHERE id='{$uid}'";
            $db->setQuery($sql);
            $result = $db->execute();
        
            if ($result) {
                $deleted_elements++;
            }
        }
        
        if ($deleted_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_CONTROL_CENTER_DELETED_ELEMENTS', $deleted_elements));
        }
        
    }

    /* Extrae los datos de la tabla  '#__securitycheckpro_online_checks' */
    public function load($key_name = null)
    {
        
        $db = JFactory::getDBO();
        
        $query = $db->getQuery(true)
            ->select(array('*'))
            ->from($db->quoteName('#__securitycheckpro_online_checks'))
            ->order('scan_date DESC');
        $db->setQuery($query);
        $websites = $db->loadRowList();
    
        /* Obtenemos el n�mero de registros del array que hemos de mostrar. Si el l�mite superior es '0', entonces devolvemos todo el array */
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
    
        /* Obtenemos los valores de los filtros */
        $filter_onlinechecks_search = $this->state->get('filter.onlinechecks_search');
        $search = htmlentities($filter_onlinechecks_search);
    
        /* Si el campo 'search' no est� vac�o, buscamos en todos los campos del array */            
        if (!empty($search)) {
            // Inicializamos el array
            $filtered_array = array();
            $filtered_array = array_values(
                array_filter(
                    $websites, function ($element) use ($search) {
                        return ((strstr($element[1], $search)) || (strstr($element[2], $search)) || (strstr($element[3], $search)));
                    }
                )
            );
    
            $websites = $filtered_array;        
        } 
    
        // N�mero total de elementos del array
        $this->total = count($websites);
        
        /* Cortamos el array para mostrar s�lo los valores mostrados por la paginaci�n */
        $websites = array_splice($websites, $upper_limit, $lower_limit);
    
        return $websites;
    }

    /* Funci�n para descargar el fichero de logs de archivos sospechosos */
    function download_log_file()
    {

        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $filename = $jinput->get('onlinechecks_logs_table', null, 'array');
    
        if (!empty($filename) && (count($filename) == 1)) {        
            // Establecemos la ruta donde se almacenar�n los escaneos
            $folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename=' . $filename[0]);
            header('Expirer: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Lenght: ' . filesize($folder_path.$filename[0]));
            ob_clean();
            flush();
            readfile($folder_path.$filename[0]);
            exit;
        }else
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_SELECT_ONLY_A_FILE'), 'error');    
        }

    }

}
