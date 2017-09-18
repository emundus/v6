jQuery(document).ready(function() {
	jQuery(document).on('click', 'label[for="jform_series0"], label[for="jform_series1"]', function() {
		if (jQuery("#jform_series1").is(':checked')) {
			jQuery('tr.dp-booking-event-row').hide();
			jQuery('tr.dp-booking-event-row select').val('0');
		} else {
			jQuery('tr.dp-booking-event-row').show();
			jQuery('tr.dp-booking-event-row select').val('1');
		}
		calculatePrice();
	});
	jQuery('tr.dp-booking-event-row').hide();

	jQuery('#dp-booking-pricing-details select').change(function() {
		calculatePrice();
	});

	calculatePrice();

	jQuery('#dp-booking-payment-images').hide();

	jQuery('#dp-booking-submit-button').click(function(e) {
		e.preventDefault;
		if (jQuery('#dp-booking-payment-images').length && !jQuery("input[name='paymentmethod']:checked").val()) {
			jQuery('#dp-booking-payment-images').fadeToggle('slow');
			jQuery(".dp-booking-payment-row").click(function() {
				jQuery(this).find("input[name='paymentmethod']").attr('checked', 'checked');
				Joomla.submitbutton('bookingform.save');
			});
		} else {
			Joomla.submitbutton('bookingform.save');
		}
		return false;
	});

	dpRadio2btngroup();
});

function calculatePrice() {
	var data = jQuery("#adminForm").find('input[name!=task], select').serialize();
	jQuery.ajax({
		type : 'POST',
		url : PRICE_URL,
		data : data,
		success : function(response) {
			var json = jQuery.parseJSON(response);

			if (json.messages != null && jQuery('#system-message-container').length) {
				Joomla.renderMessages(json.messages);
			}

			var events = json.data.events;
			for ( var id in events) {
				jQuery('#dp-booking-price-' + id).html(events[id].discount + ' ' + json.data.currency);
				if (events[id].discount != events[id].original) {
					jQuery('#dp-booking-original-price-' + id).show();
					jQuery('#dp-booking-original-price-info-' + id).show();
					jQuery('#dp-booking-original-price-' + id).html(events[id].original + ' ' + json.data.currency);
				}else{
					jQuery('#dp-booking-original-price-' + id).hide();
					jQuery('#dp-booking-original-price-info-' + id).hide();
				}
			}
			jQuery('#dp-booking-price').html(json.data.total + ' ' + json.data.currency);
		},
		complete : function(request) {
			jQuery('#dpcalendar-bookingform-loader').hide();
		}
	});
}
