<?php
/**
 * Track ACtions para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelFirewallTrackActions extends SecuritycheckproModel
{

    public function is_plugin_installed()
    {
        // Inicializamos las variables
        $installed= false;
      
        $plugin = JpluginHelper::getPlugin('system', 'trackactions');
    
        // Si el valor devuelto es un array, entonces el plugin no existe o no está habilitado
        if (!is_array($plugin)) {
            $installed = true;        
        }    
        return $installed;
    }

}
