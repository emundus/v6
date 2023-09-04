define(['jquery', 'fab/element'], function (jQuery, FbElement) {

        window.FbEmundusGeolocation = new Class({
            Extends: FbElement,

            mapContainer: null,
            mapLayer: null,
            zoom: 13,

            initialize: function (element, options) {
                this.setPlugin('emundus_geolocalisation');
                this.parent(element, options);
                this.zoom = options.default_zoom;

                if (typeof L !== 'undefined' && L !== null) {
                    this.mapContainer = L.map(element + '___map_container').setView(
                        [options.default_lat, options.default_lng], this.zoom
                    );

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapContainer);

                    this.mapLayer = L.layerGroup().addTo(this.mapContainer);

                    this.mapContainer.on('click', this.onClickMap.bind(this));

                    if (options.value && options.value.length > 0) {
                        const latlng = options.value.split(',');
                        this.onClickMap({latlng: {lat: latlng[0], lng: latlng[1]}});
                    } else if (options.get_location == 1) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                this.onClickMap({
                                    latlng: {lat: position.coords.latitude, lng: position.coords.longitude}
                                });
                            },
                            function(error) {
                                console.log(error);
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0
                            });
                    } else {
                        this.onClickMap({latlng: {lat: options.default_lat, lng: options.default_lng}});
                    }
                }
            },

            onClickMap: function(e) {
                this.mapLayer.clearLayers();
                this.mapLayer.addLayer(L.marker(e.latlng));
                this.update(e.latlng.lat + ',' + e.latlng.lng);
                this.mapContainer.setView(e.latlng, this.zoom);

                Fabrik.fireEvent('fabrik.emundus_geolocalisation.update', [this, e.latlng.lat + ',' + e.latlng.lng]);
            }
        });

        return window.FbEmundusGeolocation;
    });