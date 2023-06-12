/**
 * @package    HikaShop for Joomla!
 * @version    4.7.3
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {
var formCustom = {
	defaultOptions: {
		mainArea: '#hikashop_product_backend_page_edition .hk-container-fluid',
		type: 'product',
		handle: '.hikashop_product_part_title',
		namePrefix: 'hikashop_product_edit_',
		elements: '.hikashop_product_block:not(.hikashop_product_new_block)',
		fieldsElements: '.hika_options',
		labels: 'dd',
		labelPrefix: 'hikashop_product_',
		fields: true,
		blocks: true,
		skipEmpty: false,
		index: 0,
		customize: 1,
		hide: 1,
		template: '<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_{NAMEKEY}"><div><div class="hikashop_product_part_title hikashop_product_edit_{NAMEKEY}_title"><span class="hikashop_tile_title">{TITLE}</span><a href="#" onclick="window.formCustom.removeBlock(\'{NAMEKEY}\',{KEY}); return false;" title="Remove block" class="hikabtn hikabtn-danger btn-small block-remove-btn" style="display:inline;"><i class="fa fa-trash"></i></a></div><dl class="hika_options"></dl></div></div>',
	},
	options: [],
	initDragAndDrop: function(options) {
		for (var key in this.defaultOptions) {
			if (!options.hasOwnProperty(key)) {
				options[key] = this.defaultOptions[key];
			}
		}
		this.options.push(options);
		options.index = this.options.length - 1;

		if(options.fields) {
			if(options.customize) {
				var settings = document.querySelectorAll(options.mainArea+' '+options.fieldsElements);
				for(var i=0; i < settings.length; i++) {
					new Sortable(settings[i], {
						group: options.type+'_fields',
						filter: options.labels,
						animation: 150,
						preventOnFilter: false,
						emptyInsertThreshold: 20,
						// Element dragging started
						onChoose: function (/**Event*/evt) {
							evt.oldIndex;  // element index within parent
							var active_parent = document.querySelector('.hikashop_customize_area');
							active_parent.classList.add('hikashop_customize_area_options');
						},
						// Element is unchosen
						onUnchoose: function(/**Event*/evt) {
							// same properties as onEnd
							var active_parent = document.querySelector('.hikashop_customize_area');
							active_parent.classList.remove('hikashop_customize_area_options');
						},
						onEnd: function (evt) {
							// Remove class parent :

							if(evt.item.nodeName == 'DT') {
								var corresponding = document.querySelector(options.mainArea+' '+'dd.'+evt.item.classList);
								if(evt.item.nextSibling && evt.item.nextSibling.nodeName == 'DD') {
									evt.item.parentNode.insertBefore(evt.item.nextSibling, evt.item);
								}
								evt.item.parentNode.insertBefore(corresponding, evt.item.nextSibling);
							}
							window.formCustom.setFieldsOrder(options);
						},
					});
				}
			}
			// reorder the fields and create extra blocks on page load
			this.sortFields(options);

			// refresh the input with the structure, just in case
			if(options.customize) {
				this.setFieldsOrder(options);
			}
		}

		if(options.blocks) {
			if(options.customize) {
				var list = document.querySelector(options.mainArea);
				new Sortable(list, {
					group: options.type+'_shared',
					animation: 150,
					handle: options.handle,
					forceAutoScrollFallback: true,
					forceFallback : true,
					// Element dragging started
					onChoose: function (/**Event*/evt) {
						evt.oldIndex;  // element index within parent
						var active_parent = document.querySelector('.hikashop_customize_area');
						active_parent.classList.add('hikashop_customize_area_blocks');
					},
					// Element is unchosen
					onUnchoose: function(/**Event*/evt) {
						// same properties as onEnd
						var active_parent = document.querySelector('.hikashop_customize_area');
						active_parent.classList.remove('hikashop_customize_area_blocks');
					},
					onEnd: function (evt) {
						window.formCustom.setBlocksOrder(options);
					},
				});
			}

			// sort the blocks on page load
			this.initBlocks(options);
		}
		return options.index;
	},
	setFieldsOrder: function(options) {
		var areas = document.querySelectorAll(options.mainArea+' '+options.elements);
		var structure = [];
		for (var i = 0; i < areas.length; i++) {
			var classList = areas[i].className.split(/\s+/);

			var block = {name : '', title : '', hide : 0, fields: []};
			for (var j = 0; j < classList.length; j++) {
				if(classList[j].startsWith(options.namePrefix)) {
					block.name = classList[j].substring(options.namePrefix.length);
				}

				if(classList[j] == 'hikashop_hide_block') {
					block.hide = 1;
				}
			}
			if(block.name == '')
				continue;

			var titleEl = areas[i].querySelector(options.handle);
			if(titleEl)
				block.title = titleEl.textContent;

			var labels = areas[i].querySelectorAll(options.fieldsElements+' '+options.labels);
			for (var j = 0; j < labels.length; j++) {
				var field = '';
				var classList = labels[j].className.split(/\s+/);
				for (var k = 0; k < classList.length; k++) {
					if(classList[k].startsWith(options.labelPrefix)) {
						field = classList[k].substring(options.labelPrefix.length);
						break;
					}
				}
				if(field == '')
					continue;
				block.fields.push(field);
			}
			structure.push(block);
		}
		document.getElementById(options.type+'_areas_fields').value =  JSON.stringify(structure);
	},
	sortFields: function(options) {
		var structureInput = document.getElementById(options.type+'_areas_fields');
		if(!structureInput || structureInput.value == '')
			return;
		var resetBtn = document.querySelector('.reset_block_button');
		if(resetBtn)
			resetBtn.style.display = 'block';

		var structure = null;
		try {
			structure = JSON.parse(structureInput.value);
		} catch(e) {
			console.err(e);
			return;
		}
		var areas = document.querySelectorAll(options.mainArea+' '+options.elements);
		var mainArea = document.querySelector(options.mainArea);
		// loop on the areas in the structure
		for (var i = 0; i < structure.length; i++) {
			var areaData = structure[i];
			var area = null;
			// search the matching area in the DOM
			for (var j = 0; j < areas.length; j++) {
				var classList = areas[j].className.split(/\s+/);
				var name = '';
				for (var k = 0; k < classList.length; k++) {
					if(classList[k].startsWith(options.namePrefix)) {
						name = classList[k].substring(options.namePrefix.length);
						break;
					}
				}
				if(name == '')
					continue;
				if(name == areaData.name) {
					// match
					area = areas[j];
					break;
				}
			}
			if(!area) {
				// create a new area in the DOM
				area = this.addBlock(options, areaData, mainArea);
			}

			if(!area) {
				continue;
			}

			if(areaData.hide && options.hide) {
				this.toggleBlock(areaData.name, options.index, false);
			}

			var optionsList = area.querySelector(options.fieldsElements);

			// reorder the fields in the area
			var found = 0;
			for(var j = 0; j < areaData.fields.length; j++) {
				var field = areaData.fields[j];
				els = mainArea.querySelectorAll(options.fieldsElements+' .'+options.labelPrefix+field);
				if(els && els.length) {
					found += els.length;
					for(var k = 0; k < els.length; k++) {
						if(els[k].parentNode.style.display == 'none') {
							optionsList.appendChild(els[k].parentNode);
						} else {
							optionsList.appendChild(els[k]);
						}
					}
				}
			}
			if(areaData.fields.length > 0 && found == 0 && options.skipEmpty) {
				this.removeBlock(areaData.name, options.index);
			}

		}
	},
	reset: function(key) {
		if(!this.options[key]) {
			console.log('options key '+ key + ' is invalid. Please check your new block HTML');
			return;
		}
		var input = document.getElementById(this.options[key].type+'_reset_custom');
		input.value = 1;
		input.form.querySelector('input[name="task"]').value = 'apply';
		input.form.submit();
	},
	removeBlock: function(name, key) {
		if(!this.options[key]) {
			console.log('options key '+ key + ' is invalid. Please check your new block HTML');
			return;
		}

		var block = document.querySelector(this.options[key].mainArea+' '+this.options[key].elements+'.'+this.options[key].namePrefix+name);
		if(!block) {
			console.log('Could not find block with name '+name);
			return;
		}
		block.remove();
		/*
		var fields = block.querySelectorAll(this.options[key].fieldsElements);
		var blocks = document.querySelectorAll(this.options[key].mainArea+' '+this.options[key].elements+ ' '+this.options[key].fieldsElements);
		for (var i = 0; i < fields.length; i++) {
			while(fields[i].children.length) {
				blocks[blocks.length-1].appendChild(fields[i].children[0]);
			}
		}
		*/
		this.setFieldsOrder(this.options[key]);
	},
	addNewBlock: function(inputId, key) {
		var input = document.getElementById(inputId);
		if(!input) {
			console.log(inputId + ' not found. Please check your new block HTML');
			return;
		}
		if(input.value == '') {
			alert('Please enter a title first');
			return;
		}
		var name = input.value.replace(/[\u0250-\ue007]/g, '').replace(/ /g, '_');
		if(name == '') {
			alert('Please use latin letters');
			return;
		}

		if(!this.options[key]) {
			console.log('options key '+ key + ' is invalid. Please check your new block HTML');
			return;
		}

		var elementsOrder = document.getElementById(this.options[key].type+'_areas_order').value.split(',');
		if(elementsOrder.length && elementsOrder.includes(name)) {
			alert('The title ' + input.value + ' cannot be used. Please enter another one');
			return;
		}

		var parent = document.querySelector(this.options[key].mainArea);

		var data = {title : input.value, name : name};

		var block = this.addBlock(this.options[key], data, parent);

		this.addHideBtn(block, name, this.options[key]);

		input.value = '';
	},
	addBlock: function(options, data, parent) {
		var dv = document.createElement("div");
		var html = options.template.replace(/{TITLE}/g, data.title).replace(/{NAMEKEY}/g, data.name).replace(/{KEY}/g, options.index);
		if(!options.customize || !options.hide) {
			html.replace(/display:inline;/g, 'display:none;');
		}
		dv.innerHTML = html;
		var block = dv.childNodes[0];
		parent.appendChild(block);
		var area = block.querySelector(options.fieldsElements);

		// make its fields area drag&drop
		if(options.customize) {
			new Sortable(area, {
				group: options.type+'_fields',
				filter: options.labels,
				animation: 150,
				preventOnFilter: false,
				emptyInsertThreshold: 20,
				onEnd: function (evt) {
					if(evt.item.nodeName == 'DT') {
						var corresponding = document.querySelector(options.mainArea+' '+'dd.'+evt.item.classList);
						if(evt.item.nextSibling && evt.item.nextSibling.nodeName == 'DD') {
							evt.item.parentNode.insertBefore(evt.item.nextSibling, evt.item);
						}
						evt.item.parentNode.insertBefore(corresponding, evt.item.nextSibling);
					}
					window.formCustom.setFieldsOrder(options);
				},
			});
		}
		return block;
	},
	initBlocks: function(options) {
		if(!this.sortBlocks(options)) {
			this.setBlocksOrder(options);
		}
	},
	setBlocksOrder: function(options) {
		var newElements = document.querySelectorAll(options.mainArea+' '+options.elements);
		var elementsOrder = [];
		for (var i = 0; i < newElements.length; i++) {
			var classList = newElements[i].className.split(/\s+/);
			var name = '';
			for (var j = 0; j < classList.length; j++) {
				if(classList[j].startsWith(options.namePrefix)) {
					name = classList[j].substring(options.namePrefix.length);
					break;
				}
			}
			if(name == '' || elementsOrder.includes(name))
				continue;
			elementsOrder.push(name);
		}
		document.getElementById(options.type+'_areas_order').value =  elementsOrder.join(',');
	},
	toggleBlock: function (name, key, save) {
		if(!this.options[key]) {
			console.log('options key '+ key + ' is invalid. Please check your new block HTML');
			return;
		}

		var block = document.querySelector(this.options[key].mainArea+' '+this.options[key].elements+'.'+this.options[key].namePrefix+name);
		if(!block) {
			console.log('Could not find block with name '+name);
			return;
		}

		if(block.classList.contains('hikashop_hide_block')) {
			block.classList.remove('hikashop_hide_block');
			if(this.options[key].customize)
				block.classList.remove('hikashop_customize_block');
		} else {
			block.classList.add('hikashop_hide_block');
			if(this.options[key].customize)
				block.classList.add('hikashop_customize_block');
		}

		if(save)
			this.setFieldsOrder(this.options[key]);
	},
	sortBlocks: function(options) {
		var elementsOrder = document.getElementById(options.type+'_areas_order').value.split(',');
		if(elementsOrder.length <=1)
			return false;

		var j = 0;
		for (var i = 0; i < elementsOrder.length; i++) {
			var src = document.querySelector(options.mainArea+' '+options.elements+'[data-id="' + elementsOrder[i]+'"]');
			if(!src) {
				var src = document.querySelector(options.mainArea+' '+options.elements+'.'+ options.namePrefix + elementsOrder[i]);
				if(!src)
					continue;
			}

			this.addHideBtn(src, elementsOrder[i], options);
			var newElements = document.querySelectorAll(options.mainArea+' '+options.elements);
			if(!newElements[j])
				j--;
			this.swapNodes(src, newElements[j]);
			j++;
		}
		return true;
	},
	addHideBtn: function (src, name, options) {
		if(options.customize && options.hide && !src.querySelector('.block-hide-btn')) {
			var div  = src.querySelector('.hikashop_product_part_title');
			if(div)
				div.innerHTML = div.innerHTML + ' <a href="#" onclick="window.formCustom.toggleBlock(\''+name+'\','+options.index+', true); return false;" title="Toggle block" class="hikabtn hikabtn-primary btn-small block-hide-btn" style="display:inline;"><i class="fa fa-eye"></i></a>';
		}
	},
	swapNodes: function(n1, n2) {
		var p1 = n1.parentNode;
		var p2 = n2.parentNode;
		var i1, i2;

		if ( !p1 || !p2 || p1.isEqualNode(n2) || p2.isEqualNode(n1) ) return;

		for (var i = 0; i < p1.children.length; i++) {
			if (p1.children[i].isEqualNode(n1)) {
				i1 = i;
			}
		}
		for (var i = 0; i < p2.children.length; i++) {
			if (p2.children[i].isEqualNode(n2)) {
				i2 = i;
			}
		}

		if ( p1.isEqualNode(p2) && i1 < i2 ) {
			i2++;
		}
		p1.insertBefore(n2, p1.children[i1]);
		p2.insertBefore(n1, p2.children[i2]);
	}
};

window.formCustom = formCustom;

})();
