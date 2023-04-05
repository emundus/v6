<?php
/**
 * Modelo RulesLogs para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Pagination\Pagination as JPagination;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelRulesLogs extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{

    /**
     * Objeto Pagination * @var object 
     */
    var $_pagination = null;

    /**
     * @var int Total number of files of Pagination 
     */
    var $total = 0;

    function __construct()
    {
        parent::__construct();
    
    
        $mainframe = JFactory::getApplication();
    
        // Obtenemos las variables de paginación de la petición
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $jinput = JFactory::getApplication()->input;
        $limitstart = $jinput->get('limitstart', 0, 'int');

        // En el caso de que los límites hayan cambiado, los volvemos a ajustar
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $search = $app->getUserStateFromRequest('filter.rules_search', 'filter_rules_search');
        $this->setState('filter.rules_search', $search);
    
        parent::populateState();
    }

    /*  Función para la paginación */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todavía
        if (empty($this->_pagination)) {           
            $this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /* Función para cargar los logs de confianza */
    function load_rules_logs()
    {
        // Creamos un nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Obtenemos los grupos de Joomla
        $query->select('a.*');
        $query->from($db->quoteName('#__securitycheckpro_rules_logs') . ' AS a');
        
        // Filtramos los comentarios de las búsquedas si existen
        $search = $this->getState('filter.rules_search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.ip LIKE ' . $search . ' OR a.username LIKE '. $search . ' OR a.last_entry LIKE '. $search . ' OR a.reason LIKE '. $search .')');
        }
    
    
        $db->setQuery($query);
        $items = $db->loadObjectList();
    
        // Actualizamos el número total de elementos para la paginación
        $this->total = count($items);
    
        /* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array */
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
    
        /* Devolvemos sólo el contenido delimitado por la paginación */
        $items = array_splice($items, $upper_limit, $lower_limit);

        return $items;
    }

}
