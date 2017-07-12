
((function(){

	var AjaxButtons = new Class({

		Implements: [Options, Events],
		options:{ url: null },

		initialize: function(options){
			this.setOptions(options);

			this.request = new Request({url: this.options.url});

			this.attach();
		},

		attach: function(){
			var click = document.retrieve('gantry:ajaxbutton', function(event, element){
					this.click.call(this, event, element);
				}.bind(this));

			document.addEvent('click:relay([data-ajaxbutton])', click);
		},

		detach: function(){
			var click = document.retrieve('gantry:ajaxbutton');
			document.removeEvent('click:relay([data-ajaxbutton])', click);
		},

		click: function(event, element){
			if (event) event.preventDefault();
			var data = JSON.decode(element.get('data-ajaxbutton')),
				params = {
					model: data.model,
					action: data.action
				},

				onSuccess = function(response){
					growl.alert('Gantry', response, {duration: 6000});
					this.request.removeEvents(events);
				}.bind(this),

				events = {
					onSuccess: onSuccess
				};

			this.request.addEvents(events).post(params);
		}
	});

	window.addEvent('domready', function(){
		(typeof Gantry != 'undefined' ? Gantry : this)['AjaxButtons'] = new AjaxButtons({url: GantryAjaxURL});
	});


})());
