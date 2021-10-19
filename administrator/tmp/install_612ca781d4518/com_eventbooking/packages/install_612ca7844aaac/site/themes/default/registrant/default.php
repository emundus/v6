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

if (EventbookingHelper::isJoomla4())
{
	$containerClass = ' eb-container-j4';
}
else
{
	$containerClass = '';
}

$format = 'Y-m-d';
EventbookingHelperJquery::validateForm();;
$selectedState = '';

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$rowFluid            = $bootstrapHelper->getClassMapping('row-fluid');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');
?>
<div class="eb-container<?php echo $containerClass; ?>">
	<div class="page-header">
		<h1 class="eb_title"><?php echo Text::_('EB_EDIT_REGISTRANT'); ?></h1>
	</div>
	<div class="btn-toolbar" id="btn-toolbar">
		<?php echo JToolbar::getInstance('toolbar')->render(); ?>
	</div>
	<form action="<?php echo Route::_('index.php?option=com_eventbooking&view=registrants&Itemid=' . $this->Itemid); ?>" method="post" name="adminForm" id="adminForm" class="<?php echo $formHorizontalClass; ?>">
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_EVENT'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php
				if ($this->item->id && $this->userType == 'registrant')
				{
					echo $this->event->title;
				}
				else
				{
					echo $this->lists['event_id'];
				}
				?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_NUMBER_REGISTRANTS'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php
				if ($this->item->number_registrants)
				{
					echo $this->item->number_registrants;
				}
				else
				{
				?>
					<input class="input-small validate[required,custom[number]]" type="text" name="number_registrants"
					       id="number_registrants" size="40" maxlength="250" value="1"/>
					<small><?php echo Text::_('EB_NUMBER_REGISTRANTS_EXPLAIN'); ?></small>
				<?php
				}
				?>
			</div>
		</div>

		<?php
		if (Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('EB_USER'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo EventbookingHelper::getUserInput($this->item->user_id,'user_id',(int) $this->item->id) ; ?>
				</div>
			</div>
		<?php
		}

		if (!empty($this->ticketTypes))
		{
		?>
			<h3><?php echo Text::_('EB_TICKET_INFORMATION'); ?></h3>
			<?php
			foreach ($this->ticketTypes AS $ticketType)
			{
				if ($ticketType->capacity)
				{
					$available = $ticketType->capacity - $ticketType->registered;
				}
				elseif (!empty($this->event->event_capacity))
				{
					$available = max($this->event->event_capacity - $this->event->total_registrants, 0);
				}
				else
				{
					$available = 10;
				}

				$quantity  = 0;

				if (!empty($this->registrantTickets[$ticketType->id]))
				{
					$quantity = $this->registrantTickets[$ticketType->id]->quantity;
				}
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_($ticketType->title); ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if ($available > 0 || $quantity > 0)
						{
							$fieldName = 'ticket_type_' . $ticketType->id;

							if ($available < $quantity)
							{
								$available = $quantity;
							}

							if ($this->canChangeTicketsQuantity)
							{
								echo HTMLHelper::_('select.integerlist', 0, $available, 1, $fieldName, 'class="ticket_type_quantity input-small"', $quantity);
							}
							else
							{
								echo $quantity;
							}
						}
						else
						{
							echo Text::_('EB_NA');
						}
						?>
					</div>
				</div>
			<?php
			}
		}

		$fields = $this->form->getFields();

		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}

		if (isset($fields['email']))
		{
			$emailField = $fields['email'];
			$cssClass   = $emailField->getAttribute('class');
			$cssClass   = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
			$emailField->setAttribute('class', $cssClass);
		}

		foreach ($fields as $field)
		{
			/* @var RADFormField $field */
			$fieldType = strtolower($field->type);

			if (in_array($fieldType, ['message', 'heading']))
			{
				continue;
			}

			// Dealing with group member record
			if ($this->item->group_id > 0)
			{
	            if (empty($this->item->is_first_group_member) && $field->row->only_show_for_first_member)
	            {
		            continue;
	            }

	            if (empty($this->item->is_first_group_member) && $field->row->only_require_for_first_member)
	            {
		            $field->makeFieldOptional();
	            }
			}

			if (($field->fee_field && !$this->canChangeFeeFields) || $this->disableEdit)
			{
				// Temp code while waiting for a more proper form-api
	            $controlGroupAttributes = 'id="field_' . $field->name . '" ';

	            if ($field->hideOnDisplay)
	            {
		            $controlGroupAttributes .= ' style="display:none;" ';
	            }

	            $class = "";

	            if ($field->isMasterField)
	            {
		            if ($field->suffix)
		            {
			            $class = ' master-field-' . $field->suffix;
		            }
		            else
		            {
			            $class = ' master-field';
		            }
	            }
	        ?>
				<div class="<?php echo $controlGroupClass . $class; ?>" <?php echo $controlGroupAttributes; ?>>
					<div class="<?php echo $controlLabelClass; ?>">
			            <?php echo $field->title; ?>
			            <?php
			            if ($field->row->required)
			            {
				        ?>
							<span class="star">&#160;*</span>
				        <?php
			            }
			            ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
			            <?php echo $field->getDisplayValue(); ?>
					</div>
				</div>
			<?php
			}
			else
			{
				echo $field->getControlGroup($bootstrapHelper);
			}
		}

		if ($this->canChangeStatus)
		{
		    if (isset($this->lists['checked_in']))
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
			            <?php echo Text::_('EB_CHECKED_IN'); ?>
					</div>
		            <?php echo $this->lists['checked_in']; ?>
				</div>
			<?php
			}
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_REGISTRATION_STATUS'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['published']; ?>
				</div>
			</div>
			<?php
		}
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_REGISTRATION_DATE'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo HTMLHelper::_('date', $this->item->register_date, $format, null); ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_TOTAL_AMOUNT'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php
				if ($this->canChangeStatus)
				{
					$input = '<input type="text" class="input-medium form-control" name="total_amount" value="' . ($this->item->total_amount > 0 ? round($this->total_amount, 2) : "") . '" size="7" />';
					echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
				}
				else
				{
					echo EventbookingHelper::formatCurrency($this->item->total_amount, $this->config);
				}
				?>
			</div>
		</div>
		<?php
		if ($this->item->discount_amount > 0 || $this->item->late_fee > 0 || $this->item->tax_amount > 0 || $this->canChangeStatus || empty($this->item->id))
		{
			if ($this->item->discount_amount > 0 || $this->canChangeStatus || empty($this->item->id))
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('EB_DISCOUNT_AMOUNT'); ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if ($this->canChangeStatus)
						{
							$input = '<input type="text" class="input-medium form-control" name="discount_amount" value="' . ($this->item->discount_amount > 0 ? round($this->discount_amount, 2) : "") . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						}
						else
						{
							echo EventbookingHelper::formatCurrency($this->item->discount_amount, $this->config);
						}
						?>
					</div>
				</div>
				<?php
			}

			if ($this->item->late_fee > 0 || empty($this->item->id))
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('EB_LATE_FEE'); ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if ($this->canChangeStatus)
						{
							$input = '<input type="text" class="input-medium form-control" name="late_fee" value="' . ($this->item->late_fee > 0 ? round($this->late_fee, 2) : "") . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						}
						else
						{
							echo EventbookingHelper::formatCurrency($this->item->late_fee, $this->config);
						}
						?>
					</div>
				</div>
			<?php
			}

			if ($this->item->tax_amount > 0 || empty($this->item->id))
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('EB_TAX'); ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if ($this->canChangeStatus)
						{
							$input = '<input type="text" class="input-medium form-control" name="tax_amount" value="' . ($this->item->tax_amount > 0 ? round($this->tax_amount, 2) : "") . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						}
						else
						{
							echo EventbookingHelper::formatCurrency($this->item->tax_amount, $this->config);
						}
						?>
					</div>
				</div>
			<?php
			}
			?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_GROSS_AMOUNT'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php
					if ($this->canChangeStatus)
					{
						$input = '<input type="text" class="input-medium form-control" name="amount" value="' . ($this->item->amount > 0 ? round($this->amount, 2) : "") . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
					}
					else
					{
						echo EventbookingHelper::formatCurrency($this->item->amount, $this->config);
					}
					?>
				</div>
			</div>
			<?php
		}

		if ($this->item->deposit_amount > 0)
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo EventbookingHelper::formatCurrency($this->item->deposit_amount, $this->config); ?>
				</div>
			</div>
			<?php
			if ($this->item->payment_status == 1)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('EB_PAYMENT_MADE'); ?>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo EventbookingHelper::formatCurrency($this->item->amount - $this->item->deposit_amount, $this->config); ?>
					</div>
				</div>
			<?php
				$dueAmount = 0;
			}
			else
			{
				$dueAmount = $this->item->amount - $this->item->deposit_amount;
			}
			?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_DUE_AMOUNT'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo EventbookingHelper::formatCurrency($dueAmount, $this->config); ?>
				</div>
			</div>
		<?php
		}

		if ($this->canChangeStatus)
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_PAYMENT_STATUS'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['payment_status'];?>
				</div>
			</div>
		<?php
		}

		if ($this->canChangeStatus && $this->item->id && ($this->item->total_amount > 0 || $this->form->containFeeFields()))
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>" for="re_calculate_fee">
					<?php echo Text::_('EB_RE_CALCULATE_FEE'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<input type="checkbox" value="1" id="re_calculate_fee" name="re_calculate_fee" />
				</div>
			</div>
		<?php
		}

		if ($this->canChangeStatus && (!$this->item->id || $this->item->amount > 0))
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('EB_PAYMENT_METHOD'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['payment_method']; ?>
				</div>
			</div>
		<?php
		}

		// Members Information
		if ($this->config->collect_member_information && count($this->rowMembers))
		{
		?>
			<h3 class="eb-heading"><?php echo Text::_('EB_MEMBERS_INFORMATION') ; ?></h3>
		<?php
			for ($i = 0, $n = count($this->rowMembers); $i < $n; $i++)
			{
				$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($this->memberFormFields, $i + 1);
				$rowMember  = $this->rowMembers[$i];
				$memberId   = $rowMember->id;
				$form       = new RADForm($currentMemberFormFields);
				$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $currentMemberFormFields);

				if (!isset($memberData['country']))
				{
					$memberData['country'] = $this->config->default_country;
				}

				$form->setEventId($this->item->event_id);
				$form->bind($memberData);
				$form->setFieldSuffix($i + 1);

				if ($this->canChangeStatus)
				{
					$form->prepareFormFields('setRecalculateFee();');
				}

				$form->buildFieldsDependency();

				if ($i % 2 == 0)
				{
					echo "<div class=\"$rowFluid\">\n" ;
				}
				?>
				<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
					<h4><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i + 1); ?></h4>
					<?php
					$fields = $form->getFields();

					foreach ($fields as $field)
					{
						$fieldType = strtolower($field->type);

						switch ($fieldType)
						{
							case 'heading':
							case 'message':
								break;
							default:
								if (($field->fee_field && !$this->canChangeFeeFields) || $this->disableEdit)
								{
									$controlGroupAttributes = 'id="field_' . $field->name . '" ';

									if ($field->hideOnDisplay)
									{
										$controlGroupAttributes .= ' style="display:none;" ';
									}

									$class = '';

									if ($field->isMasterField)
									{
										if ($field->suffix)
										{
											$class = ' master-field-' . $field->suffix;
										}
										else
										{
											$class = ' master-field';
										}
									}
									?>
									<div class="<?php echo $controlGroupClass . $class; ?>" <?php echo $controlGroupAttributes; ?>>
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo $field->title; ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
	                                        <?php echo $field->getDisplayValue(); ?>
										</div>
									</div>
									<?php
								}
								else
								{
									echo $field->getControlGroup($bootstrapHelper);
								}
						}
					}
					?>
					<input type="hidden" name="ids[]" value="<?php echo $memberId; ?>" />
				</div>
				<?php
				if (($i + 1) % 2 == 0)
				{
					echo "</div>\n" ;
				}
			}
			if ($i % 2 != 0)
			{
				echo "</div>\n" ;
			}
		}
		?>
		<!-- End members information -->
		<input type="hidden" name="option" value="com_eventbooking"/>
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
		<input type="hidden" name="task" value="registrant.save"/>		
		<input type="hidden" name="return" value="<?php echo $this->return; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>

<?php
HTMLHelper::_('behavior.core');
Factory::getDocument()->addScriptOptions('selectedState', $selectedState)
	->addScriptOptions('rootUri', Uri::root(true))
	->addScriptDeclaration(' var siteUrl = "'.EventbookingHelper::getSiteUrl().'";');

EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-registrant-default.min.js');

Text::script('EB_CANCEL_REGISTRATION_CONFIRM', true);
Text::script('EB_REFUND_REGISTRATION_CONFIRM', true);