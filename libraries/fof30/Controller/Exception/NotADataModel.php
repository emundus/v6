<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Controller\Exception;

defined('_JEXEC') or die;

/**
 * Exception thrown when the provided Model is not a DataModel
 */
class NotADataModel extends \InvalidArgumentException {}