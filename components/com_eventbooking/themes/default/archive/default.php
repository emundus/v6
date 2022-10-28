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

if (!empty($this->category->id))
{
	$activeCategoryId = $this->category->id;
}
else
{
	$activeCategoryId = 0;
}

$linkThumbToEvent = $this->config->get('link_thumb_to_event_detail_page', 1);

EventbookingHelperData::prepareDisplayData($this->items, $activeCategoryId, $this->config, $this->Itemid);

$nullDate = Factory::getDbo()->getNullDate();
$config   = $this->config;
?>
<div id="eb-events-archive-page" class="eb-container">
<h1 class="eb-page-heading"><?php echo $this->params->get('page_heading') ?: Text::_('EB_EVENTS_ARCHIVE'); ?></h1>
<?php
if ($this->category)
{
?>
	<div id="eb-category">
		<h2 class="eb-page-heading"><?php echo $this->escape($this->category->name);?></h2>
		<?php
		if($this->category->description != '')
		{
		?>
			<div class="eb-description"><?php echo $this->category->description;?></div>
		<?php
		}
		?>
	</div>
	<div class="clearfix"></div>
<?php
}

if(count($this->items))
{
	$rowFluidClass = $this->bootstrapHelper->getClassMapping('row-fluid');
	$span7Class    = $this->bootstrapHelper->getClassMapping('span7');
	$span5Class    = $this->bootstrapHelper->getClassMapping('span5');
	$btnClass      = $this->bootstrapHelper->getClassMapping('btn');
?>
	<div id="eb-events">
	<?php
		for ($i = 0 , $n = count($this->items) ;  $i < $n ; $i++)
		{
			$event = $this->items[$i] ;
		?>
			<div class="eb-event">
				<div class="eb-box-heading clearfix">
					<h3 class="eb-event-title pull-left">
						<a href="<?php echo $event->url; ?>" title="<?php echo $event->title; ?>" class="eb-event-title-link">
							<?php echo $event->title; ?>
						</a>
					</h3>
				</div>
				<div class="eb-description">
					<div class="<?php echo $rowFluidClass; ?>">
						<div class="eb-description-details <?php echo $span7Class; ?>">
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

							//output event description
							if (!$event->short_description)
							{
								$event->short_description = $event->description ;
							}

							echo $event->short_description ;
							?>
						</div>
						<div class="<?php echo $span5Class; ?>">
							<table class="table table-bordered table-striped">
								<tr class="eb-event-property">
									<td class="eb-event-property-label">
										<?php echo Text::_('EB_EVENT_DATE'); ?>
									</td>
									<td class="eb-event-property-value">
										<?php
										if ($event->event_date == EB_TBC_DATE)
										{
											echo Text::_('EB_TBC');
										}
										else
										{
											echo HTMLHelper::_('date', $event->event_date, $config->event_date_format, null) ;
										}
										?>
									</td>
								</tr>
								<?php
								if ($event->event_end_date != $nullDate)
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<?php echo Text::_('EB_EVENT_END_DATE'); ?>
										</td>
										<td class="eb-event-property-value">
											<?php echo HTMLHelper::_('date', $event->event_end_date, $config->event_date_format, null) ; ?>
										</td>
									</tr>
								<?php
								}
								if ($event->cut_off_date != $nullDate)
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<?php echo Text::_('EB_CUT_OFF_DATE'); ?>
										</td>
										<td class="eb-event-property-value">
											<?php echo HTMLHelper::_('date', $event->cut_off_date, $config->event_date_format, null) ; ?>
										</td>
									</tr>
								<?php
								}
								if ($config->show_capacity == 1 || ($config->show_capacity == 2 && $event->event_capacity))
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<?php echo Text::_('EB_CAPACTIY'); ?>:
										</td>
										<td class="eb-event-property-value">
											<?php
											if ($event->event_capacity)
											{
												echo $event->event_capacity ;
											}
											else
											{
												echo Text::_('EB_UNLIMITED') ;
											}
											?>
										</td>
									</tr>
								<?php
								}
								if ($config->show_registered)
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<?php echo Text::_('EB_REGISTERED'); ?>
										</td>
										<td class="eb-event-property-value">
											<?php echo (int) $event->total_registrants ; ?>
											<?php
											if ($config->show_list_of_registrants && ($event->total_registrants > 0) && EventbookingHelperAcl::canViewRegistrantList($event->id)) {
											?>
												&nbsp;&nbsp;&nbsp;<a href="index.php?option=com_eventbooking&view=registrantlist&id=<?php echo $event->id ?>&tmpl=component" class="eb-colorbox-register-lists"><span class="view_list"><?php echo Text::_("EB_VIEW_LIST"); ?></span></a>
											<?php
											}
											?>
										</td>
									</tr>
								<?php
								}
								if ($config->show_available_place && $event->event_capacity)
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<?php echo Text::_('EB_AVAILABLE_PLACE'); ?>
										</td>
										<td class="eb-event-property-value">
											<?php echo $event->event_capacity - $event->total_registrants ; ?>
										</td>
									</tr>
								<?php
								}
								if (($event->individual_price > 0) || ($config->show_price_for_free_event))
								{
									$showPrice = true;
								}
								else
								{
									$showPrice = false;
								}
								if ($config->show_discounted_price && ($event->individual_price != $event->discounted_price))
								{
									if ($showPrice)
									{
									?>
										<tr class="eb-event-property">
											<td class="eb-event-property-label">
												<?php echo Text::_('EB_ORIGINAL_PRICE'); ?>
											</td>
											<td class="eb-event-property-value">
												<?php
												if ($event->individual_price > 0)
												{
													echo EventbookingHelper::formatCurrency($event->individual_price, $config, $event->currency_symbol);
												}
												else
												{
													echo '<span class="eb_price">'.Text::_('EB_FREE').'</span>' ;
												}
												?>
											</td>
										</tr>
										<tr class="eb-event-property">
											<td class="eb-event-property-label">
												<?php echo Text::_('EB_DISCOUNTED_PRICE'); ?>
											</td>
											<td class="eb-event-property-value">
												<?php
												if ($event->discounted_price > 0)
												{
													echo EventbookingHelper::formatCurrency($event->discounted_price, $config, $event->currency_symbol);
												}
												else
												{
													echo '<span class="eb_price">' . Text::_('EB_FREE') . '</span>';
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
										<tr class="eb-event-property">
											<td class="eb-event-property-label">
												<?php echo Text::_('EB_INDIVIDUAL_PRICE'); ?>
											</td>
											<td class="eb-event-property-value">
												<?php
												if ($event->price_text)
												{
													echo $event->price_text;
												}
                                                elseif ($event->individual_price > 0)
												{
													echo EventbookingHelper::formatCurrency($event->individual_price, $config, $event->currency_symbol);
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

								if (isset($event->paramData))
								{
									foreach ($event->paramData as $paramItem)
									{
										if ($paramItem['value'])
										{
										?>
											<tr class="eb-event-property">
												<td class="eb-event-property-label">
													<?php echo $paramItem['title']; ?>
												</td>
												<td class="eb-event-property-value">
													<?php
													echo $paramItem['value'];
													?>
												</td>
											</tr>
										<?php
										}
										?>
									<?php
									}
								}
								if ($event->location_id && $config->show_location_in_category_view)
								{
								?>
									<tr class="eb-event-property">
										<td class="eb-event-property-label">
											<strong><?php echo Text::_('EB_LOCATION'); ?>:</strong>
										</td>
										<td class="eb-event-property-value">
											<a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id); ?>" class="eb-colorbox-map"><?php echo $event->location_name ; ?></a>
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
					</div>
					<div class="eb-taskbar clearfix">
						<ul>
							<li>
								<a class="<?php echo $btnClass; ?> btn-primary" href="<?php echo $event->url; ?>">
									<?php echo Text::_('EB_DETAILS'); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
	<?php
	if ($this->pagination->total > $this->pagination->limit)
	{
	?>
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php
	}
	?>
<?php
}
?>
</div>