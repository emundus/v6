<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');

$height      = (int) $this->config->map_height ?: 600;
$height      += 20;
$zoomLevel   = (int) $this->config->zoom_level ?: 14;
$coordinates = $this->location->lat . ',' . $this->location->long;

if ($this->location->image || EventbookingHelper::isValidMessage($this->location->description))
{
	$onPopup = false;
}
else
{
	$onPopup = true;
}

$popupContent   = [];
$popupContent[] = '<h4>' . addslashes($this->location->name) . '</h4>';
$popupContent[] = '<p>' . addslashes($this->location->address) . '</p>';
$popupContent   = implode('', $popupContent);

if ((float) $this->location->lat != 0 || (float) $this->location->long != 0)
{
	$showMap = true;
}
else
{
	$showMap = false;
}

if ($showMap)
{
	$rootUri = Uri::root(true);
	Factory::getDocument()->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
		->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css')
		->addScriptOptions('lat', $this->location->lat)
		->addScriptOptions('long', $this->location->long)
		->addScriptOptions('zoomLevel', $zoomLevel)
		->addScriptOptions('popupContent', $popupContent);

	EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-map-openstreetmap.min.js');
}
?>
<div id="eb-event-map-page" class="eb-container">
	<?php
	if (!$onPopup)
	{
	?>
		<h1 class="eb-page-heading"><?php echo $this->escape($this->location->name); ?></h1>
	<?php
	}

	if ($this->location->image && file_exists(JPATH_ROOT . '/' . $this->location->image))
	{
	?>
		<img src="<?php echo Uri::root(true) . '/' . $this->location->image; ?>" class="eb-venue-image img-polaroid" />
	<?php
	}

	if (EventbookingHelper::isValidMessage($this->location->description))
	{
	?>
		<div class="eb-location-description"><?php echo $this->location->description; ?></div>
	<?php
	}

	if ($showMap)
    {
    ?>
        <div id="eb_location_map" style="height:<?php echo $height; ?>px; width:100%;"></div>
    <?php
    }
	?>
</div>
