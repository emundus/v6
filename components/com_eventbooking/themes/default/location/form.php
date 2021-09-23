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
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core') ;

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$span7Class        = $bootstrapHelper->getClassMapping('span7');
$span5Class        = $bootstrapHelper->getClassMapping('span5');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');

$config      = EventbookingHelper::getConfig();
$mapApiKye   = $config->get('map_api_key', '');
$mapProvider = $config->get('map_provider', 'googlemap');

if ($this->item->id)
{
	$coordinates = $this->item->lat . ',' . $this->item->long;
}
elseif (trim($config->center_coordinates))
{
	$coordinates = trim($config->center_coordinates);
}
else
{
	if ($mapProvider === 'googlemap')
	{
		$http     = HttpFactory::getHttp();
		$url      = "https://maps.googleapis.com/maps/api/geocode/json?address=" . str_replace(' ', '+', $config->default_country) . "&key=" . $mapApiKye;
		$response = $http->get($url);

		if ($response->code == 200)
		{
			$output_deals = json_decode($response->body);
			$latLng       = $output_deals->results[0]->geometry->location;
			$coordinates  = $latLng->lat . ',' . $latLng->lng;
		}
		else
		{
			$coordinates = '37.09024,-95.712891';
		}
	}
	else
	{
		$coordinates = '37.09024,-95.712891';
	}
}

$coordinates = explode(',', $coordinates);
$zoomLevel   = (int) $config->zoom_level ?: 14;

$document = Factory::getDocument();
$rootUri = Uri::root(true);

if ($mapProvider === 'googlemap')
{
	$document->addScript('https://maps.google.com/maps/api/js?key='.$mapApiKye);

	EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-location-form.min.js');
}
else
{
	$document->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
	->addScript($rootUri . '/media/com_eventbooking/assets/js/autocomplete/jquery.autocomplete.min.js')
	->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css')
	->addScriptOptions('baseUri', Uri::base(true));

	EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-location-openstreetmap.min.js');
}

$document->addScriptOptions('coordinates', $coordinates)
	->addScriptOptions('zoomLevel', $zoomLevel);

$languageItems = [
    'EB_ENTER_LOCATION_NAME',
    'EB_DELETE_LOCATION_CONFIRM'
];

EventbookingHelperHtml::addJSStrings($languageItems);
?>
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_ADD_EDIT_LOCATION')); ?></h1>
<form action="<?php echo Route::_('index.php?option=com_eventbooking&view=location', false); ?>" method="post" name="adminForm" id="adminForm" class="form">
<div class="row-fluid">
    <div  class="<?php echo $span5Class ?>">
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo Text::_('EB_NAME'); ?>
    			<span class="required">*</span>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
    			<input class="text_area" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->escape($this->item->name);?>" />
    		</div>
    	</div>
    
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo Text::_('EB_ADDRESS'); ?>
    			<span class="required">*</span>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
      	         <input class="input-xlarge" type="text" name="address" id="address" size="70" autocomplete="off" maxlength="250" value="<?php echo $this->escape($this->item->address);?>" />
    			<ul id="eventmaps_results" style="display:none;"></ul>
    		</div>
    	</div>
	    <?php
	    if (EventbookingHelper::isModuleEnabled('mod_eb_cities'))
	    {
		?>
		    <div class="<?php echo $controlGroupClass;  ?>">
                <label class="<?php echo $controlLabelClass; ?>">
				    <?php echo Text::_('EB_CITY'); ?>
			    </label>
                <div class="<?php echo $controlsClass; ?>">
				    <input class="text_area" type="text" name="city" id="city" size="30" maxlength="250" value="<?php echo $this->escape($this->item->city);?>" />
			    </div>
		    </div>
		<?php
	    }

	    if (EventbookingHelper::isModuleEnabled('mod_eb_states'))
	    {
		?>
            <div class="<?php echo $controlGroupClass;  ?>">
                <label class="<?php echo $controlLabelClass; ?>">
				    <?php echo Text::_('EB_STATE'); ?>
			    </label>
                <div class="<?php echo $controlsClass; ?>">
				    <input class="text_area" type="text" name="state" id="state" size="30" maxlength="250" value="<?php echo $this->escape($this->item->state);?>" />
			    </div>
		    </div>
		<?php
	    }
	    ?>
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo Text::_('EB_COORDINATES'); ?>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
    			<input class="text_area" type="text" name="coordinates" id="coordinates" size="30" maxlength="250" value="<?php echo $this->item->lat.','.$this->item->long;?>" />
    		</div>
    	</div>
    
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo Text::_('EB_PUBLISHED') ; ?>
    		</label>
		    <?php echo $this->lists['published']; ?>
    	</div>

    	<div class="form-actions">
    		<input type="button" class="<?php echo $btnPrimary; ?>" id="btn-save-location" name="btnSave" value="<?php echo Text::_('EB_SAVE'); ?>" />
    		<?php
    			if ($this->item->id)
    			{
    			?>
    				<input type="button" class="<?php echo $btnPrimary; ?>" id="btn-delete-location" name="btnDelete" value="<?php echo Text::_('EB_DELETE_LOCATION'); ?>" />
    			<?php
    			}
    		?>
    		<input type="button" class="<?php echo $btnPrimary; ?>" id="btn-cancel" name="btnCancel" value="<?php echo Text::_('EB_CANCEL_LOCATION'); ?>" />
    	</div>
     </div>
     <div class="<?php echo $span7Class ?>">
        <div class="<?php echo $controlGroupClass;  ?>">
            <?php
                if ($mapProvider === 'googlemap')
                {
                ?>
                    <input type="button" id="btn-get-location-from-address" value="<?php echo Text::_('EB_PINPOINT'); ?> &raquo;" />
                    <br/><br/>
                <?php
                }
            ?>
            <div id="map-canvas" style="width: 95%; height: 400px"></div>
    	</div>
     </div>
</div>
	<div class="clearfix"></div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>

    <?php
        if ($this->item->id)
        {
        ?>
            <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
        <?php
        }
    ?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>