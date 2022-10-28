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

$showPriceColumn = EventbookingHelperRegistration::showPriceColumnForTicketType($event->id);
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<h3 class="eb-event-tickets-heading"><?php echo Text::_('EB_TICKET_INFORMATION'); ?></h3>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed eb-ticket-information">
	<thead>
		<tr>
			<th>
				<?php echo Text::_('EB_TICKET_TYPE'); ?>
			</th>
            <?php
                if ($showPriceColumn)
                {
                ?>
                    <th class="eb-text-right">
		                <?php echo Text::_('EB_PRICE'); ?>
                    </th>
                <?php
                }

                if ($config->show_available_place)
                {
                ?>
                    <th class="center">
                        <?php echo Text::_('EB_AVAILABLE_PLACE'); ?>
                    </th>
                <?php
                }
			?>
		</tr>
	</thead>
	<tbody>
	<?php
	$currencySymbol = empty($event->currency_symbol) ? null : $event->currency_symbol;

	foreach ($ticketTypes as $ticketType)
	{
	?>
	<tr>
		<td class="eb-ticket-type-title">
			<?php
				echo Text::_($ticketType->title);

				if ($ticketType->description)
				{
				?>
					<p class="eb-ticket-type-description"><?php echo Text::_($ticketType->description); ?></p>
				<?php
				}
			?>
		</td>

		<?php
        if ($showPriceColumn)
        {
        ?>
            <td class="eb-text-right">
		        <?php echo EventbookingHelper::formatCurrency($ticketType->price, $config, $currencySymbol); ?>
            </td>
        <?php
        }

        if ($config->show_available_place)
		{
			if ($ticketType->capacity)
			{
				$available = max($ticketType->capacity - $ticketType->registered, 0);
			}
            elseif ($event->event_capacity > 0)
			{
				$available = max($event->event_capacity - $event->total_registrants, 0);
			}
			else
			{
				$available = Text::_('EB_UNLIMITED');
			}
		?>
			<td class="center">
				<?php echo $available; ?>
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
