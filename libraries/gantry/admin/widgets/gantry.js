
var Gantry = {
	init: function() {
		if (document.id('gantry-mega-form')) document.id('gantry-mega-form').set('autocomplete', 'off');
		Gantry.cookie = Cookie.read('gantry-admin');
		Gantry.cleanance();
		Gantry.initTabs();
		Gantry.inputs();
		Gantry.selectedSets();
		Gantry.Overlay = new Gantry.Layer();
		Gantry.Tips.init();
		Gantry.notices();
		Gantry.badges();
		Gantry.toolbarButtons();
		Gantry.loadDefaults();
	},

	load: function() {
	},

	toolbarButtons: function(){
		var actions = document.getElements('[data-g4-toolbaraction]');

		actions.each(function(action){
			var perform = action.get('data-g4-toolbaraction');
			if (perform == 'template.apply') return;

			action.addEvent('click', function(e){
				//e.preventDefault();
				Joomla.submitbutton(perform);
			});
			/*var a = action.getElement('a');
			var onclick = a.get('onclick').replace('javascript:', '');
			a.onclick = function(){};
			a.removeProperty('onclick');
			if (action.id != 'toolbar-new-style'){
				a.addEvent('click', function(e){
					if (a.getElement('span').hasClass('toolbar-inactive')) e.stop();
					else if (action.id != 'toolbar-purge') {
						eval(onclick);
					}
				});
			}*/

		});


		var apply = document.getElement('[data-g4-toolbaraction="template.apply"]');

		if (apply){
/*			var otherButtons = buttons.clone().filter(function(button){
				return button.id != apply.id;
			});*/

			var actionButtons = $$(document.getElements('#g4-toolbar .g4-actions > .rok-button').slice(0, -1));

			var form = document.id('adminForm');
			var currentAction = null;

			var req = new Request({
				url: GantryAjaxURL,
				method: 'post',
				'onRequest': function(){
					//currentAction.addClass('spinner');
					//actionButtons.addClass('disabled');
				},
				'onSuccess': function(response){
					//currentAction.removeClass('spinner');
					//actionButtons.removeClass('disabled');

					growl.alert('Gantry', response, {duration: 6000});
					//Gantry.NoticeBox.getElement('li').set('html', response);
					//Gantry.NoticeBoxFx.start('opacity', [0, 1]);
				}
			});

			//apply.getElement('a').onclick = function(){};
			//apply.getElement('a').removeProperty('onclick');
			apply.addEvent('click', function(e){
				//e.preventDefault();

				currentAction = apply;

				var query = form.toQueryString().parseQueryString();

				Object.each(query, (function(value, key){
					if (key.contains('[]')) {
						delete query[key];
						query[key.replace('[]', '')] = (typeof value == 'string') ? [value] : value;
					}
				}));

				req.post(Object.merge(query, {
					'model': 'template-save',
					'action': 'save',
					'task': 'ajax'
				}));
			});
		}

		var reset = document.id('toolbar-purge');
		if (reset){
			reset.addEvent('click', function(e){
				e.preventDefault();

				if (Gantry.defaults){
					var field, toggle;
					Gantry.defaults.each(function(value, key){
						field = document.id(key);
						if (field){
							field.set('value', value);
							if (field.get('tag') == 'select') field.fireEvent('change');
							if (field.hasClass('toggle-input')){
								toggle = field.getParent('.toggle');
								if (value == '0' && !toggle.hasClass('toggle-off')) toggle.removeClass('toggle-on').addClass('toggle-off');
								else if (value == '1' && !toggle.hasClass('toggle-on')) toggle.removeClass('toggle-off').addClass('toggle-on');
							} else if (field.hasClass('slider') || field.hasClass('layouts-input')) {
								var slider = window.sliders[(field.id.replace(/-/, '_')).replace("-", "_")];
								slider.hiddenEl.fireEvent('set', value);
							} else if (field.id.contains('_font_family')){
								if (!value.contains(':')) value = 's:' + value;
								field.set('value', value);
							} else if (field.className.contains('picker-input')){
								document.getElement('[data-moorainbow-trigger=' + field.id + '] .overlay').setStyle('background-color', value);
							}
						}
					});

					Scroller.involved.setStyle('display', 'none');
					document.getElements('.preset-info').dispose();

					growl.alert('Gantry', 'Fields have been reset to default values.', {duration: 6000});
					//Gantry.NoticeBox.getElement('li').set('html', 'Fields have been reset to default values.');
					//Gantry.NoticeBoxFx.start('opacity', [0, 1]);
				}
			});
		}
	},

	notices: function() {
		Gantry.NoticeBox = document.id('system-message');

		var close = Gantry.NoticeBox ? Gantry.NoticeBox.getElement('.close') : false;
		if (close) {
			Gantry.NoticeBoxFx = new Fx.Tween(Gantry.NoticeBox, {
				duration: 200,
				link: 'ignore',
				onStart: function(){
					Gantry.NoticeBox.setStyle('display', 'block');
				}
			});
			close.addEvent('click', function(){
				Gantry.NoticeBoxFx.start('opacity', 0).chain(function(){
					Gantry.NoticeBox.setStyle('display', 'none');
				});
			});
		}

		var deletOverride = $$('.overrides-button.button-del');
		deletOverride.addEvent('click', function(e) {
			var del = confirm(GantryLang.are_you_sure);
			if (!del) e.preventDefault();
		});
	},

	dropdown: function() {
		var inside = document.id('overrides-inside'), first = document.id('overrides-first'), delay = null;
		var slide = new Fx.Slide('overrides-inside', {
			duration: 100,
			onStart: function() {
				var width = document.id('overrides-actions').getSize().x - 4;
				inside.setStyle('width', width);
				this.wrapper.setStyle('width', width + 4);
			},
			onComplete: function() {
				if (!this.open) first.removeClass('slide-down');
			}
		}).hide();
		inside.setStyle('display', 'block');

		var enterFunction = function() {
			if (inside.hasClass('slidedown')) {
				slide.slideIn();
				first.addClass('slide-down');
			}
		};

		var leaveFunction = function() {
			if (inside.hasClass('slideup')) {
				slide.slideOut();
			}
		};


		$$('#overrides-toggle, #overrides-inside').addEvents({
			'mouseenter': function() {
				$clear(delay);
				inside.removeClass('slideup').addClass('slidedown');
				delay = enterFunction();
			},
			'mouseleave': function() {
				$clear(delay);
				inside.removeClass('slidedown').addClass('slideup');
				leaveFunction.delay(300);
			}
		});

		Gantry.dropdownActions();

	},

	dropdownActions: function() {
		var dropdown = document.id('overrides-actions'), tools = document.id('overrides-toolbar'), first = document.id('overrides-first');
		var toggle = document.id('overrides-toggle');
		if (tools) {
			var add = tools.getElement('.button-add'), del = tools.getElement('.button-del'), edit = tools.getElement('.button-edit');
			if (edit) {
				edit.addEvent('click', function() {
					if (first.getElement('input')) {
						first.getElement('input').empty().dispose();
						toggle.removeClass('hidden');
						return;
					}
					toggle.addClass('hidden');
					var input = new Element('input', {'type': 'text', 'class': 'add-edit-input', 'value': first.get('text').clean().trim()});
					input.addEvent('keydown', function(e) {
						if (e.key == 'esc') {
							this.empty().dispose();
							toggle.removeClass('hidden');
						}
						else if (e.key == 'enter') {
							e.preventDefault();
							var list = document.id('overrides-inside').getElements('a');
							var index = list.get('text').indexOf(this.value);
							if (index != -1) {
								this.highlight('#ff4b4b', '#fff');
								return;
							}
							document.getElement('input[name=override_name]').set('value', this.value);
							index = list.get('text').indexOf(first.get('text').clean().trim());
							if (index != -1) list[index].set('text', this.value);
							this.empty().dispose();
							toggle.removeClass('hidden');
							first.getElement('a').set('text', this.value);
						}
					});
					input.inject(first, 'top').focus();
				});
			}
		}
	},

	inputs: function() {
		var inputs = $$('.text-short, .text-medium, .text-long, .text-color');
		inputs.addEvents({
			'attach': function() {
				this.removeClass('disabled');
			},

			'detach': function() {
				this.addClass('disabled');
			},

			'set': function(value) {
				this.value = value;
			},

			'keydown': function(e) {
				if (this.hasClass('disabled')) { e.preventDefault(); return; }
			},

			'focus': function() {
				if (this.hasClass('disabled')) this.blur();
			},

			'keyup': function(e) {
				if (this.hasClass('disabled')) { e.preventDefault(); return; }
			}
		});
	},

	selectedSets: function(){
		var sets = $$('.selectedset-switcher select');

		sets.each(function(set, i){
			var id = set.id.replace('_type', '');
			//setsToggle = document.getElement('.selectedset-enabler input[id^='+id+']') || document.getElement('.chain input[id^='+id+'_enabled]');
			set.store('gantry:values', set.getElements('option').get('value'));
			set.addEvent('change', function(){
				this.retrieve('gantry:values').each(function(value){
					var layer = document.id('set-' + value);
					if (layer){ //  && setsToggle && setsToggle.value.toInt()
						layer.removeClass('selectedset-hidden-field');
						layer.setStyle('display', (value == this.value) ? 'table-row-group' : 'none');

						if (window.selectboxes && value == this.value){
							layer.getElements('.selectbox-wrapper').each(function(wrapper){
								wrapper.getElements('.selectbox-top, .selectbox-dropdown').set('style', '');
								window.selectboxes.updateSizes(wrapper);
							});
						}
					}
				}, this);

			});
			set.fireEvent('change');
		});

		$$('.selectedset-enabler input[id]').each(function(set, j){
			set.store('gantry:values', sets[j].retrieve('gantry:values'));
			set.addEvent('onChange', function(){
				this.retrieve('gantry:values').each(function(value){
					var layer = document.id('set-' + value);
					if (layer){
						if (!this.value.toInt()) layer.setStyle('display', 'none');
						else {
							layer.removeClass('selectedset-hidden-field');
							layer.setStyle('display', (value == sets[j].get('value')) ? 'table-row-group' : 'none');
						}
					}
				}, this);
			});
		});

		var menu = document.id('jform_params_menu_type');
		if (menu) menu.fireEvent('change');
	},

	cleanance: function() {
		Gantry.overridesBadges();
		Gantry.tabs = [];
		Gantry.panels = [];
		var paneSlider = document.getElement('.pane-sliders') || document.getElement('#g4-panels');
		var items = paneSlider.getChildren();
		var fieldsets = items.getElement('.panelform'),
			wrapper, container;

		Gantry.tabs = document.getElements('.g4-tabs li');

		if (!wrapper) {
			wrapper = document.getElement('.g4-wrapper');
		}

		if (!container) {
			container = document.getElement('#g4-panels');
		}

		var widgets = document.getElements('#widget-list .widget .widget-top, #wp_inactive_widgets .widget .widget-top');
		if (widgets.length) {
			widgets.each(function(widget) {
				var parent = widget.getParent();
				if (parent.id.contains('gantrydivider')) parent.addClass('gantry-divider');
			});
		}

		var innertabs = fieldsets.getElements('.inner-tabs ul li').flatten();
		var innerpanels = fieldsets.getElements('.inner-panels .inner-panel').flatten();
		innertabs = $$(innertabs); innerpanels = $$(innerpanels);
		innertabs.each(function(tab, i) {
			tab.addEvents({
				'mouseenter': function() {this.addClass('hover');},
				'mouseleave': function() {this.removeClass('hover');},
				'click': function() {
					$$(innerpanels).setStyle('position', 'absolute');
					innerpanels.fade('out');
					innerpanels[i].setStyles({'position': 'relative', 'float': 'left', 'top': 0, 'z-index': 5}).fade('in');
					//Gantry.container.tween('height', panel.retrieve('gantry:height'));
					innertabs.removeClass('active');
					this.addClass('active');
				}
			});
		});

		Gantry.panels = $$('.g4-panel');
		Gantry.wrapper = wrapper;
		Gantry.container = container;
		Gantry.tabs = $$(Gantry.tabs);

		var clearCache = document.id('cache-clear-wrap');
		if (clearCache) {
			var ajaxloading = new Asset.image('images/wpspin_dark.gif', {
				onload: function() {this.setStyles({'display': 'none'}).addClass('ajax-loading').inject(clearCache, 'top');}
			});
			clearCache.addEvent('click', function(e) {
				e.preventDefault();
				new Request.HTML({
					url: AdminURI,
					onRequest: function() { ajaxloading.setStyle('display', 'block'); },
					onSuccess: function() { window.location.reload(); }
				}).post({
					'action': 'gantry_admin',
					'model': 'cache',
					'gantry_action': 'clear'
				});
			});
		}
	},

	overridesBadges: function() {
		$$('.overrides-involved').filter(function(badge) {
			return badge.get('text').trim().clean().toInt();
		}).setStyles({'display': 'block', 'opacity': 1, 'visibility': 'visible'});
	},

	initTabs: function() {
		var max = 0;
		Gantry.panels.setStyles({'position': 'absolute'});
		var pan = document.getElement('#g4-panels .active-panel');
		(pan || Gantry.panels[0]).setStyles({'position': 'relative', 'display': 'inline-block', zIndex: 15});
		Gantry.panels.set('tween', {duration: 'short', onComplete: function() {
			if (!this.to[0].value) this.element.setStyle('display', 'none');
		}});

		Gantry.panels.each(function(panel, i) {
			var height = panel.retrieve('gantry:height');

			Gantry.tabs[i].addEvents({
				'mouseenter': function() {this.addClass('hover');},
				'mouseleave': function() {this.removeClass('hover');},
				'click': function() {
					Cookie.write('gantry-admin-tab', i);
					if (this.hasClass('active')) return;

					$$(Gantry.panels).removeClass('active-panel').setStyle('display', 'none');
					panel.addClass('active-panel');

					Gantry.panels.setStyle('position', 'absolute');
					Gantry.panels.setStyles({'visibility': 'hidden', 'opacity': 0, 'z-index': 5, 'display': 'none'});
					panel.set('morph', {duration: 330});
					panel.setStyles({'display': 'inline-block', 'visibility': 'visible', 'position': 'relative', 'top': -20, 'z-index': 15}).morph({'top': 0, 'opacity': 1});
					//Gantry.container.tween('height', panel.retrieve('gantry:height'));

					Gantry.tabs.removeClass('active');
					this.addClass('active');
				}
			});
		});
	},

	badges: function(){
		var checkboxes = $$('#menu-assignment input[type=checkbox][disabled!=disabled]');
		var toggle = $$('button.jform-rightbtn');
		var badge = $$('.menuitems-involved span');
		if (checkboxes.length && badge.length){
			badge = badge[0];
			var value = badge.get('html').clean().toInt();
			checkboxes.addEvent('click', function(){
				if (this.checked) value += 1;
				else value -= 1;

				badge.set('html', value);
			});
		}

		if (toggle.length){
			toggle = toggle[0];
			toggle.addEvent('click', function(){
				var checks = document.getElements('#menu-assignment input[type=checkbox][disabled!=disabled]');
				if (checks.length){
					checks = checks.filter(function(check){
						return check.checked;
					});

					value = checks.length;
					badge.set('html', value);
				}
			});
		}
	},

	loadDefaults: function() {
		Gantry.defaultsXHR = new Request({
		url: GantryAjaxURL,
			onSuccess: function(response) {
				Gantry.defaults = new Hash(JSON.decode(response));
			}
		}).post({
			model: 'overrides',
			action: (GantryIsMaster) ? 'get_default_values' : 'get_base_values'
		});
	}
};

