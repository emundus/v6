<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class ZooItem
 * @package RegularLabs\Library\Condition
 */
class ZooItem extends Zoo
{
	public function pass()
	{
		if ( ! $this->request->id || $this->request->option != 'com_zoo')
		{
			return $this->_(false);
		}

		if ($this->request->view != 'item')
		{
			return $this->_(false);
		}

		$pass = false;

		// Pass Article Id
		if ( ! $this->passItemByType($pass, 'ContentId'))
		{
			return $this->_(false);
		}

		// Pass Author
		if ( ! $this->passItemByType($pass, 'Author'))
		{
			return $this->_(false);
		}

		return $this->_($pass);
	}
}
