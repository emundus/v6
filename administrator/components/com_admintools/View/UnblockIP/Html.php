<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\UnblockIP;

defined('_JEXEC') or die;

use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	public function onBeforeMain()
	{
		$this->setLayout('default');
	}
}
