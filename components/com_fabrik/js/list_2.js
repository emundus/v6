/**
 * Created by Hugh on 11/28/2016.
 */

requirejs(['fab/fabrik'], function() {
	Fabrik.addEvent('fabrik.list.loaded', function(list) {
		console.log('list 2 loaded');
	});
});