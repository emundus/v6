
var InputsExclusion = ['.content_vote'];

var InputsMorph = {
	version: 1.7,
	init: function() {
		InputsMorph.rtl = document.id(document.body).getStyle('direction') == 'rtl';
		InputsMorph.list = new Hash({
			'all': []
		});
		var b = $$('input[type=radio]');
		var c = $$(InputsExclusion.join(' input[type=radio], ') + ' input[type=radio]');
		c.each(function(a) {
			b = b.erase(a);
		});
		b.each(function(a, i) {
			InputsMorph.setArray('list', 'all', a);
			if (InputsMorph.list.has(a.name)) InputsMorph.setArray('list', a.name, a);
			else InputsMorph.list.set(a.name, [a]);
			InputsMorph.morph(a, 'radios').addEvent(a, 'radios');
		});
		b = $$('input[type=checkbox]');
		c = $$(InputsExclusion.join(' input[type=checkbox], ') + ' input[type=checkbox]');
		c.each(function(a) {
			b = b.erase(a);
		});
		b.each(function(a, i) {
			InputsMorph.setArray('list', 'all', a);
			if (InputsMorph.list.has(a.name)) InputsMorph.setArray('list', a.name, a);
			else InputsMorph.list.set(a.name, [a]);
			InputsMorph.morph(a, 'checks').addEvent(a, 'checks');
		});
	},
	morph: function(a, b) {
		var c = a.getNext(),
			parent = a.getParent(),
			name = a.name.replace('[', '').replace(']', '');
		if (c && c.get('tag') == 'label') {
			a.setStyles({'position': 'absolute', 'left': '-10000px'});

			if (InputsMorph.rtl && Browser.Engine.gecko) a.setStyles({'position': 'absolute', 'right': '-10000px'});
			else a.setStyles({'position': 'absolute', 'left': '-10000px'});

			if (InputsMorph.rtl && (Browser.Engine.presto || Browser.Engine.trident)) {a.setStyle('display', 'none');}
			if (Browser.Engine.trident5) a.setStyle('display', 'none');

			c.addClass('rok' + b + ' rok' + name);
			if (a.checked) c.addClass('rok' + b + '-active');
		} else if (parent && parent.get('tag') == 'label') {

			if (InputsMorph.rtl && Browser.Engine.gecko) a.setStyles({'position': 'absolute', 'right': '-10000px'});
			else a.setStyles({'position': 'absolute', 'left': '-10000px'});

			if (InputsMorph.rtl && (Browser.Engine.presto || Browser.Engine.trident)) {a.setStyle('display', 'none');}

			parent.addClass('rok' + b + ' rok' + name);
			if (a.checked) parent.addClass('rok' + b + '-active');
		}
		return InputsMorph;
	},
	addEvent: function(a, b) {
		a.addEvent('click', function() {
			if (Browser.Engine.presto || Browser.Engine.trident) {
				if (a.opera) {InputsMorph.switchReplacement(a, b);}
				a.opera = (b == 'checks') ? false : true;
			} else InputsMorph.switchReplacement(a, b);
		});
		if (Browser.Engine.presto || Browser.Engine.trident || (a.getNext() && !a.getNext().getProperty('for'))) {
			var c = a.getNext(),
				parent = a.getParent();
			if (c && c.get('tag') == 'label' && (Browser.Engine.trident || (Browser.Engine.presto && !a.opera))) {
				c.addEvent('click', function() {
					if ((Browser.Engine.presto || Browser.Engine.trident) && !a.opera) a.opera = true;
					a.fireEvent('click');
				});
			} else if (parent && parent.get('tag') == 'label' || (a.getParent() && !a.getParent().getProperty('for'))) {
				parent.addEvent('click', function() {
					a.fireEvent('click');
				});
			}
		}
		return InputsMorph;
	},
	switchReplacement: function(d, e) {
		if (e == 'checks') {
			var f = d.getNext(),
				parent = d.getParent(),
				cls = "rok" + e + "-active";
			var g = ((f) ? f.get('tag') == 'label' : false);
			var h = ((parent) ? parent.get('tag') == 'label' : false);
			if (g || h) {
				if (g) {
					if (f.hasClass(cls) && g) {
						f.removeClass(cls);
						if (d.checked) d.checked = false;
					}
					else if (!f.hasClass(cls) && g) {
						f.addClass(cls);
						if (!d.checked) d.checked = true;
					}
				} else if (h) {
					if (parent.hasClass(cls) && h) {
						parent.removeClass(cls);
						if (d.checked) d.checked = false;
					}
					else if (!parent.hasClass(cls) && h) {
						parent.addClass(cls);
						if (!d.checked) d.checked = true;
					}
				}
			}
		} else {
			InputsMorph.list.get(d.name).each(function(a) {
				var b = a.getNext(),
					parent = a.getParent();
				var c = d.getNext(),
					radioparent = d.getParent();
				if (b) $$(b).removeClass('rok' + e + '-active');
				if (parent) $$(parent).removeClass('rok' + e + '-active');
				if (b && b.get('tag') == 'label' && c == b) {
					a.setProperty('checked', 'checked');
					b.addClass('rok' + e + '-active');
				} else if (parent && parent.get('tag') == 'label' && radioparent == parent) {
					parent.addClass('rok' + e + '-active');
					a.setProperty('checked', 'checked');
				}
			});
		}
	},
	setArray: function(a, b, c) {
		var d = InputsMorph[a].get(b);
		d.push(c);
		return InputsMorph[a].set(b, d);
	}
};
window.addEvent('domready', InputsMorph.init);
