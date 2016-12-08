<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Scaffolding\Layout;

use FOF30\Inflector\Inflector;
use FOF30\Model\DataModel;

/**
 * Erects a scaffolding XML for read views
 *
 * @package FOF30\Factory\Scaffolding
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