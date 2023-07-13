<?php
/**
 * Default list element render
 * Override this file in plugins/fabrik_element/{plugin}/layouts/fabrik-element-{plugin}-list.php
 */

defined('JPATH_BASE') or die;

$d = $displayData;
/*
 * Some code just needs the icon name itself (eg. passing to JS code so it knows what icon class to add/remove,
 * like in the rating element.
 */
if (isset($d->nameOnly) && $d->nameOnly)
{
	echo $d->icon;
	return;
}

$props = isset($d->properties) ? $d->properties : '';

$icon = str_replace('icon-', '', $d->icon);

$icon = explode(' ',$icon);
if($icon[0] == 'star')
{
	$icon[0] = 'emergency';
}
?>
<?php if($icon[0] == 'emergency'): ?>
    <span class="material-icons text-xxs text-red-500 mr-0" style="top: -5px;position: relative">
        <?php echo str_replace('icon-','',$icon[0]);?>
    </span>
<?php else : ?>
    <i data-isicon="true" class="<?php echo $d->icon;?>" <?php echo $props;?>></i>
<?php endif; ?>