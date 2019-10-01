

if (addEventListener) {
	window.addEventListener("load", function(event) { 
		const xx = document .querySelectorAll("img.jch-lazyload");

		for (var i = 0; i < xx.length; i++) {
			addHeight(xx[i]);
		}
	});

	document.addEventListener('lazybeforeunveil', function(e){
		if (e.target.nodeName == 'IMG') {
			addHeight(e.target);
		}
	});
};

function addHeight(el) {
	var ht = el.getAttribute('height');
	var wt = el.getAttribute('width');

	el.style.height = ht ? ((wt && el.offsetWidth > 40) ? (el.offsetWidth * ht)/wt : ht) + 'px' : 'auto';
};
