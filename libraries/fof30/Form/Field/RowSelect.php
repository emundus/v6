<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Field;

use FOF30\Form\Exception\DataModelRequired;
use FOF30\Form\Exception\GetInputNotAllowed;
use FOF30\Form\Exception\GetStaticNotAllowed;
use FOF30\Form\FieldInterface;
use FOF30\Form\Form;
use FOF30\Form\Header\RowSelect;
use FOF30\Model\DataModel;
use \JHtml;

defined('_JEXEC') or die;

/**
 * Form Field class for FOF
 * Alias to RowSelect (common typo)
 */
class SelectRow extends RowSelect
{
}
