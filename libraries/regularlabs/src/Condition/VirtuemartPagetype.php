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

use Joomla\CMS\Factory as JFactory;

/**
 * Class VirtuemartPagetype
 * @package RegularLabs\Library\Condition
 */
class VirtuemartPagetype extends Virtuemart
{
	public function pass()
	{
		// Because VM sucks, we have to get the view again
		$this->request->view = JFactory::getApplication()->input->getString('view');

		return $this->passByPageType('com_virtuemart', $this->selection, $this->include_type, true);
	}
}
