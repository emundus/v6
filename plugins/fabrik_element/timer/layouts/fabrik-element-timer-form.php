<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d = $displayData;
$readOnly = $d->timerReadOnly ? 'readonly=\"readonly\"' : '';
$kls = $d->timerReadOnly ? 'readonly' : '';

if ($d->elementError != '') :
	$kls .= ' elementErrorHighlight';
endif;
?>
	<table>
		<tbody>
			<tr>
				<td>
					<input type="<?php echo $d->type;?>" class="form-control fabrikinput inputbox text <?php echo $kls;?>" name="<?php echo $d->name; ?>" <?php echo $readOnly;?>
						id="<?php echo $d->id; ?>" size="<?php echo $d->size; ?>" value="<?php echo $d->value; ?>" />
				</td>
				<?php if (!$d->timerReadOnly) :	?>
				<td>
					<button class="btn" id="<?php echo $d->id; ?>_button"> <?php echo FabrikHelperHTML::icon($d->icon); ?> <span></span> </button>
				</td>
				<?php endif; ?>
			</tr>
		</tbody>
	</table>
