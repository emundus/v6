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
 * Class MijoshopProduct
 * @package RegularLabs\Library\Condition
 */
class MijoshopProduct extends Mijoshop
{
	public function pass()
	{
		if ( ! $this->request->id || $this->request->option != 'com_mijoshop' || $this->request->view != 'product')
		{
			return $this->_(false);
		}

		return $this->passSimple($this->request->id);
	}
}
