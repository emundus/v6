define(['jquery', 'fab/element'], function (jQuery, FbElement) {

        window.FbEmundusGeolocation = new Class({
            Extends: FbElement,

            mapContainer: null,
            mapLayer: null,

            initialize: function (element, options) {
                this.setPlugin('emundus_geolocalisation');
                this.parent(element, options);

                if (typeof L !== 'undefined' && L !== null) {
                    this.mapContainer = L.map(element + '___map_container').setView([48.856, 2.352], 13);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapContainer);

                    this.mapLayer = L.layerGroup().addTo(this.mapContainer);

                    this.mapContainer.on('click', this.onClickMap.bind(this));
                }
            },

            onClickMap: function(e) {
                this.mapLayer.clearLayers();
                this.mapLayer.addLayer(L.marker(e.latlng));
                this.update(e.latlng.lat + ',' + e.latlng.lng);
            }
        });

        return window.FbEmundusGeolocation;
    });