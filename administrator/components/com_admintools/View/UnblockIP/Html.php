<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\UnblockIP;

defined('_JEXEC') || die;

use FOF40\View\DataView\Html as BaseView;

class Html extends BaseView
{
	public function onBeforeMain()
	{
		$this->setLayout('default');
	}
}
