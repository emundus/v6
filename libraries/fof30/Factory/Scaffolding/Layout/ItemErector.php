<?php
/**
 * @package     FOF
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Scaffolding\Layout;

use FOF30\Inflector\Inflector;
use FOF30\Model\DataModel;

/**
 * Erects a scaffolding XML for read views
 *
 * @package FOF30\Factory\Scaffolding
 *
 * @deprecated 3.1  Support for XML forms will be removed in FOF 4
 */
class ItemErector extends FormErector implements ErectorInterface
{
	public function build()
	{
		$this->addDescriptions = false;

		parent::build();

		$this->xml->addAttribute('type', 'read');

		$this->pushResults();
	}
}
