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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if (count($rows))
{
	$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

	$rowFluidClass      = $bootstrapHelper->getClassMapping('row-fluid');
	$span3Class         = $bootstrapHelper->getClassMapping('span3');
	$span9Class         = $bootstrapHelper->getClassMapping('span9');
	$iconFolderClass    = $bootstrapHelper->getClassMapping('icon-folder-open');
	$iconMapMarkerClass = $bootstrapHelper->getClassMapping('icon-map-marker');

    $monthNames = array(
        1 => Text::_('JANUARY_SHORT'),
        2 => Text::_('FEBRUARY_SHORT'),
        3 => Text::_('MARCH_SHORT'),
        4 => Text::_('APRIL_SHORT'),
        5 => Text::_('MAY_SHORT'),
        6 => Text::_('JUNE_SHORT'),
        7 => Text::_('JULY_SHORT'),
        8 => Text::_('AUGUST_SHORT'),
        9 => Text::_('SEPTEMBER_SHORT'),
        10 => Text::_('OCTOBER_SHORT'),
        11 => Text::_('NOVEMBER_SHORT'),
        12 => Text::_('DECEMBER_SHORT')
    );
?>
    <ul class="ebm-upcoming-events ebm-upcoming-events-improved">
        <?php
        $k = 0 ;
        $baseUri = Uri::base(true);

        foreach ($rows as  $row)
        {
            $k = 1 - $k ;
            $date = HTMLHelper::_('date', $row->event_date, 'd', null);
            $month = HTMLHelper::_('date', $row->event_date, 'n', null);

	        if ($linkToRegistrationForm && EventbookingHelperRegistration::acceptRegistration($row))
	        {
		        if ($row->registration_handle_url)
		        {
			        $url = $row->registration_handle_url;
		        }
		        else
		        {
			        $url = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $row->id . '&Itemid=' . $itemId);
		        }
	        }
	        else
	        {
		        $url = Route::_(EventbookingHelperRoute::getEventRoute($row->id, $row->main_category_id, $itemId));
	        }
        ?>
            <li class="<?php echo $rowFluidClass; ?>">
                <div class="<?php echo $span3Class; ?>">
                    <div class="ebm-event-date">
                        <?php
                            if ($row->event_date == '2099-12-31 00:00:00')
                            {
                                echo Text::_('EB_TBC');
                            }
                            else
                            {
                            ?>
                                <div class="ebm-event-month"><?php echo $monthNames[$month];?></div>
                                <div class="ebm-event-day"><?php echo $date; ?></div>
                            <?php
                            }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $span9Class; ?>">
                    <?php
                        if ($titleLinkable)
                        {
                        ?>
                            <a class="url ebm-event-link" href="<?php echo $url; ?>">
		                        <?php
		                        if ($showThumb && $row->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$row->thumb))
		                        {
			                    ?>
                                    <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>" class="ebm-event-thumb" />
			                    <?php
		                        }

		                        echo $row->title;
		                        ?>
                            </a>
                        <?php
                        }
                        else
                        {
                            if ($showThumb && $row->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$row->thumb))
                            {
                            ?>
                                <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>" class="ebm-event-thumb" />
                            <?php
                            }

                            echo $row->title;
                        }

                        if ($showCategory)
                        {
                        ?>
                            <br />
                            <i class="<?php echo $iconFolderClass; ?>"></i>
                            <span class="ebm-event-categories"><?php echo $row->categories ; ?></span>
                        <?php
                        }

                        if ($showLocation && strlen($row->location_name))
                        {
                        ?>
                            <br />
                            <i class="<?php echo $iconMapMarkerClass; ?>"></i>
                            <?php
                            if ($row->location_address)
                            {
                            ?>
                                <a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$row->location_id.'&tmpl=component&format=html&Itemid='.$itemId); ?>" class="eb-colorbox-map">
                                    <?php echo $row->location_name ; ?>
                                </a>
                            <?php
                            }
                            else
                            {
                            ?>
                                <span class="ebm-location-name"><?php echo $row->location_name; ?></span>
                            <?php
                            }
                        }

                        if ($showPrice)
                        {
	                        $price = $row->price_text ?: EventbookingHelper::formatCurrency($row->individual_price, $config);
                        ?>
                            <br/>
                            <?php echo '<strong>'.Text::_('EB_PRICE').'</strong>' . ': ' . EventbookingHelper::formatCurrency($row->individual_price, $config); ?>
                        <?php
                        }
                        ?>
                </div>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
else
{
?>
    <div class="eb_empty"><?php echo Text::_('EB_NO_UPCOMING_EVENTS') ?></div>
<?php
}