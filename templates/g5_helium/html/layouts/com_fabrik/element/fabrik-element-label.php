<?php
defined('JPATH_BASE') or die;

$eMConfig = JComponentHelper::getParams('com_emundus');
$display_required_icon = $eMConfig->get('display_required_icon', 1);
$required_position_icon = $eMConfig->get('required_icon_position', 1);

$d = $displayData;

$labelText = FText::_($d->label);
$labelText = $labelText == '' ? $d->hasLabel = false : $labelText;
$l = $d->j3 ? '' : $labelText;

if($required_position_icon == 0 && $display_required_icon == 1 && !empty($labelText))
{
	$l .= $d->icons;
}

$l .= $d->j3 ? $labelText : '';

if($required_position_icon == 1 && $display_required_icon == 1 && !empty($labelText))
{
	$l .= $d->icons;
}

if($display_required_icon == 0 && $d->tipOpts->heading != 'Validation' && !empty($labelText)) {
    $l .= '<small class="ml-1 em-text-neutral-600">'.JText::_('COM_FABRIK_OPTIONNAL_FIELD').'</small>';
}

if ($d->view == 'form' && !($d->canUse || $d->canView))
{
	return '';
}

if ($d->view == 'details' && !$d->canView)
{
	return '';
}

if ($d->canView || $d->canUse)
{
	if ($d->hasLabel && !$d->hidden)
	{
		?>

		<label for="<?php echo $d->id; ?>" class="<?php echo $d->labelClass; ?>" <?php echo $d->tip;?>>
<?php
	}
	?>
	<?php echo $l;?>
<?php
	if ($d->hasLabel && !$d->hidden)
	{
	?>
		</label>
	<?php
	}
	elseif (!$d->hasLabel && !$d->hidden)
	{
	?>
		</span>
	<?php
	}
}
