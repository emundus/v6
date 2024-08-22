define(['jquery', 'fab/element'], function (jQuery, FbElement) {
        window.FbEmundusGeolocation = new Class({
            Extends: FbElement,

            mapContainer: null,
            mapLayer: null,

            initialize: function (element, options) {
                this.setPlugin('emundus_geolocalisation');
                this.parent(element, options);

                if (typeof L !== 'undefined' && L !== null) {
                    this.mapContainer = L.map(element + '___map_container').setView(
                        [options.default_lat, options.default_lng], options.default_zoom
                    );

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapContainer);
                    this.mapLayer = L.layerGroup().addTo(this.mapContainer);
                    this.mapContainer.on('click', this.updateMarkerPosition.bind(this));

                    if (options.value && options.value.length > 0) {
                        const latlng = options.value.split(',');
                        this.updateMarkerPosition({latlng: {lat: latlng[0], lng: latlng[1]}});
                    } else if (options.get_location == 1) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.updateMarkerPosition({
                                    latlng: {lat: position.coords.latitude, lng: position.coords.longitude}
                                });
                            },
                            (error) => {
                                console.log(error);

                                this.updateMarkerPosition({latlng: {lat: options.default_lat, lng: options.default_lng}});
                            },
                            {
                                enableHighAccuracy: false,
                                timeout: 5000
                            });
                    } else {
                        this.updateMarkerPosition({latlng: {lat: options.default_lat, lng: options.default_lng}});
                    }
                }
            },

            updateMarkerPosition: function(e) {
                this.mapLayer.clearLayers();
                this.mapLayer.addLayer(L.marker(e.latlng));
                this.update(e.latlng.lat + ',' + e.latlng.lng);
                this.mapContainer.setView(e.latlng);

                Fabrik.fireEvent('fabrik.emundus_geolocalisation.update', [this, e.latlng.lat + ',' + e.latlng.lng]);
            },

            updateZoom(zoom) {
                if (zoom > 0 && zoom < 21) {
                    this.mapContainer.setZoom(zoom);
                }
            }
        });

        return window.FbEmundusGeolocation;
    });