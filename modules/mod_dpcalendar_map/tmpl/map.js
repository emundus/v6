function updateModuleLocationFrame(dpcalendarMapModuleId) {
	var data = {};
	data['filter-location'] = jQuery('#mod-filter-location-' + dpcalendarMapModuleId).val();
	data['ids'] = jQuery('#mod-ids-' + dpcalendarMapModuleId).val().split(',');
	data['filter-radius'] = jQuery('#mod-radius-' + dpcalendarMapModuleId).val();
	data['filter-length_type'] = jQuery('#mod-length_type-' + dpcalendarMapModuleId).val();
	jQuery.ajax({
		type : 'post',
		url : 'index.php?option=com_dpcalendar&view=map&layout=events&format=raw&moduleId=' + dpcalendarMapModuleId,
		data : data,
		success : function(response) {
			var json = jQuery.parseJSON(response);

			var mapElement = jQuery('#mod-event-map-' + dpcalendarMapModuleId);
			if (mapElement.data('markers') != null) {
				jQuery.each(mapElement.data('markers'), function(i, marker) {
					marker.setMap(null);
				});
			}
			var lat = mapElement.data('lat');
			var long = mapElement.data('long');

			var type = google.maps.MapTypeId.ROADMAP;
			switch (mapElement.data('type')) {
			case 2:
				type = google.maps.MapTypeId.SATELLITE;
				break;
			case 3:
				type = google.maps.MapTypeId.HYBRID;
				break;
			case 4:
				type = google.maps.MapTypeId.TERRAIN;
				break;
			}
			var dpcalendarMap = new google.maps.Map(document.getElementById('mod-event-map-' + dpcalendarMapModuleId), {
				zoom : mapElement.data('zoom'),
				mapTypeId : type,
				center : new google.maps.LatLng(lat, long),
				draggable : jQuery(document).width() > 480 ? true : false,
				scrollwheel : jQuery(document).width() > 480 ? true : false
			});
			var dpcalendarMapBounds = new google.maps.LatLngBounds();
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