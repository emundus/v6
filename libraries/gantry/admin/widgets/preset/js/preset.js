
var PresetDropdown = {
	list: {},
	init: function(cls) {
		PresetDropdown.list[cls] = document.id(GantryParamsPrefix + cls);

		var objs = selectboxes.getObjects(PresetDropdown.list[cls].getPrevious());
		objs.real.addEvent('change', PresetDropdown.select.bind(PresetDropdown, cls));
	},

	newItem: function(cls, key, value) {
		if (!PresetDropdown.list[cls] && document.getElements('.' + cls).length) return Scroller.addBlock(cls, key, value);

		var li = new Element('li').set('text', value);
		var option = new Element('option', {value: key}).set('text', value);
		var objs = selectboxes.getObjects(PresetDropdown.list[cls].getPrevious());

		var dup = null;

		objs.real.getChildren().each(function(child, i) {
			if (child.value == key) dup = i;
		});

		if (dup == null) {
			option.inject(PresetDropdown.list[cls]);
			li.inject(PresetDropdown.list[cls].getPrevious().getLast().getElement('ul'));
			PresetDropdown.attach(cls);
		} else {
			var real_option = objs.real.getChildren()[dup], real_list = PresetDropdown.list[cls].getPrevious().getLast().getElement('ul').getChildren()[dup];

			real_option.replaceWith(option);
			real_list.replaceWith(li);

			PresetDropdown.attach(cls, dup);
		}

		return true;
	},

	attach: function(cls, index) {
		var objs = selectboxes.getObjects(PresetDropdown.list[cls].getPrevious()), self = this;

		if (index == null) index = objs.list.length - 1;
		var el = objs.list[index];

		el.addEvents({
			'mouseenter': function() {
				objs.list.removeClass('hover');
				this.addClass('hover');
			},
			'mouseleave': function() {
				this.removeClass('hover');
			},
			'click': function() {
				objs.list.removeClass('active');
				this.addClass('active');
				this.fireEvent('select', [objs, index]);
			},
			select: selectboxes.select.pass(selectboxes, [objs, index])
		});
		selectboxes.updateSizes(PresetDropdown.list[cls].getPrevious());
		el.fireEvent('select');
	},

	select: function(cls) {
		var preset = Presets[cls].get(PresetDropdown.list[cls].getPrevious().getElement('.selected span').get('text'));

		var master = document.id('master-items');
		if (master) master = master.hasClass('active');

		new Hash(preset).each(function(value, key) {
			var el = document.id(GantryParamsPrefix + key);

			var type = el.get('tag');

			switch(type) {
				case 'select':
					var values = el.getElements('option').getProperty('value');
					var objs = selectboxes.getObjects(el.getParent());
					selectboxes.select(objs, values.indexOf(value));

					break;

				case 'input':
					var cls = el.getProperty('class');
					el.setProperty('value', value);

					if (cls.contains('picker-input')) {
						el.fireEvent('keyup');
					} else if (cls.contains('background-picker')){
						el.fireEvent('keyup', value);
					} else if (cls.contains('slider')) {
						var slider = window['slider' + key];
						slider.set(slider.list.indexOf(value));
					} else if (cls.contains('toggle')) {
						var n = key.replace("-", '');
						window['toggle' + n].set(value.toInt());
						window['toggle' + n].fireEvent('onChange', value.toInt());
					}

					break;

			}

		});
	}
};

