<?php
/**
 * Form input group
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       4.0
 */
defined('JPATH_BASE') or die;
$d = $displayData;

$class = explode(' ', $d->class);

if (in_array('error', $class))
{
    $class[] = 'has-error';

}
?>
<div class="i<?php echo implode(' ', $class);?> <?php echo $d->span;?>" <?php echo $d->style;?>>
<?php echo $d->row;?>
</div>
