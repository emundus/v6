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

                    if (options.value) {
                        var latlng = options.value.split(',');
                        this.onClickMap({latlng: {lat: latlng[0], lng: latlng[1]}});
                    } else {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                this.onClickMap({latlng: {lat: position.coords.latitude, lng: position.coords.longitude}});
                            },
                            function(error) {
                                console.log(error);
                            }, {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0
                            });
                    }
                }
            },

            onClickMap: function(e) {
                this.mapLayer.clearLayers();
                this.mapLayer.addLayer(L.marker(e.latlng));
                this.update(e.latlng.lat + ',' + e.latlng.lng);
                this.mapContainer.setView(e.latlng, 13);

                Fabrik.fireEvent('fabrik.emundus_geolocation.update', [this, e.latlng.lat + ',' + e.latlng.lng]);
            }
        });

        return window.FbEmundusGeolocation;
    });