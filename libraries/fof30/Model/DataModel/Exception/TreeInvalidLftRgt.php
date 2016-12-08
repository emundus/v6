<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Model\DataModel\Exception;

use Exception;

defined('_JEXEC') or die;

abstract class TreeInvalidLftRgt extends \RuntimeException
{
	public function __construct( $message = '', $code = 500, Exception $previous = null )
	{
		parent::__construct( $message, $code, $previous );
	}

}