Gantry.Tips = {
	init: function() {
		// backward G3 tooltips
		if (typeof GantryPanelsTips != 'undefined'){
			var field = null;
			Object.each(GantryPanelsTips, function(tips, tabname){
				Object.each(tips, function(data, id){
					field = document.id(GantryParamsPrefix + id + '-lbl');
					if (field){
						field.set('data-original-title', data['content']).addClass('g4-tooltips');
					}
				});
			});
		}

		// twipsy tooltips
		document.getElements('.g4-tooltips').twipsy({placement: 'above-left', offset: {x: -10, y: -8}});
		document.getElements('.hasTip').each(function(tip){
			tip.removeClass('hasTip').addClass('sprocket-tip');
			tip.set('title', tip.get('title').split('::').pop());
			tip.twipsy({placement: 'below-right', offset: {x: 5, y: 5}, html: true});
		});


		var panels = $$('.g4-panel'), labels;
		if (document.id(document.body).getElement('.defaults-wrap')) {
			labels = panels.getElements('.g4-panel-left .gantry-field > label:not(.rokchecks), .g4-panel-left .gantry-field span[class!=chain-label][class!=group-label] > label:not(.rokchecks)');
		}
		else {
			labels = panels.getElements('.g4-panel-left .gantry-field .base-label label');
		}
		labels.each(function(labelsList, i){
			if (labelsList.length) {
				labelsList.addEvent('mouseenter', function() {
					var index = labelsList.indexOf(this);
					var panel = panels[i];
					if (panel) {
						var id = (!this.id) ? false : 'tip-' + this.id.replace(GantryParamsPrefix, '').replace(/-lbl$/, '');
						var tipArrow = panel.getElement('.gantrytips-left');
						if (tipArrow) {
							if (!id || !document.id(id)) tipArrow.fireEvent('jumpTo', index + 1);
							else tipArrow.fireEvent('jumpById', id);
						}
					}
				});
			}
		});
	}
};

