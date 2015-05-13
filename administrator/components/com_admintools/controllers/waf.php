<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerWaf extends F0FController
{
	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}
}
