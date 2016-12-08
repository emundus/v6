<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Toolbar\Exception;

use Exception;

defined('_JEXEC') or die;

class UnknownButtonType extends \InvalidArgumentException
{
	public function __construct($buttonType, $code = 500, Exception $previous = null)
	{
		$message = \JText::sprintf('LIB_FOF_TOOLBAR_ERR_UNKNOWNBUTTONTYPE', $buttonType);

		parent::__construct($message, $code, $previous);
	}
}