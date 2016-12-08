<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Magic;

use FOF30\Dispatcher\Dispatcher;
use FOF30\Model\DataModel;
use FOF30\View\DataView\DataViewInterface;

defined('_JEXEC') or die;

/**
 * Creates a TransparentAuthentication object instance based on the information provided by the fof.xml configuration file
 */
class TransparentAuthenticationFactory extends BaseFactory
{
	/**
	 * Create a new object instance
	 *
	 * @param   array   $config    The config parameters which override the fof.xml information
	 *
	 * @return  Dispatcher  A new Dispatcher object
	 */
	public function make(array $config = array())
	{
		$appConfig = $this->container->appConfig;
		$defaultConfig = $appConfig->get('authentication.*');
		$config = array_merge($defaultConfig, $config);

		$className = $this->container->getNamespacePrefix($this->getSection()) . 'TransparentAuthentication\\DefaultTransparentAuthentication';

		if (!class_exists($className, true))
		{
			$className = '\\FOF30\\TransparentAuthentication\\TransparentAuthentication';
		}

		$dispatcher = new $className($this->container, $config);

		return $dispatcher;
	}
}