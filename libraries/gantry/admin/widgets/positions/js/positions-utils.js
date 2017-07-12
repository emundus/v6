
var tip;
window.sliders = {};
window.slidersTips = {};
Array.prototype.compareArrays = function(arr) {
	if (!arr) return false;
    if (this.length != arr.length) return false;
    for (var i = 0; i < arr.length; i++) {
        if (this[i].compareArrays) { //likely nested array
            if (!this[i].compareArrays(arr[i])) return false;
            else continue;
        }
        if (this[i] != arr[i]) return false;
    }
    return true;
};

String.implement({
	baseConversion: function(from, to) {
		var num = this;
		if(isNaN(from) || from < 2 || from > 36 || isNaN(to) || to < 2 || to > 36)
			throw (new RangeError('Illegal radix. Radices must be integers between 2 and 36, inclusive.'));
		num = parseInt(num, from);
		num = num.toString(to);

		return num;
	},

	hex2dec: function() {
		if (!isNaN(this.toInt())) return this;
		return this.baseConversion(24, 10);
	},

	dec2hex: function() {
		return this.baseConversion(10, 24);
	}
});

var tip;
var createTip = function(id) {
	var el = document.id(id);
	if (el) return el;

	el = new Element('div', {'id': id}).inject(document.body).set('text', '2 | 2 | 2 | 2 | 2 | 2');
	el.set('tween', {duration: 200, link: 'cancel'}).fade('out');

	return el;
};

var updateTip = function(slider) {
	var blocks = slider.RT.blocks, output = '';
	blocks.each(function(block, i) {
		if (block.style.display != 'none') {
			var grid = block.className.split(' ')[1].replace('mini-grid-', '');
			output += grid.hex2dec() + ' | ';
		}
	});

	output = output.substring(0, output.length - 2);

	return output;
};

var updateSlider = function(slider, range) {
	var x = slider;
	range = range;

	x.min = 0;
	x.max = slider.RT.list[range].length - 1;
	x.range = x.max - x.min;
	x.steps = x.max;
	x.stepSize = Math.abs(x.range) / x.steps;
	x.stepWidth = Number((x.stepSize * x.full / Math.abs(x.range)).toFixed(4));

	var grid = (x.stepWidth == Infinity) ? x.full : x.stepWidth;
	x.drag.options.grid = grid;

	if (!x.steps) x.drag.detach();
	else x.drag.attach();

	slider.RT.current = range;
};

var updateBlocks = function(slider, amount, step) {
	if (!step) step = 0;
	var blocks = slider.RT.blocks;
	var current = slider.RT.list[slider.RT.current];
	amount = amount;
	blocks.removeClass('main');
	blocks.each(function(block, i) {

		if (i < slider.RT.current) blocks[i].setStyle('display', 'block');
		else blocks[i].setStyle('display', 'none');
		var grid = slider.RT.list[amount][Math.round(step, 0)].toString();
		blocks[i].className = '';

		var chr = (amount == 1) ? slider.RT.gridSize : grid.charAt(i).hex2dec();
		blocks[i].addClass('mini-grid').addClass('mini-grid-' + chr);

		var keyValue = blocks[i].innerHTML;
		if (keyValue == slider.RT.keyName && (keyValue != '')) blocks[i].addClass('main');
	});
};

var serializeSettings = function(slider, settings) {
	var serial = '';

	// grid size
	serial += 'a:1:{i:' + slider.RT.gridSize + ';';

	// main index
	serial += 'a:' + settings.getLength() + ':{';
	settings.each(function(val, key) {
		// values of index
		serial += 'i:' + key + ';a:' + val.length + ':{';

		for (i = 0, l = val.length; i < l; i++) {
			if (slider.RT.type == 'custom') {
				var tmp = slider.RT.store[key][i];
				serial += 's:' + tmp.length + ':\"' + tmp + '\";i:' + val[i].hex2dec() + ';';
			} else {
				serial += 'i:' + i + ';i:' + val[i].hex2dec() + ';';
			}
		}

		serial += '}';
	});

	serial += '}}';

	return serial;
};

