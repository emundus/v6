((function(){

	var GF = this.GantryFonts = {
			init: function(data){
				this.data = data;
				this.element = document.id(data.param);
				this.element.store('g4:fonts:value', this.element.get('value'));

				Object.each(data.paths, function(source, groupName){
					this.load(groupName, source.delim, data.baseurl + source.json);
				}, this);
			},

			load: function(groupName, delim, jsonPath){
				var optgroup = new Element('optgroup', {label: groupName}).inject(this.element),
					loading = new Element('option', {value: '-1', text: 'Loading...'}).inject(optgroup, 'top'),  value, variant, option;

				new Request.JSON({
					url: jsonPath,
					method: 'get',
					onSuccess: function(response){
						for (var i = 0, l = response.items.length; i < l; i++) {
							value = response.items[i].family;
							variant = ':';
							for (var j = 0, k = response.items[i].variants.length; j < k; j++) {
								if (j < k - 1) {
									variant = variant + response.items[i].variants[j] + ',';
								} else {
									variant = variant + response.items[i].variants[j];
								}
								//console.log(value, variant);
							}
							option = new Element('option', {text: value, value: delim + value + variant}).inject(optgroup);
						}

						//this.element.set('value', this.element.retrieve('g4:fonts:value'));
						loading.dispose();
						this.validate();
						if (typeof jQuery != 'undefined') jQuery("#" + this.data.param).trigger("liszt:updated");
					}.bind(this),
					onError: function(text, error){
						loading.set('text', 'Error(' + groupName + '): ' + error);
					}
				}).send();
			},

			validate: function(){
				var value = this.element.get('data-value');
				if (value.contains(':')) return this.element.set('value', value);

				var element = this.element.getElement('[value$=:' + value + ']');
				this.element.set('value', (element ? element : this.element.getElement('option')).get('value'));
			}
	};

})());
