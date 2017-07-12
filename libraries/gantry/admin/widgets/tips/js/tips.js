
var GantryTips = {
	init: function() {
		var gantrytips = document.getElements('.gantrytips');
		if (!gantrytips) return;

		gantrytips.each(function(gantrytip, i) {
			var arrows = gantrytip.getElements('.gantrytips-controller .gantrytips-left, .gantrytips-controller .gantrytips-right');
			var current = gantrytip.getElement('.current-tip');
			var currentValue = current.get('html').toInt();
			var tips = gantrytip.getElements('.gantrytips-tip');

			tips.each(function(tip, i) {
				tip.set('display', (i == currentValue - 1) ? 'block' : 'none');
			});

			arrows.addEvents({
				'click': function() {
					var left = this.hasClass('gantrytips-left');
					var now = currentValue;
					if (left) {
						currentValue -= 1;
						if (currentValue <= 0) currentValue = tips.length;
					} else {
						currentValue += 1;
						if (currentValue > tips.length) currentValue = 1;
					}
					this.fireEvent('jumpTo', [currentValue, now]);
				},
				jumpTo: function(index, now) {
					if (!now) now = currentValue;
					currentValue = index;
					if (!tips[currentValue - 1] || !tips[now - 1]) return;

					tips.setStyle('display', 'none');
					tips[currentValue - 1].setStyle('display', 'block');
					current.set('text', currentValue);
				},
				jumpById: function(id, now) {
					if (!now) now = currentValue;
					currentValue = tips.indexOf(document.id(id)) || 0;
					if (currentValue == -1) return;

					tips.setStyle('display', 'none');
					tips[currentValue].setStyle('display', 'block');
					currentValue += 1;
					current.set('text', currentValue);

				},
				'selectstart': function(e) {
					e.stop();
				}
			});

			arrows[0].fireEvent('jumpTo', 1);
			arrows[1].fireEvent('jumpTo', 1);
		});
	},

	pins: function(pins){
		pins.each(function(pin, i){
			var panels = pin.getParent('.gantry-panel').getElements('.gantry-panel-left, .gantry-panel-right');

			var sizes = {'left': 0, 'right': 0};
			panels.each(function(panel, i){
				var size = panel.getSize().y;
				sizes[(!i) ? 'left' : 'right'] = size;
			});

			pin.store('surround', {'panels': panels, 'sizes': sizes, 'parent': pin.getParent('.tips-field')});
			if (sizes.left <= sizes.right + 50) pin.setStyle('display', 'none');
			else GantryTips.attachPin(pin);
		});
	},

	attachPin: function(pin){
		if (!window.retrieve('pinAttached')){
			window.store('pinAttached', true);
		}

		pin.addEvents({
			'click': function(){
				var parent = pin.retrieve('surround').parent;

				pin.toggleClass('active');

				if (pin.hasClass('active')){
					parent.setStyles({
						'top': parent.getPosition().y - window.getScroll().y
					});
				}

				parent.toggleClass('fixed');
			},
			'dbclick': function(e){e.stop();},
			'selectstart': function(e){e.stop();}
		});
	}
};

window.addEvent('domready', GantryTips.init);
