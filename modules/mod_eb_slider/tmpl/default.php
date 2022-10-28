<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$document = Factory::getDocument();
$rootUri = Uri::root(true);
$document->addStyleSheet($rootUri . '/modules/mod_eb_slider/assets/tinyslier/tiny-slider.css');
$document->addStyleSheet($rootUri . '/modules/mod_eb_slider/assets/css/styles.css');
$document->addScript($rootUri.'/modules/mod_eb_slider/assets/tinyslier/tiny-slider.js');

$config = EventbookingHelper::getConfig();
$return     = base64_encode(Uri::getInstance()->toString());
$timeFormat = $config->event_time_format ?: 'g:i a';
$dateFormat = $config->date_format;

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
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


$activeCategoryId = 0;

EventbookingHelperData::prepareDisplayData($rows, $activeCategoryId, $config, $itemId);
?>
<div class="eb-slider-container">
    <ul class="controls" id="customize-controls" aria-label="Carousel Navigation" tabindex="0">
        <li class="prev" data-controls="prev" aria-controls="customize" tabindex="-1">
            <i class="fa fa-angle-left fa-5x"></i>
        </li>
        <li class="next" data-controls="next" aria-controls="customize" tabindex="-1">
            <i class="fa fa-angle-right fa-5x"></i>
        </li>
    </ul>
    <div class="my-eb-slider">
        <?php
            foreach ($rows as $event)
            {
	            require JModuleHelper::getLayoutPath('mod_eb_slider', 'default_item');
            }
        ?>
    </div>
</div>
<script>
    const slider = tns(<?php echo json_encode($sliderSettings) ?>);
</script>