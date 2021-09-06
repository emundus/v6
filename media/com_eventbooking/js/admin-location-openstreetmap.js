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

    $(document).ready(function () {
        var centerCoordinates = Joomla.getOptions('coordinates');
        var zoomLevel = Joomla.getOptions('zoomLevel');
        var baseUri = Joomla.getOptions('baseUri');

        var mymap = L.map('map-canvas', {
            center: [centerCoordinates[0], centerCoordinates[1]],
            zoom: zoomLevel,
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            id: 'mapbox.streets',
        }).addTo(mymap);

        var marker = L.marker([centerCoordinates[0], centerCoordinates[1]], {draggable: true}).addTo(mymap);

        marker.on('dragend', function (e) {
            var target = e.target.getLatLng();
            $('#coordinates').val(target.lat + ',' + target.lng);
            // Make an ajax request to get the address
            var ajaxUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + target.lat + '&lon=' + target.lng;
            $.ajax({
                type: 'GET',
                url: ajaxUrl,
                dataType: 'json',
                success: function (data, textStatus, xhr)
                {
                    $('#address').val(data.display_name);
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    alert(textStatus);
                }
            });
        });

        $('#address').autocomplete({
            serviceUrl: baseUri + '/index.php?option=com_eventbooking&task=location.search',
            minChars: 3,
            onSelect: function (suggestion) {
                var form = document.adminForm;

                if (suggestion.name && form.name.value === '') {
                    form.name.value = suggestion.name;
                }

                if (suggestion.coordinates) {
                    form.coordinates.value = suggestion.coordinates;
                }

                if (suggestion.city) {
                    $('#city').val(suggestion.city);
                }

                if (suggestion.state) {
                    $('#state').val(suggestion.state);
                }

                var newPosition = L.latLng(suggestion.lat, suggestion.long);

                marker.setLatLng(newPosition);
                mymap.panTo(newPosition);
            }
        });
    });

})(document, jQuery);