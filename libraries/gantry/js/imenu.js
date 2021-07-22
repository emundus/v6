
var iDropdowns = function(el, options) {
	var list = new Hash({}), current, from, to, isanimating = false;	
	var container = new Element('div', {id: 'idrops'}).inject('rt-menu');
	
	var ul = $$('#rt-menu ul')[0];
	
	var width = 0;
	ul.getChildren().each(function(li) {
		width += li.offsetWidth;
	});
	ul.setStyle('width', width + document.getElementById('rt-right-menu').offsetWidth);
	
	var roots = $$('#rt-menu li.root');
	var dropdowns = $$('#rt-menu ul.menu li.parent ul');
	var items = [];
	dropdowns.each(function(drop) {
		var parentID = drop.getLast().getProperty('parent_id').replace('idrops-', '');
		if (!list.get(parentID)) list.set(parentID, []);
		list.get(parentID).push(drop);
		
		new Element('div', {'id': 'idown-'+parentID, 'class': 'idown'}).adopt(drop).inject(container);
		
		items.push(drop.getElements('li').filter(function(item) {
			return item.getProperty('parent_id');
		}));
	});

	list = Array.from(roots).combine(items).flatten();
	list.each(function(item) {
			if (item.hasClass('parent')) {
				var lnk = item.getLast();
				if (lnk.get('tag') == 'a') {
					lnk.storedLink = lnk.href;
					lnk.setProperty('href', '#' + item.id.replace('idrops-', 'idown-'));
				}
				
				item.addEvent('click', function(e) {
					e.preventDefault();
					if (isanimating) return false;
					var fromID = item.get('parent_id').replace('idrops-', '');
					var toID = item.get('id').replace('idrops-', '');
					from = document.id('idown-' + fromID);
					to = document.id('idown-' + toID);

					to.addEventListener('webkitAnimationEnd', animCallback, false);
					to.addClass('selected');
					
					if (fromID == 1 && !from) {
						var height = to.getSize().y;
						if (current) {
							from = current;
							height = Math.max(height, current.getSize().y);
							current.addClass('slidedown').addClass('out');
						}
						document.id('idrops').setStyles({'overflow': 'hidden', 'height': height});
						to.addClass('slidedown').addClass('in');
					} else {
						/*height = Math.max(from.getSize().size.y, to.getSize().size.y);
						$('idrops').setStyles({'overflow': 'hidden', 'height': height});*/
						from.addClass(animation).addClass('out');
						to.addClass(animation).addClass('in');
					}

					isanimating = true;
					current = to;
				});
			}
	});
	
	var backmenus = $$('#rt-menu #idrops .backmenu');
	var closemenus = $$('#rt-menu #idrops .closemenu');
	
	backmenus.each(function(back) {
		back.addEvent('click', function(e) {
			e.preventDefault();
			if (isanimating) return false;
			var parent = back.getProperty('parent_id').replace('idrops-', 'idown-');
			to.addEventListener('webkitAnimationEnd', animCallback, false);
			to = document.id(parent).addClass('selected');
			from = current;
			current = to;
			from.addClass(animation).addClass('reverse').addClass('out');
			to.addClass(animation).addClass('reverse').addClass('in');
			
			isanimating = true;
		});
	});
	
	closemenus.each(function(close) {
		close.addEvent('click', function(e) {
			e.preventDefault();
			if (isanimating) return false;
			var height = current.getSize().y;
			var from = current;
			var to = current;
			current = null;
			document.id('idrops').setStyles({'overflow': 'hidden', 'height': height});
			to.addEventListener('webkitAnimationEnd', animCallback, false);
			to.addClass('slidedown').addClass('out');
			
			isanimating = true;
		});
	});
	
	var animCallback = function() {
		document.id('idrops').setStyle('overflow', '');

		if (from) from.className = 'idown';
		if (current == to && current == from) { to.className = 'idown'; current = null; }
		else to.className = 'idown selected';
		
		if (!current) {
			document.id('idrops').setStyle('height', 0);
			to.removeClass('selected');
		}
		
		to.removeEventListener('webkitAnimationEnd', animCallback, false);
		isanimating = false;
	};
	
};

window.addEvent('domready', iDropdowns);