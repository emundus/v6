jQuery(document).ready(function() {
	jQuery('#scheduling-options, #jform_scheduling_monthly_options').bind('click', function(e) {
		changeVisiblity();
		updateRuleFromForm();
	});
	jQuery('#jform_scheduling_end_date, #jform_scheduling_interval, #jform_scheduling_repeat_count').bind('change', function() {
		updateRuleFromForm();
	});
	jQuery('#jform_scheduling_daily_weekdays, #jform_scheduling_weekly_days, #jform_scheduling_monthly_days, #jform_scheduling_monthly_week, #jform_scheduling_monthly_week_days').bind('click', function() {
		updateRuleFromForm();
	});
	jQuery('#jform_rrule').bind('change', function(e) {
		updateFormFromRule();
	});
	jQuery(window).bind("load", function() {
		updateFormFromRule();
	});

	jQuery('#scheduling-expert-button').click(function() {
		jQuery('#scheduling-rrule').fadeToggle();
	});
	jQuery('#scheduling-rrule').hide();

	jQuery('#jform_location_ids').bind('change', function(e) {
		updateLocationFrame();
	});
	updateLocationFrame();

	jQuery('#location-activator').click(function() {
		jQuery('#location-form').fadeToggle();
	});
	jQuery('#location-remove').click(function() {
		var data = {};
		data[jQuery('#location_token').val()] = '1';
		data['ajax'] = '1';
		data['cid'] = jQuery('#jform_location_ids option:selected').map(function() {
			return this.value
		}).get();
		jQuery.ajax({
			type : 'POST',
			url : 'index.php?option=com_dpcalendar&task=locations.trash',
			data : data,
			success : function(data) {
				var json = jQuery.parseJSON(data);
				if (json.success) {
					jQuery('#jform_location_ids option:selected').remove();
					jQuery('#jform_location_ids').trigger("liszt:updated");
					updateLocationFrame();
				}
				Joomla.renderMessages(json.messages);
			}
		});
	});

	jQuery('#location-save-button').click(function() {
		var data = {
			jform : {
				title : jQuery('#location_title').val(),
				country : jQuery('#location_country').val(),
				province : jQuery('#location_province').val(),
				city : jQuery('#location_city').val(),
				zip : jQuery('#location_zip').val(),
				street : jQuery('#location_street').val(),
				number : jQuery('#location_number').val(),
				room : jQuery('#location_room').val(),
				state : 1,
				language : '*'
			}
		};
		data[jQuery('#location_token').val()] = '1';
		data['ajax'] = '1';
		data['id'] = 0;
		jQuery.ajax({
			type : 'POST',
			url : 'index.php?option=com_dpcalendar&task=location.save',
			data : data,
			success : function(data) {
				var json = jQuery.parseJSON(data);
				if (json.data.id != null && json.data.display != null) {
					jQuery('#jform_location_ids').append('<option value="' + json.data.id + '" selected="selected">' + json.data.display + '</option>');
					jQuery('#jform_location_ids').trigger("liszt:updated");
					updateLocationFrame();
				}
				Joomla.renderMessages(json.messages);
				jQuery('#location-form').fadeToggle();
			}
		});
	});
	jQuery('#location-cancel-button').click(function() {
		jQuery('#location-form').fadeToggle();
	});
	jQuery('#location-form').hide();

	jQuery('#jform_all_day input').bind('click', function(e) {
		if (jQuery(this).val() == 0) {
			jQuery('#jform_start_date_time, #jform_end_date_time').show();
		} else {
			jQuery('#jform_start_date_time, #jform_end_date_time').hide();
		}
	});

	// Booking
	jQuery('#book-state-checkbox').click(function() {
		jQuery('.book-control-group').fadeToggle();
	});

	if (!jQuery('#book-state-checkbox').is(':checked')) {
		jQuery('.book-control-group').hide();
	}
});

