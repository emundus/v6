
var GantrySliders = {
	add: function(id, children, steps, current){
		var name = id.replace(/-/, '_'),
			slider = document.id(name + '-wrapper').getElement('.slider'),
			knob = document.id(name + '-wrapper').getElement('.knob'),
			hidden = document.id(id);
		
		
		if (!window.sliders) window.sliders = {};
		hidden.addEvents({
			'set': function(value) {
				var slider = window.sliders[name];
				var index = slider.list.indexOf(value);

				slider.set(index).fireEvent('onComplete');
			}
		});
		window.sliders[name] = new RokSlider(slider, knob, {
			steps: steps,
			snap: true,
			initialize: function() {
				this.hiddenEl = hidden;
			},
			onComplete: function() {
				this.knob.removeClass('down');
			},
			onDrag: function(now) {
				this.element.getFirst().setStyle('width', now + 10);
			},
			onChange: function(step) {
				hidden.setProperty('value', this.list[step]);
			},
			onTick: function(position) {
				if(this.options.snap) position = this.toPosition(this.step);
				this.knob.setStyle(this.property, position);
				this.fireEvent('onDrag', position);
			}
		});
		window.sliders[name].list = children;
		window.sliders[name].set(current);
		
		knob.addEvents({
			'mousedown': function() {this.addClass('down');},
			'mouseup': function() {this.removeClass('down');}
		});
	}
};