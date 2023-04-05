<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$d    = $displayData;
$from = $d->from;
$to   = $d->to;

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
<div class="fabrikDateListFilterRange" >
	<div class="row">
		<div class="col-2 text-end">
		<label for="<?php echo $from->id; ?>"><?php echo Text::_('COM_FABRIK_DATE_RANGE_BETWEEN') . ' '; ?>
		</label></div>
		<div class="w-auto"><?php echo $d->jCalFrom; ?></div>
	</div>
	<div class="row">
		<div class="col-2 text-end">
		<label for="<?php echo $to->id; ?>">	<?php echo Text::_('COM_FABRIK_DATE_RANGE_AND') . ' '; ?>
		</label></div>
		<div class="w-auto"><?php echo $d->jCalTo; ?></div>
	</div>
</div>
<?php
endif;