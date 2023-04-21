(function (document, $) {
    $(document).ready(function () {
        var zoomLevel = Joomla.getOptions('zoomLevel');
        var homeCoordinates = Joomla.getOptions('homeCoordinates');
        var locations = Joomla.getOptions('mapLocations');
        var moduleId = Joomla.getOptions('moduleId');
        var markerUri = Joomla.getOptions('markerUri');

        var mymap = L.map('map' + moduleId, {
            center: [homeCoordinates[0], homeCoordinates[1]],
            zoom: zoomLevel,
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false
        });

        L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
            id: 'mapbox.streets'
        }).addTo(mymap);

        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];
            var marker = L.marker([location.lat, location.long], {
                draggable: false,
                autoPan: true,
                title: location.name
            }).addTo(mymap);
            marker.bindPopup(location.popupContent);
        }
    });
})(document, Eb.jQuery);
