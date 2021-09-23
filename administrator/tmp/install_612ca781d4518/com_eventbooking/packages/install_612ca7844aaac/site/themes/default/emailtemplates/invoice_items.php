<?php
/**
 * @package			   Joomla
 * @subpackage		   Event Booking
 * @author			   Tuan Pham Ngoc
 * @copyright		   Copyright (C) 2010 - 2021 Ossolution Team
 * @license			   GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$cols = 3;

if ($config->show_event_date)
{
	$itemColumnWidth = '40%';
	$cols++;
}
else
{
	$itemColumnWidth = '60%';
}
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
    <thead>
    <tr>
        <th align="left" valign="top" width="10%">#</th>
        <th align="left" valign="top" width="<?php echo $itemColumnWidth; ?>"><?php echo Text::_('EB_ITEM_NAME'); ?></th>
		<?php
		if ($config->show_event_date)
		{
			?>
            <th align="left" valign="top" width="20%"><?php echo Text::_('EB_EVENT_DATE'); ?></th>
			<?php
		}
		?>
        <th align="right" valign="top" width="20%"><?php echo Text::_('EB_PRICE'); ?></th>
        <th align="right" valign="top" width="10%"><?php echo Text::_('EB_SUB_TOTAL'); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	$i = 1;
	foreach($rowEvents as $rowEvent)
	{
		?>
        <tr>
            <td>
				<?php echo $i++; ?>
            </td>
            <td>
				<?php echo $rowEvent->title; ?>
            </td>
			<?php
			if ($config->show_event_date)
			{
				?>
                <td align="left">
					<?php
					if ($rowEvent->event_date == EB_TBC_DATE)
					{
						echo Text::_('EB_TBC');
					}
					else
					{
						echo HTMLHelper::_('date', $rowEvent->event_date, $config->event_date_format, null);
					}
					?>
                </td>
				<?php
			}
			?>

            <td align="right">
				<?php echo EventbookingHelper::formatCurrency($rowEvent->total_amount, $config); ?>
            </td>
            <td align="right">
				<?php echo EventbookingHelper::formatCurrency($rowEvent->total_amount, $config); ?>
            </td>
        </tr>
		<?php
	}
	?>
    <tr>
        <td colspan="<?php echo $cols; ?>" align="right" valign="top" width="90%"><?php echo Text::_('EB_AMOUNT'); ?> :</td>
        <td align="right" valign="top" width="10%"><?php echo EventbookingHelper::formatCurrency($subTotal, $config);  ?></td>
    </tr>
    <tr>
        <td colspan="<?php echo $cols; ?>" align="right" valign="top" width="90%"><?php echo Text::_('EB_DISCOUNT_AMOUNT'); ?> :</td>
        <td align="right" valign="top" width="10%"><?php echo EventbookingHelper::formatCurrency($discountAmount, $config); ?></td>
    </tr>
    <tr>
        <td colspan="<?php echo $cols; ?>" align="right" valign="top" width="90%"><?php echo Text::_('EB_TAX');?> :</td>
        <td align="right" valign="top" width="10%"><?php echo EventbookingHelper::formatCurrency($taxAmount, $config); ?></td>
    </tr>
	<?php
	if ($paymentProcessingFee > 0)
	{
		?>
        <tr>
            <td colspan="<?php echo $cols; ?>" align="right" valign="top" width="90%"><?php echo Text::_('EB_PAYMENT_FEE');?> :</td>
            <td align="right" valign="top" width="10%"><?php echo EventbookingHelper::formatCurrency($paymentProcessingFee, $config); ?></td>
        </tr>
		<?php
	}
	?>
    <tr>
        <td colspan="<?php echo $cols; ?>" align="right" valign="top" width="90%"><?php echo Text::_('EB_GROSS_AMOUNT');?></td>
        <td align="right" valign="top" width="10%"><?php echo EventbookingHelper::formatCurrency($total, $config);?></td>
    </tr>
    </tbody>
</table>