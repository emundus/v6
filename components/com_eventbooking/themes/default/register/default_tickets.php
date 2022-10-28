<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$showPriceColumn = EventbookingHelperRegistration::showPriceColumnForTicketType($this->event->id);
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<h3 class="eb-heading"><?php echo Text::_('EB_TICKET_INFORMATION'); ?></h3>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed">
	<thead>
	<tr>
		<th>
			<?php echo Text::_('EB_TICKET_TYPE'); ?>
		</th>
        <?php
        if ($showPriceColumn)
        {
        ?>
            <th>
                <?php echo Text::_('EB_PRICE'); ?>
            </th>
        <?php
        }

        if ($this->config->show_available_place)
		{
		?>
			<th class="center">
				<?php echo Text::_('EB_AVAILABLE_PLACE'); ?>
			</th>
		<?php
		}
		?>
		<th class="center">
			<?php echo Text::_('EB_QUANTITY'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->ticketTypes as $ticketType)
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
                <td>
		            <?php echo EventbookingHelper::formatCurrency($ticketType->price, $this->config, $this->event->currency_symbol); ?>
                </td>
            <?php
            }

			$isUnlimited = false;

			if ($ticketType->capacity)
			{
				$available = max($ticketType->capacity - $ticketType->registered, 0);
			}
            elseif ($this->event->event_capacity > 0)
			{
				$available = max($this->event->event_capacity - $this->event->total_registrants, 0);
			}
			else
			{
				$available   = Text::_('EB_UNLIMITED');
				$isUnlimited = true;
			}

			if ($this->config->show_available_place)
			{
			?>
				<td class="center">
					<?php echo $available; ?>
				</td>
			<?php
			}
			?>
			<td class="center">
				<?php
                    $fieldName = 'ticket_type_' . $ticketType->id;

                    if ($this->waitingList || $isUnlimited)
                    {
                        if ($isUnlimited && $ticketType->max_tickets_per_booking)
                        {
                            $available = $ticketType->max_tickets_per_booking;
                        }
                        else
                        {
                            $available = 10;
                        }
                    }

                    if ($available > 0)
                    {
	                    if ($ticketType->max_tickets_per_booking > 0 && ($ticketType->max_tickets_per_booking < $available))
	                    {
		                    $available = $ticketType->max_tickets_per_booking;
	                    }

                        $fieldName = 'ticket_type_' . $ticketType->id;

                        echo HTMLHelper::_('select.integerlist', 0, $available, 1, $fieldName, 'class="ticket_type_quantity input-small" onchange="calculateIndividualRegistrationFee(1);"', $this->input->getInt($fieldName, 0));
                    }
                    else
                    {
                        echo Text::_('EB_NA');
                    }
				?>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
