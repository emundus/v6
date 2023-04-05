<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$d = $displayData;
$attribs = 'class="form-select fabrikinput inputbox ' . $d->advancedClass . ' ' . $d->errorCss . '" ';
?>

<div class="fabrikSubElementContainer" id="<?php echo $d->id; ?>">
	<table>
		<tbody>
			<tr>
				<td>
					<?php if ($d->format != 'i:s') { echo  HTMLHelper::_('select.genericlist', $d->hours, preg_replace('#(\[\])$#', '[0]', $d->name), $attribs, 'value', 'text', $d->hourValue); }?>
				</td>
				<td> <?php echo "&nbsp;".$d->sep."&nbsp;";?></td>
				<td>
					<?php echo HTMLHelper::_('select.genericlist', $d->mins, preg_replace('#(\[\])$#', '[1]', $d->name), $attribs, 'value', 'text', $d->minValue); ?>
				</td>
				<td> <?php echo "&nbsp;".$d->sep."&nbsp;";?></td>
				<td>
					<?php	if ($d->format != 'H:i') {	echo HTMLHelper::_('select.genericlist', $d->secs, preg_replace('#(\[\])$#', '[2]', $d->name), $attribs, 'value', 'text', $d->secValue); }?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
