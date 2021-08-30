<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$cols = 4;
$return = base64_encode(Uri::getInstance()->toString());
HTMLHelper::_('formbehavior.chosen', 'select');

if (in_array('last_name', EventbookingHelper::getPublishedCoreFields()))
{
    $showLastName = true;
}
else
{
    $showLastName = false;
}

$isJoomla4        = EventbookingHelper::isJoomla4();
$bootstrapHelper  = EventbookingHelperBootstrap::getInstance();
$btnPrimary       = $bootstrapHelper->getClassMapping('btn btn-primary');
$btnDanger        = $bootstrapHelper->getClassMapping('btn btn-danger');
$hiddenPhoneClass = $bootstrapHelper->getClassMapping('hidden-phone');

$pageHeading = $this->params->get('page_heading') ?: Text::_('EB_REGISTRATION_HISTORY');
?>
<div id="eb-registration-history-page" class="eb-container<?php if ($isJoomla4) echo ' eb-joomla4-container'; ?>">
<h1 class="eb-page-heading"><?php echo $this->escape($pageHeading); ?></h1>
<form action="<?php echo Route::_('index.php?option=com_eventbooking&view=history&Itemid='.$this->Itemid); ; ?>" method="post" name="adminForm"  id="adminForm">
	<div class="filters btn-toolbar clearfix mt-2 mb-2">
		<?php echo $this->loadTemplate('search_bar'); ?>
	</div>
