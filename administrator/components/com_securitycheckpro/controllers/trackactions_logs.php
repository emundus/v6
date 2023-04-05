<?php
/**
 * Track Actions
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
JLoader::register('TrackActionsHelper', JPATH_ROOT . '/plugins/system/trackactions/helpers/trackactions.php');


/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerTrackActions_Logs extends JControllerLegacy
{
    /**
     constructor (registers additional tasks to methods)
     *
     @return void
     */
    function __construct()
    {
        parent::__construct();

    }

    /* Redirecciona las peticiones al Panel de Control */
    function redireccion_control_panel()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }


    /**
     * Method to export logs
     *
     * @return void
     */
    public function exportLogs()
    {
        // Get the logs data
        $data = $this->getModel('trackactions_logs')->getLogsData();
    
        // Export data to CSV file
        TrackActionsHelper::dataToCsv($data);
    }
    

    /**
     * Borrar log(s) de la base de datos
     */
    function delete()
    {
        $model = $this->getModel('trackactions_logs');
        $read = $model->delete();
    
        parent::display();
    }

    /**
     * Borrar todos los log(s) de la base de datos
     */
    function delete_all()
    {
        $model = $this->getModel('trackactions_logs');
        $read = $model->delete_all();
    
        parent::display();
    }


}
