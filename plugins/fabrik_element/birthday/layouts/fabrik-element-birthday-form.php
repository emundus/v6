<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$d = $displayData;
?>

<div class="fabrikSubElementContainer" id="<?php echo $d->id;?>">
	<table>
		<tbody>
			<tr>
				<td>
					<?php echo HTMLHelper::_('select.genericlist', $d->day_options, $d->day_name, $d->attribs, 'value', 'text', $d->day_value, $d->day_id);?>
				</td>
				<td> <?php echo "&nbsp;".$d->separator."&nbsp;";?></td>
				<td>
					<?php echo HTMLHelper::_('select.genericlist', $d->month_options, $d->month_name, $d->attribs, 'value', 'text', $d->month_value, $d->month_id); ?>
				</td>
				<td> <?php echo "&nbsp;".$d->separator."&nbsp;";?></td>
				<td>
			<?php echo HTMLHelper::_('select.genericlist', $d->year_options, $d->year_name, $d->attribs, 'value', 'text', $d->year_value, $d->year_id); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
