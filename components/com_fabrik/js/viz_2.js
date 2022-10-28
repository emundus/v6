/**
 * Created by Hugh on 1/30/2017.
 */

requirejs(['fab/fabrik'], function () {
	Fabrik.addEvent('fabrik.viz.fullcalendar.dateinlimits', function (viz, date) {
		fconsole('date: ' + date);
		return true;
	});
});
