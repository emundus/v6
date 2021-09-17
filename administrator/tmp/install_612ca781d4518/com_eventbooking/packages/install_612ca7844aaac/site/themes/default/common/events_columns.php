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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

EventbookingHelperJquery::equalHeights();

$return     = base64_encode(Uri::getInstance()->toString());
$timeFormat = $config->event_time_format ?: 'g:i a';
$dateFormat = $config->date_format;

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$btnInverseClass   = $bootstrapHelper->getClassMapping('btn-inverse');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$iconCalendarClass = $bootstrapHelper->getClassMapping('icon-calendar');
$iconMapMakerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
$clearfixClass     = $bootstrapHelper->getClassMapping('clearfix');
$btnPrimaryClass   = $bootstrapHelper->getClassMapping('btn-primary');
$btnBtnPrimary     = $bootstrapHelper->getClassMapping('btn btn-primary');

$linkThumbToEvent   = $config->get('link_thumb_to_event_detail_page', 1);

$numberColumns = Factory::getApplication()->getParams()->get('number_columns', 2);

if (!$numberColumns)
{
	$numberColumns = 2;
}

$baseUri      = Uri::base(true);
$span         = 'span' . intval(12 / $numberColumns);
$span         = $bootstrapHelper->getClassMapping($span);
$numberEvents = count($events);

if (!empty($category->id))
{
	$activeCategoryId = $category->id;
}
else
{
	$activeCategoryId = 0;
}

