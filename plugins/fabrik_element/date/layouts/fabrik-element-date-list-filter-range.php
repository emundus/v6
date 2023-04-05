<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$d    = $displayData;
$from = $d->from;
$to   = $d->to;

$calOpts = ArrayHelper::toString($d->calOpts);

$from->img = '<a href="#" id ="' . $from->id . '_cal_img" class="calendarbutton btn btn-primary btn-sm">' . $from->img . '<span class="visually-hidden">' . Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR') . '</span></a>';
$to->img   = '<a href="#" id ="' . $to->id . '_cal_img" class="calendarbutton btn btn-primary btn-sm">' . $to->img . '<span class="visually-hidden">' . Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR') . '</span></a>';

if ($d->filterType === 'range-hidden') :
	?>
	<input type="hidden" name="<?php echo $from->name; ?>"
		class="<?php echo $d->class; ?>"
		value="<?php echo $from->value; ?>"
		id="<?php echo $d->htmlId; ?>-0" />

	<input type="hidden" name="<?php echo $to->name; ?>"
		class="<?php echo $d->class; ?>"
		value="<?php echo $to->value; ?>"
		id="<?php echo $d->htmlId; ?>-1" />
<?php
else :
	?>
	<div class="fabrikDateListFilterRange row">
		<div class="col-sm-6">
			<label for="<?php echo $from->id; ?>"><?php echo Text::_('COM_FABRIK_DATE_RANGE_BETWEEN') . ' '; ?></label>
			<div class="input-group">
				<input type="text" name="<?php echo $from->name; ?>" id="<?php echo $from->id; ?>"
					value="<?php echo $from->value; ?>"<?php echo $calOpts; ?> />
			<span class="input-group-addon">
				<?php echo $from->img; ?>
			</span>
			</div>
		</div>
		<div class="col-sm-6">
			<label for="<?php echo $to->id; ?>"><?php echo Text ::_('COM_FABRIK_DATE_RANGE_AND') . ' '; ?></label>
			<div class="input-group">
				<input type="text" name="<?php echo $to->name; ?>" id="<?php echo $to->id; ?>"
					value="<?php echo $to->value; ?>"<?php echo $calOpts; ?> />
			<span class="input-group-addon">
				<?php echo $to->img; ?>
			</span>
			</div>
		</div>
	</div>
<?php
endif;