Gantry.Layer = new Class({
	Implements: [Events, Options],
	options: {
		duration: 200,
		opacity: 0.8
	},

	initialize: function(options) {
		var self = this;

		this.setOptions(options);

		this.id = new Element('div', {id: 'gantry-layer'}).inject(document.body);

		this.fx = new Fx.Tween(this.id, {
			duration: this.options.duration,
			link: 'cancel',
			onStart: function(){
				this.id.setStyle('visibility', 'visible');
			}.bind(this),
			onComplete: function() {
				if (!this.to[0].value) {
					self.open = false;
					self.id.setStyle('visibility', 'hidden');
				} else {
					self.open = true;
					self.fireEvent('show');
				}
			}
		}).set('opacity', 0);
		this.id.setStyle('visibility', 'hidden');
		this.open = false;

	},

	show: function() {
		this.fx.start('opacity', this.options.opacity);
	},

	hide: function() {
		this.fireEvent('hide');
		this.fx.start('opacity', 0);
	},

	toggle: function() {
		this[this.open ? 'hide' : 'show']();
	},

	calcSizes: function() {
		this.id.setStyles({
			'width': window.getScrollSize().x,
			'height': window.getScrollSize().y
		});
	}
});

if (!Browser.Engine){
	if (Browser.Platform.ios) Browser.Platform.ipod = true;

	Browser.Engine = {};

	var setEngine = function(name, version){
		Browser.Engine.name = name;
		Browser.Engine[name + version] = true;
		Browser.Engine.version = version;
	};

	if (Browser.ie){
		Browser.Engine.trident = true;

		switch (Browser.version){
			case 6: setEngine('trident', 4); break;
			case 7: setEngine('trident', 5); break;
			case 8: setEngine('trident', 6);
		}
	}

	if (Browser.firefox){
		Browser.Engine.gecko = true;

		if (Browser.version >= 3) setEngine('gecko', 19);
		else setEngine('gecko', 18);
	}

	if (Browser.safari || Browser.chrome){
		Browser.Engine.webkit = true;

		switch (Browser.version){
			case 2: setEngine('webkit', 419); break;
			case 3: setEngine('webkit', 420); break;
			case 4: setEngine('webkit', 525);
		}
	}

	if (Browser.opera){
		Browser.Engine.presto = true;

		if (Browser.version >= 9.6) setEngine('presto', 960);
		else if (Browser.version >= 9.5) setEngine('presto', 950);
		else setEngine('presto', 925);
	}

	if (Browser.name == 'unknown'){
		var ua = navigator.userAgent;
		switch ((ua.match(/(?:webkit|khtml|gecko|trident)/) || [])[0]){
			case 'trident':
				Browser.Engine.trident = true;
				break;
			case 'webkit':
			case 'khtml':
				Browser.Engine.webkit = true;
			break;
			case 'gecko':
				Browser.Engine.gecko = true;
		}
	}
}

window.addEvent('domready', Gantry.init);
//window.addEvent('load', Gantry.load);