var GantryPositions = {
	add: function(hidden, name, maxgrid, loadValue, keyName, type, combinations, keys, activeNav){
		hidden = document.id(hidden);
		var name2 = name.replace(/-/, '_'),
			slider = document.id(name + '-grp').getElement('.position'),
			knob = document.id(name + '-grp').getElement('.knob');

		if (!window.sliders) window.sliders = {};
		GantryPositionsTools.setEvent(hidden, name2);
		window.sliders[name2] = new RokSlider(slider, knob, {
			offset: 5,
			snap: true,
			initialize: function() {
				this.hiddenEl = hidden;
				this.RT = {};
				this.RT.current = $$('#'+name+'-grp .list .active a')[0].getFirst().innerHTML.toInt();
				this.RT.list = combinations;
				this.RT.keys = keys;
				this.RT.navigation = document.id(name+'-grp').getElement('.list').getChildren();
				this.RT.blocks = document.id(name+'-grp').getElements('.mini-grid');
				this.RT.settings = {};
				this.RT.gridSize = maxgrid;
				this.RT.defaults = loadValue;
				this.RT.keyName = keyName || '';
				this.RT.type = type;
				this.RT.store = {};

				GantryPositionsTools.init.bind(this, [name2])();
			},

			onComplete: function() {
				if (MooTools.lang) GantryPositionsTools.complete.bind(this, [name, hidden])();
				else GantryPositionsTools.complete.bind(this, name, hidden)();
			},
			onDrag: function(now) {
				if (MooTools.lang) GantryPositionsTools.drag.bind(this, [now, name2])();
				else GantryPositionsTools.drag.bind(this, now, name2)();
			},
			onChange: function(position) {
				GantryPositionsTools.change.bind(this, position)();
			}
		});

		window.sliders[name2].RT.navigation[activeNav].fireEvent('click');

		knob.addEvents({
			'mousedown': function() {this.addClass('down');},
			'mouseup': function() {this.removeClass('down');}
		});

		GantryPositionsTools.wrapperTip(name, name2);
	}
};

