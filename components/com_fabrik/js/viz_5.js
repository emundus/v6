/**
 * Created by Hugh on 9/13/2016.
 */

requirejs(['fab/fabrik'], function() {
	Fabrik.addEvent('fabrik.list.update', function(list, data) {
		var viz = Fabrik.getBlock('visualization_5');
		if (viz) {
			viz.update();
		}
	});
});