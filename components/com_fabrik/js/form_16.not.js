function doAddRemove(el) {
	return;
	var repeats = el.form.repeatGroupMarkers[el.groupid.toInt()];
	var disable = false;
	if (repeats > 0) {
		for (var x = 0; x < repeats; x++) {
			var thisElId = el.origId + '_' + x;
			var thisEl = el.form.formElements.get(thisElId);
			var thisVal = thisEl.getValue();
			if (thisVal === '0') {
				disable = true;
			}
		}
	}
	if (disable) {
		//jQuery('#group17 .fabrikGroupRepeater').hide();
	}
	else {
		//jQuery('#group17 .fabrikGroupRepeater').show();
	}
}

requirejs(['fab/fabrik'], function() {
	Fabrik.addEvent('fabrik.form.elements.added', function(form) {
		return;
		alert('Elements added');
		var disable = false;
		form.formElements.each(function(el) {
			if (el.origId === 'fab_repeat_test_17_repeat___disable') {
				if (el.getValue() === '0') {
					disable = true;
				}
			}
		});
		if (disable) {
			jQuery('#group17 .fabrikGroupRepeater').hide();
		}
		else {
			jQuery('#group17 .fabrikGroupRepeater').show();
		}
	});
});

requirejs(['fab/fabrik'], function () {

	Fabrik.addEvent('fabrik.form.loaded', function(form) {
		alert('form loaded');
	});

	Fabrik.addEvent('fabrik.form.group.duplicate.end', function (form, event) {
		alert('dupe end');

		var groupId = 17;

		// Get the number of times the group has been repeated
		var repeatMax = form.repeatGroupMarkers[groupId];

		// Get the newly added element
		var el = form.formElements['fab_repeat_test_17_repeat___repeat_calc_' + repeatMax];

		// Update the element with an empty string
		el.update('');

		var d0el = form.formElements['fab_repeat_test_17_repeat___repeat_date_0'];
		var d0val = d0el.getValue();
		d0 = new Date(d0val);
		d0.setFullYear(d0.getFullYear() + repeatMax);
		var dnew = form.formElements['fab_repeat_test_17_repeat___repeat_date_' + repeatMax];
		dnew.update(d0.format('%Y-%m-%d'));
	});

	alert("I bin loaded");
});
