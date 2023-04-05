<?php
/**
 * Rules Controller para Securitycheck Pro
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
class SecuritycheckprosControllerRules extends JControllerLegacy
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

    /* Método para aplicar las reglas a un grupo o conjunto de grupos */
    public function apply_rules()
    {
        // Inicializamos las variables.
        $jinput = JFactory::getApplication()->input;
        $ids    =$jinput->getVar('cid', '', 'array');
    
        if (empty($ids)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_RULES_NO_GROUPS_SELECTED'), 'warning');
        } else
        {
            // Obtenemos el modelo
            $model = $this->getModel("rules");

            // Cambiamos el estado de los registros seleccionados
            if (!$model->apply_rules()) {
                JFactory::getApplication()->enqueueMessage($model->getError(), 'warning');
            } else 
            {
                $this->setMessage(JText::plural('COM_SECURITYCHECKPRO_RULES_N_GROUPS_SELECTED', count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_securitycheckpro&controller=securitycheckpro&view=rules&'. JSession::getFormToken() .'=1');
    }

    /* Método para NO aplicar las reglas a un grupo o conjunto de grupos */
    public function not_apply_rules()
    {
        // Inicializamos las variables.
        $jinput = JFactory::getApplication()->input;
        $ids    =$jinput->getVar('cid', '', 'array');
    
        if (empty($ids)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_RULES_NO_GROUPS_SELECTED'), 'warning');
        } else 
        {
            // Obtenemos el modelo
            $model = $this->getModel("rules");

            // Cambiamos el estado de los registros seleccionados
            if (!$model->not_apply_rules()) {
                JFactory::getApplication()->enqueueMessage($model->getError(), 'warning');
            } else 
            {
                $this->setMessage(JText::plural('COM_SECURITYCHECKPRO_RULES_N_GROUPS_SELECTED', count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_securitycheckpro&controller=securitycheckpro&view=rules&'. JSession::getFormToken() .'=1');
    }

    /* Muestra las entradas de confianza */
    function rules_logs()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set('view', 'ruleslogs');
    
        parent::display();
    }

    /* Redirecciona las peticiones al componente */
    function redireccion()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=rules&view=rules');
    }

    /* Redirecciona las peticiones al Panel de Control */
    function redireccion_control_panel()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }

}
