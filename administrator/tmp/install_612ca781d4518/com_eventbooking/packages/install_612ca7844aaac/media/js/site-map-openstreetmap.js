(function (document, $) {
    $(document).ready(function () {
        var centerLocation = [Joomla.getOptions('lat'), Joomla.getOptions('long')];
        var mymap = L.map('eb_location_map').setView(centerLocation, Joomla.getOptions('zoomLevel'));
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
            zoom: Joomla.getOptions('zoomLevel'),
        }).addTo(mymap);

        var marker = L.marker(centerLocation, {draggable: true}).addTo(mymap);
        marker.bindPopup(Joomla.getOptions('popupContent'));
    });
})(document, Eb.jQuery);