function updateFormFromRule() {
	if (jQuery('#jform_rrule').val() == undefined) {
		return;
	}
	var frequency = null;
	jQuery.each(jQuery('#jform_rrule').val().split(';'), function() {
		var parts = this.split('=');
		if (parts.length > 1) {
			switch (parts[0]) {
			case 'FREQ':
				jQuery('#jform_scheduling input').each(function() {
					var sched = jQuery(this);
					if (parts[1] == sched.val()) {
						sched.attr('checked', 'checked');
						if (parts[1] == '0') {
							jQuery('#jform_scheduling label[for="' + sched.attr('id') + '"]').attr('class', 'btn active btn-danger');
						} else {
							jQuery('#jform_scheduling label[for="' + sched.attr('id') + '"]').attr('class', 'btn active btn-success');
							frequency = sched.val();
						}
						if (parts[1] == 'DAILY' && jQuery('#jform_scheduling_daily_weekdays label[for="jform_scheduling_daily_weekdays0"]').hasClass('btn-danger')) {
							jQuery('#jform_scheduling_daily_weekdays1').attr('checked', 'checked');
							jQuery('#jform_scheduling_daily_weekdays label[for="jform_scheduling_daily_weekdays0"]').toggleClass('active btn-danger');
							jQuery('#jform_scheduling_daily_weekdays label[for="jform_scheduling_daily_weekdays1"]').toggleClass('active btn-success');
						}
					} else {
						jQuery('#jform_scheduling label[for="' + sched.attr('id') + '"]').attr('class', 'btn');
					}
				});
				break;
			case 'BYDAY':
				jQuery.each(parts[1].split(','), function() {
					if (frequency == 'MONTHLY') {
						var pos = this.length;
						var day = this.substring(pos - 2, pos);
						var week = this.substring(0, pos - 2);

						if (week == -1) {
							week = 'last';
						}

						jQuery('#jform_scheduling_monthly_week input[value="' + week + '"]').attr('checked', 'checked');
						jQuery('#jform_scheduling_monthly_week_days input[value="' + day + '"]').attr('checked', 'checked');
					} else {
						jQuery('#jform_scheduling_weekly_days input[value="' + this + '"]').attr('checked', 'checked');
					}
				});
				break;
			case 'BYMONTHDAY':
				jQuery('#jform_scheduling_monthly_options input[value="by_day"]').attr('checked', 'checked');
				jQuery('#jform_scheduling_monthly_options label[for="jform_scheduling_monthly_options0"]').attr('class', 'btn active btn-success');
				jQuery('#jform_scheduling_monthly_options label[for="jform_scheduling_monthly_options1"]').attr('class', 'btn');
				jQuery.each(parts[1].split(','), function() {
					jQuery('#jform_scheduling_monthly_days input[value="' + this + '"]').attr('checked', 'checked');
				});
				break;
			case 'COUNT':
				jQuery('#jform_scheduling_repeat_count').val(parts[1]);
				break;
			case 'INTERVAL':
				jQuery('#jform_scheduling_interval').val(parts[1]);
				break;
			case 'UNTIL':
				var t = parts[1];
				jQuery('#jform_scheduling_end_date').val(t.substring(0, 4) + '-' + t.substring(4, 6) + '-' + t.substring(6, 8));
				break;
			}
		}
	});
	changeVisiblity();
}
function updateRuleFromForm() {
	var rule = '';
	if (jQuery('#jform_scheduling1')[0].checked) {
		rule = 'FREQ=DAILY';
		if (jQuery('#jform_scheduling_daily_weekdays0')[0].checked) {
			rule = 'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR';
		}
	}
	if (jQuery('#jform_scheduling2')[0].checked) {
		rule = 'FREQ=WEEKLY';

		var boxes = jQuery('#jform_scheduling_weekly_days :input:checked');
		if (boxes.length > 0) {
			rule += ';BYDAY=';
			boxes.each(function() {
				rule += jQuery(this).val() + ',';
			});
			rule = rule.slice(0, -1);
		}
	}
	if (jQuery('#jform_scheduling3')[0].checked) {
		rule = 'FREQ=MONTHLY';
		if (jQuery('#jform_scheduling_monthly_options0')[0].checked) {
			var boxes = jQuery('#jform_scheduling_monthly_days :input:checked');
			if (boxes.length > 0) {
				rule += ';BYMONTHDAY=';
				boxes.each(function() {
					rule += jQuery(this).val() + ',';
				});
				rule = rule.slice(0, -1);
			}
		} else {
			var weeks = jQuery('#jform_scheduling_monthly_week :input:checked');
			var trim = false;
			if (weeks.length > 0) {
				rule += ';BYDAY=';
				weeks.each(function() {
					var days = jQuery('#jform_scheduling_monthly_week_days :input:checked');
					if (days.length > 0) {
						var week = jQuery(this).val();
						if (week == 'last') {
							week = -1;
						}
						days.each(function() {
							rule += week + jQuery(this).val() + ',';
							trim = true;
						});
					}
				});
				if (trim)
					rule = rule.slice(0, -1);
			}
		}
	}
	if (jQuery('#jform_scheduling4')[0].checked) {
		rule = 'FREQ=YEARLY';
	}
	if (rule.length > 1) {
		var interval = jQuery('#jform_scheduling_interval').val();
		if (interval > 0) {
			rule += ';INTERVAL=' + interval;
		}
		var count = jQuery('#jform_scheduling_repeat_count').val();
		if (count > 0) {
			rule += ';COUNT=' + count;
		}
		var until = jQuery('#jform_scheduling_end_date').val();
		if (until != '0000-00-00' && until.length == 10) {
			rule += ';UNTIL=' + until.replace(/\-/g, '') + 'T235900Z';
		}
	}
	jQuery('#jform_rrule').val(rule);
}

