<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\View\Engine;

defined('_JEXEC') or die;

/**
 * View engine for plain PHP template files (no translation).
 */
class PhpEngine extends AbstractEngine implements EngineInterface
{
	/**
	 * Get the evaluated contents of the view template.
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content evaluation information
	 */
	public function get($path, array $forceParams = array())
	{
		return array(
			'type'    => 'path',
			'content' => $path
		);
	}
}