<?php
/**
 * Layoutfile for Icon rendering
 */

defined('JPATH_BASE') or die;

$d = $displayData;
$props = isset($d->properties) ? $d->properties : '';

/**
 * Handle cases where additional classes are in the $d->icon string, like the calendar
 * uses "icon-clock timeButton".  Also handle multiple icon-foo, like "icon-spinner icon-spin"
 */

$iconParts = explode(' ', trim($d->icon));
$spareParts = array();

foreach ($iconParts as $key => $part) {
	if (!strstr($part, 'icon-')) {
		unset($iconParts[$key]);
		$spareParts[] = $part;
	}
	else if (empty($part)) {
		unset($iconParts[$key]);
	}
}

/**
 * Now test for any icon-xy names that you want to change
 * In J!4 joomla-fontawesome.css the following icon-xy are defined, but slightly different from the now used fa-xyz
 */

foreach ($iconParts as $key => $part)
{

	$test = str_replace('icon-', '', trim($part));

	switch ($test) {
		case 'question-sign':
			$iconParts[$key] = 'fa-question-circle';
			break;
		case 'next':
 			$iconParts[$key] = 'fa-angle-right';
 			break;
 		case 'previous':
 			$iconParts[$key] = 'fa-angle-left';
 			break;
		default :
			$iconParts[$key] = $part;
			break;
	}
}

$d->icon = implode(' ', $iconParts);

/*
 * Some code just needs the icon name itself (eg. passing to JS code so it knows what icon class to add/remove,
 * like in the rating element.
 */
if (isset($d->nameOnly) && $d->nameOnly)
{
	echo $d->icon;
	return;
}

/**
 * Add any additional non-icon classes back
 */

if (!empty($spareParts))
{
	$d->icon .= ' ' . implode(' ', $spareParts);
}

?>

<span data-isicon="true" class="fa <?php echo $d->icon;?>" <?php echo $props;?>></span>
