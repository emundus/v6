jQuery(document).ready(function() {
	var chartUrl = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	if (document.location.protocol == 'https:') {
		chartUrl = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	}

	var mapData = jQuery('#dpcalendar_component_map').data()
	var dpcalendarMap = new google.maps.Map(document.getElementById('dpcalendar_component_map'), {
		zoom : mapData.zoom,
		mapTypeId : google.maps.MapTypeId.ROADMAP,
		center : new google.maps.LatLng(mapData.latitude, mapData.longitude),
		draggable : jQuery(document).width() > 480 ? true : false,
		scrollwheel : jQuery(document).width() > 480 ? true : false
	});
	var dpcalendarMapBounds = new google.maps.LatLngBounds();
	var dpcalendarMapMarkers = [];

	// Adding the locations to the map
	jQuery('.dp-list-container .dp-location').each(function(i) {
		var loc = jQuery(this).data();
		if (loc.title == undefined || loc.title == '' || loc.title == null)
			return;
		var l = new google.maps.LatLng(loc.latitude, loc.longitude);

		var marker = new google.maps.Marker({
			position : l,
			map : dpcalendarMap,
			title : loc.location,
			icon : {
				url : chartUrl + loc.color.toString().replace('#', ''),
				size : new google.maps.Size(21, 34),
				origin : new google.maps.Point(0, 0),
				anchor : new google.maps.Point(10, 34)
			}
		});
		dpcalendarMapMarkers.push(marker);

		var parent = jQuery(this).parent().parent();
		var desc = parent.find('.event-description').html();
		var infowindow = new google.maps.InfoWindow({
			content : desc
		});
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(dpcalendarMap, marker);
		});
		dpcalendarMapBounds.extend(l);
		dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
	});
});