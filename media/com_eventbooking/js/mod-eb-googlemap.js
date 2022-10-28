(function (document, $) {
    $(document).ready(function () {
        var zoomLevel = Joomla.getOptions('zoomLevel');
        var homeCoordinates = Joomla.getOptions('homeCoordinates');
        var locations = Joomla.getOptions('mapLocations');
        var moduleId = Joomla.getOptions('moduleId');
        var home = new google.maps.LatLng(homeCoordinates[0], homeCoordinates[1]);
        var markerUri = Joomla.getOptions('markerUri');

        var mapOptions = {
            zoom: zoomLevel,
            streetViewControl: true,
            scrollwheel: false,
            mapTypeControl: true,
            panControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: home,
        };

        var map = new google.maps.Map(document.getElementById("map" + moduleId), mapOptions);
        var infoWindow = new google.maps.InfoWindow();

        google.maps.event.addListener(map, 'click', function () {
            infoWindow.close();
        });

        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];
            var position = new google.maps.LatLng(location.lat, location.long);
            var options = {
                position: position,
                title: location.name,
                content: location.popupContent,
                icon: markerUri
            };

            makeMarker(options);
        }

        function makeMarker(options) {
            var pushPin = new google.maps.Marker({map: map});
            pushPin.setOptions(options);
            google.maps.event.addListener(pushPin, 'click', function () {
                infoWindow.setOptions(options);
                infoWindow.open(map, pushPin);
            });
        }
    });
})(document, Eb.jQuery);