function updateLocationFrame() {
	var lat = 0;
	var long = 0;
	if (typeof geoip_latitude === 'function') {
		lat = geoip_latitude();
		long = geoip_longitude();
	}
	var dpcalendarMap = new google.maps.Map(document.getElementById('event-location-frame'), {
		zoom : 4,
		mapTypeId : google.maps.MapTypeId.ROADMAP,
		center : new google.maps.LatLng(lat, long),
		draggable : jQuery(document).width() > 480 ? true : false,
		scrollwheel : jQuery(document).width() > 480 ? true : false
	});
	var dpcalendarMapBounds = new google.maps.LatLngBounds();

	jQuery('#jform_location_ids option:selected').each(function() {
		var content = jQuery(this).html();
		var parts = content.substring(content.lastIndexOf('[') + 1, content.lastIndexOf(']')).split(':');
		if (parts.length < 2)
			return;
		if (parts[0] == 0 && parts[1] == 0)
			return;

		var l = new google.maps.LatLng(parts[0], parts[1]);
		var marker = new google.maps.Marker({
			position : l,
			map : dpcalendarMap,
			title : content
		});

		var infowindow = new google.maps.InfoWindow({
			content : content
		});
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(dpcalendarMap, marker);
		});

		dpcalendarMapBounds.extend(l);
		dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
	});
}

function changeVisiblity() {
	if (jQuery('#jform_scheduling0')[0].checked) {
		jQuery('#scheduling-options-start').hide();
		jQuery('#scheduling-options-end').hide();
		jQuery('#scheduling-options-interval').hide();
		jQuery('#scheduling-options-repeat_count').hide();
		jQuery('#scheduling-expert-button').hide();
		jQuery('#scheduling-rrule').hide();
	} else {
		jQuery('#scheduling-options-start').show();
		jQuery('#scheduling-options-end').show();
		jQuery('#scheduling-options-interval').show();
		jQuery('#scheduling-options-repeat_count').show();
		jQuery('#scheduling-expert-button').show();
		jQuery('#scheduling-rrule').show();
	}
	if (jQuery('#jform_scheduling1')[0].checked) {
		jQuery('#scheduling-options-day').show();
	} else {
		jQuery('#scheduling-options-day').hide();
	}
	if (jQuery('#jform_scheduling2')[0].checked) {
		jQuery('#scheduling-options-week').show();
	} else {
		jQuery('#scheduling-options-week').hide();
	}
	if (jQuery('#jform_scheduling3')[0].checked) {
		jQuery('.scheduling-options-month').show();
		if (jQuery('#jform_scheduling_monthly_options0')[0].checked) {
			jQuery('#scheduling-options-month-week').hide();
			jQuery('#scheduling-options-month-week-days').hide();
			jQuery('#scheduling-options-month-days').show();
		} else {
			jQuery('#scheduling-options-month-week').show();
			jQuery('#scheduling-options-month-week-days').show();
			jQuery('#scheduling-options-month-days').hide();
		}
	} else {
		jQuery('.scheduling-options-month').hide();
	}
}
