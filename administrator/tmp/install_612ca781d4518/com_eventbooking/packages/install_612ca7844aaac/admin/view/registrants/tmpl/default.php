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

$isJoomla4 = EventbookingHelper::isJoomla4();

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.core');

if (!$isJoomla4)
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

Factory::getDocument()->addScript(Uri::root(true).'/media/com_eventbooking/js/admin-registrants-default.min.js');

$colSpan = 10;

if (in_array('last_name', $this->coreFields))
{
	$colSpan++;
	$showLastName = true;
}
else
{
	$showLastName = false;
}

$nullDate = Factory::getDbo()->getNullDate();

$dateFields = [
	'filter_from_date',
	'filter_to_date'
];

foreach ($dateFields as $dateField)
{
	if ($this->state->{$dateField} == $nullDate)
	{
		$this->state->{$dateField} = '';
	}
	elseif($this->state->{$dateField})
    {
        try
        {
	        $date = DateTime::createFromFormat($this->dateFormat.' H:i:s', $this->state->{$dateField});

	        if ($date !== false)
	        {
		        $this->state->{$dateField} = $date->format('Y-m-d H:i:s');
	        }
        }
        catch (Exception $e)
        {

        }
    }
}

Text::script('EB_SELECT_EVENT_TO_ADD_REGISTRANT', true);
?>
<form action="index.php?option=com_eventbooking&view=registrants" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div id="j-main-container"<?php if ($isJoomla4) echo ' class="eb-joomla4-container"'; ?>>
        <div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('EB_FILTER_SEARCH_REGISTRANTS_DESC');?></label>
				<input type="text" name="filter_search" id="filter_search" inputmode="search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_REGISTRANTS_DESC'); ?>" />
			</div>
            <div class="btn-group pull-left">
	            <?php
	            echo HTMLHelper::_('calendar', $this->state->filter_from_date, 'filter_from_date', 'filter_from_date', $this->datePickerFormat, array('class' => $isJoomla4 ? 'input-medium' : 'input-small', 'placeholder' => Text::_('EB_FROM')));
	            echo HTMLHelper::_('calendar', $this->state->filter_to_date, 'filter_to_date', 'filter_to_date', $this->datePickerFormat, array('class' => $isJoomla4 ? 'input-medium' : 'input-small', 'placeholder' => Text::_('EB_TO')));
	            ?>
            </div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" onclick="document.getElementById('task').value=''; return true;"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value=''; document.getElementById('task').value=''; this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right btn-filter-second-row">
				<?php
					echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['filter_event_id']);

                    $hasFilterFields = false;

                    foreach ($this->filters as $filter)
                    {
                        $hasFilterFields = true;
                        echo $filter;
                    }

                    echo $this->lists['filter_published'];

					if ($this->config->activate_checkin_registrants)
					{
						echo $this->lists['filter_checked_in'];
					}

					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="2%" class="text_center">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title" style="text-align: left;" width="10%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_FIRST_NAME'), 'tbl.first_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<?php
					if ($showLastName)
					{
					?>
						<th class="title" style="text-align: left;" width="10%">
							<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_LAST_NAME'), 'tbl.last_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
						</th>
					<?php
					}
				?>
				<th class="title" style="text-align: left;" width="15%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT'), 'ev.title', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<?php
				if ($this->config->show_event_date)
				{
					$colSpan++;
				?>
					<th width="7%" class="title" nowrap="nowrap">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT_DATE'), 'ev.event_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
				<?php
				}
				?>
				<th width="10%" class="title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EMAIL'), 'tbl.email', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th class="title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_NUMBER_REGISTRANTS'), 'tbl.number_registrants', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<?php
					if (count($this->tickets))
					{
						$colSpan++;
					?>
						<th width="10%" class="title" nowrap="nowrap">
							<?php echo Text::_('EB_TICKETS'); ?>
						</th>
					<?php
					}
				?>
				<th width="10%" class="title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_REGISTRATION_DATE'), 'tbl.register_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="5%" class="title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_AMOUNT'), 'tbl.amount', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<?php
				foreach ($this->fields as $field)
				{
					$colSpan++;

					if ($field->is_core || $field->is_searchable)
					{
					?>
						<th class="title" nowrap="nowrap">
							<?php echo HTMLHelper::_('grid.sort', Text::_($field->title), 'tbl.' . $field->name, $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}
					else
					{
					?>
						<th class="title" nowrap="nowrap"><?php echo $field->title; ?></th>
					<?php
					}
				}

				if ($this->config->activate_deposit_feature)
				{
					$colSpan++;
				?>
					<th width="5%" class="title" nowrap="nowrap">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_PAYMENT_STATUS'), 'tbl.payment_status', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
				<?php
				}
				if ($this->config->show_coupon_code_in_registrant_list)
				{
					$colSpan++;
				?>
					<th width="7%" class="title" nowrap="nowrap">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_COUPON'), 'cp.code', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
				<?php
				}
				if ($this->totalPlugins > 1)
				{
					$colSpan++;
				?>
					<th width="5%" class="title" nowrap="nowrap">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_PAYMENT_METHOD'), 'tbl.payment_method', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
				<?php
				}
				if ($this->config->activate_tickets_pdf)
				{
					$colSpan++;
				?>
					<th width="8%" class="center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_TICKET_NUMBER'), 'tbl.ticket_number', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
				<?php
				}
				?>
				<th width="5%" class="title">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_REGISTRATION_STATUS'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<?php
				if ($this->config->activate_checkin_registrants)
				{
					$colSpan++;
				?>
					<th width="8%" class="title">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_CHECKED_IN'), 'tbl.checked_in', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
				<?php
				}

				if ($this->config->activate_invoice_feature)
				{
					$colSpan++;
				?>
					<th width="8%" class="center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_INVOICE_NUMBER'), 'tbl.invoice_number', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
				<?php
				}

				if ($this->config->show_certificate_sent_status)
				{
				    $colSpan++;
				?>
                    <th class="center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_CERTIFICATE_SENT'), 'tbl.certificate_sent', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                    </th>
				<?php
				}
				?>
				<th width="3%" class="title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo $colSpan ; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
			$iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
			$iconUnPublish = $bootstrapHelper->getClassMapping('icon-unpublish');

			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				$row = $this->items[$i];
				$link 	= Route::_( 'index.php?option=com_eventbooking&view=registrant&id='. $row->id );
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				if (in_array($row->published, [0, 1]))
				{
					$published = HTMLHelper::_('jgrid.published', $row->published, $i);
				}
				elseif($row->published == 3)
				{
					$published = Text::_('EB_WAITING_LIST');
				}
				elseif ($row->published == 4)
				{
					$published = Text::_('EB_WAITING_LIST_CANCELLED');
				}
				else
				{
					$imageSrc = 'components/com_eventbooking/assets/icons/cancelled.jpg' ;
					$title = Text::_('EB_CANCELLED') ;
					$published = '<img src="'.$imageSrc.'" title="'.$title.'" />';
				}

				$isMember = $row->group_id > 0 ? true : false ;

				if ($isMember)
				{
					$groupLink = Route::_( 'index.php?option=com_eventbooking&view=registrant&id='. $row->group_id );
				}

				$iconClass = $row->checked_in ? $iconPublish : $iconUnPublish;
				$alt       = $row->checked_in ? Text::_('EB_CHECKED_IN') : Text::_('EB_NOT_CHECKED_IN');
				$img       = '<span class="' . $iconClass . '"></span>';
				$action    = $row->checked_in ? Text::_('EB_UN_CHECKIN') : Text::_('EB_CHECKIN');
				$task      = $row->checked_in ? 'reset_check_in' : 'check_in';
				$href      = '
					<a class="tbody-icon" href="javascript:void(0);" onclick="return listItemTask(\'cb' . $i . '\',\'' . $task . '\')" title="' . $action . '">' .
					$img . '</a>';
				?>
				<tr class="<?php echo "row$k"; if ($row->is_group_billing) echo ' eb-row-group-billing'; ?>">
					<td class="text_center">
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php
								echo $row->first_name;

								if ($row->username)
								{
								?>
									<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . $row->user_id); ?>" title="View Profile" target="_blank">&nbsp;<strong>[<?php echo $row->username ; ?>]</strong></a>
								<?php
								}
							?>
						</a>
						<?php
						if ($row->is_group_billing)
						{
							echo '<br />' ;
							echo Text::_('EB_GROUP_BILLING');
						}

						if ($isMember && $row->group_name)
						{
						?>
							<br />
							<?php echo Text::_('EB_GROUP'); ?><a href="<?php echo $groupLink; ?>"><?php echo $row->group_name ;  ?></a>
						<?php
						}
						?>
					</td>
					<?php
						if ($showLastName)
						{
						?>
							<td>
								<?php echo $row->last_name ; ?>
							</td>
						<?php
						}
					?>
					<td>
						<a href="index.php?option=com_eventbooking&view=event&id=<?php echo $row->event_id; ?>"><?php echo $row->title ; ?></a>
					</td>
					<?php
					if ($this->config->show_event_date)
					{
					?>
						<td class="text_center">
							<?php
							if ($row->event_date == EB_TBC_DATE)
							{
								echo Text::_('EB_TBC');
							}
							else
							{
								echo HTMLHelper::_('date', $row->event_date, $this->config->date_format.' H:i', null);
							}
							?>
						</td>
					<?php
					}
					?>
					<td>
						<a href="mailto:<?php echo $row->email;?>"><?php echo $row->email;?></a>
					</td>
					<td class="center" style="font-weight: bold;">
						<?php echo $row->number_registrants; ?>
					</td>
					<?php
						if (count($this->tickets))
						{
							$ticketsOutput = array();

							if (!empty($this->tickets[$row->id]))
							{
								$tickets = $this->tickets[$row->id];

								foreach ($this->ticketTypes as $ticketType)
								{
									if (!empty($tickets[$ticketType->id]))
									{
										$ticketsOutput[] = Text::_($ticketType->title) . ': ' . $tickets[$ticketType->id];
									}
								}
							}
						?>
							<td>
								<?php echo implode("<br />", $ticketsOutput); ?>
							</td>
						<?php
						}
					?>
					<td class="center">
						<?php echo HTMLHelper::_('date', $row->register_date, $this->config->date_format.' H:i'); ?>
					</td>
					<td>
						<?php echo EventbookingHelper::formatAmount($row->amount, $this->config) ; ?>
					</td>
					<?php
					foreach ($this->fields as $field)
					{
						$fieldValue = isset($this->fieldsData[$row->id][$field->id]) ? $this->fieldsData[$row->id][$field->id] : '';

						if ($fieldValue && $field->fieldtype == 'File')
                        {
	                        $fieldValue = '<a href="' . Route::_('index.php?option=com_eventbooking&task=controller.download_file&file_name=' . $fieldValue) . '">' . $fieldValue . '</a>';
                        }
					?>
						<td>
							<?php echo $fieldValue; ?>
						</td>
					<?php
					}

					if ($this->config->activate_deposit_feature)
					{
						?>
						<td>
							<?php
							if($row->payment_status == 1)
							{
								echo Text::_('EB_FULL_PAYMENT');
							}
							elseif($row->payment_status == 2)
							{
								echo Text::_('EB_DEPOSIT_PAID');
							}
							else
							{
								echo Text::_('EB_PARTIAL_PAYMENT');
							}
							?>
						</td>
					<?php
					}
					if ($this->config->show_coupon_code_in_registrant_list)
					{
					?>
						<td>
							<a href="index.php?option=com_eventbooking&view=coupon&id=<?php echo $row->coupon_id; ?>" target="_blank"><?php echo $row->coupon_code ; ?></a>
						</td>
					<?php
					}
					if ($this->totalPlugins > 1)
					{
						$method = EventbookingHelperPayments::getPaymentMethod($row->payment_method) ;
						?>
						<td>
							<?php if ($method) echo Text::_($method->getTitle()); ?>
						</td>
					<?php
					}

					if ($this->config->activate_tickets_pdf)
					{
					?>
						<td class="center">
							<?php
							if ($row->ticket_code)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_ticket&id='.$row->id); ?>" title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo $row->ticket_number ? EventbookingHelperTicket::formatTicketNumber($row->ticket_prefix, $row->ticket_number, $this->config) : Text::_('EB_DOWNLOAD_TICKETS');?></a>
							<?php
							}
							?>
						</td>
					<?php
					}

					?>
					<td class="center">
						<?php
							echo $published ;
						?>
					</td>
					<?php
					if ($this->config->activate_checkin_registrants)
					{
					?>
						<td class="center">
                            <?php
                                echo $href;

                                if ($row->checked_in && $row->checked_in_at && $row->checked_in_at != $nullDate)
                                {
                                ?>
                                    <br /><?php echo Text::sprintf('EB_CHECKED_IN_AT', HTMLHelper::_('date', $row->checked_in_at, $this->config->date_format.' H:i:s')); ?>
                                <?php
                                }

                                if (!$row->checked_in && $row->checked_out_at && $row->checked_out_at != $nullDate)
                                {
                                ?>
                                    <br /><span style="color: red;"><?php echo Text::sprintf('EB_CHECKED_OUT_AT', HTMLHelper::_('date', $row->checked_out_at, $this->config->date_format.' H:i:s')); ?></span>
                                <?php
                                }
                            ?>
                        </td>
					<?php
					}
					if ($this->config->activate_invoice_feature)
					{
						?>
						<td class="center">
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

					if ($this->config->show_certificate_sent_status)
                    {
                    ?>
                        <td class="center">
                            <a class="tbody-icon"><span class="<?php echo $row->certificate_sent ? $iconPublish : $iconUnPublish; ?>"></span></a>
                        </td>
                    <?php
                    }
					?>
					<td class="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>

		<?php echo HTMLHelper::_(
				'bootstrap.renderModal',
				'collapseModal',
				array(
						'title' => Text::_('EB_MASS_MAIL'),
						'footer' => $this->loadTemplate('batch_footer')
				),
				$this->loadTemplate('batch_body')
		); ?>

	</div>
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />	
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<?php
    if (!$hasFilterFields)
    {
    ?>
        <form action="index.php?option=com_eventbooking&view=registrants" method="post" name="registrantsExportForm" id="registrantsExportForm">
            <input type="hidden" name="task" value=""/>
            <input type="hidden" id="export_filter_search" name="filter_search"/>
            <input type="hidden" id="export_filter_from_date" name="filter_from_date" value="">
            <input type="hidden" id="export_filter_to_date" name="filter_to_date" value="">
            <input type="hidden" id="export_filter_event_id" name="filter_event_id" value="">
            <input type="hidden" id="export_filter_published" name="filter_published" value="">
            <input type="hidden" id="export_cid" name="cid" value="">
		    <?php
		    if ($this->config->activate_checkin_registrants)
		    {
			?>
                <input type="hidden" id="export_filter_checked_in" name="filter_checked_in"  value="">
			<?php
		    }
		    ?>
            <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
		    <?php echo HTMLHelper::_( 'form.token' ); ?>
        </form>
    <?php
    }
?>