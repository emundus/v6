/**
 * @package    HikaShop for Joomla!
 * @version    4.7.3
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
var hikaVote = function(el,opt) {
	this.init(el,opt);
};
hikaVote.options = {};
hikaVote.setOptions = function(opts) {
	for(var opt in opts) {
		if(!opts.hasOwnProperty(opt))
			continue;
		hikaVote.options[opt] = opts[opt];
	}
};
hikaVote.updateVote = function(type, ref_id, value, tooltip) {
	for(var i = window.hikaVotes.length - 1; i >= 0; i--) {
		if(window.hikaVotes[i].type != type || window.hikaVotes[i].ref_id != ref_id)
			continue;
		window.hikaVotes[i].setRating(value, tooltip);
	}
};
hikaVote.vote = function(val, from){
	var d = document,
		re = new RegExp('id_(.*?)_hikashop'),
		m = re.exec(from),
		ref_id = null,
		infos = false;

	if(m != null) {
		ref_id = '';
		for (i = 1; i < m.length; i++) {
			ref_id = ref_id + m[i] + "\n";
		}
	} else {
		infos = d.querySelector("[data-ref][id=\"hikashop_vote_rating_id\"]");
		ref_id = infos.getAttribute("data-ref");
		if(!val && infos.value) {
			val = infos.value;
		}
	}

	if(!infos) {
		infos = d.querySelectorAll("input[data-ref=\""+parseInt(ref_id)+"\"]:not([id=\"hikashop_vote_rating_id\"])");
		infos = infos[0];
	}

	var el = null;
	if(from == "hikashop_vote_rating_id") {
		el = d.getElementById("hikashop_vote_status_form");
	} else {
		el = d.getElementById("hikashop_vote_status_"+parseInt(ref_id));
	}

	var hikashop_vote_comment = "", pseudo_comment = 0, email_comment = 0, recaptcha_comment = '';
	if(d.getElementById("hikashop_vote_comment")) {
		hikashop_vote_comment = d.getElementById("hikashop_vote_comment").value;

		// don't submit the votes alone when the vote and comment are connected
		if(hikaVote.options.both == '1' && hikashop_vote_comment == '' && val != 0)
			return;
		pseudo_comment = d.getElementById("pseudo_comment").value;
		email_comment = d.getElementById("email_comment").value;
		if(d.getElementById("g-recaptcha-response")){
			recaptcha_comment = d.getElementById("g-recaptcha-response").value;
		}
	}

	var type = infos.getAttribute("data-votetype"), comment_task = 0;
	if(hikaVote.options.both == '1' || hikashop_vote_comment != '' || (val == 0 && infos.id == "hikashop_vote_rating_id")) {
		comment_task = 1;
	}

	data = "vote_type=" + encodeURIComponent(type) + "&hikashop_vote_type=vote" +
		"&hikashop_vote_ref_id=" + parseInt(ref_id) +
		"&hikashop_vote=" + parseInt(val) +
		"&hikashop_vote_comment=" + encodeURIComponent(hikashop_vote_comment) +
		"&email_comment=" + encodeURIComponent(email_comment) +
		"&pseudo_comment=" + encodeURIComponent(pseudo_comment) +
		"&recaptcha_comment=" + encodeURIComponent(recaptcha_comment);
	window.Oby.xRequest(hikaVote.options.urls.save, {mode: "POST", data: data}, function(xhr) {
		response = window.Oby.evalJSON(xhr.response);
		if(response.error) {
			el.innerHTML = response.error.message;
			return;
		}

		if(!response.success)
			return;

		el.innerHTML = response.success.message;
		setTimeout(function(){ el.innerHTML = ''; }, 3500);
		if(comment_task) {
			// Clear the comment textarea
			d.getElementById('hikashop_vote_comment').value = '';

			// Call a function to refresh the "vote / listing" only
			var section = d.getElementById("hikashop_vote_listing");
			if(!section)
				section = d.getElementById("hikashop_product_vote_listing");
			if(!section)
				return;

			data = "data_id="+parseInt(ref_id);
			data += "&main_ctrl="+String(type);
			window.Oby.xRequest(hikaVote.options.urls.show, {mode: "POST", data: data}, function(xhr) {
				section.innerHTML = xhr.response;
				if(hkjQuery().chosen)
					hkjQuery('#' + section.id + ' select').chosen();
			});
		}
		if(response.values) {
			// type / ref_id / value / tooltip
			window.hikaVote.updateVote(String(type), parseInt(ref_id), parseInt(response.values.rounded), String(response.tooltip));
		}
	});
};
hikaVote.useful = function(vote_id, val) {
	var section = document.getElementById("hikashop_vote_listing"),
		type = 'product';
	if(section) {
		type = section.getAttribute('data-votetype');
	} else {
		section = document.getElementById("hikashop_product_vote_listing");
	}
	if(!section)
		return;
	var el = document.getElementById(vote_id);
	data = "hikashop_vote_type=useful&value=" + parseInt(val) + "&hikashop_vote_id=" + parseInt(vote_id) + "&vote_type=" + encodeURIComponent(type);
	window.Oby.xRequest(hikaVote.options.urls.save, {mode: "POST", data: data}, function(xhr) {
		response = window.Oby.evalJSON(xhr.response);
		if(response.error)
			el.innerHTML = response.error.message;
		else if(response.success)
			el.innerHTML = response.success.message;
	});

	data = "data_id=" + parseInt(hikaVote.options.itemId) + "&main_ctrl=" + encodeURIComponent(type) + "&content_type=listing";
	window.Oby.xRequest(hikaVote.options.urls.show, {mode: "POST", data: data}, function(xhr) {
		setTimeout(function(){
			section.innerHTML = xhr.response;
			if(hkjQuery().chosen)
				hkjQuery('#' + section.id + ' select').chosen();
		}, 5000);
	});
};

hikaVote.prototype = {
	options : {},
	selectBox: null,
	container: null,
	max: null,
	cb: null,
	type: null,
	ref_id: null,
	/**
	 *
	 */
	init: function(el, opt, cb) {
		var t = this, d= document;
		t.setOptions(opt);

		this.options.style = 'star';
		if(el.getAttribute('data-votestyle'))
			this.options.style = el.getAttribute('data-votestyle');

		if(typeof(el) == 'string')
			t.selectBox = d.getElementById(el);
		else
			t.selectBox = el;

		if(el.voteInit)
			return;

		if(!t.options.showSelectBox && t.selectBox && t.selectBox.nodeName.toLowerCase() == 'select' && typeof(jQuery) != 'undefined' && jQuery().chosen) {
			setTimeout(function(){
				var id = selectBox.getAttribute('id') + '_chzn';
				if(d.getElementById(id) != null) {
					jQuery(id).detach();
					try{ jQuery(id+'-chzn').remove(); }catch(e){}
				}
			}, 50);
		}

		// set the container
		t.setContainer();
		// add stars
		var max = t.selectBox.getAttribute('data-max');
		if(max) {
			try{ parseInt(max); } catch(e) { t.max = max; }
			for(var i = 1; i <= max; i++)
				t.createStar(null, i);
		} else {
			var elems = t.selectBox.getElementsByTagName('option');
			t.max = 0;
			if(elems && elems.length) {
				for(var i = 0; i <= elems.length; i++) {
					t.createStar(elems[i]);
					if(elems[i].value > t.max)
						t.max = elems[i].value;
				}
			}
		}

		t.cb = cb || null;

		t.addEvent(t.container, 'mouseover', function(e) { t.mouseOver(e); });
		t.addEvent(t.container, 'mouseout', function(e) { t.mouseOut(e); });
		t.addEvent(t.container, 'click', function(e) { t.click(e); });

		// bind change event for selectbox if shown
		if (t.options.showSelectBox)
			t.addEvent(t.selectBox, 'change', t.change);

		// set the initial rating
		t.setRating(t.options.defaultRating);

		el.voteInit = true;
	},
	setOptions: function(opt) {
		var t = this;
		t.options.showSelectBox = opt.showSelectBox || false;
		t.options.container = opt.container || null;
		t.options.defaultRating = opt.defaultRating || null;
		t.options.id = opt.id || 'hikashop_vote_';
		t.type = opt.type;
		t.ref_id = opt.ref_id;
	},
	setContainer: function() {
		var t = this, d = document;
		if(d.getElementById(t.options.container)) {
			t.container = d.getElementById(t.options.container);
			return;
		}
		t.createContainer();
	},
	createContainer: function() {
		var t = this, d = document;
		t.container = d.createElement('div');
		t.container.className = 'hk-rating';
		t.container.setAttribute('data-toggle', 'hk-tooltip');
		t.container.setAttribute('data-original-title', t.selectBox.getAttribute('data-original-title'));
		if(t.selectBox.nextSibling)
			t.selectBox.parentNode.insertBefore(t.container, t.selectBox.nextSibling);
		else
			t.selectBox.parentNode.appendChild(t.container);
	},
	reset: function() {
		if(t.container)
			t.container.parentNode.removeChild(t.container);
		t.createContainer();
	},
	createStar: function(el, value) {
		var t = this, d = document;
		if(el) value = el.getAttribute('value');
		var e = d.createElement('a');
		e.id = t.options.id + '_' + value;
		e.className = 'hk-rate-' + this.options.style + ' state-empty';
		e.title = '' + value;
		e.value = value;

		t.container.appendChild(e);
	},
	mouseOver: function(e) {
		var t = this, d = document;
		if(!e.target)
			e.target = e.srcElement;
		if(!e.target)
			return;
		el = e.target;
		if(typeof(el) == 'string')
			el = d.getElementById(el);
		if(!el)
			return;
		t.addClass(el, 'state-hover');
		var c = el.previousSibling;
		while(c) {
			t.addClass(c, 'state-hover');
			c = c.previousSibling;
		}
	},
	mouseOut: function(e) {
		var t = this, d = document, el = null;
		if(!e.target)
			e.target = e.srcElement;

		if(!e.target)
			return;
		el = e.target;
		if(typeof(el) == 'string')
			el = d.getElementById(el);
		if(!el)
			return;
		t.removeClass(el, 'state-hover');

		var c = el.previousSibling;
		while(c) {
			t.removeClass(c, 'state-hover');
			c = c.previousSibling;
		}
	},
	click: function(e) {
		var t = this, d = document;
		if (!e.target)
			e.target = e.srcElement;
		var rating = e.target.getAttribute('title').replace('', ''),
			from = t.selectBox.getAttribute('id');
		t.setRating(rating);
		t.selectBox.value = rating;
		// Send the id of the view which send the vote ( mini / form )
		if(hikashop_send_vote){
			var el = d.getElementById('hikashop_vote_rating_id');
			if(el) el.value = rating;
			hikashop_send_vote(rating, from);
		}
		if(t.cb)
			t.cb(rating, from);
	},
	change: function(e) {
		var t = this, d = document, rating = null;
			el = d.getElementById(e.target);
		if(!el) return;
		t.setRating(el.value);
	},
	setRating: function(rating, tooltip) {
		var t = this, d = document;
		// use selected rating if none supplied
		if (!rating) {
			rating = t.selectBox.getAttribute('value');
			// use first rating option if none selected
			if(!rating)
				rating = 0;
		}
		// get the current selected rating star
//		var current = t.container.getElement('a[title=' + rating + ']');
		var e = null, current = null, elements = t.container.getElementsByTagName('a');
		for(var i = elements.length - 1; i >= 0; i--) {
			e = elements[i];
			if(e && e.title && e.title == rating) {
				current = e;
				break;
			}
		}

		// highlight current and previous stars in yellow
		if(current && rating != 0) {
			current.className = 'hk-rate-'+this.options.style+' state-full';
			var c = current.previousSibling;
			while(c) {
				c.className = 'hk-rate-'+this.options.style+' state-full';
				c = c.previousSibling;
			}

			// remove highlight from higher ratings
			var c = current.nextSibling;
			while(c) {
				c.className = 'hk-rate-'+this.options.style+' state-empty';
				c = c.nextSibling;
			}
		}
		// synchronize the rate with the selectbox
		t.selectBox.value = rating;

		if(!tooltip)
			return;

		// update the tooltip
		t.container.setAttribute('data-original-title', tooltip);
	},
	addEvent : function(d,e,f) {
		if( d.attachEvent )
			d.attachEvent('on' + e, f);
		else if (d.addEventListener)
			d.addEventListener(e, f, false);
		else
			d['on' + e] = f;
		return f;
	},
	hasClass : function(o,n) {
		if(o.className == '' ) return false;
		var reg = new RegExp("(^|\\s+)"+n+"(\\s+|$)");
		return reg.test(o.className);
	},
	addClass : function(o,n) {
		if(o.className == '')
			o.className = n;
		else if(!this.hasClass(o,n))
			o.className += ' '+n;
	},
	trim : function(s) {
		return (s ? '' + s : '').replace(/^\s*|\s*$/g, '');
	},
	removeClass : function(e, c) {
		var t = this;
		if( e.className != '' && t.hasClass(e,c) ) {
			var cn = ' ' + e.className + ' ';
			e.className = t.trim(cn.replace(' '+c+' ',' '));
		}
	}
};

var initVote = function(mainDiv){
	var d = document, el = null, r = null, voteContainers = null;
	if(mainDiv)
		voteContainers = mainDiv.querySelectorAll('input[name=hikashop_vote_rating]');
	else
		voteContainers = d.getElementsByName('hikashop_vote_rating');
	if(voteContainers.length == 0)
		return;
	for(var i=0; i < voteContainers.length; i++) {
		el = d.getElementById(voteContainers[i].id);
		if(!el || !el.getAttribute("data-votetype"))
			continue;
		r = new hikaVote(el, {
			id : 'hikashop_vote_rating_'+el.getAttribute("data-votetype")+'_'+el.getAttribute("data-ref"),
			showSelectBox : false,
			container : null,
			defaultRating :  el.getAttribute("data-rate"),
			type : el.getAttribute("data-votetype"),
			ref_id : el.getAttribute("data-ref"),
		});
		if(r.container)
			window.hikaVotes.push(r);
	}
	el = d.getElementById('hikashop_vote_rating_id');
	if(el) el.value = '0';
};

if(!window.hikaVotes)
	window.hikaVotes = [];

/* Vote initialization */
window.hikashop.ready(function(){
	initVote();
});
