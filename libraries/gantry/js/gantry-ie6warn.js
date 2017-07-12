
var RokIEWarn = new Class({
	'site': 'sitename',
	'initialize': function(msg) {
		var warning = msg;
		this.box = new Element('div', {'id': 'iewarn'}).inject(document.body, 'top');
		var div = new Element('div').inject(this.box).set('html', warning);
		
		var click = this.toggle.bind(this);
		var button = new Element('a', {'id': 'iewarn_close'}).addEvents({
			'mouseover': function() {
				this.addClass('cHover');
			},
			'mouseout': function() {
				this.removeClass('cHover');
			},
			'click': function() {
				click();	
			}
		}).inject(div, 'top');
		
		this.height = document.id('iewarn').getSize().y;
		this.fx = new Fx.Morph(this.box, {duration: 1000}).set({'top': document.id('iewarn').getStyle('top').toInt()});
		this.open = false;
		
		var cookie = Cookie.read('rokIEWarn'), height = this.height;
		//cookie = 'open'; // added for debug to not use the cookie value
		if (!cookie || cookie == "open") this.show();
		else this.fx.set({'top': -height});

		
		return ;
	},
	
	'show': function() {
		this.fx.start({
			'top': 0
		});
		this.open = true;
		Cookie.write('rokIEWarn', 'open', {duration: 7});
	},	
	'close': function() {
		var margin = this.height;
		this.fx.start({
			'top': -margin
		});
		this.open = false;
		Cookie.write('rokIEWarn', 'close', {duration: 7});
	},	
	'status': function() {
		return this.open;
	},
	'toggle': function() {
		if (this.open) this.close();
		else this.show();
	}
});