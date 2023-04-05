<?php
/**
 * Modelo Securitychecks para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
/**
 * Modelo Vulninfo
 */
class SecuritycheckprosModelVulninfo extends SecuritycheckproModel
{
    /**
     Array de datos
     *
     @var array
     */
    var $_data;
    /**
     Total items
     *
     @var integer
     */
    var $_total = null;
    /**
     Objeto Pagination
     *
     @var object
     */
    var $_pagination = null;

    function __construct()
    {
        parent::__construct();
    
    
        $mainframe = JFactory::getApplication();
 
        // Obtenemos las variables de paginación de la petición
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $jinput = JFactory::getApplication()->input;
        $limitstart = $jinput->set('limitstart', 0, 'int');

        // En el caso de que los límites hayan cambiado, los volvemos a ajustar
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    
    }


    /* 
    * Función para obtener el número de registros de la BBDD 'securitycheck_db'
    */
    function getTotal()
    {
        // Cargamos el contenido si es que no existe todavía
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    /* 
    * Función para la paginación 
    */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todavía
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /*
    * Devuelve todos los componentes almacenados en la BBDD 'securitycheckpro_db'
    */
    function _buildQuery()
    {
        $query = 'SELECT * FROM #__securitycheckpro_db ORDER BY id DESC';
        return $query;
    }

    /**
     * Método para cargar todas las vulnerabilidades de los componentes
     */
    function datos()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__securitycheckpro_db ORDER BY id DESC';
        $db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
        $data = $db->loadAssocList();
        
        return $data;
    }
}
