
var GantrySmartLoad = new Class({
	Implements: [Events, Options],
	options: {
		placeholder: 'blank.gif',
		container: window,
		cssrule: 'img',
		offset: {x: 200, y: 200},
		exclusion: []
	},
	initialize: function(options) {
		this.setOptions(options);
				
		this.container = document.id(this.options.container);
		this.images = $$(this.options.cssrule);
		this.dimensions = {
			size: this.container.getSize(),
			scroll: this.container.getScroll(),
			scrollSize: this.container.getScrollSize()
		};
		var tmp = this.options.exclusion[0].split(',');
		if (tmp.length && (tmp.length != 1 && tmp[0] != "")) tmp.each(function(excl) {
			var imgs = $$(excl + ' ' + this.options.cssrule);
			imgs.each(function(img) {
				this.images.erase(img);
			}, this);
		}, this);
		
		this.init = 0;
		this.storage = new Hash({});
		
		this.images.each(function(img, id) {
			if (typeof img == 'undefined') return;
			
			if (!img.get('width') && !img.get('height')){
				this.storage.erase(img.get('smartload'));
				this.images.erase(img);
				return;
			}
			
			var size = img.getSize();
			if (img.getProperty('width')) {
				size.x = img.getProperty('width');
				size.y = img.getProperty('height');
			}
			
			if (!img.getProperty('width') && size.x && size.y) {
				img.setProperty('width', size.x).setProperty('height', size.y);
			}
			img.setProperty('smartload', id);
			this.storage.set(id, {
				'src': img.src,
				'width': size.x,
				'height': size.y,
				'fx': new Fx.Tween(img, {duration: 250, transition: Fx.Transitions.Sine.easeIn})
			});
			if (!this.checkPosition(img)) {
				img.setProperty('src', this.options.placeholder).addClass('spinner');
			} else {
				this.storage.erase(img.getProperty('smartload'));
				this.images.erase(img);
			}
		}, this);
		
		if (this.images.length) document.id(this.container).addEvent('scroll', this.scrolling.bind(this));
		var container = this.container;
	},
	checkPosition: function(img) {
		var position = img.getPosition(), offset = this.options.offset;
		var dimensions = {
			size: this.container.getSize(),
			scroll: this.container.getScroll(),
			scrollSize: this.container.getScrollSize()
		};
		return ((position.y >= dimensions.scroll.y - offset.y) && (position.y <= dimensions.scroll.y + this.dimensions.size.y + offset.y));
	},
	scrolling: function(e) {
		var self = this;
		if (!this.images || !this.init) {
			this.init = 1;
			return;
		}
		
		this.images.each(function(img) {
			if (typeof img == 'undefined') return;
			if (this.checkPosition(img) && this.storage.get(img.getProperty('smartload'))) {
				var storage = this.storage.get(img.getProperty('smartload'));
				
				new Asset.image(storage.src, {
					onload: function() {
						//var size = {width: this.width, height: this.height};
						var size = {width: storage.width, height: storage.height};
						if (size.width && !size.height) size.height = size.width;
						if (!size.width && size.height) size.width = size.height;
						if (!size.width && !size.height){ size.width = this.width; size.height = this.height; }
						
						if (size.width != this.width && size.height == this.height) size.width = this.width;
						else if (size.width == this.width && size.height != this.height) size.height = this.height;

						storage.fx.start('opacity', 0).chain(function() {
							//if (!Browser.Engine.trident6) img.setProperty('width', 'auto').setProperty('height', 'auto');
							//else img.setProperty('width', size.width).setProperty('height', size.height);
							img.setProperty('width', size.width).setProperty('height', size.height);
							
							img.setProperty('src', storage.src).removeClass('spinner');
							
							this.start('opacity', 1);
						});

						self.images.erase(img);
						self.storage.erase(img.getProperty('smartload'));
					}
				});
				
			}
		}, this);
	}
});