var Scroller = {
	init: function(cls) {
		Scroller.wrapper = document.getElements('.' + cls + ' .scroller .wrapper')[0];
		Scroller.bar = document.getElements('.' + cls + ' .bar')[0];

		if (!Scroller.wrapper || !Scroller.bar) return;

		var HookCookie = 'hide';

		Scroller.hook = document.id('toolbar-show-presets');
		if (Scroller.hook){
			HookCookie = Cookie.read('gantry-'+GantryTemplate+'-adminpresets') || 'hide';
			/*Scroller.hook.getElement('a').onclick = function(){};
			Scroller.hook.getElement('a').removeProperty('onclick');*/
			//Scroller.buttonText('Show Presets');
			Scroller.hook.removeClass('rok-button-active');

			document.id('hack-panel').getFirst().setStyle('display', 'block');
			Scroller.slide = new Fx.Slide('hack-panel', {
				duration: 250,
				transition: 'quad:out',
				link: 'cancel',
				resetHeight: true,
				onStart: function(){
					if (!this.open){
						//document.id('g4-details').setStyle('border-bottom-width', 0);
						document.id('g4-details').addClass('presets-showing');
						//document.id('g4-presets').setStyle('border-bottom-width', 1);
					}

					if (!this.open) Scroller.attachResize();
					else Scroller.detachResize();
				},
				onComplete: function(){
					if (this.open){
						//document.id('g4-details').setStyle('border-bottom-width', 1);
						document.id('g4-details').removeClass('presets-showing');
						//document.id('g4-presets').setStyle('border-bottom-width', 0);
					}
				}
			});

			//if (HookCookie == 'show') Scroller.buttonText('Hide Presets');
			//else Scroller.buttonText('Show Presets');
			Scroller.hook[HookCookie == 'show' ? 'addClass' : 'removeClass']('rok-button-active');

			Scroller.hook.addEvent('click', function(e){
				e.preventDefault();
				if (!Scroller.slide.open) {
					this.addClass('rok-button-active');
					Scroller.slide.slideIn();
					Cookie.write('gantry-'+GantryTemplate+'-adminpresets', 'show');
				} else {
					this.removeClass('rok-button-active');
					Scroller.slide.slideOut();
					Cookie.write('gantry-'+GantryTemplate+'-adminpresets', 'hide');
				}
			});

			//document.id('g4-presets').setStyle('border-bottom', document.id('g4-details').getStyle('border-bottom'));
			//document.id('g4-presets').setStyle('border-bottom-width', !Scroller.slide.open ? 0 : 1);
			//document.id('g4-details').setStyle('border-bottom-width', Scroller.slide.open ? 0 : 1);
			Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
			Scroller[HookCookie == 'show' ? 'attachResize' : 'detachResize']();
			document.id('g4-details')[Scroller.slide.open ? 'addClass' : 'removeClass']('presets-showing');
		}

		Scroller.childrens = Scroller.wrapper.getChildren();

		var size = Scroller.wrapper.getParent().getSize();
		var wrapSize = Scroller.wrapper.getSize();
		Scroller.barWrapper = new Element('div', {
			'class': 'presets-scrollbar',
			'styles': {
				'width': Scroller.bar.getSize().x
			}
		}).inject(Scroller.bar, 'before');

		Scroller.getBarSize();
		Scroller.bar.inject(Scroller.barWrapper).setStyles({'left': 0});

		Scroller.children(cls);

		Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();

		var deleters = document.getElements('.delete-preset');

		deleters.each(function(deleter) {
			deleter.addEvent('click', function(e) {
				e.preventDefault();
				Scroller.deleter(this, cls);
			});
		});

		Scroller.bar.setStyle('width', Scroller.size);
		Scroller.drag(Scroller.wrapper, Scroller.bar);

		if (Scroller.size > size.x){
			Scroller.barWrapper.setStyle('display', 'none');
			Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', 0);
			Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();

			return;
		}


	},

	buttonText: function(txt){
		Scroller.hook.set('text', txt);
	},

	deleter: function(item, cls) {
		var key = item.id.replace('keydelete-', '');

		new Request.HTML({
			url: GantryAjaxURL,
			onSuccess: function(r) {
				Scroller.deleteAction(r, item, cls, key);
				growl.alert('Gantry', 'Preset "'+key+'" has been successfully deleted.', {duration: 6000});
			}
		}).post({
			'model': 'presets-saver',
			'action': 'delete',
			'preset-title': cls,
			'preset-key': key
		});
	},

	deleteAction: function(r, item, cls, key) {
		var wrapperSize,
			HookCookie = Cookie.read('gantry-'+GantryTemplate+'-adminpresets') || 'hide';

		if (PresetsKeys[cls].contains(key)) {
			item.dispose();
		} else {
			var block = item.getParent();
			Scroller.childrens.erase(block);
			block.empty().dispose();

			var last = Scroller.childrens.getLast().addClass('last');
			var first = Scroller.childrens[0].addClass('first');

			wrapperSize = Scroller.wrapper.getStyle('width').toInt();
			Scroller.wrapper.setStyle('width', wrapperSize - 200);
			Scroller.bar.setStyle('width', Scroller.getBarSize());
		}

		if (Scroller.size >= Scroller.wrapper.getParent().getSize().x){
			Scroller.barWrapper.setStyle('display', 'none');
			Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', 0);
			Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
		}

		Scroller.bar.setStyle('left', -2 +(Scroller.barWrapper.getSize().x * Scroller.wrapper.getParent().getScroll().x / Scroller.wrapper.getParent().getScrollSize().x));

		if (typeof CustomPresets != 'undefined' && CustomPresets[key]) delete CustomPresets[key];
	},

	getBarSize: function() {
		var size = Scroller.wrapper.getParent().getSize();
		var wrapSize = Scroller.wrapper.getSize();
		Scroller.size = size.x * Scroller.barWrapper.getStyle('width').toInt() / wrapSize.x;

		return Scroller.size;
	},

	addBlock: function(cls, key, value) {
		var preset = Presets[cls].get(value),
			HookCookie = Cookie.read('gantry-'+GantryTemplate+'-adminpresets') || 'hide';

		if (!preset) {
			if (document.id('contextual-preset-wrap').getStyle('display') == 'none') {
				document.id('contextual-preset-wrap').setStyles({'position': 'absolute', 'top': -3000, 'display': 'block'});
			}
			var last = Scroller.childrens[Scroller.childrens.length - 1], length = Scroller.childrens.length;
			var newBlock = last.clone();
			newBlock.inject(last, 'after').addClass('last').className = "";
			newBlock.className = 'preset' + (length + 1) + ' block last';
			newBlock.getElement('span').set('html', value);
			last.removeClass('last');

			var bg = newBlock.getFirst().getStyle('background-image');
			var tmp = bg.split("/");

			var img = tmp[tmp.length - 1];
			var end = 'url(' + key + '.png)';
			var fin = tmp.join("/").replace(img, end);

			newBlock.getElement('.presets-bg').setStyle('background-image', '');
			newBlock.getElement('.presets-bg').setStyle('background-image', fin);

			var wrapperSize = Scroller.wrapper.getStyle('width').toInt();
			var blockSize = newBlock.getSize().x;
			Scroller.wrapper.setStyle('width', wrapperSize + 200);

			Scroller.bar.setStyle('width', Scroller.getBarSize());
			Scroller.childrens.push(newBlock);

			if (Scroller.size >= Scroller.wrapper.getParent().getSize().x){
				Scroller.barWrapper.setStyle('display', 'none');
				Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', 0);
				Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
			} else {
				Scroller.barWrapper.setStyle('display', 'block');
				Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', null);
				Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
			}

			Scroller.child(cls, newBlock);

			var x = new Element('div', {id: 'keydelete-' + key, 'class': 'delete-preset'}).set('html', '<span>&times;</span>').inject(newBlock);
			x.addEvent('click', function(e) {
				e.preventDefault();
				Scroller.deleter(this, cls);
			});

			if (document.id('contextual-preset-wrap').getStyle('display') == 'block' && document.id('contextual-preset-wrap').getStyle('top').toInt() == -3000) {
				document.id('contextual-preset-wrap').setStyles({'position': 'relative', 'top': 0, 'display': 'none'});
			}
		}
	},

	drag: function(wrapper, bar) {
		Scroller.dragger = new Drag.Move(bar, {
			container: Scroller.barWrapper,
			modifiers: {x: 'left', y: false},
			onDrag: function() {
				var parent = Scroller.wrapper.getParent();
				var size = parent.getSize();
				var x = this.value.now.x * parent.getScrollSize().x / size.x;
				if (x > x / 2) x += 10;
				else x -= 10;
				parent.scrollTo(x);
			}
		});
		Scroller.wrapper.getParent().scrollTo(0);
	},

	child: function(cls, child) {
		child.addEvent('click', function(e) {
			e.preventDefault();
			Scroller.updateParams(cls, child);

			this.addClass('pulsing');
			this.removeClass.delay(250, this, 'pulsing');
			this.addClass.delay(500, this, 'pulsing');
			this.removeClass.delay(750, this, 'pulsing');
		});
	},

	children: function(cls) {
		Scroller.childrens.each(function(child, i) {
			Scroller.labs = new Hash({});
			Scroller.involved = document.getElements('.presets-involved');
			Scroller.involvedFx = [];
			Scroller.involved.each(function(inv) {
				Scroller.involvedFx.push(new Fx.Tween(inv, {link: 'cancel'}).set('opacity', 0));
			});

			child.addEvent('click', function(e) {
				e.preventDefault();
				Scroller.updateParams(cls, child, i);

				this.addClass('pulsing');
				this.removeClass.delay(250, this, 'pulsing');
				this.addClass.delay(500, this, 'pulsing');
				this.removeClass.delay(750, this, 'pulsing');
			});
		});
	},

	attachResize: function(){
		if (window.retrieve('gantry:presets:resize')) return;

		window.store('gantry:presets:resize', true);
		window.addEvent('resize', Scroller.resize);
		Scroller.resize.delay(5, Scroller.resize);
	},

	detachResize: function(){
		if (!window.retrieve('gantry:presets:resize')) return;

		window.store('gantry:presets:resize', false);
		window.removeEvent('resize', Scroller.resize);
	},

	resize: function(){
		var winsize = window.getSize().x,
			HookCookie = Cookie.read('gantry-'+GantryTemplate+'-adminpresets') || 'hide';

		winsize = winsize >= 1000 ? 1000 : winsize - 30;

		Scroller.barWrapper.setStyle('width', winsize);
		Scroller.barWrapper.getParent('.presets').setStyle('width', winsize);
		Scroller.bar.setStyle('width', Scroller.getBarSize());
		Scroller.bar.setStyle('left', -2 +(Scroller.barWrapper.getSize().x * Scroller.wrapper.getParent().getScroll().x / Scroller.wrapper.getParent().getScrollSize().x));

		if (Scroller.size >= Scroller.wrapper.getParent().getSize().x){
			Scroller.barWrapper.setStyle('display', 'none');
			Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', 0);
			Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
		} else {
			Scroller.barWrapper.setStyle('display', 'block');
			Scroller.barWrapper.getPrevious('.scroller').setStyle('margin-bottom', null);
			Scroller.slide[HookCookie == 'show' ? 'show' : 'hide']();
		}
	},

	updateParams: function(cls, child, index) {
		var keyPreset = child.getElement('span').get('text');
		var preset = Presets[cls].get(keyPreset);

		var del = child.getElement('.delete-preset');
		if (del) {
			var customKey = del.id.replace("keydelete-", "");
			if (CustomPresets[customKey]) preset = CustomPresets[customKey];
		}


		var master = document.id('master-items');
		if (master) master = master.hasClass('active');


		var currentParams = {};
		var labels = Scroller.labs;

		labels.each(function(labelsList) {
			labelsList.each(function(label) {
				var txt = label.retrieve('gantry:text', false);
				if (txt) {
					label.set('text', txt);
					label.store('gantry:notice', false);
				}
				Scroller.involved.set('text', 0);
			});
		});

		new Hash(preset).each(function(value, key) {

			if (key == 'name') return;
			var el = document.id(GantryParamsPrefix + key.replace(/-/g, '_'));
			if (!el) return;

			if (!labels.get(keyPreset)) labels.set(keyPreset, []);
			var type = el.get('tag');

			var panel = el.getParent('.g4-panel').className.replace(/[panel|\-|\s|g4]/g, '').toInt() - 1;

			if (!currentParams[panel]) currentParams[panel] = 0;
			currentParams[panel]++;
			Scroller.involved[panel].set('text', currentParams[panel]);

			var label;
			if (!el.getParent('.gantry-field')){
				label = el.getParent().getPrevious().getElement('label');
			} else {
				if (el.getParent('.gantry-field').getElement('.base-label')) label = el.getParent('.gantry-field').getElement('.base-label label');
				else label = el.getParent('.gantry-field').getElement('label');
			}

			var lKey = labels.get(keyPreset);

			if (!lKey.contains(label)) lKey.push(label);
			if (!label.retrieve('gantry:notice', false)) {
				label.store('gantry:text', label.get('html'));
				label.set('html', '<span class="preset-info"></span> ' + label.retrieve('gantry:text'));
				label.store('gantry:notice', true);
			}

			switch(type) {
				case 'select':
					//var values = el.getElements('option').getProperty('value');
					//var objs = selectboxes.getObjects(el.getParent());
					//selectboxes.select(objs, values.indexOf(value));
					el.set('value', value);
					el.fireEvent('change');
					if (typeof jQuery != 'undefined') jQuery("#" + el.id).trigger("liszt:updated");
					break;

				case 'input':
					var cls = el.get('class');
					el.set('value', value);

					if (!cls){
						el.set('value', value);
					}
					else if (cls.contains('picker-input')) {
						document.getElement('[data-moorainbow-trigger=' + el.id + '] .overlay').setStyle('background-color', value);
					} else if (cls.contains('background-picker')){
						el.fireEvent('keyup', value);
					} else if (cls.contains('slider')) {
						var slider = window.sliders[(GantryParamsPrefix + key.replace(/-/g, '_')).replace("-", "_")];
						slider.set(slider.list.indexOf(value));
						slider.hiddenEl.fireEvent('set', value);
					} else if (cls.contains('toggle')) {
						el.set('value', value);
						el.getParent('.toggle').removeClass('toggle-off').removeClass('toggle-on').addClass(value == '1' ? 'toggle-on' : 'toggle-off');
						//var field = (GantryParamsPrefix + key.replace(/-/, '_')).replace("-", '');
						//field = document.id(field);
						//field.getParent('.toggle-container').fireEvent('mouseenter');
						//field.fireEvent('set', [field.retrieve('details'), value.toInt()]);
						//field.fireEvent('onChange', value.toInt());
					}

					break;

			}

		});

		Scroller.involved.each(function(inv, i) {
			var value = inv.get('text').toInt();
			if (!value) {
				Scroller.involvedFx[i].element.getParent().removeClass('double-badge');
				Scroller.involvedFx[i].cancel().start('opacity', [1, 0]).chain(function() { this.element.setStyle('display', 'none');});
				return;
			}

			var overrides = Scroller.involvedFx[i].element.getNext('span');
			if (overrides && overrides.getStyle('display') == 'block') Scroller.involvedFx[i].element.getParent().addClass('double-badge');
			else Scroller.involvedFx[i].element.getParent().removeClass('double-badge');
			inv.setStyle('display', 'block');
			Scroller.involvedFx[i].element.setStyles({'visibility': 'visible', 'display': 'block', opacity: 0});
			Scroller.involvedFx[i].start('opacity', [0, 1]);
		});
	}
};


