
var GantryGradient = {
	init: function() {		
		var previews = $$('.gradient-preview');
		
		previews.each(function(preview) {
			new Element('div').set('text', 'Sorry. Gradient previews can be seen only on WebKit and Gecko based browsers.').inject(preview.addClass('error'));
		});
	},
	
	add: function(id, name) {
		var previewBox = document.id(id);
		var preview = new Element('div').set('text', 'Sorry. Gradient previews can only be seen on WebKit and Gecko based browsers.').inject(previewBox);
		previewBox.getParent().addClass('error');
		
		if (Browser.Engine.webkit || Browser.Engine.gecko) {
			
			var list = ['from', 'fromopacity', 'toopacity', 'to', 'gradient', 'direction_start', 'direction_end'];
			
			var r = window.moorainbow;
			var bound = GantryGradient.updateGradient.pass([name, previewBox], GantryGradient);
			list.each(function(tag) {
				
				var title = name + '_' + tag;
				var title2 = title.replace(/-/g, '_');
				var bound = GantryGradient.updateGradient.pass([name, previewBox], GantryGradient);

			if (typeof r['r_'+title2] != 'undefined') {
					
					var cleanTitle = title2;
					r['r_' + cleanTitle].addEvent('onChange', bound);
					
				} else if (document.id(title) && (tag == 'fromopacity' || tag == 'toopacity')) {
					window.sliders[title2].addEvent('onDrag', bound);
					
				} else if (document.id(title) && (tag == 'gradient' || tag == 'direction_start' || tag == 'direction_end')) {
				
					document.id(title).addEvent('change', bound);
					
				}
			});
			GantryGradient.updateGradient(name, previewBox);
		}
	},
	
	updateGradient: function(name, previewBox) {
		var settings = {
			'from': document.id(name+'_from'),
			'to': document.id(name+'_to'),
			'fromOp': document.id(name+'_fromopacity'),
			'toOp': document.id(name+'_toopacity'),
			'type': document.id(name+'_gradient'),
			'direction_start': document.id(name+'_direction_start'),
			'direction_end': document.id(name+'_direction_end')
		};

		var fromColor = settings.from.value.hexToRgb(true);
		var toColor = settings.to.value.hexToRgb(true);

		fromColor = fromColor.join(', ') + ', ' +(settings.fromOp ? settings.fromOp.value : 1);
		toColor = toColor.join(', ') + ', ' +(settings.toOp ? settings.toOp.value : 1);
	
		var gradient;
		if (Browser.Engine.webkit) {

			gradient = '-webkit-gradient(' + (settings.type ? settings.type.value : 'linear') + ', ' + settings.direction_start.value.replace('-', ' ') + ', ' + settings.direction_end.value.replace('-', ' ') + ', from(rgba(' + fromColor + ')), to(rgba(' + toColor + ')))';
			previewBox.getParent().removeClass('error').getFirst().empty().style.background = gradient;
		} else if (Browser.Engine.gecko) {
			var start = settings.direction_start.value.split('-');
			var end = settings.direction_end.value.split('-');

			var pointA, pointB;

			pointA = start[0];
			pointB = start[1];
			if (start[0] == end[0]) pointA = 'center';
			if (start[1] == end[1]) pointB = 'center';


			gradient = '-moz-'+(settings.type ? settings.type.value : 'linear')+'-gradient('+ pointA + ' ' + pointB + ', rgba(' + fromColor + '), rgba(' + toColor + '))';
			previewBox.getParent().removeClass('error').getFirst().empty().style.background = gradient;
		}
	}
};