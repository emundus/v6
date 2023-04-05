<?php
/**
 * Form element grid row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 */
defined('JPATH_BASE') or die;
$d = $displayData;

$class = explode(' ', $d->class);

if (in_array('error', $class))
{
	$class[] = 'has-error';

}
$class[] = 'form-group';
if ($d->column) {
    if ($d->startRow) {
        echo "<div class='row'>";
    }
    $class[] = $d->column;
}
?>
<div class="<?php echo implode(' ', $class);?>" > 
<?php echo $d->row;?>
</div>
<?php
if ($d->column && $d->endRow) echo "</div>";