var PresetsBadges = {
	init: function(cls) {
		if (!PresetsBadges.list) PresetsBadges.list = new Hash();

		var label = PresetsBadges.getLabel(cls);
		var params = [];

		PresetsBadges.list.set(cls, []);

		Presets[cls].each(function(value, key) {
			if (!params.length) {
				for (var p in value) {
					params.push(p);
					var labelChild = PresetsBadges.getLabel(p);
					if (labelChild) {
						var badge = PresetsBadges.build(p, labelChild, label, false);
						PresetsBadges.list.get(cls).push(badge);
					}
				}
			}
		});

		if (!PresetsBadges.buttons) PresetsBadges.buttons = [];

		var button = PresetsBadges.build(cls, label, false, params.length);
		PresetsBadges.buttons.push(button);

		button.addEvents({
			'click': function(e) {
				e.preventDefault()

				this.fireEvent('toggle');
			},

			'show': function() {
				this.getElement('.number').setStyle('visibility', 'visible');
				document.getElements(PresetsBadges.list.get(cls)).setStyle('display', 'block');

				this.showing = true;
			},

			'hide': function() {
				this.getElement('.number').setStyle('visibility', 'hidden');
				document.getElements(PresetsBadges.list.get(cls)).setStyle('display', 'none');

				this.showing = false;
			},

			'toggle': function() {
				PresetsBadges.buttons.each(function(b) {
					if (b != button) b.fireEvent('hide');
				});

				if (this.showing) this.fireEvent('hide');
				else this.fireEvent('show');
			}
		});
	},

	build: function(cls, label, parent, count) {
		var children = label.getChildren(), height = label.getSize().y, badge;

		var wrapper = label.getElement('.presets-wrapper');
		if (!wrapper) {
			wrapper = new Element('div', {'class': 'presets-wrapper', 'styles': {'position': 'relative'}}).inject(label, 'top');
			children.each(wrapper.adopt.bind(wrapper));
			wrapper.setStyle('height', height + 15);
			label.getElement('.hasTip').setStyle('line-height', height + 15);
		}

		var text = (parent) ? parent.getElement('.hasTip').innerHTML : GantryLang.show_parameters;

		badge = new Element('div', {'class': 'presets-badge'}).inject(wrapper, 'top');

		var left = new Element('span', {'class': 'left'}).inject(badge);
		var right = new Element('span', {'class': 'right'}).inject(left).set('text', text);

		if (count != null) {
			var number = new Element('span', {'class': 'number'}).inject(right);
			number.set('text', count).setStyle('visibility', 'hidden');
			badge.setStyle('cursor', 'pointer').addClass('parent');
		} else {
			badge.setStyle('display', 'none');
			var layer = label.getNext().getFirst().getLast();
			if (layer) {
				var top = layer.getStyle('top').toInt();
				layer.setStyle('top', top - 10);
			}
		}

		return badge;

	},

	getLabel: function(cls) {
		var search = document.id(GantryParamsPrefix + cls);
		if (search) {
			var parent = search.getParent(), match = null;
			while (parent && parent.get('tag') != 'table') {
				if (parent.get('tag') == 'tr') match = parent;
				parent = parent.getParent();
			}

			return match.getFirst();
		} else {
			return null;
		}
	}
};
