(function (document, $) {
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

        $("#adminForm").validationEngine('attach', {
            onValidationComplete: function(form, status){
                if (status == true) {
                    $.ajax({
                        type:'POST',
                        data: $('#adminForm input[type=\'radio\']:checked, #adminForm input[type=\'checkbox\']:checked, #adminForm input[type=\'text\'], #adminForm input[type=\'hidden\'],  #adminForm select'),
                        dataType: 'json',
                        url: baseUri + '/index.php?option=com_eventbooking&task=location.save_ajax',
                        beforeSend: function () {
                            $('#save_location').prop('disabled',true);
                        },
                        success : function(json){
                            $( "#adminForm" ).before( '<div class="alert alert-success"><h4 class="alert-heading">Message</h4><div class="alert-message">'+json['message']+'</div></div>' );
                            $('#save_location').prop('disabled',false);
                            var parentJQuery = parent.jQuery;
                            parentJQuery('#location_id').append(parentJQuery('<option>', {
                                value: json['id'],
                                text: json['name']
                            }));
                            parentJQuery('#location_id').val(json['id']);
                            parentJQuery.colorbox.close();
                            parentJQuery('#location_id').trigger("liszt:updated");
                        }
                    });
                    return false;
                }
                return false;
            }
        });
    });

})(document, Eb.jQuery);