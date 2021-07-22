((function(){

	var rainbow = this.MooRainbow = new Class({

		Implements: [Options, Events],

		options: {
			data: 'moorainbow',
			startColor: '#f00',
			transparency: 1,
			activeForm: 'hsb',
			forms: {
				'rgb': ['red', 'green', 'blue'],
				'hsb': ['hue', 'saturation', 'brightness']
			},
			defaults: {
				relative: null,
				position: 'right',
				offset: {x: 10, y: 0}
			},
			onChange: function(sets, element){
				if (!element) return;

				var trigger;
				if (element.get('data-moorainbow-trigger')) {
					trigger = element;
					element = document.id(element.get('data-moorainbow-trigger'));
				} else {
					trigger = document.getElements('[data-moorainbow-trigger='+element.get('id')+']');
				}

				element.set('value', this.sets.hex);
				if (this.sets.hex != 'transparent') trigger.getElement('.overlay').removeClass('overlay-transparent');
				trigger.getElement('.overlay').setStyle('backgroundColor', this.sets.hex);
			},
			onComplete: function(){}
		},

		initialize: function(options){
			this.setOptions(options);
			this.data = 'data-' + this.options.data;

			this.sets = {rgb: [], hsb: [], hex: ''};
			this.coords = {overlay: {x: 0, y: 0}, slider: 0};
			this.offsets = {overlay: 0, slider: 0};

			if (!this.layout) this.build();

			this.attach();
			this.setColor(this.options.startColor.hexToRgb().rgbToHsb(), 'force');

			this.hide();
		},

		attach: function(){
			var buttonType = this.layout.retrieve('moor:buttontype:click', function(event, element){
					this.buttonTypeClick.call(this, event, element);
				}.bind(this)),

				trigger = this.layout.retrieve('moor:trigger:click', function(event, element){
					this.toggle.call(this, event, element);
				}.bind(this)),

				smartToggle = document.retrieve('moor:smarttoggle:click', function(event, element){
					this.smartToggle.call(this, event, element);
				}.bind(this)),

				overlayMouseDown = this.layout.retrieve('moor:overlay:mousedown', function(event, element){
					this.overlayMouseDown.call(this, event, element);
				}.bind(this)),

				sliderMouseDown = this.layout.retrieve('moor:slider:mousedown', function(event, element){
					this.sliderMouseDown.call(this, event, element);
				}.bind(this)),

				backupColor = this.layout.retrieve('moor:backupcolor:click', function(event, element){
					var color = element.retrieve('moor:backup:color', this.options.startColor);
					if (color != 'transparent') this.setColor.call(this, color, 'force');
					else this.triggerTransparent.call(this, event, element);
				}.bind(this)),

				elementKeyup = this.layout.retrieve('moor:element:keyup', function(event, element){
					this.keyup.call(this, event, element);
				}.bind(this));

			this.layout.addEvents({
				'click:relay([data-moorainbow-button-type])': buttonType,
				'click:relay(.moor-colorsbox-selected)': backupColor,
				'keyup:relay([data-moorainbow-input] input)': elementKeyup,
				'mousedown:relay([data-moorainbow-overlay])': overlayMouseDown,
				'mousedown:relay([data-moorainbow-slider])': sliderMouseDown
			});

			document.addEvents({
				'mousedown:relay([data-moorainbow-trigger])': trigger,
				'focus:relay([data-moorainbow])': trigger,
				'keyup:relay([data-moorainbow])': elementKeyup
			});

			if (!document.retrieve('moor:document:events')){
				document.addEvent('click', smartToggle);
				document.store('moor:document:events', true);
			}

			this.attachDrag();
		},

		attachDrag: function(){
			var overlay = this.layout.getElement('[data-moorainbow-overlay]'),
				slider = this.layout.getElement('[data-moorainbow-slider]'),
				overlaySize = overlay.getComputedSize(),
				sliderSize = slider.getComputedSize(),
				events;

			this.overlayDragInstance = new Drag(this.overlayCursor, {
				limit: {
					x: [0 - this.offsets.overlay, overlaySize.width - this.offsets.overlay],
					y: [0 - this.offsets.overlay, overlaySize.height - this.offsets.overlay]
				},
				//onBeforeStart: this.overlayDrag.bind(this),
				//onStart: this.overlayDrag.bind(this),
				//onDrag: this.overlayDrag.bind(this),
				snap: 0
			});

			this.sliderDragInstance = new Drag(this.sliderCursor, {
				limit: {
					y: [0 - this.offsets.slider, sliderSize.height - this.offsets.slider]
				},
				modifiers: {x: false},
				//onBeforeStart: this.sliderDrag.bind(this),
				//onStart: this.sliderDrag.bind(this),
				//onDrag: this.sliderDrag.bind(this),
				snap: 0
			});

			events = {
				onBeforeStart: this.setColor.bind(this),
				onStart: this.setColor.bind(this),
				onDrag: this.setColor.bind(this)
			};

			this.overlayDragInstance.addEvents(events);
			this.sliderDragInstance.addEvents(events);
		},

		overlayMouseDown: function(event, element){
			var overlay = this.layout.getElement('[data-moorainbow-overlay]'),
				overlayPosition = overlay.getPosition(),
				overlayBorders = {
					x: overlay.getStyle('border-left').toInt() + overlay.getStyle('border-right').toInt(),
					y: overlay.getStyle('border-top').toInt() + overlay.getStyle('border-bottom').toInt()
				};

			this.overlayCursor.style.left = event.page.x - overlayPosition.x - this.offsets.overlay - overlayBorders.x + 'px';
			this.overlayCursor.style.top = event.page.y - overlayPosition.y - this.offsets.overlay - overlayBorders.y + 'px';

			this.overlayDragInstance.start(event);
		},

		sliderMouseDown: function(event, element){
			var slider = this.layout.getElement('[data-moorainbow-slider]'),
				sliderPosition = slider.getPosition().y;
				sliderBorders = slider.getStyle('border-top').toInt() + slider.getStyle('border-bottom').toInt()

			this.sliderCursor.style.top = event.page.y - sliderPosition - this.offsets.slider - sliderBorders + 'px';

			this.sliderDragInstance.start(event);
		},

		toggle: function(event, element){
			var color = null,
				activeTrigger = this.active ? this.active.get('data-moorainbow-trigger') : null,
				elementTrigger = element.get('data-moorainbow-trigger');

			if (this.isShown && this.active == element) return;
			if (this.isShown && activeTrigger && activeTrigger == element.get('id')) return;
			if (this.isShown && this.active && elementTrigger && elementTrigger == this.active.get('id')) return;

			this.active = element;
			this.show();

			if (!element.get('data-moorainbow-trigger') && document.getElement('[data-moorainbow-trigger=' + element.get('id') + ']')){
				color = element.get('value');
				element = document.getElement('[data-moorainbow-trigger=' + element.get('id') + ']');
			}

			if (!color) color = document.id(element.get('data-moorainbow-trigger')).get('value');

			this.backupColor.setStyle('background-color', color).store('moor:backup:color', color);
			this.reposition({relative: element});
			if (color != 'transparent') this.setColor(color, 'force');
		},

		smartToggle: function(event, element){
			if (event){
				var target = event.target;

				if (target.get('data-moorainbow') !== null || target.get('data-moorainbow-trigger') !== null || target.get('data-moorainbow-layout') !== null ||
					target.getParent('[data-moorainbow]') || target.getParent('[data-moorainbow-trigger]') || target.getParent('[data-moorainbow-layout]')) return;
				else this.hide();
			}
		},

		reposition: function(options){
			var element, position, offset, axis = {x: 0, y: 0}, defaults = this.options.defaults;

			defaults = Object.merge(defaults, options);
			element = defaults.relative;
			position = defaults.position;
			offset = defaults.offset;

			if (!element) return;

			switch(position){
				case 'left':
					axis.x = 0 - this.layout.getSize().x; offset.x *= -1; break;
				case 'right':
					axis.x = element.getSize().x; offset.x *= 1; break;
				case 'top':
					axis.y = 0 - this.layout.getSize().y; offset.x *= -1; break;
				case 'bottom':
					axis.y = element.getSize().y; offset.x *= 1; break;
			}

			this.layout.style.left = element.getPosition().x + axis.x + offset.x + 'px';
			this.layout.style.top = element.getPosition().y + axis.y + offset.y + 'px';

		},

		setColor: function(color, updateCursors){
			var isSlider, hue, type = 'hsb';
			if (typeOf(color) == 'element'){
				if (color.hasClass('moor-slider-cursor')) isSlider = true;
				color = this._getHSB();
			}

			if (!color) color = this._getHSB();
			if (color[0] == '#'){
				type = 'rgb';
				color = color.hexToRgb(true);
			}

			if (updateCursors) this._updateCursors(color, type);
			this.updateData(color, type);
		},

		updateData: function(color, type){
			type = type || 'hsb';

			var input, hue, hsb, rgb, hex, data;

			switch(type){
				case 'rgb':
					rgb = color;
					hsb = color.rgbToHsb();
					hex = color.rgbToHex();
					break;
				case 'hsb':
					hsb = color || this._getHSB();
					rgb = hsb.hsbToRgb(true);
					hex = this._hsbToHex(hsb);
			}

			data = {
				red: rgb[0], green: rgb[1], blue: rgb[2],
				hue: hsb[0], saturation: hsb[1], brightness: hsb[2],
				hex: hex
			};

			this.sets = {rgb: rgb, hsb: hsb, hex: hex};
			hue = new Color([this.sets.hsb[0], 100, 100]).hsbToRgb(true);

			['red', 'green', 'blue', 'hue', 'saturation', 'brightness', 'hex'].each(function(set, i){
				input = this.layout.getElement('[data-moorainbow-input-'+set+']');
				input.set('value', data[set]);
			}, this);

			this.selectedColor.setStyle('backgroundColor', this.sets.hex);
			this.layout.getElement('[data-moorainbow-overlay]').setStyle('backgroundColor', hue);

			this.fireEvent('onChange', [this.sets, this.active], 1);
		},

		hide: function(){
			this.layout.setStyles({display: 'none', visibility: 'hidden'});
			this.isShown = false;
		},

		show: function(){
			this.layout.setStyles({display: 'block', visibility: 'visible'});
			this.isShown = true;
		},

		keyup: function(event, element){
			var parent, data, dataKey, dataElement, dataColor = [], match = element.value.match(/^(#[a-f0-9]{6}|[0-9]{1,3})$/ig), color;
			if (!match) return;

			parent = element.getParent('[data-moorainbow-input]:not([data-moorainbow-input=hex])');
			if (!this._validateData(event, element) && null === element.get('data-moorainbow')) return false;

			color = match[0];

			if (parent){
				data = parent.get('data-moorainbow-input');
				Object.each(this.options.forms, function(value, key){
					if (value.contains(data)) dataKey = key;
				}, this);

			}

			if (dataKey){
				this.options.forms[dataKey].each(function(type){
					dataElement = this.layout.getElement('[data-moorainbow-input-' + type + ']');
					dataColor.push(dataElement.get('value').toInt());
				}, this);

				if (dataKey == 'rgb') color = dataColor.rgbToHex();
				else color = dataColor;
			}

			this.setColor(color, 'force');
		},

		build: function(){
			var input, label, suffix;

			var layout = new Element('div.moor-layout[data-moorainbow-layout]').inject(document.body),

				colorwheel = new Element('div.moor-overlay[data-moorainbow-overlay]').adopt(
					new Element('div.moor-overlay-white'),
					new Element('div.moor-overlay-black'),
					new Element('div.moor-overlay-cursor')
				).inject(layout),

				sliderwheel = new Element('div.moor-slider[data-moorainbow-slider]').adopt(
					new Element('div.moor-slider-colors'),
					new Element('div.moor-slider-cursor')
				).inject(layout),

				colorsbox = new Element('div.moor-colorsbox[data-moorainbow-colorsbox]').adopt(
					new Element('div.moor-colorsbox-current'),
					new Element('div.moor-colorsbox-selected')
				).inject(layout),

				inputs = new Element('div.moor-inputs').inject(layout),

				buttons = new Element('div.moor-buttons').inject(layout),

				indicator = new Element('span.arrow').inject(layout);

			['red', 'green', 'blue', 'hue', 'saturation', 'brightness', 'hex'].each(function(type){
				label = (type != 'hex') ? type[0].capitalize() : '# ' + type;
				suffix = ['saturation', 'brightness'].contains(type) ? '%' : (type == 'hue') ? '&deg;' : '';

				input = new Element('div.moor-inputs-block.moor-inputs-' + type + '[data-moorainbow-input=' + type + ']').adopt(
					new Element('span', {html: label}),
					new Element('input[data-moorainbow-input-' + type + ']')
				);

				if (suffix) input.adopt(new Element('span', {html: suffix}));

				inputs.adopt(input);
			});

			['close', 'transparent', 'RGB', 'HSB'].each(function(button){
				label = button == 'close' ? '&times;' : button == 'transparent' ? '' : button;
				new Element('div.moor-button.moor-button-' + button.toLowerCase() + '[data-moorainbow-button-type=' + button.toLowerCase() + ']', {html: label}).inject(buttons);
			});

			// offsets
			this.overlayCursor = colorwheel.getElement('.moor-overlay-cursor');
			this.sliderCursor = sliderwheel.getElement('.moor-slider-cursor');

			// selected / backup colors
			this.selectedColor = layout.getElement('div.moor-colorsbox-current');
			this.backupColor = layout.getElement('div.moor-colorsbox-selected');

			this.offsets = {
				overlay: this.overlayCursor.getSize().x / 2,
				slider: this.sliderCursor.getSize().y / 2
			};

			this.layout = layout;
			this.buttonTypeClick.call(this);

		},

		buttonTypeClick: function(event, element){
			var type = element ? element.get('data-moorainbow-button-type') || this.options.activeForm : this.options.activeForm;

			if (!this.options.forms[type]){
				var method = this['trigger' + type.capitalize()];
				return method ? method.call(this, event, element) : false;
			}

			Object.each(this.options.forms, function(inputs, key){
				if (key == type){
					this.layout.getElement('[data-moorainbow-button-type=' + key + ']').addClass('moor-button-active');
					inputs.each(function(input){
						this.layout.getElement('[data-moorainbow-input=' + input + ']').addClass('moor-inputs-block-active');
					}, this);
				} else {
					this.layout.getElement('[data-moorainbow-button-type=' + key + ']').removeClass('moor-button-active');
					inputs.each(function(input){
						this.layout.getElement('[data-moorainbow-input=' + input + ']').removeClass('moor-inputs-block-active');
					}, this);
				}
			}, this);
		},

		triggerClose: function(event, element){
			this.hide();
		},

		triggerTransparent: function(event, element){
			element = this.active;
			if (!element) return;

			var trigger, value = 'transparent';
			if (element.get('data-moorainbow-trigger')) {
				trigger = element;
				element = document.id(element.get('data-moorainbow-trigger'));
			} else {
				trigger = document.getElements('[data-moorainbow-trigger='+element.get('id')+']');
			}

			element.set('value', value);
			trigger.getElement('.overlay').addClass('overlay-' + value).setStyle('backgroundColor', value);
			this.hide();
		},

		_hsbToHex: function(hsb){
			hsb = hsb || this._getHSB();

			return hsb.hsbToRgb().rgbToHex();
		},

		_hexToHsb: function(hex){
			return hex.hexToRgb().rgbToHsb();
		},

		_getHSB: function(){
			var coords = this._getCoordinates(),
				overlayElement = this.layout.getElement('[data-moorainbow-overlay]'),
				sliderElement = this.layout.getElement('[data-moorainbow-slider]'),
				overlaySize = overlayElement.getComputedSize(),
				sliderSize = sliderElement.getComputedSize();

			var s = Math.round((coords.overlay.x * 100) / overlaySize.width),
				b = 100 - Math.round((coords.overlay.y * 100) / overlaySize.height),
				h = 360 - Math.round((coords.slider.y * 360) / sliderSize.height);

			h = (h >= 360) ? 0 : (h < 0) ? 0 : h;
			s = (s > 100) ? 100 : (s < 0) ? 0 : s;
			b = (b > 100) ? 100 : (b < 0) ? 0 : b;

			return [h, s, b];
		},

		_updateCursors: function(hsb, type){
			var	overlayCursor = this.overlayCursor,
				sliderCursor = this.sliderCursor,
				overlayElement = this.layout.getElement('[data-moorainbow-overlay]'),
				sliderElement = this.layout.getElement('[data-moorainbow-slider]'),
				overlaySize = overlayElement.getComputedSize(),
				sliderSize = sliderElement.getComputedSize();

			if (type == 'rgb') hsb = hsb.rgbToHsb();

			var coordinates = {
				x: Math.round(((overlaySize.width * hsb[1]) / 100) - this.offsets.overlay),
				y: Math.round(- ((overlaySize.height * hsb[2]) / 100) + overlaySize.height - this.offsets.overlay),
				z: Math.round((sliderSize.height * hsb[0]) / 360)
			};

			if (coordinates.z == 360) coordinates.z = 0;
			coordinates.z = sliderSize.height - coordinates.z - this.offsets.slider; //sliH - c + this.snippet('slider') - arwH;

			overlayCursor.style.top = coordinates.y + 'px';
			overlayCursor.style.left = coordinates.x + 'px';
			sliderCursor.style.top = coordinates.z + 'px';
		},

		_getCoordinates: function(){
			var	overlayCursor = this.overlayCursor,
				sliderCursor = this.sliderCursor,
				overlayElement = this.layout.getElement('[data-moorainbow-overlay]'),
				sliderElement = this.layout.getElement('[data-moorainbow-slider]');

			return {
				overlay: {
					x: overlayCursor.getPosition(overlayElement).x + this.offsets.overlay,
					y: overlayCursor.getPosition(overlayElement).y + this.offsets.overlay
				},
				slider: {
					y: sliderCursor.getPosition(sliderElement).y + this.offsets.slider
				}
			};
		},

		_validateData: function(event, element){
			var parent = element.getParent('[data-moorainbow-input]'),
				value = element.get('value'),
				data;

			if (!parent) return false;

			data = parent.get('data-moorainbow-input');

			switch(data){
				case 'hue':
					if (!value.match(/^[0-9]{1,3}$/) || value.toInt() >= 360){
						element.set('value', 0);
						return true;
					}
					break;
				case 'saturation':
				case 'brightness':
					if (!value.match(/^[0-9]{1,3}$/) || value.toInt() > 100){
						element.set('value', 100);
						return true;
					}
					break;
				case 'red':
				case 'green':
				case 'blue':
					if (!value.match(/^[0-9]{1,3}$/) || value.toInt() > 255){
						element.set('value', 0);
						return true;
					}
					break;
			}

			return true;
		}

	});

window.addEvent('domready', function(){
	this.moorainbow = new MooRainbow();
});

})());
