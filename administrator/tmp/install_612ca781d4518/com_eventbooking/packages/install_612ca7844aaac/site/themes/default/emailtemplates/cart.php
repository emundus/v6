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

$controlGroupClass  = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass  = $bootstrapHelper->getClassMapping('control-label');
$controlsClass      = $bootstrapHelper->getClassMapping('controls');
$formFormHorizontal = $bootstrapHelper->getClassMapping('form form-horizontal');
?>
<form id="adminForm" class="<?php echo $formFormHorizontal; ?>">
	<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed">
		<thead>
		<tr>
			<th class="col_event">
				<?php echo Text::_('EB_EVENT'); ?>
			</th>
			<?php
				if ($config->show_event_date)
				{
			    ?>
                    <th class="col_event_date text-center">
                        <?php echo Text::_('EB_EVENT_DATE'); ?>
                    </th>
			    <?php
				}
			?>
			<th class="col_price text-right">
				<?php echo Text::_('EB_PRICE'); ?>
			</th>
			<th class="col_quantity text-center">
				<?php echo Text::_('EB_QUANTITY'); ?>
			</th>
			<th class="col_subtotal text-right">
				<?php echo Text::_('EB_SUB_TOTAL'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$total = 0 ;
			for ($i = 0 , $n = count($items) ; $i < $n; $i++)
			{
				$item = $items[$i] ;
				$rate = EventbookingHelper::callOverridableHelperMethod('Registration', 'getRegistrationRate', [$item->event_id, $item->number_registrants]);
				$total += $item->number_registrants*$rate;
				$url = Route::_(EventbookingHelperRoute::getEventRoute($item->event_id, 0, $Itemid));
			?>
				<tr>
					<td class="col_event">
						<a href="<?php echo $url; ?>"><?php echo $item->title; ?></a>
					</td>
					<?php
						if ($config->show_event_date)
						{
						?>
							<td class="col_event_date text-center">
								<?php
									if ($item->event_date == EB_TBC_DATE)
									{
										echo Text::_('EB_TBC');
									}
									else
									{
										if (strpos($item->event_date, '00:00:00') !== false)
										{
											$dateFormat = $config->date_format;
										}
										else
										{
											$dateFormat = $config->event_date_format;
										}

										echo HTMLHelper::_('date', $item->event_date,  $dateFormat, null);
									}
								?>
							</td>
						<?php
						}
					?>
					<td class="col_price text-right">
						<?php echo EventbookingHelper::formatAmount($rate, $config); ?>
					</td>
					<td class="col_quantity text-center">
						<?php echo $item->number_registrants ; ?>
					</td>
					<td class="col_price text-right">
						<?php echo EventbookingHelper::formatAmount($rate*$item->number_registrants, $config); ?>
					</td>
				</tr>
			<?php
			}
		?>
		</tbody>
	</table>
	<?php
		if ($config->collect_member_information_in_cart)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			foreach($items as $item)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($item->event_id, 2);
				$query->clear()
						->select('*')
						->from('#__eb_registrants')
						->where('group_id = ' . $item->id);
				$db->setQuery($query);
				$rowMembers = $db->loadObjectList();
			?>
				<h3 class="eb-heading"><?php echo Text::sprintf('EB_EVENT_REGISTRANTS_INFORMATION', $item->title); ?></h3>
			<?php
				$i = 0;
				foreach($rowMembers as $rowMember)
				{
					$i++;
					$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i);
					$memberForm = new RADForm($currentMemberFormFields);
					$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $currentMemberFormFields);
					$memberForm->bind($memberData);
					$memberForm->buildFieldsDependency();
					$fields = $memberForm->getFields();
					?>
					<h4 class="eb-heading"><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i); ?></h4>
					<?php
					foreach ($fields as $field)
					{
						if ($field->hideOnDisplay)
						{
							continue;
						}

						echo $field->getOutput(true, $bootstrapHelper);
					}
				}
			}
		?>
			<h3 class="eb-heading"><?php echo Text::_('EB_BILLING_INFORMATION'); ?></h3>
		<?php
		}
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if ($field->hideOnDisplay || $field->row->hide_on_email)
			{
				continue;
			}
			echo $field->getOutput(true, $bootstrapHelper);
		}
		if ($totalAmount > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo EventbookingHelper::formatCurrency($totalAmount, $config); ?>
			</div>
		</div>
		<?php
			if ($discountAmount > 0)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('EB_DISCOUNT_AMOUNT'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($discountAmount, $config); ?>
					</div>
				</div>
			<?php
			}

			if ($lateFee > 0)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('EB_LATE_FEE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($lateFee, $config); ?>
					</div>
				</div>
			<?php
			}

			if ($taxAmount > 0)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('EB_TAX'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($taxAmount, $config); ?>
					</div>
				</div>
			<?php
			}
			if ($paymentProcessingFee > 0)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('EB_PAYMENT_FEE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($paymentProcessingFee, $config); ?>
					</div>
				</div>
			<?php
			}
			if ($discountAmount > 0 || $taxAmount > 0 || $paymentProcessingFee > 0 || $lateFee > 0)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('EB_GROSS_AMOUNT'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($amount, $config);?>
					</div>
				</div>
			<?php
			}
		}
		if ($depositAmount > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo EventbookingHelper::formatCurrency($depositAmount, $config); ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_DUE_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo EventbookingHelper::formatCurrency($amount - $depositAmount, $config); ?>
			</div>
		</div>
		<?php
		}
		if ($amount > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo  Text::_('EB_PAYMENT_METHOD'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
			<?php
				$method = EventbookingHelperPayments::loadPaymentMethod($row->payment_method);
				if ($method)
				{
					echo Text::_($method->title) ;
				}
			?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_TRANSACTION_ID'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $row->transaction_id ; ?>
			</div>
		</div>
		<?php
		}
	?>
</form>