<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d         = $displayData;
$multisize = $d->multisize === '' ? '' : 'size="' . $d->multisize . '"';
?>

<select name="<?php echo $d->name ?>" id="<?php echo $d->id ?>" <?php $d->multiple ? 'multiple' : ''; ?>
	<?php echo $multisize; ?> <?php echo $d->attribs; ?>>
	<?php foreach ($d->options as $opt) :
		$disabled = $opt->disable === true ? ' disabled' : ''; 
		$selected = in_array($opt->value, $d->selected) ? ' selected' : ''; ?>
		<option value="<?php echo $opt->value;?>" <?php  echo $disabled; ?><?php echo $selected; ?>><?php echo Text::_($opt->text); ?></option>
	<?php endforeach; ?>
</select>
