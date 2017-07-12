
var GantryBuildSpans = function(modules, headers, k) {
	(modules.length).times(function(i) {
		var rules = "." + modules[i];
		var replacer = function(title) {
			title.setStyle('visibility', 'visible');
			var text = title.get('text');
			var split = text.split(" ");
			first = split[0];
			rest = split.slice(1).join(" ");
			html = title.innerHTML;
			if (rest.length > 0) {
				var clone = title.clone().set('text', ' ' + rest),
					span = new Element('span').set('text', first);
				span.inject(clone, 'top');
				clone.replaces(title);
			}
		};
		$$(rules).each(function(rule) {
			headers.each(function(header) {
				rule.getElements(header).each(function(title) {
					var first = title.getFirst();
					if (first && first.get('tag') == 'a') replacer(first);
					else replacer(title);
				});
			});
		});
	});
};