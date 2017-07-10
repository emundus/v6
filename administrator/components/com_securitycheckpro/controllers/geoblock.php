<?php
/**
* Geoblock Controller para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
//jimport('joomla.application.component.controller');

class SecuritycheckProsControllerGeoblock extends SecuritycheckproController
{
	
/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallcpanel&view=firewallcpanel' );
}

public function save()
{
	$model = $this->getModel('geoblock');
	
	/* Obtenemos los datos del formulario */
	$data = JRequest::get('post');
		
	/* Continentes seleccionados */
	if ( array_key_exists('continent',$data) ) {
		$continents = $data['continent'];		
		$continents = array_keys($continents);
		$continents = implode(',', $continents);
	} else {
		$continents = '';
	}
	

	/* Países seleccionados */
	if ( array_key_exists('country',$data) ) {
		$countries = $data['country'];		
		$countries = array_keys($countries);
		$countries = implode(',', $countries);
	} else {
		$countries = '';
	}

	$config = array('geoblockcountries' => $countries, 'geoblockcontinents' => $continents);
	
	/* Guardamos los datos en la BBDD */
	$model->saveConfig($config,'geoblock');
		
	/* Redirección al Cpanel */
	$this->setRedirect('index.php?option=com_securitycheckpro',JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
		
}

/* Guarda los cambios */
public function apply()
{
	$this->save('geoblock');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=geoblock&view=geoblock&'. JSession::getFormToken() .'=1',JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
}

/* Función para descargar la bbdd de Maxmind 2 */
public function update_geoblock_database()
{
	$model = $this->getModel('geoblock');
	$msg = $model->update_geoblock_database();

	$this->setRedirect('index.php?option=com_securitycheckpro&controller=geoblock&view=geoblock&'. JSession::getFormToken() .'=1',$msg);
}	
	
}