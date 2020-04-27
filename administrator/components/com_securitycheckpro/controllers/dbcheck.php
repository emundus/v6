<?php
/**
 * @package   RSFirewall!
 * @copyright (C) 2009-2014 www.rsjoomla.com
 * @license   GPL, http://www.gnu.org/licenses/gpl-2.0.html
 * @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
 */

// Protección frente a accesos no autorizados
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class SecuritycheckprosControllerDbCheck extends JControllerLegacy
{

    public function __construct()
    {
        parent::__construct();
        
        
    }
    
    public function optimize()
    {
        $app     = JFactory::getApplication();
        $model     = $this->getModel('DbCheck');
        
        if (!($result = $model->optimizeTables())) {
            echo $model->getError();
        } else
        {
            echo JText::sprintf('COM_SECURITYCHECKPRO_DB_OPTIMIZE_RESULT', $result['optimize'], $result['repair']);
        }
        
        $app->close();
    }

    /* Redirecciona las peticiones al Panel de Control */
    function redireccion_control_panel()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro');
    }
}