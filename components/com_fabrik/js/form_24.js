/**
 * Created by Hugh on 7/20/2016.
 */

requirejs(['fab/fabrik'], function() {
	Fabrik.addEvent('fabrik.element.date.calendar.create', function (el) {
		el.options.calendarSetup.showOthers = true;
	});
});

requirejs(['fab/fabrik'], function () {
	Fabrik.addEvent('fabrik.form.loaded', function (form) {
		jQuery(window).bind('pageshow', function(form) {
			//form.reset();
		});
	});
});
