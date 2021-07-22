
Gantry.PresetsSaver = {
	init: function() {
		var saver = document.id('toolbar-save-preset');

		Gantry.PresetsSaver.bounds = {
			'show': Gantry.PresetsSaver.build.bind(Gantry.PresetsSaver)
		};

		saver.addEvent('click', function(e) {
			e.stop();

			Gantry.Overlay.addEvent('show', Gantry.PresetsSaver.bounds.show);
			Gantry.Overlay.show();
		});

		Gantry.PresetsSaver.Template = $$('input[name=id]');
		if (Gantry.PresetsSaver.Template.length) Gantry.PresetsSaver.Template = Gantry.PresetsSaver.Template[0].value;
		else Gantry.PresetsSaver.Template = false;
	},

	build: function() {
		var table = new Element('div.presets-wrapper-table').inject(document.body);
		var row = new Element('div.presets-wrapper-row').inject(table);
		var cell = new Element('div.presets-wrapper-cell').inject(row);
		var wrapper = new Element('div', {'id': 'presets-namer', 'class': 'gantry-layer-wrapper'}).inject(cell);
		var title = new Element('h2').set('text', GantryLang.preset_title).inject(wrapper);
		var innerWrapper = new Element('div', {'class': 'preset-namer-inner'}).inject(wrapper);

		Gantry.PresetsSaver.wrapper = wrapper;
		Gantry.PresetsSaver.innerWrapper = innerWrapper;

		var desc = new Element('p').set('text', GantryLang.preset_select).inject(innerWrapper),
			skip;

		var hash = new Hash(Presets);
		hash.each(function(value, key) {
			var namer = new Element('div', {'class': 'preset-namer valid-preset-' + key}).inject(innerWrapper);
			var head = new Element('h3', {'class': 'preset-namer-title'}).set('html', GantryLang.preset_naming + ' "<span>'+key+'</span>"').inject(namer);
			if (hash.length > 1) skip = new Element('span', {'class': 'skip'}).set('text', GantryLang.preset_skip).inject(head);
			var valuename = new Element('div').set('html', '<label><span>'+GantryLang.preset_name+'</span><input type="text" class="text-long input-name" id="'+key+'_namer_name" /></label>').inject(namer);
			var keyname = new Element('div').set('html', '<label><span>'+GantryLang.key_name+'</span><input type="text" class="text-long input-key example" tabindex="-1" id="'+key+'_namer_key" /></label>').inject(namer);

			var inputValue = valuename.getElement('input'), inputKey = keyname.getElement('input');

			Gantry.PresetsSaver.valExample = "ex, Preset 1";
			Gantry.PresetsSaver.keyExample = "(optional) ex, preset1";
			var valExample = Gantry.PresetsSaver.valExample, keyExample = Gantry.PresetsSaver.keyExample;

			inputValue.addClass('example').value = valExample;
			inputKey.value = keyExample;

			inputValue.addEvents({
				'focus': function() {if (this.value == valExample) this.value = '';this.removeClass('example');},
				'blur': function() {if (this.value == '') this.addClass('example').value = valExample;Gantry.PresetsSaver.checkInputs();},
				'keyup': function() {
					this.value = this.value.replace(/[^a-z0-9\s]/gi, '');
					if (this.value.length) inputKey.value = this.value.toLowerCase().clean().replace(/\s/g, "");
					Gantry.PresetsSaver.checkInputs();
				}
			});

			inputKey.addEvents({
				'focus': function() {if (this.value == keyExample) this.value = '';this.removeClass('example');},
				'blur': function() {
					if (this.value == '' && (inputValue.value != '' && inputValue.value != valExample)) {
						this.value = inputValue.value.toLowerCase().clean().replace(/\s/g, "");
					}
					else if (this.value == '') {
						this.value = keyExample;
					}
					this.addClass('example');
					Gantry.PresetsSaver.checkInputs();
				},
				'keyup': function(e) {
					this.value = this.value.replace(/[^a-z0-9\s]/gi, '');
					this.value = this.value.toLowerCase().clean().replace(/\s/g, "");
					Gantry.PresetsSaver.checkInputs();
				}
			});

			if (hash.getLength() > 1) {
				var fx = new Fx.Morph(namer, {
					'duration': 200,
					onComplete: function() {
						namer.empty().dispose();
						//Gantry.PresetsSaver.center(wrapper);
						Gantry.PresetsSaver.checkInputs();
					}
				}).set({'opacity': 1});
				skip.addEvent('click', function() {
					inputValue.removeEvents('focus').removeEvents('blur').removeEvents('keyup');
					inputKey.removeEvents('focus').removeEvents('blur').removeEvents('keyup');
					namer.setStyle('overflow', 'hidden');
					fx.start({
						'opacity': 0,
						'height': 0
					});
				});
			}
		});


		GantryLang['save'] = GantryLang['save'].toLowerCase().capitalize();
		GantryLang['cancel'] = GantryLang['cancel'].toLowerCase().capitalize();
		GantryLang['close'] = GantryLang['close'].toLowerCase().capitalize();
		GantryLang['retry'] = GantryLang['retry'].toLowerCase().capitalize();

		var bottom = new Element('div', {'class': 'preset-bottom'}).inject(wrapper);
		Gantry.PresetsSaver.savePreset = new Element('div', {'class': 'rok-button rok-button-primary rok-button-disabled'}).set('text', GantryLang['save']).inject(bottom);
		Gantry.PresetsSaver.cancel = new Element('div', {'class': 'rok-button'}).set('text', GantryLang['cancel']).inject(bottom);

		Gantry.PresetsSaver.savePreset.addClass('rok-button-primary');

		Gantry.PresetsSaver.cancel.addEvent('click', function(e) {
			Gantry.Overlay.removeEvent('show', Gantry.PresetsSaver.bounds.show);
			table.empty().dispose();
			Gantry.Overlay.hide();
		});

		Gantry.PresetsSaver.savePreset.addEvent('click', Gantry.PresetsSaver.save);

		//Gantry.PresetsSaver.center(wrapper);
	},

	checkInputs: function() {
		var checks = [], inputs = Gantry.PresetsSaver.wrapper.getElements('input');
		inputs.each(function(input, i) {
			if (input.value != '' && input.value != Gantry.PresetsSaver[(!i % 2) ? 'valExample' : 'keyExample']) checks[i] = true;
			else checks[i] = false;
		});

		var check = checks.contains(false);


		if (check || !inputs.length) Gantry.PresetsSaver.savePreset.addClass('rok-button-disabled');
		else Gantry.PresetsSaver.savePreset.removeClass('rok-button-disabled');

		return check;
	},

	save: function() {
		if (!Gantry.PresetsSaver.checkInputs || Gantry.PresetsSaver.savePreset.hasClass('rok-button-disabled')) return;
		var inputs = [];
		Gantry.PresetsSaver.wrapper.getElements('.preset-namer').each(function(box) {
			inputs.push(box.getElements('input'));
		});

		Gantry.PresetsSaver.data = Gantry.PresetsSaver.getPresets(inputs);
		var data = Gantry.PresetsSaver.data;

		new Request.HTML({
			url: GantryAjaxURL,
			onSuccess: Gantry.PresetsSaver.handleResponse
		}).post({
			'model': 'presets-saver',
			'action': 'add',
			'presets-data': JSON.encode(data)
		});
	},

	handleResponse: function(tree, nodes, response) {
		var wrapper = Gantry.PresetsSaver.wrapper, inner = Gantry.PresetsSaver.innerWrapper;

		var icon, title, msg;

		if (response.clean() == "success") {
			$H(Gantry.PresetsSaver.data).each(function(value, key) {
				$H(value).each(function(inner_value, inner_key) {
					var name = inner_value.name;
					PresetDropdown.newItem(key, inner_key, name);
					delete inner_value.name;
					Presets[key].set(name, inner_value);
				});
			});

			/*var success = new Element('div', {'class': 'preset-success'}).inject(inner, 'after');
			inner.empty().dispose();
			icon = new Element('div', {'class': 'success-icon'}).inject(success);
			title = new Element('div').set('html', '<h3>'+ GantryLang.success_save.toLowerCase().capitalize() +'</h3>').inject(success);
			msg = new Element('div').set('html', GantryLang.success_msg).inject(success);
			Gantry.PresetsSaver.savePreset.setStyle('display', 'none');
			Gantry.PresetsSaver.cancel.set('html', GantryLang.close);*/

			Gantry.PresetsSaver.cancel.fireEvent('click');
			growl.alert('Gantry', GantryLang.success_msg, {duration: 5000});
		} else {
			inner.setStyle('display', 'none');
			var error = new Element('div', {'class': 'preset-error'}).inject(inner, 'after');
			icon = new Element('div', {'class': 'error-icon'}).inject(error);
			title = new Element('div').set('html', '<h3>' + GantryLang.fail_save + '</h3>').inject(error);
			msg = new Element('div').set('html', GantryLang.fail_msg).inject(error);

			var retry = Gantry.PresetsSaver.savePreset.clone();
			Gantry.PresetsSaver.savePreset.setStyle('display', 'none');
			retry.inject(Gantry.PresetsSaver.savePreset, 'before').set('html', GantryLang.retry).addEvent('click', function() {
				error.empty().dispose();
				inner.setStyle('display', 'block');
				Gantry.PresetsSaver.savePreset.setStyle('display', '');
				retry.dispose();
			});
		}

	},

	center: function(el) {
		var winSize = window.getSize();
		var elSize = el.getSize();

		var positions = {
			'left': (winSize.x / 2) + window.getScroll().x - elSize.x / 2,
			'top': (winSize.y / 2) + window.getScroll().y - elSize.y / 2
		};

		el.setStyles(positions);
	},

	getPresets: function(inputs) {
		var presets = new Hash(Presets);
		var i = 1, j = 0;
		var storing = {};

		presets.each(function(value, key) {
			if (!Gantry.PresetsSaver.wrapper.getElement('.valid-preset-' + key)) return;
			var hash = presets.get(key);

			storing[key] = {};
			storing[key][inputs[j][1].value] = {};
			storing[key][inputs[j][1].value].name = inputs[j][0].value;

			i = 1;
			hash.each(function(presetvalue, presetkey) {
				presetvalue = new Hash(presetvalue);

				if (i>1) return;
				else {
					presetvalue.each(function(param, paramkey) {
						var paramkeyUnderscore = paramkey.replace(/-/g, '_'),
							paramkeyDash = paramkey.replace(/_/g, '-');
						if (document.id(GantryParamsPrefix+paramkeyUnderscore)) storing[key][inputs[j][1].value][paramkeyDash] = document.id(GantryParamsPrefix+paramkeyUnderscore).get('value') || '';
					});
				}
				i++;
			});

			j++;
		});

		return storing;
	}
};

window.addEvent('domready', Gantry.PresetsSaver.init);
