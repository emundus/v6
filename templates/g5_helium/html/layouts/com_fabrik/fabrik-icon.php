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

$d->icon = str_replace('icon-', '', $d->icon);

$icons = explode(' ',$d->icon);
if($icons[0] == 'star')
{
    $icons[0] = 'emergency';
}
?>
<?php if($icons[0] != 'question-sign'): ?>
    <span class="material-icons text-xxs icon-order text-red-500"><?php echo str_replace('icon-','',$icons[0]);?></span>
<?php endif; ?>