define(['jquery', 'fab/element'], function (jQuery, FbElement) {

        window.FbEmundusGeolocation = new Class({
            Extends: FbElement,

            mapContainer: null,

            initialize: function (element, options) {
                console.log(options);
                console.log(element);

                this.setPlugin('emundus_geolocalisation');
                this.parent(element, options);

                if (typeof L !== 'undefined' && L !== null) {
                    this.mapContainer = L.map(element).setView([48.856, 2.352], 13);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapContainer);
                }
            }
        });

        return window.FbEmundusGeolocation;
    });