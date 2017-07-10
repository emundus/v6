/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'scp_icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-locked' : '&#xe025;',
			'icon-unlocked' : '&#xe026;',
			'icon-checkmark' : '&#xe027;',
			'icon-close' : '&#xe028;',
			'icon-help' : '&#xe029;',
			'icon-drawer' : '&#xe02a;',
			'icon-drawer-2' : '&#xe02b;',
			'icon-checkbox-checked' : '&#xe02c;',
			'icon-alert' : '&#xe02d;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c) {
			addIcon(el, icons[c[0]]);
		}
	}
};