<?php
	if (count($this->items))
	{
	?>
		<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-hover eb-responsive-table">
		<thead>
			<tr>
                <th>
	                <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_FIRST_NAME'), 'tbl.first_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>
                <?php
                    if ($showLastName)
                    {
                        $cols++;
                    ?>
                        <th>
		                    <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_LAST_NAME'), 'tbl.last_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                        </th>
                    <?php
                    }
                ?>
				<th class="list_event">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT'), 'ev.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php
					if ($this->config->show_event_date)
					{
						$cols++;
					?>
						<th class="list_event_date">
							<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT_DATE'), 'ev.event_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
					<?php
					}
				?>
				<th class="list_event_date">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_REGISTRATION_DATE'), 'tbl.register_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
                <?php
                    if ($this->config->get('history_show_number_registrants', 1))
                    {
                        $cols++;
                    ?>
                        <th class="list_registrant_number <?php echo $hiddenPhoneClass; ?>">
		                    <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_REGISTRANTS'), 'tbl.number_registrants', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                        </th>
                    <?php
                    }

                    if ($this->config->get('history_show_amount', 1))
                    {
                        $cols++;
                    ?>
                        <th class="list_amount <?php echo $hiddenPhoneClass; ?>">
		                    <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_AMOUNT'), 'tbl.amount', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                        </th>
                    <?php
                    }

                    if ($this->config->activate_deposit_feature && $this->showDueAmountColumn)
					{
						$cols++;
					?>
						<th style="text-align: right;">
							<?php echo Text::_('EB_DUE_AMOUNT'); ?>
						</th>
					<?php
					}
				?>
				<th class="list_id">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_REGISTRATION_STATUS'), 'tbl.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php
					if ($this->config->activate_invoice_feature)
					{
						$cols++;
					?>
						<th class="center">
							<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_INVOICE_NUMBER'), 'tbl.invoice_number', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
					<?php
					}

					if ($this->showDownloadTicket)
					{
						$cols++;
					?>
						<th class="center">
							<?php echo Text::_('EB_TICKET'); ?>
						</th>
					<?php
					}

					if ($this->showDownloadCertificate)
					{
						$cols++;
					?>
						<th class="center">
							<?php echo Text::_('EB_CERTIFICATE'); ?>
						</th>
					<?php
					}
				?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php
					if ($this->pagination->total > $this->pagination->limit)
					{
					?>
						<td colspan="<?php echo $cols; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					<?php
					}
				?>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		$Itemid = EventbookingHelper::getItemid();
		$nullDate = Factory::getDbo()->getNullDate();

		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$row       = $this->items[$i];
			$link      = Route::_('index.php?option=com_eventbooking&view=registrant&id=' . $row->id . '&Itemid=' . $this->Itemid . '&return=' . $return);
			$eventLink = Route::_(EventbookingHelperRoute::getEventRoute($row->event_id, $row->main_category_id, $Itemid));
			?>
			<tr>
                <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_FIRST_NAME'); ?>">
                    <a href="<?php echo $link; ?>"><?php echo $row->first_name ; ?></a>
                </td>
                <?php
                    if ($showLastName)
                    {
                    ?>
                        <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_LAST_NAME'); ?>">
		                    <?php echo $row->last_name ; ?>
                        </td>
                    <?php
                    }
                ?>
                <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_EVENT'); ?>">
                    <a href="<?php echo $eventLink; ?>" target="_blank"><?php echo $this->escape($row->title); ?></a>
                </td>
				<?php
					if ($this->config->show_event_date)
					{
					?>
                        <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_EVENT_DATE'); ?>">
							<?php
							if ($row->event_date == EB_TBC_DATE)
							{
								echo Text::_('EB_TBC');
							}
							else
							{
								echo HTMLHelper::_('date', $row->event_date, $this->config->event_date_format, null);
							}
							?>
						</td>
					<?php
					}
				?>
                <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_REGISTRATION_DATE'); ?>">
					<?php echo HTMLHelper::_('date', $row->register_date, $this->config->date_format) ; ?>
				</td>
                <?php
                if ($this->config->get('history_show_number_registrants', 1))
                {
                ?>
                    <td class="center <?php echo $hiddenPhoneClass; ?>" style="font-weight: bold;">
		                <?php echo $row->number_registrants; ?>
                    </td>
                <?php
                }

                if ($this->config->get('history_show_amount', 1))
                {
                ?>
                    <td align="right" class="<?php echo $hiddenPhoneClass; ?>">
		                <?php echo EventbookingHelper::formatCurrency($row->amount, $this->config, $row->currency_symbol) ; ?>
                    </td>
                <?php
                }

                if ($this->config->activate_deposit_feature && $this->showDueAmountColumn)
				{
				?>
					<td style="text-align: right;" class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_DUE_AMOUNT'); ?>">
						<?php
						if ($row->payment_status != 1 && $row->published != 2)
						{
							// Check to see if there is an online payment method available for this event
							if ($row->payment_methods)
							{
								$hasOnlinePaymentMethods = count(array_intersect($this->onlinePaymentPlugins, explode(',', $row->payment_methods)));
							}
							else
							{
								$hasOnlinePaymentMethods = count($this->onlinePaymentPlugins);
							}

							echo EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $this->config);

							if ($hasOnlinePaymentMethods)
							{
							?>
								<a class="<?php echo $btnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&view=payment&order_number=' . $row->registration_code . '&Itemid=' . $this->Itemid); ?>"><?php echo Text::_('EB_MAKE_PAYMENT'); ?></a>
							<?php
							}
						}
						?>
					</td>
				<?php
				}
				?>
                <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_REGISTRATION_STATUS'); ?>">
					<?php
						switch($row->published)
						{
							case 0 :
								echo Text::_('EB_PENDING');
								break ;
							case 1 :
								echo Text::_('EB_PAID');
								break ;
							case 2 :
								echo Text::_('EB_CANCELLED');
								break;
							case 4:
								echo Text::_('EB_WAITING_LIST_CANCELLED');
								break;
							case 3:
								echo Text::_('EB_WAITING_LIST');

								// If there is space, we will display payment link here to allow users to make payment to become registrants
								if ($this->config->enable_waiting_list_payment && $row->group_id == 0)
								{
									$event = EventbookingHelperDatabase::getEvent($row->event_id);

									if ($event->event_capacity == 0 || ($event->event_capacity - $event->total_registrants >= $row->number_registrants))
									{
										// Check to see if there is an online payment method available for this event
										if ($row->payment_methods)
										{
											$hasOnlinePaymentMethods = count(array_intersect($this->onlinePaymentPlugins, explode(',', $row->payment_methods)));
										}
										else
										{
											$hasOnlinePaymentMethods = count($this->onlinePaymentPlugins);
										}

										if ($hasOnlinePaymentMethods)
										{
										?>
											<a class="<?php echo $btnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&view=payment&layout=registration&order_number='.$row->registration_code.'&Itemid='.$this->Itemid); ?>"><?php echo Text::_('EB_MAKE_PAYMENT'); ?></a>
										<?php
										}
									}
								}

								break;
						}

						if (!$row->group_id && !empty($row->enable_cancel_registration) && in_array($row->published, [0, 1, 3]) && EventbookingHelperRegistration::canCancelRegistrationNow($row))
                        {
                        	if ($row->published == 3)
	                        {
	                        	$buttonText = 'EB_CANCEL_WAITING_LIST';
	                        }
                        	else
	                        {
		                        $buttonText = 'EB_CANCEL_REGISTRATION';
	                        }
                        ?>
                            <a class="<?php echo $btnDanger; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=cancel_registration_confirm&cancel_code='.$row->registration_code.'&Itemid='.$this->Itemid); ?>"><?php echo Text::_($buttonText); ?></a>
                        <?php
                        }
					?>
				</td>
				<?php
					if ($this->config->activate_invoice_feature)
					{
					?>
                        <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_INVOICE_NUMBER'); ?>">
							<?php
							if ($row->invoice_number)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_invoice&id='.($row->cart_id ? $row->cart_id : ($row->group_id ? $row->group_id : $row->id))); ?>" title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo EventbookingHelper::callOverridableHelperMethod('Helper', 'formatInvoiceNumber', [$row->invoice_number, $this->config, $row]) ; ?></a>
							<?php
							}
							?>
						</td>
					<?php
					}

					if ($this->showDownloadTicket)
					{
					?>
                        <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_TICKET'); ?>">
							<?php
							if ($row->ticket_code && $row->published == 1 && $row->payment_status == 1)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_ticket&id='.$row->id); ?>" title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo $row->ticket_number ? EventbookingHelperTicket::formatTicketNumber($row->ticket_prefix, $row->ticket_number, $this->config) : Text::_('EB_DOWNLOAD_TICKETS');?></a>
							<?php
							}
							?>
						</td>
					<?php
					}

					if ($this->showDownloadCertificate)
					{
					?>
                        <td class="tdno<?php echo $i; ?> center" data-content="<?php echo Text::_('EB_CERTIFICATE'); ?>">
							<?php
							if ($row->show_download_certificate)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_certificate&id='.$row->id); ?>" title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo EventbookingHelper::callOverridableHelperMethod('Helper', 'formatCertificateNumber', [$row->id, $this->config]) ;?></a>
							<?php
							}
							?>
						</td>
					<?php
					}
				?>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
	<?php
	}
	else
	{
		echo '<div class="text-info">'.Text::_('EB_NO_REGISTRATION_RECORDS').'</div>' ;
	}
?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
</div>