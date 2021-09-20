<?php

use Joomla\CMS\Language\Text;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$showPriceColumn = EventbookingHelperRegistration::showPriceColumnForTicketType($eventId);
?>
<table class="table table-striped table-bordered table-condensed">
    <thead>
    <tr>
        <th>
			<?php echo Text::_('EB_TICKET_TYPE'); ?>
        </th>
		<?php
		if ($showPriceColumn)
		{
		?>
            <th class="text-right">
				<?php echo Text::_('EB_PRICE'); ?>
            </th>
		<?php
		}
		?>
        <th class="text-center">
			<?php echo Text::_('EB_QUANTITY'); ?>
        </th>
		<?php
		if ($showPriceColumn)
		{
		?>
            <th class="text-right">
				<?php echo Text::_('EB_SUB_TOTAL'); ?>
            </th>
		<?php
		}
		?>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ($ticketTypes as $ticketType)
	{
		?>
        <tr>
            <td>
				<?php echo Text::_($ticketType->title); ?>
            </td>
			<?php
			if ($showPriceColumn)
			{
			?>
                <td class="text-right">
					<?php echo EventbookingHelper::formatCurrency($ticketType->price, $config); ?>
                </td>
			<?php
			}
			?>
            <td class="text-center">
				<?php echo $ticketType->quantity; ?>
            </td>
			<?php
			if ($showPriceColumn)
			{
			?>
                <td class="text-right">
					<?php echo EventbookingHelper::formatCurrency($ticketType->price * $ticketType->quantity, $config); ?>
                </td>
			<?php
			}
			?>
        </tr>
		<?php
	}
	?>
    </tbody>
</table>