EventbookingHelperData::prepareDisplayData($events, $activeCategoryId, $config, $Itemid);
?>
<div id="eb-events" class="<?php echo $rowFluidClass . ' ' . $clearfixClass; ?> eb-columns-layout-container">
	<?php
        $rowCount = 0;

        for ($i = 0 ;  $i < $numberEvents ; $i++)
		{
			$event = $events[$i];

			if ($i % $numberColumns == 0)
			{
			    $rowCount++;
				$newRowClass = ' eb-first-child-of-new-row';
			}
			else
			{
				$newRowClass = '';
			}

			$cssClasses = ['eb-event-wrapper', 'eb-category-' . $event->category_id];

			if ($event->featured)
			{
				$cssClasses[] = 'eb-featured-event';
			}

			if ($event->published == 2)
			{
				$cssClasses[] = 'eb-cancelled-event';
			}

			$cssClasses[] = 'eb-event-box';
			$cssClasses[] = 'eb-event-' . $event->id;
			$cssClasses[] = $clearfixClass;
		?>
        <div class="<?php echo $span.$newRowClass; ?> eb-row-<?php echo $rowCount; ?>">
			<div class="<?php echo implode(' ', $cssClasses); ?>">
				<?php
				if (!empty($event->thumb_url))
				{
					if ($linkThumbToEvent)
					{
					?>
                        <a href="<?php echo $event->url; ?>"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
					<?php
					}
					else
					{
					?>
                        <a href="<?php echo $event->image_url; ?>" class="eb-modal"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
					<?php
					}
				}
				?>
				<h2 class="eb-event-title-container">
					<?php
					if ($config->hide_detail_button !== '1')
					{
					?>
						<a class="eb-event-title" href="<?php echo $event->url; ?>"><?php echo $event->title; ?></a>
					<?php
					}
					else
					{
						echo $event->title;
					}
					?>
				</h2>
				<div class="eb-event-date-time <?php echo $clearfixClass; ?>">
					<i class="<?php echo $iconCalendarClass; ?>"></i>
					<?php
					if ($event->event_date != EB_TBC_DATE)
					{
						echo HTMLHelper::_('date', $event->event_date, $dateFormat, null);
					}
					else
					{
						echo Text::_('EB_TBC');
					}

					if (strpos($event->event_date, '00:00:00') === false)
					{
					?>
						<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_date, $timeFormat, null) ?></span>
					<?php
					}

					if ($event->event_end_date != $nullDate)
					{
						if (strpos($event->event_end_date, '00:00:00') === false)
						{
							$showTime = true;
						}
						else
						{
							$showTime = false;
						}

						$startDate =  HTMLHelper::_('date', $event->event_date, 'Y-m-d', null);
						$endDate   = HTMLHelper::_('date', $event->event_end_date, 'Y-m-d', null);

						if ($startDate == $endDate)
						{
							if ($showTime)
							{
							?>
								-<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_end_date, $timeFormat, null) ?></span>
							<?php
							}
						}
						else
						{
							echo " - " .HTMLHelper::_('date', $event->event_end_date, $dateFormat, null);

							if ($showTime)
							{
							?>
								<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_end_date, $timeFormat, null) ?></span>
							<?php
							}
						}
					}
					?>
				</div>
				<div class="eb-event-location-price <?php echo $rowFluidClass . ' ' . $clearfixClass; ?>">
					<?php
					if ($event->location_id)
					{
					?>
						<div class="eb-event-location <?php echo $bootstrapHelper->getClassMapping('span9'); ?>">
							<i class="<?php echo $iconMapMakerClass; ?>"></i>
							<?php
							if ($event->location_address)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&tmpl=component'); ?>" class="eb-colorbox-map"><span><?php echo $event->location_name ; ?></span></a>
							<?php
							}
							else
							{
								echo $event->location_name;
							}
							?>
						</div>
					<?php
					}

					if ($config->show_discounted_price)
					{
						$price = $event->discounted_price;
					}
					else
					{
						$price = $event->individual_price;
					}

					if ($event->price_text)
					{
						$priceDisplay = $event->price_text;
					}
					elseif ($price > 0)
					{
						$symbol        = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
						$priceDisplay  = EventbookingHelper::formatCurrency($price, $config, $symbol);
					}
					elseif ($config->show_price_for_free_event)
					{
						$priceDisplay = Text::_('EB_FREE');
					}
					else
					{
						$priceDisplay = '';
					}

					if ($priceDisplay)
					{
					?>
						<div class="eb-event-price <?php echo $btnPrimaryClass . ' ' . $bootstrapHelper->getClassMapping('span3'); ?> pull-right">
							<span class="eb-individual-price"><?php echo $priceDisplay; ?></span>
						</div>
					<?php
					}
					?>
				</div>
				<div class="eb-event-short-description <?php echo $clearfixClass; ?>">
					<?php echo $event->short_description; ?>
				</div>
				<?php
				    // Event message to tell user that they already registered, need to login to register or don't have permission to register...
				    echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $event));
				?>
				<div class="eb-taskbar <?php echo $clearfixClass; ?>">
					<ul>
						<?php
						if ($config->get('show_register_buttons', 1) && !$event->is_multiple_date)
						{
							if ($event->can_register)
							{
								$registrationUrl = trim($event->registration_handle_url);

								if ($registrationUrl)
								{
								?>
									<li>
										<a class="<?php echo $btnBtnPrimary; ?>" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo Text::_('EB_REGISTER');; ?></a>
									</li>
								<?php
								}
								else
								{
									if (in_array($event->registration_type, [0, 1]))
									{
										if ($config->multiple_booking && !$event->has_multiple_ticket_types)
										{
											$url = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $event->id . '&Itemid=' . (int) $Itemid;

											if ($event->event_password)
											{
												$extraClass = '';
											}
											else
											{
												$extraClass = 'eb-colorbox-addcart';
											}

											$text = Text::_('EB_REGISTER');
										}
										else
										{
											$url = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl);

											if ($event->has_multiple_ticket_types)
											{
												$text = Text::_('EB_REGISTER');
											}
											else
											{
												$text = Text::_('EB_REGISTER_INDIVIDUAL');
											}

											$extraClass = '';
										}
										?>
                                            <li>
                                                <a class="<?php echo $btnBtnPrimary . ' ' . $extraClass; ?>" href="<?php echo $url; ?>"><?php echo $text; ?></a>
                                            </li>
										<?php
									}

									if (in_array($event->registration_type, [0, 2]) && !$config->multiple_booking && !$event->has_multiple_ticket_types)
									{
									?>
										<li>
											<a class="<?php echo $btnBtnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_GROUP');; ?></a>
										</li>
									<?php
									}
								}
							}
                            elseif ($event->waiting_list && $event->registration_type != 3 && !EventbookingHelperRegistration::isUserJoinedWaitingList($event->id))
							{
								if ($event->waiting_list_capacity == 0)
								{
									$numberWaitingListAvailable = 1000; // Fake number
								}
								else
								{
									$numberWaitingListAvailable = max($event->waiting_list_capacity - EventbookingHelperRegistration::countNumberWaitingList($event), 0);
								}

								if (in_array($event->registration_type, [0, 1]) && $numberWaitingListAvailable)
								{
								?>
									<li>
										<a class="<?php echo $btnBtnPrimary; ?>"
										   href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST');; ?></a>
									</li>
								<?php
								}

								if (in_array($event->registration_type, [0, 2]) && $numberWaitingListAvailable > 1 && !$config->multiple_booking)
								{
								?>
									<li>
										<a class="<?php echo $btnBtnPrimary; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_GROUP_WAITING_LIST');; ?></a>
									</li>
								<?php
								}
							}
						}

						if ($config->hide_detail_button !== '1' || $event->is_multiple_date)
						{
						?>
							<li>
								<a class="<?php echo $btnClass ?>" href="<?php echo $event->url; ?>">
									<?php echo $event->is_multiple_date ? Text::_('EB_CHOOSE_DATE_LOCATION') : Text::_('EB_DETAILS');?>
								</a>
							</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>
        </div>
		<?php
		}
	?>
</div>
<?php

// Add Google Structured Data
PluginHelper::importPlugin('eventbooking');
Factory::getApplication()->triggerEvent('onDisplayEvents', [$events]);

$equalHeightScript[] = 'Eb.jQuery(window).load(function() {';

for ($i = 1; $i <= $rowCount; $i++)
{
	$equalHeightScript[] = 'Eb.jQuery(".eb-row-' . $i . ' .eb-event-wrapper").equalHeights(250);';
}

$equalHeightScript[] = '});';

Factory::getDocument()->addScriptDeclaration(implode("\r\n", $equalHeightScript));