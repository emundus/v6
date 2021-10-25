<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

/**
 * Class VirtuemartProduct
 * @package RegularLabs\Library\Condition
 */
class VirtuemartProduct extends Virtuemart
{
	public function pass()
	{
		// Because VM sucks, we have to get the view again
		$this->request->view = JFactory::getApplication()->input->getString('view');

		if ( ! $this->request->id || $this->request->option != 'com_virtuemart' || $this->request->view != 'productdetails')
		{
			return $this->_(false);
		}

		return $this->passSimple($this->request->id);
	}
}
