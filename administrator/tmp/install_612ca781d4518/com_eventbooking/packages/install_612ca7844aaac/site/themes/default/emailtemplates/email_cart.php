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
?>
<table class="table table-striped table-bordered table-condensed" cellspacing="0" cellpadding="0">
	<thead>
	<tr>		
		<th class="col_event text-left">
			<?php echo Text::_('EB_EVENT'); ?>
		</th>		
		<?php
			if ($config->show_event_date) 
			{
			?>
				<th class="col_event_date center">
					<?php echo Text::_('EB_EVENT_DATE'); ?>
				</th>
			<?php		
			}
		?>
		<th class="col_price text-right">
			<?php echo Text::_('EB_PRICE'); ?>
		</th>									
		<th class="col_quantity center">
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
		$k = 0 ;					
		for ($i = 0 , $n = count($items) ; $i < $n; $i++) 
		{
			$item = $items[$i] ;
			$rate = EventbookingHelper::callOverridableHelperMethod('Registration', 'getRegistrationRate', [$item->event_id, $item->number_registrants]);
			$total += $item->number_registrants*$rate ;
            $url = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).Route::_(EventbookingHelperRoute::getEventRoute($item->event_id, 0, $Itemid));
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
			$k = 1 - $k ;				
		}
	?>			
	</tbody>					
</table>	
<table width="100%" class="os_table" cellspacing="0" cellpadding="0">	
<?php
	if ($config->collect_member_information_in_cart)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		foreach ($items as $item)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($item->event_id, 2);
			$query->clear()
					->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . $item->id);
			$db->setQuery($query);
			$rowMembers = $db->loadObjectList();
			?>
			<tr><td colspan="2"><h3 class="eb-heading"><?php echo Text::sprintf('EB_EVENT_REGISTRANTS_INFORMATION', $item->title); ?></h3></td></tr>
			<?php
			$i = 0;
			foreach ($rowMembers as $rowMember)
			{
				$i++;
				$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i);
				$memberForm = new RADForm($currentMemberFormFields);
				$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $currentMemberFormFields);
				$memberForm->bind($memberData);
				$memberForm->buildFieldsDependency();
				$fields = $memberForm->getFields();
				?>
				<tr><td colspan="2"><h4 class="eb-heading"><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i); ?></h4></td></tr>
				<?php
				foreach ($fields as $field)
				{
					if ($field->hideOnDisplay || $field->row->hide_on_email)
					{
						continue;
					}

					echo $field->getOutput(false);
				}
			}
		}
		?>
		<tr><td colspan="2"><h3 class="eb-heading"><?php echo Text::_('EB_BILLING_INFORMATION'); ?></h3></td></tr>
		<?php
	}

	$fields = $form->getFields();

	foreach ($fields as $field)
	{
		if ($field->hideOnDisplay || $field->row->hide_on_email)
		{
			continue;
		}

		echo $field->getOutput(false);
	}

	if ($totalAmount > 0)
	{
	?>
	<tr>
		<td class="title_cell">
			<?php echo Text::_('EB_AMOUNT'); ?>
		</td>
		<td class="field_cell">
			<?php echo EventbookingHelper::formatCurrency($totalAmount, $config); ?>
		</td>
	</tr>
	<?php	
		if ($discountAmount > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_DISCOUNT_AMOUNT'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($discountAmount, $config); ?>
				</td>
			</tr>
		<?php
		}

		if ($lateFee > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_LATE_FEE'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($lateFee, $config); ?>
				</td>
			</tr>
		<?php
		}

		if ($taxAmount > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_TAX'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($taxAmount, $config); ?>
				</td>
			</tr>
		<?php
		}

		if ($paymentProcessingFee > 0)
		{
		?>
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_PAYMENT_FEE'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($paymentProcessingFee, $config); ?>
				</td>
			</tr>
		<?php
		}
		if ($discountAmount > 0 || $taxAmount > 0 || $paymentProcessingFee > 0)
		{
		?>                
			<tr>
				<td class="title_cell">
					<?php echo  Text::_('EB_GROSS_AMOUNT'); ?>
				</td>
				<td class="field_cell">
					<?php echo EventbookingHelper::formatCurrency($amount, $config);?>
				</td>
			</tr>
		<?php
		}            
	}
	if ($depositAmount > 0)
	{
	?>
	<tr>
		<td class="title_cell">
			<?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?>
		</td>
		<td class="field_cell">
			<?php echo EventbookingHelper::formatCurrency($depositAmount, $config); ?>
		</td>
	</tr>
	<tr>
		<td class="title_cell">
			<?php echo Text::_('EB_DUE_AMOUNT'); ?>
		</td>
		<td class="field_cell">
			<?php echo EventbookingHelper::formatCurrency($amount - $depositAmount, $config); ?>
		</td>
	</tr>
	<?php
	}
	if ($amount > 0)
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
?>																	
</table>	