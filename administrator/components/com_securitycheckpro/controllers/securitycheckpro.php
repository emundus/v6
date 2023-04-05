<?php
/**
 * Securitycheckpro Controller para Securitycheck Pro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Session\Session as JSession;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerSecuritycheckpro extends SecuritycheckproController
{
    /**
     constructor (registers additional tasks to methods)
     *
     @return void
     */
    function __construct()
    {
        parent::__construct();
        $jinput = JFactory::getApplication()->input;
    }
    /**
     Muestra los componentes de la BBDD
     */
    function mostrar()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set('view', 'vulninfo');
            
        parent::display();
    }

    /**
     * Busca cambios entre los componentes almacenados en la BBDD y la BBDD de vulnerabilidades
     */
    function buscar()
    {
        $model = $this->getModel('securitycheckpros');
        if (!$model->buscar()) {
            $msg = JText::_('COM_SECURITYCHECKPRO_CHECK_FAILED');
            JFactory::getApplication()->enqueueMessage($msg, 'warning');
        } else
        {
            $eliminados = $jinput->get('comp_eliminados', 0, int);
            $core_actualizado = $jinput->get('core_actualizado', 0, int);
            $comps_actualizados = $jinput->get('componentes_actualizados', 0, int);    
            $comp_ok = JText::_('COM_SECURITYCHECKPRO_CHECK_OK ');
            $msg = JText::_($eliminados ."</li><li>" .$core_actualizado ."</li><li>" .$comps_actualizados ."</li><li>" .$comp_ok);
        }
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=securitycheckpro', $msg);
    }

    /* Ver los logs almacenados por el plugin */
    function view_logs()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set('view', 'logs');

        parent::display(); 
    }

    /* Redirecciona las peticiones al componente */
    function redireccion()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1');
    }

    /* Redirecciona las peticiones al Panel de Control */
    function redireccion_control_panel()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }

    /* Filtra los logs según el término de búsqueda especificado*/
    function search()
    {
        $model = $this->getModel('logs');
        if (!$model->search()) {
            $msg = JText::_('COM_SECURITYCHECKPRO_CHECK_FAILED');
            JFactory::getApplication()->enqueueMessage($msg, 'warning');        
        } else
        {
            $this->view_logs();
        }
    
    }

    /**
     * Ver los logs
     */
    function view()
    {
        $jinput->set('view', 'securitycheckpro');
        $jinput->set('layout', 'form');
        parent::display();
    }
    
    
    /**
     * Cancelar una acción
     */
    function cancel()
    {
        $msg = JText::_('Operación cancelada');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=securitycheckpro', $msg);
    }
 
    /**
     * Exportar logs en formato csv
     */
    function csv_export()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__securitycheckpro_logs';
        $db->setQuery($query);
        $rows = $db->loadRowList();
        $csv_export = "";
            
        // Cabecera del archivo
        $headers = array('Id','Ip',JText::_('COM_SECURITYCHECKPRO_GEOLOCATION_LABEL'),JText::_('COM_SECURITYCHECKPRO_USER'),JText::_('COM_SECURITYCHECKPRO_LOG_TIME'),JText::_('COM_SECURITYCHECKPRO_LOG_DESCRIPTION'),JText::_('COM_SECURITYCHECKPRO_DETAILED_DESCRIPTION'), JText::_('COM_SECURITYCHECKPRO_LOG_TYPE'), JText::_('COM_SECURITYCHECKPRO_LOG_URI'),JText::_('COM_SECURITYCHECKPRO_TYPE_COMPONENT'),JText::_('COM_SECURITYCHECKPRO_LOG_READ'),JText::_('COM_SECURITYCHECKPRO_ORIGINAL_STRING_CSV'));
        $csv_export .= implode(",", $headers);

        for ($i = 0 , $n = count($rows); $i < $n ; $i++)
        {
            $rows[$i][5] = JText::_('COM_SECURITYCHECKPRO_' .$rows[$i][5]);
            $rows[$i][7] = JText::_('COM_SECURITYCHECKPRO_TITLE_' .$rows[$i][7]);
            //$rows[$i][11] = base64_decode($rows[$i][11]);
            if ($rows[$i][10] == 0) {
                  $rows[$i][10] = JText::_('COM_SECURITYCHECKPRO_NO');
            } else
            {
                $rows[$i][10] = JText::_('COM_SECURITYCHECKPRO_YES');
            }
            $csv_export .= "\n" .implode(",", $rows[$i]);
        }
    
        // Mandamos el contenido al navegador
        $config = JFactory::getConfig();
        $sitename = $config->get('sitename');
        // Remove whitespaces of sitename
        $sitename = str_replace(' ', '', $sitename);
        $timestamp = date('mdy_his');
        $filename = "securitycheckpro_logs_" . $sitename . "_" . $timestamp . ".csv";
        @ob_end_clean();    
        ob_start();    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);
        print $csv_export;
        exit();
    
    }

    /**
     * Marcar log(s) como leídos
     */
    function mark_read()
    {
        $model = $this->getModel('logs');
        $read = $model->mark_read();
        $this->view_logs();
    }

    /**
     * Marcar log(s) como no leídos
     */
    function mark_unread()
    {
        $model = $this->getModel('logs');
        $read = $model->mark_unread();
        $this->view_logs();
    }

    /**
     * Borrar log(s) de la base de datos
     */
    function delete()
    {
        $model = $this->getModel('logs');
        $read = $model->delete();
        $this->view_logs();
    }

    /**
     * Añadir Ip(s)  a la lista negra
     */
    function add_to_blacklist()
    {
        $model = $this->getModel('logs');
        $model->add_to_blacklist();
        $this->view_logs();
    }

    /* Redirecciona las peticiones a System Info */
    function redireccion_system_info()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1');
    }

    /**
     * Borrar todos los log(s) de la base de datos
     */
    function delete_all()
    {
        $model = $this->getModel('logs');
        $read = $model->delete_all();
        $this->view_logs();
    }

    /**
     * Añadir Ip(s) a la lista blanca
     */
    function add_to_whitelist()
    {
        $model = $this->getModel('logs');
        $model->add_to_whitelist();
        $this->view_logs();
    }

    /* Añadir Ip(s) a la lista blanca */
    function filter_vulnerable_extension()
    {
        $jinput = JFactory::getApplication()->input;
        $product = $jinput->get('product', '', 'string');		
        $model = $this->getModel('securitycheckpros');
        $vuln_extensions = $model->filter_vulnerable_extension($product);
        
        echo $vuln_extensions;
    }
	
	/**
     * Añadir componente como excepcion
     */
    function add_exception()
    {
        $model = $this->getModel('logs');
        $model->add_exception();
        $this->view_logs();
    }

}
