<?php
defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

$d    = $displayData;
$from = $d->from;

$calOpts = ArrayHelper::toString($d->calOpts);

	$from->img = '<button id ="' . $from->id . '_cal_img" class="btn calendarbutton">' . $from->img . '</button>';

$prepend = '<div class="input-append">';
$append  = '</div>';
?>
<div class="fabrik_filter_container">
	<?php echo $prepend; ?>
	<input type="text" name="<?php echo $from->name; ?>" id="<?php echo $from->id; ?>"
		value="<?php echo $from->value; ?>"<?php echo $calOpts; ?> />
	<?php echo $from->img; ?>
	<?php echo $append; ?>
	<br />
</div>

