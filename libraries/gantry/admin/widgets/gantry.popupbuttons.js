((function(){
	var G4PopupButtons = this.G4PopupButtons = new Class({
		Implements: [Options, Events],
		options: {
			data: 'g4'
		},
		initialize: function(options){
			this.setOptions(options);

			this.active = null;
			this.attach();
		},

		attach: function(){

			var relay = {
				toggle: document.retrieve(this.options.data + ':events:buttonstoggle', function(event, target){
					this.show.call(this, event, target);
				}.bind(this)),
				click: document.retrieve(this.options.data + ':events:itemclick', function(event, target){
					this.hide.call(this, event, target);
				}.bind(this))
			};

			document.addEvent('click:relay([data-' + this.options.data + '-toggle])', relay.toggle);
			document.addEvent('click:relay([data-' + this.options.data + '-dropdown] >)', relay.click);
		},

		attachDocument: function(){
			if (document.retrieve(this.options.data + ':attached:document')) return;

			var relay = {
				document: document.retrieve(this.options.data + ':events:hide', function(event, target){
					target = target || event.target;
					this.hide.call(this);
				}.bind(this))
			};

			document.addEvent('click', relay.document);
			document.store(this.options.data + ':attached:document', true);
		},

		detachDocument: function(){
			if (!document.retrieve(this.options.data + ':attached:document')) return;

			var hide = document.retrieve(this.options.data + ':events:hide');
			document.removeEvent('click', hide);
			document.store(this.options.data + ':attached:document', false);
		},

		show: function(event, target){
			if (this.active == target) return;

			this.hide();
			this.attachDocument();
			this.active = target;

			var value = this.active.get('data-'+this.options.data+'-toggle'),
				popup = document.getElement('[data-'+this.options.data+'-dropdown="'+value+'"]');

			this.active.addClass('rok-button-active');
			popup.setStyle('display', 'block');
		},

		hide: function(){
			if (this.active){
				var value = this.active.get('data-'+this.options.data+'-toggle'),
					popup = document.getElement('[data-'+this.options.data+'-dropdown="'+value+'"]');

				this.active.removeClass('rok-button-active');
				popup.setStyle('display', 'none');

				this.active = null;
				this.detachDocument();
			}
		}
	});

	this.g4PopupButtons = new G4PopupButtons();
})());
