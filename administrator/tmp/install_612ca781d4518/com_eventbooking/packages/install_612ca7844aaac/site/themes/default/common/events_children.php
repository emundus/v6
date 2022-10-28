<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$btnPrimary             = $bootstrapHelper->getClassMapping('btn btn-primary');

$loginLink          = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()), false);
$loginToRegisterMsg = str_replace('[LOGIN_LINK]', $loginLink, Text::_('EB_LOGIN_TO_REGISTER'));
?>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed eb-responsive-table">
	<thead>
		<tr>
		<th class="date_col">
			<?php echo Text::_('EB_EVENT_DATE'); ?>
		</th>
		<?php
			if ($config->show_event_end_date_in_table_layout)
			{
			?>
				<th class="date_col">
					<?php echo Text::_('EB_EVENT_END_DATE'); ?>
				</th>
			<?php
			}

			if ($config->show_location_in_category_view)
			{
			?>
				<th class="location_col">
					<?php echo Text::_('EB_LOCATION'); ?>
				</th>
			<?php
			}

			if ($config->show_price_in_table_layout)
			{
			?>
				<th class="table_price_col">
					<?php echo Text::_('EB_INDIVIDUAL_PRICE'); ?>
				</th>
			<?php
			}

			if ($config->show_capacity)
			{
			?>
				<th class="capacity_col">
					<?php echo Text::_('EB_CAPACITY'); ?>
				</th>
			<?php
			}

			if ($config->show_registered)
			{
			?>
				<th class="registered_col">
					<?php echo Text::_('EB_REGISTERED'); ?>
				</th>
			<?php
			}

			if ($config->show_available_place)
			{
			?>
				<th class="center available-place-col">
					<?php echo Text::_('EB_AVAILABLE_PLACE'); ?>
				</th>
			<?php
			}
			?>
			<th class="center actions-col">
				<?php echo Text::_('EB_REGISTER'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
		for ($i = 0 , $n = count($items) ; $i < $n; $i++)
		{
			$item = $items[$i] ;
			$canRegister = EventbookingHelper::callOverridableHelperMethod('Registration', 'acceptRegistration', [$item]);

			if ($item->activate_waiting_list == 2)
			{
				$activateWaitingList = $config->activate_waitinglist_feature;
			}
			else
			{
				$activateWaitingList = $item->activate_waiting_list;
			}

			if ($item->cut_off_date != $nullDate)
			{
				$registrationOpen = ($item->cut_off_minutes < 0);
			}
			elseif (isset($item->event_start_minutes))
            {
	            $registrationOpen = ($item->event_start_minutes < 0);
            }
			else
			{
				$registrationOpen = ($item->number_event_dates > 0);
			}

			if (($item->event_capacity > 0) && ($item->event_capacity <= $item->total_registrants) && $activateWaitingList && !$item->user_registered && $registrationOpen)
			{
				$waitingList = true ;
			}
			else
			{
				$waitingList = false ;
			}
		?>
			<tr>
				<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_EVENT_DATE'); ?>">
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

							echo HTMLHelper::_('date', $item->event_date, $dateFormat, null);
						}
					?>
				</td>
				<?php
					if ($config->show_event_end_date_in_table_layout)
					{
					?>
						<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_EVENT_END_DATE'); ?>">
							<?php
								if ($item->event_end_date == EB_TBC_DATE)
								{
									echo Text::_('EB_TBC');
								}
								else
								{
									if (strpos($item->event_end_date, '00:00:00') !== false)
									{
										$dateFormat = $config->date_format;
									}
									else
									{
										$dateFormat = $config->event_date_format;
									}

									echo HTMLHelper::_('date', $item->event_end_date, $dateFormat, null);
								}
							?>
						</td>
					<?php
					}

					if ($config->show_location_in_category_view)
					{
					?>
					<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_LOCATION'); ?>">
						<?php
							if ($item->location_id)
							{
								if ($item->location_address)
								{
								?>
									<a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$item->location_id.'&Itemid='.$Itemid.'&tmpl=component'); ?>" class="eb-colorbox-map"><?php echo $item->location_name ; ?></a>
								<?php
								}
								else
								{
									echo $item->location_name;
								}
							}
							else
							{
								echo '';
							}
						?>
					</td>
					<?php
					}

					if ($config->show_price_in_table_layout)
					{
						if ($item->price_text)
						{
							$price = $item->price_text;
						}
						elseif ($config->show_discounted_price)
						{
							$price = EventbookingHelper::formatCurrency($item->discounted_price, $config, $item->currency_symbol);
						}
						else
						{
							$price = EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
						}
						?>
							<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_INDIVIDUAL_PRICE'); ?>">
								<?php echo $price; ?>
							</td>
						<?php
					}

					if ($config->show_capacity)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_CAPACITY'); ?>">
							<?php
								if ($item->event_capacity)
								{
									echo $item->event_capacity ;
								}
								elseif ($config->show_capacity != 2)
								{
									echo Text::_('EB_UNLIMITED') ;
								}
							?>
						</td>
					<?php
					}

					if ($config->show_registered)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_REGISTERED'); ?>">
							<?php
								if ($item->registration_type != 3)
								{
									echo $item->total_registrants ;
								}
								else
								{
									echo ' ';
								}

                                if ($config->show_list_of_registrants && ($item->total_registrants > 0) && EventbookingHelperAcl::canViewRegistrantList($item->id))
                                {
                                ?>
                                    <a href="<?php echo Route::_('index.php?option=com_eventbooking&view=registrantlist&id=' . $item->id . '&tmpl=component'); ?>"
                                       class="eb-colorbox-register-lists"><span class="view_list"><?php echo Text::_("EB_VIEW_LIST"); ?></span>
                                    </a>
                                <?php
                                }
							?>
						</td>
					<?php
					}

					if ($config->show_available_place)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_AVAILABLE_PLACE'); ?>">
							<?php
								if ($item->event_capacity)
								{
									echo $item->event_capacity - $item->total_registrants;
								}
							?>
						</td>
					<?php
					}
				?>
					<td class="center tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_REGISTER'); ?>">
						<?php
							if ($waitingList || $canRegister || ($item->registration_type != 3 && $config->display_message_for_full_event))
							{
								if ($canRegister)
								{
								?>
								<div class="eb-taskbar">
									<ul>
										<?php
											$registrationUrl = trim($item->registration_handle_url);

											if ($registrationUrl)
											{
											?>
												<li>
													<a class="<?php echo $btnPrimary; ?>" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo Text::_('EB_REGISTER');; ?></a>
												</li>
											<?php
											}
											else
											{
												if ($item->registration_type == 0 || $item->registration_type == 1)
												{
													if ($config->multiple_booking)
													{
														$url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $Itemid;

														if ($item->event_password)
														{
															$extraClass = '';
														}
														else
														{
															$extraClass = 'eb-colorbox-addcart';
														}
														$text       = Text::_('EB_REGISTER');
													}
													else
													{
														$url        = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl);
														$text       = Text::_('EB_REGISTER_INDIVIDUAL');
														$extraClass = '';
													}
												?>
													<li>
														<a class="<?php echo $btnPrimary . ' ' . $extraClass;?>" href="<?php echo $url; ?>"><?php echo $text; ?></a>
													</li>
												<?php
												}

												if ($item->min_group_number > 0)
												{
													$minGroupNumber = $item->min_group_number;
												}
												else
												{
													$minGroupNumber = 2;
												}

												if ($item->event_capacity > 0 && (($item->event_capacity - $item->total_registrants) < $minGroupNumber))
												{
													$groupRegistrationAvailable = false;
												}
												else
												{
													$groupRegistrationAvailable = true;
												}

												if ($groupRegistrationAvailable && ($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking)
												{
												?>
													<li>
														<a class="<?php echo $btnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo Text::_('EB_REGISTER_GROUP');; ?></a>
													</li>
												<?php
												}
											}
										?>
									</ul>
								</div>
								<?php
								}
								elseif($waitingList)
								{
								?>
								<div class="eb-taskbar">
									<ul>
										<?php
										if ($item->registration_type == 0 || $item->registration_type == 1)
										{
										?>
											<li>
												<a class="<?php echo $btnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo Text::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
											</li>
										<?php
										}

										if (($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking)
										{
										?>
											<li>
												<a class="<?php echo $btnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo Text::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
											</li>
										<?php
										}
										?>
									</ul>
								</div>
								<?php
								}
								elseif($item->registration_type != 3 && $config->display_message_for_full_event && !$waitingList && $item->registration_start_minutes >= 0)
								{
									if (@$item->user_registered)
									{
										$msg = Text::_('EB_YOU_REGISTERED_ALREADY');
									}
                                    elseif ($item->event_capacity && ($item->total_registrants >= $item->event_capacity))
									{
										$msg = Text::_('EB_EVENT_IS_FULL');
									}
                                    elseif (!in_array($item->registration_access, $viewLevels))
									{
										if (Factory::getUser()->id)
										{
											$msg = Text::_('EB_REGISTRATION_NOT_AVAILABLE_FOR_ACCOUNT');
										}
										else
										{
											$msg = $loginToRegisterMsg;
										}
									}
									else
									{
										$msg = Text::_('EB_NO_LONGER_ACCEPT_REGISTRATION');
									}
								?>
									<div class="eb-notice-message">
										<?php echo $msg ; ?>
									</div>
								<?php
								}
							}
						?>
					</td>
			</tr>
			<?php
		}
	?>
	</tbody>
</table>