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

/**
 * Layout variables
 * -----------------
 * @var   EventbookingTableEvent $item
 * @var   RADConfig              $config
 * @var   boolean                $showLocation
 * @var   stdClass               $location
 * @var   boolean                $isMultipleDate
 * @var   string                 $nullDate
 * @var   int                    $Itemid
 */
 
 if (isset($item->original_event))
{
	$item = clone $item->original_event;
}
?>
<table class="eb-calendar-event-tooltip table table-bordered">
	<tbody>
	<tr>
		<td style="width: 30%;">
			<strong><?php echo Text::_('EB_EVENT') ?></strong>
		</td>
		<td>
			<?php echo $item->title; ?>
		</td>
	</tr>
		<tr>
			<td style="width: 30%;">
				<strong><?php echo Text::_('EB_EVENT_DATE') ?></strong>
			</td>
			<td>
				<?php
				if ($item->event_date == EB_TBC_DATE)
				{
					echo Text::_('EB_TBC');
				}
				else
				{
				?>
					<?php
					if (strpos($item->event_date, '00:00:00') !== false)
					{
						$dateFormat = $config->date_format;
					}
					else
					{
						$dateFormat = $config->event_date_format;
					}

					echo HTMLHelper::_('date', $item->event_date, $dateFormat, null);
				}
				?>
			</td>
		</tr>

		<?php
		if ($item->event_end_date != $nullDate)
		{
			if (strpos($item->event_end_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
				<tr>
					<td>
						<strong><?php echo Text::_('EB_EVENT_END_DATE'); ?></strong>
					</td>
					<td>
						<?php echo HTMLHelper::_('date', $item->event_end_date, $dateFormat, null); ?>
					</td>
				</tr>
			<?php
		}

		if ($item->registration_start_date != $nullDate)
		{
			if (strpos($item->registration_start_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
				<tr>
					<td>
						<strong><?php echo Text::_('EB_REGISTRATION_START_DATE'); ?></strong>
					</td>
					<td>
						<?php echo HTMLHelper::_('date', $item->registration_start_date, $dateFormat, null); ?>
					</td>
				</tr>
			<?php
		}

		if ($config->show_capacity == 1 || ($config->show_capacity == 2 && $item->event_capacity))
		{
		?>
			<tr>
				<td>
					<strong><?php echo Text::_('EB_CAPACITY'); ?></strong>
				</td>
				<td>
					<?php
					if ($item->event_capacity)
					{
						echo $item->event_capacity;
					}
					else
					{
						echo Text::_('EB_UNLIMITED');
					}
					?>
				</td>
			</tr>
		<?php
		}

		if ($config->show_registered && $item->registration_type != 3)
		{
		?>
			<tr>
				<td>
					<strong><?php echo Text::_('EB_REGISTERED'); ?></strong>
				</td>
				<td>
					<?php
						echo (int) $item->total_registrants;
					?>
				</td>
			</tr>
		<?php
		}

		if ($config->show_available_place && $item->event_capacity)
		{
		?>
			<tr>
				<td>
					<strong><?php echo Text::_('EB_AVAILABLE_PLACE'); ?></strong>
				</td>
				<td>
					<?php echo $item->event_capacity - $item->total_registrants; ?>
				</td>
			</tr>
		<?php
		}

		if ($nullDate != $item->cut_off_date)
		{
			if (strpos($item->cut_off_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
			<tr>
				<td>
					<strong><?php echo Text::_('EB_CUT_OFF_DATE'); ?></strong>
				</td>
				<td>
					<?php echo HTMLHelper::_('date', $item->cut_off_date, $dateFormat, null); ?>
				</td>
			</tr>
			<?php
		}

		if ($item->individual_price > 0 || ($config->show_price_for_free_event))
		{
			$showPrice = true;
		}
		else
		{
			$showPrice = false;
		}

		if ($config->show_discounted_price && ($item->individual_price != $item->discounted_price))
		{
			if ($showPrice)
			{
			?>
				<tr>
					<td>
						<strong><?php echo Text::_('EB_ORIGINAL_PRICE'); ?></strong>
					</td>
					<td class="eb_price">
						<?php
						if ($item->individual_price > 0)
						{
							echo EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
						}
						else
						{
							echo '<span class="eb_free">' . Text::_('EB_FREE') . '</span>';
						}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo Text::_('EB_DISCOUNTED_PRICE'); ?></strong>
					</td>
					<td class="eb_price">
						<?php
						if ($item->discounted_price > 0)
						{
							echo EventbookingHelper::formatCurrency($item->discounted_price, $config, $item->currency_symbol);

							if ($item->early_bird_discount_amount > 0 && $item->early_bird_discount_date != $nullDate)
							{
								echo '<em>' . Text::sprintf('EB_UNTIL_DATE', HTMLHelper::_('date', $item->early_bird_discount_date, $config->date_format, null)) . '</em>';
							}
						}
						else
						{
							echo '<span class="eb_free">' . Text::_('EB_FREE') . '</span>';
						}
						?>
					</td>
				</tr>
			<?php
			}
		}
		else
		{
			if ($showPrice)
			{
			?>
				<tr>
					<td>
						<strong><?php echo Text::_('EB_INDIVIDUAL_PRICE'); ?></strong>
					</td>
					<td class="eb_price">
						<?php
						if ($item->price_text)
						{
							echo $item->price_text;
						}
						elseif ($item->individual_price > 0)
						{
							echo EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
						}
						else
						{
							echo '<span class="eb_free">' . Text::_('EB_FREE') . '</span>';
						}
						?>
					</td>
				</tr>
			<?php
			}
		}

		if ($item->fixed_group_price > 0)
		{
		?>
			<tr>
				<td>
					<strong><?php echo Text::_('EB_FIXED_GROUP_PRICE'); ?></strong>
				</td>
				<td class="eb_price">
					<?php
					echo EventbookingHelper::formatCurrency($item->fixed_group_price, $config, $item->currency_symbol);
					?>
				</td>
			</tr>
		<?php
		}

		if ($item->late_fee > 0)
		{
		?>
			<tr class="eb-event-property">
				<td class="eb-event-property-label">
					<?php echo Text::_('EB_LATE_FEE'); ?>
				</td>
				<td class="eb-event-property-value">
					<?php
					echo EventbookingHelper::formatCurrency($item->late_fee, $config, $item->currency_symbol);
					echo '<em>' . Text::sprintf('EB_FROM_DATE', HTMLHelper::_('date', $item->late_fee_date, $config->date_format.' H:i', null)) . '</em>';
					?>
				</td>
			</tr>
		<?php
		}

		if (isset($item->paramData))
		{
			echo EventbookingHelperHtml::loadCommonLayout('common/event_fields.php', ['item' => $item]);
		}

		if ($item->location_name)
        {
        ?>
            <tr>
                <td>
                    <strong><?php echo Text::_('EB_LOCATION'); ?></strong>
                </td>
                <td>
                    <?php echo $item->location_name; ?>
                </td>
            </tr>
        <?php
        }
	?>
	</tbody>
</table>