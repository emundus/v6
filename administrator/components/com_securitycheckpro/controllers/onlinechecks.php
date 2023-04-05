<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 *  @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerOnlineChecks extends SecuritycheckproController
{

    public function __construct()
    {
        parent::__construct();    
        
        $task = JFactory::getApplication()->input->get('task', null);
                                        
        if ($task != "view_file") {
            $mainframe = JFactory::getApplication();
            // Si la tarea es distinta a "view_file" inicializamos la variable de estado 'contenido'
            $mainframe->setUserState('contenido', "vacio");        
        }
        
    }

    /* Borra ficheros de logs */
    function delete_files()
    {
        $model = $this->getModel("onlinechecks");
        $model->delete_files();    
        $jinput = JFactory::getApplication()->input;
        $jinput->set('view', 'onlinechecks');
    
        parent::display();    
    }

    /* Download suspicious file log */
    function download_log_file()
    {
        $model = $this->getModel("onlinechecks");    
        $model->download_log_file();
        
        $jinput = JFactory::getApplication()->input;
        $jinput->set('view', 'onlinechecks');
        
        parent::display();    
        
    }

    /* View onlinechecks log */
    function view_log()
    {
        $model = $this->getModel("onlinechecks");    
        $model->view_log();
            
        parent::display();    
        
    }

}
