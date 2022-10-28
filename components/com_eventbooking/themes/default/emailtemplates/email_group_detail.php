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

$nullDate = Factory::getDbo()->getNullDate();
?>
<table width="100%" class="os_table" cellspacing="0" cellpadding="0">
	<tr>
		<td class="eb-heading" colspan="2">
			<?php echo Text::_('EB_GENERAL_INFORMATION') ; ?>
		</td>
	</tr>
	<tr>
		<td class="title_cell">
			<?php echo  Text::_('EB_EVENT_TITLE') ?>
		</td>
		<td class="field_cell">
			<?php echo $rowEvent->title ; ?>
		</td>
	</tr>
	<?php
		if ($config->show_event_date)
		{
		?>
		<tr>
			<td class="title_cell">
				<?php echo  Text::_('EB_EVENT_DATE') ?>
			</td>
			<td class="field_cell">
				<?php
					if ($rowEvent->event_date == EB_TBC_DATE)
					{
						echo Text::_('EB_TBC');
					}
					else
					{
						echo HTMLHelper::_('date', $rowEvent->event_date, $config->event_date_format, null) ;
					}
				?>
			</td>
		</tr>
		<?php
			if ($rowEvent->event_end_date != $nullDate)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_EVENT_END_DATE') ?>
					</td>
					<td class="field_cell">
						<?php echo HTMLHelper::_('date', $rowEvent->event_end_date, $config->event_date_format, null); ?>
					</td>
				</tr>
			<?php
			}
		}

		if ($config->show_event_location_in_email && $rowLocation)
		{
			$location = $rowLocation ;
			$locationInformation = array();

			if ($location->address)
			{
				$locationInformation[] = $location->address;
			}
		?>
		<tr>
			<td class="title_cell">
				<?php echo  Text::_('EB_LOCATION') ?>
			</td>
			<td class="field_cell">
				<?php echo $location->name.' ('.implode(', ', $locationInformation).')' ; ?>
			</td>
		</tr>
		<?php
		}
	?>
	<tr>
		<td class="title_cell">
			<?php echo  Text::_('EB_NUMBER_REGISTRANTS') ?>
		</td>
		<td class="field_cell">
			<?php echo $row->number_registrants ; ?>
		</td>
	</tr>
	<?php
	$showBillingStep = EventbookingHelperRegistration::showBillingStep($row->event_id);
	if ($showBillingStep)
	{
	?>
	<tr>
		<td colspan="2" class="eb-heading">
			<?php echo Text::_('EB_BILLING_INFORMATION') ; ?>
		</td>
	</tr>
	<?php
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if ($field->hideOnDisplay || $field->row->hide_on_email)
			{
				continue;
			}
			echo $field->getOutput(false);
		}
		if ($row->total_amount > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('EB_AMOUNT'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($row->total_amount, $config, $rowEvent->currency_symbol); ?>
				</td>
			</tr>
			<?php
			if ($row->discount_amount > 0)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_DISCOUNT_AMOUNT'); ?>
					</td>
					<td class="field_cell">
						<?php echo EventbookingHelper::formatCurrency($row->discount_amount, $config, $rowEvent->currency_symbol); ?>
					</td>
				</tr>
			<?php
			}
			if ($row->late_fee > 0)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_LATE_FEE'); ?>
					</td>
					<td class="field_cell">
						<?php echo EventbookingHelper::formatCurrency($row->late_fee, $config, $rowEvent->currency_symbol); ?>
					</td>
				</tr>
			<?php
			}
			if ($row->tax_amount > 0)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_TAX'); ?>
					</td>
					<td class="field_cell">
						<?php echo EventbookingHelper::formatCurrency($row->tax_amount, $config, $rowEvent->currency_symbol); ?>
					</td>
				</tr>
			<?php
			}
			if ($row->payment_processing_fee > 0)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_PAYMENT_FEE'); ?>
					</td>
					<td class="field_cell">
						<?php echo EventbookingHelper::formatCurrency($row->payment_processing_fee, $config, $rowEvent->currency_symbol); ?>
					</td>
				</tr>
			<?php
			}
			if ($row->discount_amount > 0 || $row->tax_amount > 0 || $row->payment_processing_fee > 0 || $row->late_fee > 0)
			{
			 ?>
				<tr>
					<td class="title_cell">
						<?php echo  Text::_('EB_GROSS_AMOUNT'); ?>
					</td>
					<td class="field_cell">
						<?php echo EventbookingHelper::formatCurrency($row->amount, $config, $rowEvent->currency_symbol) ; ?>
					</td>
				</tr>
			<?php
			}
		}
		if ($row->deposit_amount > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($row->deposit_amount, $config, $rowEvent->currency_symbol); ?>
				</td>
			</tr>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('EB_DUE_AMOUNT'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config, $rowEvent->currency_symbol); ?>
				</td>
			</tr>
		<?php
		}
		if ($row->amount > 0 && $row->published != 3)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_PAYMENT_METHOD'); ?>
				</td>
				<td class="field_cell">
					<?php
					$method = EventbookingHelperPayments::loadPaymentMethod($row->payment_method);
					if ($method)
					{
						echo Text::_($method->title) ;
					}
					?>
				</td>
			</tr>
			<?php
			if (!empty($last4Digits))
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo Text::_('EB_LAST_4DIGITS'); ?>
					</td>
					<td class="field_cell">
						<?php echo $last4Digits; ?>
					</td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('EB_TRANSACTION_ID'); ?>
				</td>
				<td class="field_cell">
					<?php echo $row->transaction_id ; ?>
				</td>
			</tr>
		<?php
		}
	}

	if (!empty($autoCouponCode))
	{
	?>
        <tr>
            <td class="title_cell">
				<?php echo Text::_('EB_AUTO_COUPON_CODE'); ?>
            </td>
            <td class="field_cell">
				<?php echo $autoCouponCode ; ?>
            </td>
        </tr>
	<?php
	}

	if ($config->show_agreement_on_email)
	{
	?>
        <tr>
            <td class="title_cell">
				<?php echo Text::_('EB_PRIVACY_POLICY'); ?>
            </td>
            <td class="field_cell">
				<?php echo Text::_('EB_ACCEPTED'); ; ?>
            </td>
        </tr>
		<?php
		if ($config->show_subscribe_newsletter_checkbox)
		{
		?>
            <tr>
                <td class="title_cell">
					<?php echo Text::_('EB_SUBSCRIBE_TO_NEWSLETTER'); ?>
                </td>
                <td class="field_cell">
					<?php echo $row->subscribe_newsletter ? Text::_('JYES') : Text::_('JNO'); ?>
                </td>
            </tr>
		<?php
		}
	}

	if ($rowEvent->collect_member_information === '')
	{
		$collectMemberInformation = $config->collect_member_information;
	}
	else
	{
		$collectMemberInformation = $rowEvent->collect_member_information;
	}

	if ($collectMemberInformation && count($rowMembers))
	{
	?>
		<tr>
			<td class="eb-heading" colspan="2">
				<?php echo Text::_('EB_MEMBERS_INFORMATION') ; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%" class="os_member_list" cellspacing="0" cellpadding="0">
					<?php
                        if ($row->published == 3)
                        {
                            $typeOfRegistration = 2;
                        }
                        else
                        {
                            $typeOfRegistration = 1;
                        }

                        $rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 2, $row->language, null, $typeOfRegistration);

						for ($i = 0 , $n  = count($rowMembers); $i < $n; $i++)
						{
							if ($i %2 == 0)
							{
								echo "<tr>\n" ;
							}

							$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i + 1);
							$memberForm = new RADForm($currentMemberFormFields);
							$rowMember               = $rowMembers[$i];
							$memberData              = EventbookingHelperRegistration::getRegistrantData($rowMember, $currentMemberFormFields);
							$memberForm->bind($memberData);
							$memberForm->buildFieldsDependency();
							$fields = $memberForm->getFields();

							foreach ($fields as $field)
							{
								if ($field->hideOnDisplay || $field->row->hide_on_email)
								{
									unset($fields[$field->name]);
								}
							}

							$memberForm->setFields($fields);
						?>
							<td>
								<table class="os_table" width="100%" cellspacing="0" cellpadding="0">
									<tr>
										<td colspan="2" class="os_row_heading"><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i + 1) ; ?></td>
									</tr>
									<?php
                                        if ($rowEvent->has_multiple_ticket_types)
                                        {
                                            $ticketType = EventbookingHelperRegistration::getGroupMemberTicketType($rowMember->id);

                                            if ($ticketType)
                                            {
                                            ?>
                                                <tr>
                                                    <td class="title_cell">
                                                        <?php echo Text::_('EB_TICKET_TYPE'); ?>
                                                    </td>
                                                    <td class="field_cell">
                                                        <?php echo $ticketType; ?>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                        }

										$fields = $memberForm->getFields();

                                        foreach ($fields as $field)
										{
											echo $field->getOutput(false);
										}
									?>
								</table>
							</td>
						<?php
							if (($i + 1) %2 == 0)
							{
								echo "</tr>\n" ;
							}
						}
						if ($i %2 != 0)
						{
							echo "<td>&nbsp;</td></tr>" ;
						}
					?>
				</table>
			</td>
		</tr>
	<?php
	}
?>
</table>