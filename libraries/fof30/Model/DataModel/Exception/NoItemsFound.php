<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Model\DataModel\Exception;

use Exception;

defined('_JEXEC') or die;

class NoItemsFound extends BaseException
{
	public function __construct( $className, $code = 404, Exception $previous = null )
	{
		$message = \JText::sprintf('LIB_FOF_MODEL_ERR_NOITEMSFOUND', $className);

		parent::__construct( $message, $code, $previous );
	}

}