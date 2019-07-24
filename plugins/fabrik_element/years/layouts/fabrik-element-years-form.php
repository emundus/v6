<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<div class="fabrikSubElementContainer" id="<?php echo $d->id;?>">
	<?php echo JHTML::_('select.genericlist', $d->year_options, $d->year_name, $d->attribs, 'value', 'text', $d->year_value, $d->year_id); ?>
</div>
