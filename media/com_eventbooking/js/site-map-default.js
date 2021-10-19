(function (document) {
    function initialize() {
        var centerLocation = new google.maps.LatLng(Joomla.getOptions('lat'), Joomla.getOptions('long'));

        var options = {
            zoom: Joomla.getOptions('zoomLevel'),
            center: centerLocation,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("inline_map"), options);

        var marker = new google.maps.Marker({
            map: map,
            position: centerLocation,
        });

        google.maps.event.trigger(map, "resize");

        var infoWindow = new google.maps.InfoWindow({
            content: Joomla.getOptions('popupContent'),
            maxWidth: 250
        });

        google.maps.event.addListener(marker, "click", function () {
            infoWindow.open(map, marker);
        });

        infoWindow.open(map, marker);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
})(document);