var GantryPositionsTools = {
	init: function(name) {
		this.options.steps = this.RT.list[this.RT.current].length - 1;
		this.setOptions(this.options);

		var current = this.RT.current, navigation = this.RT.navigation, blocks = this.RT.blocks;
		var settings = this.RT.settings;
		navigation.each(function(nav, i) {
			settings[current] = [];
			nav.addEvent('click', function(event) {
				if (event) event.stop();
				navigation.removeClass('active');
				this.addClass('active');

				updateSlider(window.sliders[name], this.getFirst().getFirst().innerHTML.toInt());

				var value = window.sliders[name].RT.defaults[window.sliders[name].RT.current][0];
				if (window.sliders[name].RT.type == 'custom') {
					var defaults = window.sliders[name].RT.defaults[window.sliders[name].RT.current];
					var keys = window.sliders[name].RT.keys[window.sliders[name].RT.current];
					var tests = [];
					keys.each(function(key, i) {
						if (key.compareArrays(defaults.keys)) tests.push(i);
					});
					var list = window.sliders[name].RT.list[window.sliders[name].RT.current];

					tests.each(function(test, j) {
						if (list[test] == defaults.values[0]) {
							window.sliders[name].set(test);
						}
					});

				} else {
					window.sliders[name].set(window.sliders[name].RT.list[window.sliders[name].RT.current].indexOf(value));
				}
			});
		});
		updateBlocks(this, current);
	},
	complete: function(name, hidden) {
		this.knob.removeClass('down');
		hidden.set('value', serializeSettings(this, new Hash(this.RT.settings)));
		var setting = '';
		var step = Math.round(this.step);
		for (i = 0, len = this.RT.current; i < len; i++) {
			setting += this.RT.list[this.RT.current][(isNaN(step) || step < 0) ? 0 : step].toString().charAt(i);
		}
		if (this.RT.type != 'custom') this.RT.defaults[this.RT.current] = [setting];
		else {
			this.RT.defaults[this.RT.current].values = [setting];
			var keys = [];
			for (i=0,l=this.RT.current;i<l;i++) {
				keys.push(this.RT.blocks[i].innerHTML);
			}
			this.RT.defaults[this.RT.current].keys = keys;
		}
	},
	drag: function(now, name) {
		if (typeOf(now) == 'array') {
			name = now[1];
			now = now[0];
		}
		this.element.getFirst().setStyle('width', now + 10);
		var step = this.step;

		var layout = this.RT.list[this.RT.current][Math.round(step, 0)], output = '';
		if (!layout) return;

		layout = layout.toString();
		this.RT.settings[this.RT.current] = [];
		this.RT.store[this.RT.current] = [];
		for (i = 0, len = this.RT.current; i < len; i++) {
			output += layout.charAt(i).hex2dec() + ((i == len - 1) ? '' : ' | ');

			if (this.RT.type == 'custom') {
				this.RT.settings[this.RT.current].push(layout.charAt(i));
				this.RT.store[this.RT.current].push(this.RT.keys[this.RT.current][Math.round(step,0)][i]);

			} else {
				this.RT.settings[this.RT.current].push(layout.charAt(i));
			}
			if (this.RT.keys) {
				var keyIndex = this.RT.keys[this.RT.current][Math.round(step,0)][i];
				if (this.RT.type == 'custom') this.RT.blocks[i].set('text', keyIndex);
			}
		}

		if (!tip) tip = createTip('positions-tip');
		tip.set('html', output);

		updateBlocks(window.sliders[name], this.RT.current, step);
	},

	change: function(position) {
		if(this.options.snap) position = this.toPosition(this.step);
		position = position || 0;
		this.knob.setStyle(this.property, position);
		this.fireEvent('onDrag', position);
	},

	wrapperTip: function(name, name2) {
		document.id(name + '-wrapper').addEvents({
			'mouseenter': function() {
				var container = this.getElement('.mini-container');
				var pos = container.getCoordinates();
				tip.setStyles({
					'left': pos.left + pos.width + 5,
					'top': pos.top - 5
				});
				tip.set('html', updateTip(window.sliders[name2]));
				tip.fade('in');
			},
			'mouseleave': function() {
				tip.fade('out');
			}
		});
	},

	showMax: function(name, name2) {
		var wrapper = document.id(name+'-grp').getParent('.chain');
		if (!wrapper) return;
		wrapper = wrapper.getParent();
		//var toggle = wrapper.getElement('.chain-toggle input[type=hidden]');
		var select = wrapper.getElement('.chain-showmax select');

		if (!select) return;
		//if (!toggle || !select) return;

		var list = document.id(name+'-grp').getElements('ul.list li');
		/*var tgl_name = 'toggle' + toggle.id.replace(/\-/g, '');
		if (window[tgl_name]) window[tgl_name].addEvent('change', function(state) {
			var value = select.get('value').toInt();
			if (!state) {
				list.setStyle('display', 'inline');
				select.fireEvent('detach');
			}
			else {
				var excluded = $$(list.diff(list.slice(0, value), true));
				list[value - 1].fireEvent('click');
				excluded.setStyle('display', 'none');
				select.fireEvent('attach');
			}
		});*/

		select.addEvent('change', function(index) {
			if (!index || typeof index == 'object') index = this.get('value').toInt();
			else index += 1;

			var active = document.id(name+'-grp').getElement('li.active'),
				excluded = list.filter(function(item, i){
					return i + 1 > index;
				});

			if (list.indexOf(active) > index - 1) list[index - 1].fireEvent('click');
			list.setStyle('display', 'inline-block');
			excluded.setStyle('display', 'none');

		}).fireEvent('change');

		/*if (window[tgl_name]) {
			window[tgl_name].fireEvent('change', window[tgl_name].state);
			if (window[tgl_name].state) select.fireEvent('attach');
		}*/
	},

	setEvent: function(hidden, name) {
		hidden.addEvent('set', function(value) {

			var slider = window.sliders[name].RT, currentValue = value;

			if (value.contains(',')) {
				var split = currentValue.split(',');
				value = {};
				value[slider.gridSize] = {};
				value[slider.gridSize][split.length] = split;
				value = serialize(value);
			}

			if (!value.contains('{')) value = serialize(value.replace(/\s/g, '').split(','));
			value = value.unserialize();

			if (!value[slider.gridSize]) return;
			else value = new Hash(value[slider.gridSize]);

			var arrayMulti = {};
			var arraySingle = {};

			value.each(function(wrapper_value, key) {
				arrayMulti[key] = [];
				arraySingle[key] = [];
				if (slider.type == 'custom') {
					arrayMulti[key] = {};
					arraySingle[key] = {};
					arrayMulti[key].keys = [];
					arrayMulti[key].values = [];
					arraySingle[key].keys = [];
					arraySingle[key].values = [];
				}

				$H(wrapper_value).each(function(inner_value, inner_key) {
					var val = inner_value.toString().dec2hex();
					if (slider.type != 'custom') {
						arrayMulti[key].push(val);
						arraySingle[key].push(val);
					} else {
						arrayMulti[key].keys.push(inner_key);
						arraySingle[key].keys.push(inner_key);
						arrayMulti[key].values.push(val);
						arraySingle[key].values.push(val);
					}

				});
				if (slider.type != 'custom') arraySingle[key] = [arraySingle[key].join('')];
				else arraySingle[key].values = [arraySingle[key].values.join('')];
			});

			slider.defaults = Object.merge(slider.defaults, arraySingle);

			if (slider.type != 'custom') {
				var cur = arraySingle[slider.current];
				if (cur) {
					var current = slider.list[slider.current].indexOf(cur[0]) || 0;
					window.sliders[name].set(current).fireEvent('onComplete');
				}
			} else {
				var defaults = slider.defaults[slider.current];
				var keys = slider.keys[slider.current];
				var tests = [];

				keys.each(function(key, i) {
					if (key.compareArrays(defaults.keys)) tests.push(i);
				});

				var list = slider.list[slider.current];

				tests.each(function(test, j) {
					if (list[test] == defaults.values[0]) {
						window.sliders[name].set(test);
						//.fireEvent('onComplete');
					}
				});
			}
		});
	}
};

Array.implement({
	diff: function(compare, copy) {
		var daddy = (copy) ? Array.clone(this) : this;

		for (i = 0; i < compare.length; i++) {
			if (daddy.contains(compare[i])) daddy.erase(compare[i]);
		}

		return daddy;
	}
});
