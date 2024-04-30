var FbEmundusGeolocationViz = new Class({
    Implements: [Options],
    options   : {
        'lat'               : '48.85341',
        'lng'               : '2.3488',
        'zoom'              : 13,
        'markers'           : []
    },
    initialize: function (options) {
        this.mapContainer = L.map('map_container').setView([options.lat, options.lng], options.zoom);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(this.mapContainer);

        // add all markers
        options.markers.forEach(this.addMarker.bind(this));
    },

    addMarker(marker) {
        L.marker([marker.lat, marker.lng]).addTo(this.mapContainer).bindPopup(marker.popup);
    }
});