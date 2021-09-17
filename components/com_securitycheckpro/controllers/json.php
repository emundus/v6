<?php
/**
 * @author Jose A. Luque
 * @copyright Copyright (c) 2013 - Jose A. Luque
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;

class SecuritycheckprosControllerJson extends SecuritycheckproController
{
	// Definimos las variables
	protected $input = array();

	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;
		parent::__construct($config);
	}
	public function execute($task)
	{
		$task = 'json';
				
		parent::execute($task);
	}

	/**
	 * Manejamos las llamadas al API
	 * @return  void
	 */
	public function json()
	{
		$referrer = null;
		
		// String json de la petición
		$clientJSON = $this->input->get('json', null, 'raw', 2);
						
		// Decodificamos el string para añadir el referrer, que será usado en caso de fallo (por ejemplo cuando las claves secretas no coinciden)
		$request = json_decode($clientJSON, true);
		if (array_key_exists('HTTP_REFERER', $_SERVER)) {
			$referrer = $_SERVER['HTTP_REFERER'];
		}		
		
		if ( (!is_null($request)) && (is_array($request)) && (!is_null($referrer)) )
		{
			$request['referrer'] = $referrer;
		}
		
		// Volvemos a codificar el string en formato json
		$clientJSON = json_encode($request);
						
		$model = $this->getModel('json');
		$json = $model->register_task($clientJSON);
		
		// Devolvemos la respuesta
		echo $json;		
	}
}
