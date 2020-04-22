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

		if (function_exists('ob_start'))
		{
			@ob_start();
		}

		// String json de la petición
		$clientJSON = $this->input->get('json', null, 'raw', 2);

		// Elininamos posibles barras añadidas si magic_quotes_gpc está habilitado
		if (function_exists('get_magic_quotes_gpc'))
		{
			if (PHP_VERSION_ID < 50400 && get_magic_quotes_gpc())			
			{
				$clientJSON = stripslashes($clientJSON);
			}
		}

		// Parseamos el mensaje utilizando el modelo
		$model = $this->getModel('json');
		$json = $model->execute($clientJSON);

		if (function_exists('ob_clean'))
		{
			@ob_clean();
		}

		// Devolvemos ela respuesta y paramos la aplicación
		echo $json;
		$app = JFactory::getApplication();
		$app->close();
	}
}
