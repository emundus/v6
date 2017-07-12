
var InputsExclusion = ['.content_vote'];

var InputsMorph = {
	version: 1.7,
	init: function() {
		InputsMorph.rtl = $(document.body).getStyle('direction') == 'rtl';
		InputsMorph.list = new Hash({
			'all': []
		});
		var b = $$('.g-surround input[type=radio]');
		var c = $$(InputsExclusion.join(' input[type=radio], ') + ' input[type=radio]');
		c.each(function(a) {
			b = b.remove(a);
		});
		b.each(function(a, i) {
			InputsMorph.setArray('list', 'all', a);
			if (InputsMorph.list.hasKey(a.name)) InputsMorph.setArray('list', a.name, a);
			else InputsMorph.list.set(a.name, [a]);
			InputsMorph.morph(a, 'radios').addEvent(a, 'radios');
		});
		b = $$('input[type=checkbox]');
		c = $$(InputsExclusion.join(' input[type=checkbox], ') + ' input[type=checkbox]');
		c.each(function(a) {
			b = b.remove(a);
		});
		b.each(function(a, i) {
			InputsMorph.setArray('list', 'all', a);
			if (InputsMorph.list.hasKey(a.name)) InputsMorph.setArray('list', a.name, a);
			else InputsMorph.list.set(a.name, [a]);
			InputsMorph.morph(a, 'checks').addEvent(a, 'checks');
		});
	},
	morph: function(a, b) {
		var c = a.getNext(),
			parent = a.getParent(),
			name = a.name.replace('[', '').replace(']', '');
		if (c && c.getTag() == 'label') {
			a.setStyles({'position': 'absolute', 'left': '-10000px'});

			if (InputsMorph.rtl && window.gecko) a.setStyles({'position': 'absolute', 'right': '-10000px'});
			else a.setStyles({'position': 'absolute', 'left': '-10000px'});

			if (InputsMorph.rtl && (window.opera || window.ie)) {a.setStyle('display', 'none');}
			if (window.ie7) a.setStyle('display', 'none');

			c.addClass('rok' + b + ' rok' + name);
			if (a.checked) c.addClass('rok' + b + '-active');
		} else if (parent && parent.getTag() == 'label') {
			
			if (InputsMorph.rtl && window.gecko) a.setStyles({'position': 'absolute', 'right': '-10000px'});
			else a.setStyles({'position': 'absolute', 'left': '-10000px'});
			
			if (InputsMorph.rtl && (window.opera || window.ie)) {a.setStyle('display', 'none');}
			
			parent.addClass('rok' + b + ' rok' + name);
			if (a.checked) parent.addClass('rok' + b + '-active');
		}
		return InputsMorph;
	},
	addEvent: function(a, b) {
		a.addEvent('click', function() {
			if (window.opera || window.ie) {
				if (a.opera) {InputsMorph.switchReplacement(a, b);}
				a.opera = (b == 'checks') ? false : true;
			} else InputsMorph.switchReplacement(a, b);
		});
		if (window.opera || window.ie || (a.getNext() && !a.getNext().getProperty('for'))) {
			var c = a.getNext(),
				parent = a.getParent();
			if (c && c.getTag() == 'label' && (window.ie || (window.opera && !a.opera))) {
				c.addEvent('click', function() {
					if ((window.opera || window.ie) && !a.opera) a.opera = true;
					a.fireEvent('click');
				});
			} else if (parent && parent.getTag() == 'label' || (a.getParent() && !a.getParent().getProperty('for'))) {
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
			var g = ((f) ? f.getTag() == 'label' : false);
			var h = ((parent) ? parent.getTag() == 'label' : false);
			if (g || h) {
				if (g) {
					if (f.hasClass(cls) && g) f.removeClass(cls);
					else if (!f.hasClass(cls) && g) f.addClass(cls);
				} else if (h) {
					if (parent.hasClass(cls) && h) parent.removeClass(cls);
					else if (!parent.hasClass(cls) && h) parent.addClass(cls);
				}
			}
		} else {
			InputsMorph.list.get(d.name).each(function(a) {
				var b = a.getNext(),
					parent = a.getParent();
				var c = d.getNext(),
					radioparent = d.getParent();
				$$(b, parent).removeClass('rok' + e + '-active');
				if (b && b.getTag() == 'label' && c == b) {
					a.setProperty('checked', 'checked');
					b.addClass('rok' + e + '-active');
				} else if (parent && parent.getTag() == 'label' && radioparent == parent) {
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
