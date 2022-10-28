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
use RegularLabs\Library\Condition;

/**
 * Class Hikashop
 * @package RegularLabs\Library\Condition
 */
abstract class Hikashop extends Condition
{
	public function beforePass()
	{
		$input = JFactory::getApplication()->input;

		// Reset $this->request because HikaShop messes with the view after stuff is loaded!
		$this->request->option = $input->get('option', $this->request->option);
		$this->request->view   = $input->get('view', $input->get('ctrl', $this->request->view));
		$this->request->id     = $input->getInt('id', $this->request->id);
		$this->request->Itemid = $input->getInt('Itemid', $this->request->Itemid);
	}
}
