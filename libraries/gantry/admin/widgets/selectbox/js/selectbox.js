
var SelectBox = new Class({
	Implements: [Events],
	initialize: function(context) {
		if (!context) context = document.body;
		this.elements = document.id(context).getElements('.selectbox-wrapper');

		this.elements.each(function(element, i) {
			this.updateSizes(element);
			element.store('g:bound', this.mouseEnter.bind(this, element));
			element.addEvent('mouseenter', element.retrieve('g:bound'));
		}, this);
	},

	mouseEnter: function(element){
		var objs = this.getObjects(element);
		this.init(element);

		objs.real.store('gantry:objs', objs);
		objs.real.addEvent('detach', this.detach.bind(this, objs.element));
		objs.real.addEvent('attach', this.attach.bind(this, objs.element));
		objs.real.addEvents({
			'set': function(value) {
				var list = objs.opts.get('value');
				var index = list.indexOf(value);
				if (index != -1) objs.list[index].fireEvent('click');
			}
		});

		this.lisEvents(element);

		if (element.hasClass('disabled')) this.detach(element);

		element.removeEvent('mouseenter', element.retrieve('g:bound'));
	},

	updateSizes: function(element) {

		var objs = this.getObjects(element);
		var sizes = {
			dropdown: objs.dropdown.getSize().x,
			arrow: objs.arrow.getSize().x,
			ul: objs.ul.getSize().y
		};
		var max = objs.ul.getStyle('max-height').toInt();
		var offset = (sizes.ul > max) ? 10 : 0;

		objs.top.setStyle('width', sizes.dropdown + offset);
		objs.dropdown.setStyle('width', sizes.dropdown + sizes.arrow + offset);
		if (offset > 0) objs.ul.setStyle('overflow', 'auto');
	},

	getObjects: function(element) {
		return {
			element: element,
			selected: element.getElement('.selectbox-top .selected span'),
			top: element.getElement('.selectbox-top'),
			dropdown: element.getElement('.selectbox-dropdown'),
			arrow: element.getElement('.arrow'),
			ul: element.getElement('ul'),
			list: element.getElements('li'),
			real: element.getParent().getElement('select'),
			opts: element.getParent().getElement('select').getChildren()
		};
	},

	init: function(element) {
		element.addEvents({
			click: this.toggle.bind(this, element),
			disable: this.disable.bind(this, element),
			enable: this.enable.bind(this, element),
			mousedown: this.preventDefault.bind(this, element),
			onselectstart: this.preventDefault.bind(this, element),
			mouseenter: this.enter.bind(this, element),
			mouseleave: this.leave.bind(this, element)
		}, this);

	},

	lisEvents: function(element) {
		var objs = this.getObjects(element), self = this;
		var realChildren = objs.real.getChildren();

		objs.list.each(function(el, i) {
			el.addEvents({
				'mouseenter': function() {
					if (realChildren[i].getProperty('disabled')) return;
					objs.list.removeClass('hover');
					this.addClass('hover');
				},
				'mouseleave': function() {
					if (realChildren[i].getProperty('disabled')) return;
					this.removeClass('hover');
				},
				'click': function() {
					if (realChildren[i].getProperty('disabled')) return;
					objs.list.removeClass('active');
					this.addClass('active');
					this.fireEvent('select', [objs, i]);
				},
				select: self.select.bind(self)
			});
		});
	},

	attach: function(element) {
		element.addEvent('click', this.toggle.bind(this, element));
		element.stat = 'close';
		element.fireEvent('enable', element);
	},

	detach: function(element) {
		element.removeEvents('click');
		element.fireEvent('disable', element);
	},

	toggle: function(element) {
		var objs = this.getObjects(element);
		if (element.stat == 'open') return this.hide(objs);
		else if (element.stat == 'close') return this.show(objs);

		return this.show(objs);
	},

	enter: function(element) {
		var objs = this.getObjects(element);

		clearTimeout(element.timer);
	},

	leave: function(element) {
		var objs = this.getObjects(element);

		clearTimeout(element.timer);
		element.timer = this.hide.delay(500, this, objs);
	},

	show: function(objs) {
		objs.dropdown.setStyle('visibility', 'visible');
		objs.element.addClass('pushed');
		objs.element.stat = 'open';
	},

	hide: function(objs) {
		objs.dropdown.setStyle('visibility', 'hidden');
		objs.element.removeClass('pushed');
		objs.element.stat = 'close';
	},

	select: function(objs, index) {
		if (index == -1) return;
		objs.selected.set('html', objs.list[index].innerHTML);
		objs.real.selectedIndex = index;

		objs.real.fireEvent('change', index);
	},

	enable: function(element) {
		element.removeClass('disabled');
	},

	disable: function(element) {
		clearTimeout(element.timer);
		this.hide(this.getObjects(element));
		element.addClass('disabled');
	},

	preventDefault: function(element, e) {
		e.stop();
		return false;
	}

});

window.addEvent('domready', function() {window.selectboxes = new SelectBox();});
