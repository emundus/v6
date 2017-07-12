
window.addEvent('domready', function() {
	var handle = document.id('gantry-totop');
	if (handle) {
		var scroller = new Fx.Scroll(window);
		handle.setStyle('outline', 'none').addEvent('click', function(e) {
			e.stop();
			scroller.toTop();
		});
	}
});