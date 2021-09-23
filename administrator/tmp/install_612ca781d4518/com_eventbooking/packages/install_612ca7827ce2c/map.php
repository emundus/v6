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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgEventBookingMap extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param $subject
	 * @param $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		Factory::getLanguage()->load('plg_eventbooking_map', JPATH_ADMINISTRATOR);
	}

	/**
	 * Display event location in a map
	 *
	 * @param $row
	 *
	 * @return array|string
	 */
	public function onEventDisplay($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from('#__eb_locations AS a')
			->innerJoin('#__eb_events AS b ON a.id = b.location_id')
			->where('b.id = ' . (int) $row->id);

		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			EventbookingHelperDatabase::getMultilingualFields($query, ['a.name', 'a.alias', 'a.description'], $fieldSuffix);
		}

		$db->setQuery($query);
		$location = $db->loadObject();

		$print = $this->app->input->getInt('print', 0);

		if (empty($location->address) || $print)
		{
			return '';
		}
		else
		{
			ob_start();

			$config = EventbookingHelper::getConfig();

			if ($config->get('map_provider', 'googlemap') == 'googlemap')
			{
				$this->drawMap($location);
			}
			else
			{
				$this->drawOpenStreetMap($location);
			}

			$form = ob_get_clean();

			return ['title'    => Text::_('PLG_EB_MAP'),
			        'form'     => $form,
			        'position' => $this->params->get('output_position', 'after_register_buttons'),
			];
		}
	}

	/**
	 * Display event location in a map
	 *
	 * @param $location
	 */
	private function drawMap($location)
	{
		$config           = EventbookingHelper::getConfig();
		$zoomLevel        = $config->zoom_level ? (int) $config->zoom_level : 14;
		$disableZoom      = $this->params->get('disable_zoom', 1) == 1 ? 'false' : 'true';
		$mapHeight        = $this->params->def('map_height', 500);
		$bubbleText       = "<ul class=\"bubble\">";
		$bubbleText       .= "<li class=\"location_name\"><h4>";
		$bubbleText       .= addslashes($location->name);
		$bubbleText       .= "</h4></li>";
		$bubbleText       .= "<li class=\"address\">" . addslashes($location->address) . "</li>";
		$getDirectionLink = 'https://maps.google.com/maps?daddr=' . str_replace(' ', '+', addslashes($location->address));
		$bubbleText       .= "<li class=\"address getdirection\"><a href=\"" . $getDirectionLink . "\" target=\"_blank\">" . Text::_('EB_GET_DIRECTION') . "</li>";
		$bubbleText       .= "</ul>";
		$session          = Factory::getSession();
		Factory::getDocument()->addScript('https://maps.googleapis.com/maps/api/js?key=' . $config->get('map_api_key', ''));
		?>
        <script type="text/javascript">
            Eb.jQuery(document).ready(function ($) {
				<?php
				if ($session->get('eb_device_type') == 'mobile')
				{
				?>
                var height = $(window).height() - 80;
                $("#map_canvas").height(height);
				<?php
				}
				?>

                var latlng = new google.maps.LatLng(<?php echo $location->lat ?>, <?php echo $location->long; ?>);
                var myOptions = {
                    zoom: <?php echo $zoomLevel; ?>,
                    streetViewControl: true,
                    scrollwheel: <?php echo $disableZoom; ?>,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
                var marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    title: "<?php echo addslashes($location->name); ?>"
                });
                google.maps.event.trigger(map, "resize");
                var contentString = '<?php echo $bubbleText; ?>';
                var infowindow = new google.maps.InfoWindow({
                    content: contentString
                });

                var openOptions = {
                    map: map,
                    shouldFocus: false
                };

                google.maps.event.addListener(marker, 'click', function () {
                    infowindow.open(openOptions, marker);
                });

                infowindow.open(openOptions, marker);
            });
        </script>
        <div id="mapform">
            <div id="map_canvas" style="width: 100%; height: <?php echo $mapHeight; ?>px"></div>
        </div>
		<?php
	}

	/**
	 * Display location on openstreetmap
	 *
	 * @param   EventbookingTableLocation  $location
	 */
	private function drawOpenStreetMap($location)
	{
		$rootUri = Uri::root(true);
		Factory::getDocument()
			->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
			->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css');

		$config    = EventbookingHelper::getConfig();
		$zoomLevel = (int) $config->zoom_level ? (int) $config->zoom_level : 14;
		$mapHeight = $this->params->def('map_height', 500);

		$popupContent   = [];
		$popupContent[] = '<h4 class="eb-location-name">' . $location->name . '</h4>';
		$popupContent[] = '<p class="eb-location-address">' . $location->address . '</p>';
		$popupContent   = addslashes(implode("", $popupContent));
		?>
        <script type="text/javascript">
            Eb.jQuery(document).ready(function ($) {
				<?php
				if (Factory::getSession()->get('eb_device_type') == 'mobile')
				{
				?>
                var height = $(window).height() - 80;
                $("#map_canvas").height(height);
				<?php
				}
				?>

                var mymap = L.map('map_canvas', {
                    center: [<?php echo $location->lat ?>, <?php echo $location->long; ?>],
                    zoom: <?php echo $zoomLevel; ?>,
                    zoomControl: true,
                    attributionControl: false,
                    scrollWheelZoom: false
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    id: 'mapbox.streets',
                }).addTo(mymap);

                var marker = L.marker([<?php echo $location->lat ?>, <?php echo $location->long;?>], {draggable: false}).addTo(mymap);
                marker.bindPopup('<?php echo $popupContent; ?>').openPopup();
            });
        </script>
        <div id="mapform">
            <div id="map_canvas" style="width: 100%; height: <?php echo $mapHeight; ?>px"></div>
        </div>
		<?php
	}
}
