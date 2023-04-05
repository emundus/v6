<?php
/**
 * Tabs layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;

$thisName = $d->type === 'checkbox' ? FabrikString::rtrimword($d->name, '[]') . '[' . $d->i . ']' : $d->name;
$thisId = str_replace(array('[', ']'), array('_', '_'), $thisName);
$thisId = rtrim($thisId, '_');
$thisId .=  '_input_' . $d->i;

$labelSpan    = '<span>' . $d->label . '</span>';
$labelClass = $d->buttonGroup ? 'btn ' : 'form-check-label';
$inputClass = $d->buttonGroup ? 'btn-check' : 'form-check-input';


if (array_key_exists('input', $d->classes))
{
	$inputClass .= ' ' . implode(' ', $d->classes['input']);
}

$chx = '<input type="' . $d->type . '" class="fabrikinput ' . $inputClass . '" ' . $d->inputDataAttributes .
	' name="' . $thisName . '" id="' . $thisId . '" value="' . $d->value . '" ';

$sel = in_array($d->value, $d->selected);
$chx .= $sel ? ' checked="checked" />' : ' />';

if (array_key_exists('label', $d->classes))
{
	$labelClass .= ' ' . implode(' ', $d->classes['label']);
}
$label = '<label for="'.$thisId.'" class="'.$labelClass.' fabrikgrid_'.FabrikString::clean($d->value).'">'.$labelSpan.'</label>';
$html = $d->elementBeforeLabel == '1' ? $chx . $label : $label . $chx; 
?>

<?php echo $html; ?>
