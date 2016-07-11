<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Configuration Editor controller class
 *
 */
class AdmintoolsControllerSchedule extends F0FController
{
	public function add()
	{
		$this->layout = 'form';
		$this->display(false);
	}
}