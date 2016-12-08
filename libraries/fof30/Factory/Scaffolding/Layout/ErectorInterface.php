<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Scaffolding\Layout;

use FOF30\Model\DataModel;

interface ErectorInterface
{
	/**
	 * Construct the erector object
	 *
	 * @param   \FOF30\Factory\Scaffolding\Layout\Builder  $parent    The parent builder
	 * @param   \FOF30\Model\DataModel              $model     The model we're erecting a scaffold against
	 * @param   string                              $viewName  The view name for this model
	 */
	public function __construct(Builder $parent, DataModel $model, $viewName);

	/**
	 * Erects a scaffold. It then uses the parent's setXml and setStrings to assign the erected scaffold and the
	 * additional language strings to the parent which will decide what to do with that.
	 *
	 * @return  void
	 */
	public function build();
}