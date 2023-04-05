<?php
/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2013 - Jose A. Luque
 *
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No Permission
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Session\Session as JSession;
use Joomla\CMS\Factory as JFactory;

class SecuritycheckproController extends \Joomla\CMS\MVC\Controller\BaseController
{
    
    function __construct()
    {
        parent::__construct();
    }

    /* Redirecciona las peticiones al Panel de Control */
    function redireccion_control_panel()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }

    /* Redirecciona las peticiones a System Info */
    function redireccion_system_info()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1');
    }

    /* Acciones a ejecutar cuando se pulsa el botón 'Purge sessions' */
    function purge_sessions()
    {
    
        $model = $this->getModel();
        $model->purge_sessions();
        
        $this->setRedirect('index.php?option=com_securitycheckpro');
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
            
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $this->config = new JRegistry();
        } else
        {
            $this->config = new JRegistry('securitycheckpro');
        }
        
        if (!empty($res)) {
            $res = json_decode($res, true);
            $this->config->loadArray($res);
        }
    }

    /* Acciones al pulsar el botón para exportar la configuración */
    function Export_config()
    {
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
        $json_string = array_merge($json_string, $json_string_config);
        
        // ...Y los codificamos en formato json
        $json_string = json_encode($json_string);
        
        // Cargamos los parámetros del Control Center porque necesitamos eliminar su clave secreta
        $this->load("controlcenter");
        
        // Buscamos si el campo ha sido configurado
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $secret_key = $this->config->get("secret_key", false);
        } else
        {
            $secret_key = $this->config->getValue("secret_key", false);
        }
                
        // Si ha sido configurado, buscamos su valor en el string_json y lo borramos
        if ($secret_key) {
            $json_string = str_replace($secret_key, "", $json_string);
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
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment;filename=' . $filename);
        print $json_string;
        exit();
    }

}
