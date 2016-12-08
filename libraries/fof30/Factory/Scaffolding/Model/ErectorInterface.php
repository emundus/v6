<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Scaffolding\Model;

use FOF30\Model\DataModel;

interface ErectorInterface
{
	/**
	 * Construct the erector object
	 *
	 * @param   Builder     $parent         The parent builder
	 * @param   DataModel   $model          The model we're erecting a scaffold against
	 * @param   string      $viewName       The view name for this controller
	 */
	public function __construct(Builder $parent, DataModel $model, $viewName);

	/**
	 * Erects a scaffold. It then uses the parent's methods to assign the erected scaffold.
	 *
	 * @return  void
	 */
	public function build();

    /**
     * @return string
     */
    public function getSection();

    /**
     * @param string $section
     */
    public function setSection($section);
}