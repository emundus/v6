<?php
/**
 * @package     FOF
 * @copyright   2010-2017 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Hal\Render;

defined('_JEXEC') or die;

/**
 * Interface for HAL document renderers
 *
 * @see http://stateless.co/hal_specification.html
 *
 * @codeCoverageIgnore
 */
interface RenderInterface
{
	/**
	 * Render a HAL document into a representation suitable for consumption.
	 *
	 * @param   array  $options  Renderer-specific options
	 *
	 * @return  string
	 */
	public function render($options = array());
}
