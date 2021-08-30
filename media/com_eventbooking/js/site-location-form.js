(function (document, $) {
    Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;
        if (pressbutton === 'cancel') {
            Joomla.submitform(pressbutton);
        } else {
            //Should validate the information here
            if (form.name.value === '') {
                alert(Joomla.JText._('EB_ENTER_LOCATION'));
                form.name.focus();
                return;
            }
            Joomla.submitform(pressbutton);
        }
    };

    var map;
    var geocoder;
    var marker;

    function initialize() {
        var centerCoordinates = Joomla.getOptions('coordinates');
        var mapDiv = document.getElementById('map-canvas');
        geocoder = new google.maps.Geocoder();

        // Create the map object
        map = new google.maps.Map(mapDiv, {
            center: new google.maps.LatLng(centerCoordinates[0], centerCoordinates[1]),
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false
        });

        // Create the default marker icon
        marker = new google.maps.Marker({
            map: map,
            position: new google.maps.LatLng(centerCoordinates[0], centerCoordinates[1]),
            draggable: true
        });

        // Add event to the marker
        google.maps.event.addListener(marker, 'drag', function () {
            geocoder.geocode({'latLng': marker.getPosition()}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        document.getElementById('address').value = results[0].formatted_address;
                        document.getElementById('coordinates').value = marker.getPosition().toUrlValue();
                    }
                }
            });
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);


    $(document).ready(function () {
        var addressInput = $('#address');

        addressInput.on('keyup', function () {
            var term = $(this).val();
            var suggestedLocationsContainer = $('#eventmaps_results');
            var addressWidth = $('#address').width();
            suggestedLocationsContainer.width('width', addressWidth - 21);
            suggestedLocationsContainer.hide();
            suggestedLocationsContainer.empty();

            if (term !== '') {
                geocoder.geocode({'address': term}, function (results, status) {
                    if (status === 'OK') {
                        $(results).each(function (itemIndex, item) {
                            var li = $('<li>');
                            var a = $("<a />", {
                                href: 'javascript:void(0)',
                                text: item.formatted_address
                            });
                            a.click(function () {
                                $('#address').val(item.formatted_address);
                                var lat = item.geometry.location.lat().toFixed(7);
                                var long = item.geometry.location.lng().toFixed(7);
                                var location = new google.maps.LatLng(lat, long);

                                $('#coordinates').val(lat + ',' + long);
                                marker.setPosition(location);
                                map.setCenter(location);
                                suggestedLocationsContainer.hide();
                            });

                            suggestedLocationsContainer.append(li.append(a));
                        });

                        suggestedLocationsContainer.show();
                    }
                });
            }

        });

        addressInput.on('blur', function () {
            setTimeout(function () {
                $('#eventmaps_results').hide();
            }, 10000);
        });

        $('#btn-get-location-from-address').on('click', function () {
            var address = document.getElementById('address').value;
            geocoder.geocode({'address': address}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {

                    var location = results[0].geometry.location;
                    map.setCenter(location);
                    marker.setPosition(location);
                    $('#coordinates').val(location.lat().toFixed(7) + ',' + location.lng().toFixed(7));
                } else {
                    alert("We're sorry but your location was not found.");
                }
            });
        });

        $('#btn-save-location').on('click', function () {
            Joomla.submitbutton('save');
        });

        $('#btn-cancel').on('click', function () {
            Joomla.submitbutton('cancel');
        });

        $('#btn-delete-location').on('click', function () {
            if (confirm(Joomla.JText._('EB_DELETE_LOCATION_CONFIRM'))) {
                Joomla.submitform('delete');
            }
        });
    });

})(document, jQuery);