<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

$btn = $bootstrapHelper->getClassMapping('btn');
?>
<table  id="price_list">
	<tr>
		<th width="30%">
			<?php echo Text::_('EB_REGISTRANT_NUMBER'); ?>
		</th>
		<th>
			<?php echo Text::_('EB_RATE'); ?>
		</th>
	</tr>
	<?php
	$n = max(count($this->prices), 3);

	for ($i = 0 ; $i < $n ; $i++)
	{
		if (isset($this->prices[$i]))
		{
			$price            = $this->prices[$i];
			$registrantNumber = $price->registrant_number;
			$price            = $price->price;
		}
		else
		{
			$registrantNumber = null;
			$price            = null;
		}
		?>
		<tr>
			<td>
				<input type="text" class="input-small form-control" name="registrant_number[]" size="10" value="<?php echo $registrantNumber; ?>" />
			</td>
			<td>
				<input type="text" class="input-small form-control" name="price[]" size="10" value="<?php echo $price; ?>" />
			</td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td colspan="3">
			<input type="button" class="<?php echo $btn; ?> button" value="<?php echo Text::_('EB_ADD'); ?>" onclick="addRow();" />
			&nbsp;
			<input type="button" class="<?php echo $btn; ?> button" value="<?php echo Text::_('EB_REMOVE'); ?>" onclick="removeRow();" />
		</td>
	</tr>
</table>