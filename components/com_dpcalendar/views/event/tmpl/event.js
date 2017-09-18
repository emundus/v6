jQuery(document).ready(function() {
	// Chart url for markers
	var chartUrl = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	if (document.location.protocol == 'https:') {
		chartUrl = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	}

	// Create global map
	if (jQuery('#dp-event-details-map').length > 0) {
		var dpcalendarMapZoom = jQuery('#dp-event-details-map').attr('data-zoom');
		if (dpcalendarMapZoom == null) {
			dpcalendarMapZoom = 4;
		}
		var lat = jQuery('#dp-event-details-map').attr('data-lat');
		if (lat == null) {
			lat = 47;
		}
		var long = jQuery('#dp-event-details-map').attr('data-long');
		if (long == null) {
			long = 4;
		}

		var dpcalendarMap = new google.maps.Map(document.getElementById('dp-event-details-map'), {
			zoom : parseInt(dpcalendarMapZoom),
			mapTypeId : google.maps.MapTypeId.ROADMAP,
			center : new google.maps.LatLng(lat, long),
			draggable : jQuery(document).width() > 480 ? true : false,
			scrollwheel : jQuery(document).width() > 480 ? true : false
		});
		var dpcalendarMapBounds = new google.maps.LatLngBounds();
		var dpcalendarMapMarkers = [];

		jQuery('.dp-location').each(function(i) {
			var latitude = jQuery(this).data('latitude');
			var longitude = jQuery(this).data('longitude');
			if (latitude == null || latitude == "") {
				return;
			}

			var l = new google.maps.LatLng(latitude, longitude);
			var marker = new google.maps.Marker({
				position : l,
				map : dpcalendarMap,
				title : jQuery(this).data('title'),
				icon : {
					url : chartUrl + String(jQuery('#dp-event-details-map').data('color')).replace('#', ''),
					size : new google.maps.Size(21, 34),
					origin : new google.maps.Point(0, 0),
					anchor : new google.maps.Point(10, 34)
				}
			});

			dpcalendarMapBounds.extend(l);
			dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
		});
	}

	// Create location details maps
	jQuery('.dp-event-details-map-single').each(function(i) {
		var latitude = jQuery(this).data('lat');
		var longitude = jQuery(this).data('long');
		if (latitude == null || latitude == "") {
			return;
		}
		var zoom = jQuery(this).data('zoom');

		var dpcalendarMap = new google.maps.Map(document.getElementById(jQuery(this).attr('id')), {
			zoom : parseInt(zoom),
			mapTypeId : google.maps.MapTypeId.ROADMAP,
			center : new google.maps.LatLng(latitude, longitude),
			draggable : jQuery(document).width() > 480 ? true : false,
			scrollwheel : jQuery(document).width() > 480 ? true : false
		});
		var dpcalendarMapBounds = new google.maps.LatLngBounds();
		var dpcalendarMapMarkers = [];

		var l = new google.maps.LatLng(latitude, longitude);
		var marker = new google.maps.Marker({
			position : l,
			map : dpcalendarMap,
			icon : {
				url : chartUrl + String(jQuery(this).data('color')).replace('#', ''),
				size : new google.maps.Size(21, 34),
				origin : new google.maps.Point(0, 0),
				anchor : new google.maps.Point(10, 34)
			}
		});

		dpcalendarMapBounds.extend(l);
		dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
	});
});