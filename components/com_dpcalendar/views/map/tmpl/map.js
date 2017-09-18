jQuery(document).ready(function() {
	jQuery('#filter-location, #radius, #length_type, #ids').bind('change', function(e) {
		updateMainLocationFrame();
	});
});

function updateMainLocationFrame() {
	var data = {};
	var location = jQuery('#filter-location').val();
	data['filter-location'] = jQuery('#filter-location').val();
	if (data['filter-location'] == '' && false) {
		return;
	}
	data['ids'] = jQuery('#ids').val().split(',');
	data['filter-radius'] = jQuery('#radius').val();
	data['filter-length_type'] = jQuery('#length_type').val();
	jQuery.ajax({
		type : 'post',
		url : 'index.php?option=com_dpcalendar&view=map&layout=events&format=raw&Itemid=' + jQuery('#Itemid').val(),
		data : data,
		success : function(response) {
			var json = jQuery.parseJSON(response);

			var mapElement = jQuery('#event-map');
			if (mapElement.data('markers') != null) {
				jQuery.each(mapElement.data('markers'), function(i, marker) {
					marker.setMap(null);
				});
			}

			var lat = mapElement.data('lat');
			var long = mapElement.data('long');
			var dpcalendarMap = new google.maps.Map(document.getElementById('event-map'), {
				zoom : mapElement.data('zoom'),
				mapTypeId : google.maps.MapTypeId.ROADMAP,
				center : new google.maps.LatLng(lat, long),
				draggable : jQuery(document).width() > 480 ? true : false,
				scrollwheel : jQuery(document).width() > 480 ? true : false
			});
			var dpcalendarMapBounds = new google.maps.LatLngBounds();
			if (jQuery('#system-message-container').length) {
				if (json.length == 0) {
					Joomla.renderMessages({
						'info' : [ Joomla.JText._('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT') ]
					});
				} else {
					Joomla.removeMessages();
				}
			}
			var markers = [];

			var chartUrl = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
			if (document.location.protocol == 'https:') {
				chartUrl = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
			}
			for (index in json) {
				var event = json[index];

				if (event === null || typeof event !== 'object') {
					continue;
				}

				jQuery.each(event.location, function(i, loc) {
					if (loc.latitude == null || loc.latitude == "") {
						return;
					}
					var l = new google.maps.LatLng(loc.latitude, loc.longitude);
					var marker = new google.maps.Marker({
						position : l,
						map : dpcalendarMap,
						title : event.title,
						icon : {
							url : chartUrl + event.color.toString().replace('#', ''),
							size : new google.maps.Size(21, 34),
							origin : new google.maps.Point(0, 0),
							anchor : new google.maps.Point(10, 34)
						}
					});
					markers.push(marker);

					var infowindow = new google.maps.InfoWindow({
						content : event.description
					});
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(dpcalendarMap, marker);
					});

					dpcalendarMapBounds.extend(l);
					dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
				});
			}
			mapElement.data('markers', markers);
		}
	});
}