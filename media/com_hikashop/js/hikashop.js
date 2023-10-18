/**
 * @package    HikaShop for Joomla!
 * @version    4.7.4
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {

function preventDefault() { this.returnValue = false; }
function stopPropagation() { this.cancelBubble = true; }

var Oby = {
	version: 20171104,
	ajaxEvents : {},

	hasClass: function(o,n) {
		if(o.classList && o.classList.contains)
			return o.classList.contains(n);
		if(o.className == '' ) return false;
		var reg = new RegExp("(^|\\s+)"+n+"(\\s+|$)");
		return reg.test(o.className);
	},
	addClass: function(o,n) {
		if(o.classList && o.classList.add)
			return o.classList.add(n);
		if( !this.hasClass(o,n) ) {
			if( o.className == '' ) {
				o.className = n;
			} else {
				o.className += ' '+n;
			}
		}
	},
	trim: function(s) {
		if(s.trim) return s.trim();
		return (s ? '' + s : '').replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
	},
	removeClass: function(e, c) {
		if(e.classList && e.classList.remove)
			return e.classList.remove(c);
		var t = this;
		if( t.hasClass(e,c) ) {
			var cn = ' ' + e.className + ' ';
			e.className = t.trim(cn.replace(' '+c+' ',' '));
		}
	},
	toggleClass: function(e,c) {
		if(e.classList && e.classList.toggle)
			return e.classList.toggle(c);
		var t = this;
		if( t.hasClass(e,c) ) {
			return t.removeClass(e,c);
		}
		return t.addClass(e,c);
	},
	addEvent: function(d,e,f) {
		if( d.attachEvent )
			d.attachEvent('on' + e, f);
		else if (d.addEventListener)
			d.addEventListener(e, f, false);
		else
			d['on' + e] = f;
		return f;
	},
	removeEvent: function(d,e,f) {
		try {
			if( d.detachEvent )
				d.detachEvent('on' + e, f);
			else if( d.removeEventListener)
				d.removeEventListener(e, f, false);
			else
				d['on' + e] = null;
		} catch(e) {}
	},
	cancelEvent: function(e) {
		if( !e ) {
			e = window.event;
			if( !e )
				return false;
		}
		if(e.stopPropagation)
			e.stopPropagation();
		else
			 e.cancelBubble = true;
		if( e.preventDefault )
			e.preventDefault();
		else
			e.returnValue = false;
		return false;
	},
	fireEvent: function(obj,e,data) {
		var d = document, evt = null;
		if(document.createEvent) {
			evt = d.createEvent('HTMLEvents');
			evt.initEvent(e, false, true);
			if(data) evt.data = data;
			obj.dispatchEvent(evt);
			return;
		}
		if(data && d.createEventObject) {
			evt = d.createEventObject();
			evt.data = data;
			obj.fireEvent('on'+e, evt);
			return;
		}
		obj.fireEvent('on'+e);
	},
	fireAjax: function(name,params) {
		var t = this, ev, r = null, ret = [];
		if( t.ajaxEvents[name] === undefined )
			return false;
		for(var e in t.ajaxEvents[name]) {
			if( e == '_id' )
				continue;
			ev = t.ajaxEvents[name][e];
			if(!ev || typeof(ev) != 'function')
				continue;
			try {
				r = ev(params);
				if(r !== undefined)
					ret.push(r);
			}catch(e){}
		}
		return ret;
	},
	registerAjax: function(name, fct) {
		var t = this;
		if(typeof(name) == 'object') {
			var r = [];
			for(var k = name.length - 1; k >= 0; k--) {
				r[r.length] = t.registerAjax(name[k], fct);
			}
			return r;
		}
		if( t.ajaxEvents[name] === undefined )
			t.ajaxEvents[name] = {'_id':0};
		var id = t.ajaxEvents[name]['_id'];
		t.ajaxEvents[name]['_id'] += 1;
		t.ajaxEvents[name][id] = fct;
		return id;
	},
	unregisterAjax: function(name, id) {
		if( t.ajaxEvents[name] === undefined || t.ajaxEvents[name][id] === undefined)
			return false;
		t.ajaxEvents[name][id] = null;
		return true;
	},
	ready: function(fct) {
		var w = window, d = document, t = this;
		if(d.readyState === "complete") {
			fct();
			return;
		}
		var done = false, top = true, root = d.documentElement,
			init = function(e) {
				if(e.type == 'readystatechange' && d.readyState != 'complete') return;
				t.removeEvent((e.type == 'load' ? w : d), e.type, init);
				if(!done && (done = true))
					fct();
			},
			poll = function() {
				try{ root.doScroll('left'); } catch(e){ setTimeout(poll, 50); return; }
				init('poll');
			};
		if(d.createEventObject && root.doScroll) {
			try{ top = !w.frameElement; } catch(e){}
			if(top) poll();
		}
		t.addEvent(d,'DOMContentLoaded',init);
		t.addEvent(d,'readystatechange',init);
		t.addEvent(w,'load',init);
	},
	evalJSON: function(text, secure) {
		if( typeof(text) != "string" || !text.length) return null;
		if(JSON !== undefined && typeof(JSON.parse) == 'function') {
			try { var ret = JSON.parse(text); return ret; } catch(e) { }
		}
		if(secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(text.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
		try { var ret = eval('(' + text + ')'); return ret; } catch(e) { }
		return null;
	},
	getXHR: function() {
		var xhr = null, w = window;
		if(w.XMLHttpRequest || w.ActiveXObject) {
			if(w.ActiveXObject) {
				try {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				} catch(e) {}
			} else
				xhr = new w.XMLHttpRequest();
		}
		return xhr;
	},
	xRequest: function(url, options, cb, cbError) {
		var t = this, xhr = t.getXHR();
		if(!options) options = {};
		if(!cb) cb = function(){};
		options.mode = options.mode || 'GET';
		options.update = options.update || false;
		xhr.onreadystatechange = function() {
			if(xhr.readyState != 4)
				return;
			if( xhr.status == 200 || (xhr.status == 0 && xhr.responseText > 0) || !cbError ) {
				if(cb)
					cb(xhr,options.params);
				if(options.update)
					t.updateElem(options.update, xhr.responseText);
			} else {
				cbError(xhr,options.params);
			}
		};
		xhr.open(options.mode, url, true);
		if(options.mode.toUpperCase() == 'POST' && typeof(options.data) == 'string') {
			xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		}
		xhr.send( options.data );
	},
	getFormData: function(target, encoded) {
		var d = document, ret = {};
		if(encoded === undefined) encoded = true;
		if( typeof(target) == 'string' )
			target = d.getElementById(target);
		if( target === undefined )
			target = d;
		var typelist = ['input','select','textarea'];
		for(var t in typelist) {
			if(!typelist.hasOwnProperty(t))
				continue;
			t = typelist[t];
			var inputs = target.getElementsByTagName(t);
			for(var i = 0; i < inputs.length; i++) {
				if( !inputs[i].name || inputs[i].disabled )
					continue;
				var evalue = inputs[i].value, n = inputs[i].name, etype = '';
				if( t == 'input' )
					etype = inputs[i].type.toLowerCase();
				if( (etype == 'radio' || etype == 'checkbox') && !inputs[i].checked )
					evalue = null;
				if(t == 'select' && inputs[i].multiple) {
					for(var k = inputs[i].options.length - 1; k >= 0; k--) {
						if(!inputs[i].options[k].selected)
							continue;
						//if( ret != '' ) ret += '&';
						//ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(inputs[i].options[k].value);
						if(ret.hasOwnProperty(n)) {
							if(typeof(ret[n]) != 'object')
								ret[n] = [ ret[n] ];
							ret[n][ ret[n].length ] = inputs[i].options[k].value;
						} else
							ret[ n ] = inputs[i].options[k].value;
						evalue = null;
					}
				}
				if( (etype != 'file' && etype != 'submit') && evalue != null ) {
					//if( ret != '' ) ret += '&';
					//ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(evalue);

					if(ret.hasOwnProperty(n)) {
						if(typeof(ret[n]) != 'object')
							ret[n] = [ ret[n] ];
						ret[n][ ret[n].length ] = evalue;
					} else
						ret[ n ] = evalue;
				}
			}
		}
		if(encoded)
			return this.encodeFormData(ret);
		return ret;
	},
	encodeFormData: function(data) {
		var ret = '', v = null;
		if(typeof(data) == "string")
			return data;
		for(var k in data) {
			if(!data.hasOwnProperty(k))
				continue;
			v = data[k];
			if(typeof(v) == 'object') {
				for(var i in v) {
					if(!v.hasOwnProperty(i))
						continue;
					if( ret != '' ) ret += '&';
					ret += encodeURI(k) + '=' + encodeURIComponent(v[i]);
				}
			} else {
				if( ret != '' ) ret += '&';
				ret += encodeURI(k) + '=' + encodeURIComponent(v);
			}
		}
		return ret;
	},
	updateElem: function(elem, data) {
		var d = document, scripts = '';
		if( typeof(elem) == 'string' )
			elem = d.getElementById(elem);
		var text = data.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(all, code){
			if(all.indexOf('type="application/json"')>=0 || all.indexOf('type="application/ld+json"')>=0)
				return all;
			scripts += code + '\n';
			return '';
		});
		elem.innerHTML = text;
		if( scripts != '' ) {
			var script = d.createElement('script');
			script.setAttribute('type', 'text/javascript');
			script.text = scripts;
			try {
				d.head.appendChild(script);
				d.head.removeChild(script);
			} catch(e) {}
		}
	},
	ease: function(v) {
		return 1+Math.pow(v-1,3);
	},
	easeInOut: function(t,s,dt,du) {
		return dt/2 * (1 - Math.cos(Math.PI*t/du)) + s;
	},
	scrollTo: function(name, anim, visible, margin) {
		var t = this, d = document, w = window,
			elem = d.getElementById(name);
		if(!elem)
			return;
		if(margin === undefined) margin = 0;
		if(!anim) {
			window.scrollTo(0, elem.offsetTop - margin);
			return;
		}
		if( t.anim && t.anim.timer )
			clearInterval( t.anim.timer );
		t.anim = { timer:null, s:null, dt:0, du:150, t:0, inc:10 };
		if( w.scrollY )
			t.anim.s = w.scrollY;
		else if( d.documentElement.scrollTop )
			t.anim.s = d.documentElement.scrollTop;
		else
			t.anim.s = d.body.scrollTop;
		if(visible) {
			if( d.documentElement.scrollTop )
				h = d.documentElement.clientHeight;
			else
				h = d.body.clientHeight;
			if(t.anim.s <= elem.offsetTop && (t.anim.s + h - 150) > elem.offsetTop)
				return;
		}
		t.anim.dt = elem.offsetTop - t.anim.s - margin;
		var o = this;
		t.anim.timer = setInterval( function() {
			var a = o.anim;
			if( !a || !a.timer )
				return;
			a.t += a.inc;
			if( a.t < a.du ) {
				window.scrollTo(0, o.easeInOut(a.t, a.s, a.dt, a.du), false, margin);
			} else {
				window.scrollTo(0, a.s + a.dt, false, margin);
				clearInterval(a.timer);
				a.timer = null;
			}
		}, t.anim.inc );
	}
};
if((typeof(window.Oby) == 'undefined') || window.Oby.version < Oby.version) {
	window.Oby = Oby;
	window.obscurelighty = Oby;
}

var oldHikaShop = window.hikashop || hikashop;

var hikashop = {
	translations: {},
	translations_url: null,
	submitFct: null,
	filterRefreshTimer: false,

	setCookie: function (name,value,delay) {
		document.cookie = name + "=" + (value || "")  +  "; expires=" + delay + "; path=/";
   	},
	submitBox: function(data) {
		var t = this, d = document, w = window;
		if( t.submitFct ) {
			try {
				t.submitFct(data);
			} catch(err) {}
		}
		t.closeBox();
	},
	deleteId: function(id) {
		var t = this, d = document, el = id;
		if( typeof(id) == "string") {
			el = d.getElementById(id);
		}
		if(!el)
			return;
		el.parentNode.removeChild(el);
	},
	dup: function(tplName, htmlblocks, id, extraData, appendTo) {
		var d = document, tplElem = d.getElementById(tplName);
		if(!tplElem) return;
		var container = tplElem.parentNode;
		elem = tplElem.cloneNode(true);
		if(!appendTo) {
			container.insertBefore(elem, tplElem);
		} else {
			if(typeof(appendTo) == "string")
				appendTo = d.getElementById(appendTo);
			appendTo.appendChild(elem);
		}
		elem.style.display = "";
		elem.id = '';
		if(id)
			elem.id = id;
		for(var k in htmlblocks) {
			elem.innerHTML = elem.innerHTML.replace(new RegExp("{"+k+"}","g"), htmlblocks[k]);
			elem.innerHTML = elem.innerHTML.replace(new RegExp("%7B"+k+"%7D","g"), htmlblocks[k]);
		}
		if(extraData) {
			for(var k in extraData) {
				elem.innerHTML = elem.innerHTML.replace(new RegExp('{'+k+'}','g'), extraData[k]);
				elem.innerHTML = elem.innerHTML.replace(new RegExp('%7B'+k+'%7D','g'), extraData[k]);
			}
		}
		return elem;
	},
	deleteRow: function(id) {
		var t = this, d = document, el = id;
		if( typeof(id) == "string") {
			el = d.getElementById(id);
		} else {
			while(el != null && el.tagName.toLowerCase() != 'tr') {
				el = el.parentNode;
			}
		}
		if(!el)
			return;
		var table = el.parentNode;
		table.removeChild(el);
		if( table.tagName.toLowerCase() == 'tbody' )
			table = table.parentNode;
		t.cleanTableRows(table);
		return;
	},
	dupRow: function(tplName, htmlblocks, id, extraData) {
		var d = document, tplLine = d.getElementById(tplName),
				tableUser = tplLine.parentNode;
		if(!tplLine) return;
		trLine = tplLine.cloneNode(true);
		tableUser.insertBefore(trLine, tplLine);
		trLine.style.display = "";
		trLine.id = "";
		if(id)
			trLine.id = id;
		for(var i = tplLine.cells.length - 1; i >= 0; i--) {
			if(trLine.cells[i]) {
				for(var k in htmlblocks) {
					if(!htmlblocks.hasOwnProperty(k))
						continue;
					trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp("{"+k+"}","g"), htmlblocks[k]);
					trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp("%7B"+k+"%7D","g"), htmlblocks[k]);
				}
				if(extraData) {
					for(var k in extraData) {
						if(!extraData.hasOwnProperty(k))
							continue;
						trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp('{'+k+'}','g'), extraData[k]);
						trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp('%7B'+k+'%7D','g'), extraData[k]);
					}
				}
			}
		}
		if(tplLine.className == "row0") tplLine.className = "row1";
		else if(tplLine.className == "row1") tplLine.className = "row0";
		return trLine;
	},
	cleanTableRows: function(id) {
		var d = document, el = id;
		if(typeof(id) == "string")
			el = d.getElementById(id);
		if(el == null || el.tagName.toLowerCase() != 'table')
			return;

		var k = 0, c = '', line = null, lines = el.getElementsByTagName('tr');
		for(var i = 0; i < lines.length; i++) {
			line = lines[i];
			if( line.style.display != "none") {
				c = ' '+line.className+' ';
				if( c.indexOf(' row0 ') >= 0 || c.indexOf(' row1 ') >= 0 ) {
					line.className = c.replace(' row'+(1-k)+' ', ' row'+k+' ').replace(/^\s*|\s*$/g, '');
					k = 1 - k;
				}
			}
		}
	},
	checkRow: function(id) {
		var t = this, d = document, el = id;
		if(typeof(id) == "string")
			el = d.getElementById(id);
		if(el == null || el.tagName.toLowerCase() != 'input')
			return;
		if(this.clicked) {
			this.clicked = null;
			t.isChecked(el);
			return;
		}
		el.checked = !el.checked;
		t.isChecked(el);
	},
	isChecked: function(id,cancel) {
		var d = document, el = id;
		if(typeof(id) == "string")
			el = d.getElementById(id);
		if(el == null || el.tagName.toLowerCase() != 'input')
			return;
		if(el.form.boxchecked) {
			if(el.checked)
				el.form.boxchecked.value++;
			else
				el.form.boxchecked.value--;
		}
	},
	checkAll: function(checkbox, stub) {
		stub = stub || 'cb';
		if(!checkbox.form)
			return false;
		var o = window.Oby, cb = checkbox.form, c = 0;
		for(var i = 0, n = cb.elements.length; i < n; i++) {
			var e = cb.elements[i];
			if (e != checkbox && e.type == checkbox.type && ((stub && e.id.indexOf(stub) == 0) || !stub)) {
				e.checked = checkbox.checked;
				o.fireEvent(e, 'change');
				o.fireEvent(e, 'click');
				c += (e.checked == true ? 1 : 0);
			}
		}
		if (cb.boxchecked) {
			cb.boxchecked.value = c;
		}
		return true;
	},
	submitform: function(task, form, extra) {
		var d = document;
		if(typeof form == 'string') {
			var f = d.getElementById(form);
			if(!f)
				f = d.forms[form];
			if(!f)
				return true;
			form = f;
		}
		if(task) {
			form.task.value = task;
		}
		if(typeof form.onsubmit == 'function')
			form.onsubmit();
		form.submit();
		return false;
	},
	get: function(elem, target) {
		window.hikashop.xRequest(elem.getAttribute('href'), {update: target});
		return false;
	},
	form: function(elem, target) {
		var data = window.Oby.getFormData(target);
		window.hikashop.xRequest(elem.getAttribute('href'), {update: target, mode: 'POST', data: data});
		return false;
	},
	openBox: function(elem, url, jqmodal) {
		var w = window;
		if(typeof(elem) == "string")
			elem = document.getElementById(elem);
		if(!elem)
			return false;
		try {
			var hkpopup = elem.getAttribute('data-hk-popup');
			if(jqmodal === undefined) {
				jqmodal = false;
				var test_rel = elem.getAttribute('rel');
				if(test_rel == null && hkpopup == null && typeof(jQuery) != "undefined")
					jqmodal = true;
			}
			if(hkpopup) {
				var fct = this['openBox_' + hkpopup.toLowerCase()];
				if(fct) {
					var ret = fct(elem, url);
					if(ret == true)
						return false;
				}
			}
			if(!jqmodal && this.openBox_squeezebox(elem, url))
				return false;
			if(this.openBox_bootstrap(elem, url))
				return false;
			console.log('no popup system found');
		} catch(e) { console.log(e); }
		return false;
	},
	openBox_squeezebox: function(elem, url) {
		if(window.SqueezeBox === undefined)
			return false;
		if(url !== undefined && url !== null)
			elem.href = url;
		if(!elem.rel && elem.getAttribute('data-hk-popup') == 'squeezebox')
			elem.rel = elem.getAttribute('data-squeezebox');
		if(window.SqueezeBox.open !== undefined)
			SqueezeBox.open(elem, {parse: 'rel'});
		else if(window.SqueezeBox.fromElement !== undefined)
			SqueezeBox.fromElement(elem);
		setTimeout(function(){
			jQuery('#sbox-content').find('iframe').attr('name', 'hikashop_popup_iframe');
		},500);
		return true;
	},
	openBox_bootstrap: function(elem, url) {
		if(typeof(jQuery) == "undefined")
			return false;
		var id = elem.getAttribute('id');
		jQuery('#modal-' + id).modal('show');
		if(!url)
			return true;
		if(document.getElementById('modal-' + id + '-container'))
			jQuery('#modal-' + id + '-container').find('iframe').attr('src', url);
		else
			jQuery('#modal-' + id).find('iframe').attr('src', url);
		jQuery('#modal-' + id).find('iframe').attr('name', 'hikashop_popup_iframe');
		return true;
	},
	openBox_vex: function(elem, url) {
		if(typeof(vex) == "undefined")
			return false;
		var href = elem.href || null;
		if(url !== undefined && url !== null)
			href = url;
		if(!href) href = elem.getAttribute('href');
		settings = window.Oby.evalJSON(elem.getAttribute('data-vex'));
		if(settings.x && settings.y && href) {
			settings.content = '<iframe style="border:0;margin:0;padding:0;" name="hikashop_popup_iframe" width="'+settings.x+'px" height="'+settings.y+'px" src="'+href+'"></iframe>';
			settings.afterOpen = function(context) { context.width(settings.x + 'px'); };
		}
		vex.defaultOptions.className = 'vex-theme-default';
		vex.open( settings );
		return true;
	},
	closeBox: function(parent) {
		var d = document, w = window;
		if(parent) {
			d = window.parent.document;
			w = window.parent;
		}
		try {
			var e = d.getElementById('sbox-window');
			if(e && typeof(e.close) != "undefined") {
				e.close();
			} else if(typeof(w.jQuery) != "undefined" && w.jQuery('div.modal.in') && w.jQuery('div.modal.in').hasClass('in')) {
				w.jQuery('div.modal.in').modal('hide');
			} else if(typeof(vex) != 'undefined' && vex.close && vex.close() === true) {
				return;
			} else if(w.SqueezeBox !== undefined) {
				w.SqueezeBox.close();
			}
		} catch(err) {}
	},
	submitPopup: function(id, task, form) {
		var d = document, t = this, el = d.getElementById('modal-'+id+'-iframe');
		if(!el) {
			if(document.getElementById('modal-' + id + '-container'))
				el = jQuery('#modal-' + id + '-container').find('iframe').get(0);
			else
				el = jQuery('#modal-' + id).find('iframe').get(0);
		}
		if(el && el.contentWindow.hikashop) {
			if(task === undefined) task = null;
			if(form === undefined) form = 'adminForm';
			el.contentWindow.hikashop.submitform(task, form);
		}
		return false;
	},
	tabSelect: function(m,c,id) {
		var d = document, sub = null;
		if(typeof m == 'string')
			m = d.getElementById(m);
		if(!m) return;
		if(typeof id == 'string')
			id = d.getElementById(id);
		sub = m.getElementsByTagName('div');
		if(sub) {
			for(var i = sub.length - 1; i >= 0; i--) {
				if(sub[i].getAttribute('class') == c) {
					sub[i].style.display = 'none';
				}
			}
		}
		if(id) id.style.display = '';
	},
	changeState: function(el, id, url) {
		var d = document;
		if(!d.getElementById(id + '_container'))
			return false;
		var mainDiv = el.closest('.hikashop_checkout_address');
		if(mainDiv)
			window.checkout.setLoading(mainDiv, true, true);
		window.Oby.xRequest(url, null, function(xhr){
			var w = window;
			w.hikashop.updateElem(id + '_container', xhr.responseText);
			var defaultVal = '', defaultValInput = d.getElementById(id + '_default_value'), stateSelect = d.getElementById(id);
			if(defaultValInput) { defaultVal = defaultValInput.value; }
			if(stateSelect && w.hikashop.optionValueIndexOf(stateSelect.options, defaultVal) >= 0)
				stateSelect.value = defaultVal;
			if(typeof(jQuery) != "undefined" && jQuery().chosen) { jQuery('#'+id).chosen(); }
			if(mainDiv)
				window.checkout.setLoading(mainDiv, false);
			w.Oby.fireAjax('hikashop.stateupdated', {id: id, elem: stateSelect});
		});
	},
	optionValueIndexOf: function(options, value) {
		for(var i = options.length - 1; i >= 0; i--) {
			if(options[i].value == value)
				return i;
		}
		return -1;
	},
	getOffset: function(el) {
		var x = 0, y = 0;
		while(el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop )) {
			x += el.offsetLeft - el.scrollLeft;
			y += el.offsetTop - el.scrollTop;
			el = el.offsetParent;
		}
		return { top: y, left: x };
	},
	dataStore: function(name, value) {
		if(localStorage) {
			localStorage.setItem(name, value);
		} else {
			var expire = new Date(); expire.setDate(expire.getDate() + 5);
			document.cookie = name+"="+value+"; expires="+expire;
		}
	},
	dataGet: function(name) {
		if(localStorage) {
			return localStorage.getItem(name);
		}
		if(document.cookie.length > 0 && document.cookie.indexOf(name+"=") != -1) {
			var s = name+"=", o = document.cookie.indexOf(s) + s.length, e = document.cookie.indexOf(";",o);
			if(e == -1) e = document.cookie.length;
			return unescape(document.cookie.substring(o, e));
		}
		return null;
	},
	setArrayDisplay: function(fields, displayValue) {
		var d = document, e = null;
		if(displayValue === true) displayValue = '';
		if(displayValue === false) displayValue = 'none';
		for(var i = 0; i < fields.length; i++) {
			e = d.getElementById(fields[i]);
			if(e) e.style.display = displayValue;
		}
	},
	ready: function(fct) {
		var w = window, d = w.document;
		if(d.readyState === "complete") {
			fct();
			return;
		}
		if(w.jQuery !== undefined) {
			jQuery(d).ready(fct);
		} else if(window.addEvent) {
			w.addEvent("domready", fct);
		} else
			w.Oby.ready(fct);
	},
	noChzn: function() {
		if(!window.jQuery)
			return false;
		jQuery('.no-chzn').each(function(i,el) {
			var id = el.getAttribute('id'), chzn = null;
			if(id) {
				id = id.replace('{','_').replace('}','_');
				chzn = jQuery('#'+id+'_chzn');
			} else {
				chzn = el.nextSibling;
			}
			if(chzn) chzn.remove();
			jQuery(el).removeClass('chzn-done').show();
		});
		return true;
	},
	switchTab: function(el) {
		if(!el || !el.parentNode || !el.parentNode.parentNode) return false;
		var d = document, w = window, o = w.Oby,
			c = el.parentNode.parentNode,
			r = c.getAttribute('rel'),
			current = el.getAttribute('rel'),
			dest = null;
		if(!r || r.substring(0,5) != 'tabs:') return false;
		if(current.substring(0,4) != 'tab:') return false;
		var id = r.substring(5),
			tabs = c.childNodes;
		current = current.substring(4);
		dest = d.getElementById(id + current);
		if(!dest) return false;
		for(var k = 0; k < tabs.length; k++) {
			if(!tabs[k] || tabs[k].nodeName.toLowerCase() != 'li') continue;
			var i = 0, l = tabs[k].childNodes[i], lr = null;
			while(l.nodeName.toLowerCase() != 'a' && i < tabs[k].childNodes.length)
				l = tabs[k].childNodes[++i];
			if(l.nodeName.toLowerCase() == 'a')
				lr = l.getAttribute('rel');
			if(!lr || lr.substring(0,4) != 'tab:')
				continue;
			var lid = lr.substring(4);
			if(lid == current) continue;
			o.removeClass(tabs[k], 'active');
			var ld = d.getElementById(id + lid);
			if(ld) ld.style.display = 'none';
		}
		dest.style.display = '';
		o.addClass(el.parentNode, 'active');
		el.blur();
		return false;
	},
	dlTitle: function(parent) {
		var t = this, d = document, w = window;
		if(parent && typeof(parent) == 'string')
			parent = d.getElementById(parent);
		if(!parent)
			parent = d;
		var dt = parent.getElementsByTagName('dt'), val = null,
			hkTip = (typeof(hkjQuery) != "undefined" && hkjQuery().hktooltip);
		for(var i = 0; i < dt.length; i++) {
			if(dt[i].offsetWidth === 0) {
				dt[i].dlTitleFct = function(evt){
					t.dlTitle(this.parentNode);
					if(hkTip)
						hkjQuery(this).hktooltip('show');
					this.removeEventListener('mouseover', this.dlTitleFct);
					this.dlTitleFct = null;
				};
				dt[i].addEventListener('mouseover', dt[i].dlTitleFct);
			}
			if(dt[i].offsetWidth < dt[i].scrollWidth && !dt[i].getAttribute('title')) {
				val = (dt[i].innerText !== undefined) ? dt[i].innerText : dt[i].textContent;

				if(hkTip) {
					dt[i].setAttribute('data-title', val);
					hkjQuery(dt[i]).hktooltip({"html": true,"container": "body"});
				} else
					dt[i].setAttribute('title', val);
			}
		}
	},
	checkConsistency: function() {
		if(!document.querySelectorAll)
			return;
		var s = null, elems = null,
			parents = document.querySelectorAll('[data-consistencyheight]');
		if(!parents || !parents.length)
			return;
		for(var i = parents.length - 1; i >= 0; i--) {
			s = parents[i].getAttribute('data-consistencyheight');
			if(s == '' || s == 'true')
				continue;
			var reg = new RegExp('^\.[-_a-z0-9]+$', 'i');
			if(reg.test(s) && document.getElementsByClassName)
				elems = parents[i].getElementsByClassName(s.substring(1));
			else
				elems = parents[i].querySelectorAll(s);
			if(!elems || !elems.length)
				continue;
			if(this.setConsistencyHeight(elems, 'min') === false)
				continue;
			parents[i].setAttribute('data-consistencyheight-done', s);
			parents[i].removeAttribute('data-consistencyheight');
		}
	},
	setConsistencyHeight: function(elems, mode) {
		if(!elems || !elems.length || elems.length == 0)
			return;
		var maxHeight = 0, cpt = 0, h = 0, w = window;
		for(var i = elems.length - 1; i >= 0; i--) {
			try {
				h = parseFloat( w.getComputedStyle(elems[i], '').getPropertyValue('height') );
				h = Math.ceil(h);
			} catch(e) {
				h = NaN;
			}
			if(isNaN(h))
				h = (elems[i].currentStyle ? elems[i].currentStyle.height : elems[i].clientHeight);

			if(maxHeight > 0 && h < maxHeight) {
				cpt++;
			} else if(h > maxHeight) {
				maxHeight = h;
				cpt++;
			}
		}
		if(maxHeight <= 0)
			return false;

		if(cpt <= 1)
			return;
		for(var i = elems.length - 1; i >= 0; i--) {
			if(mode !== undefined && mode == 'min')
				elems[i].style.minHeight = maxHeight + 'px';
			else
				elems[i].style.height = maxHeight + 'px';
		}
	},
	refreshFilters: function (el, skipSelf) {
		"use strict";
		var d = document, t = this, o = window.Oby,
		container = null, data = null, containerName = el.getAttribute('data-container-div');

		if(containerName)
			container = d.forms[containerName];

		if(!container)
			return false;

		var url = container.getAttribute('action');
		var scrollToTop = container.getAttribute('data-scroll');

		// delay timer to avoid too many ajax calls
		if(t.filterRefreshTimer !== false) clearTimeout(t.filterRefreshTimer);
		t.filterRefreshTimer = setTimeout(function() {

			data = o.getFormData(container);
			data += '&tmpl=raw';
			o.xRequest(url, {mode:'POST', data: data}, function(xhr) {
				var resp = o.evalJSON(xhr.responseText);

				if(resp.newURL) {
					var urlInHistory = resp.newURL.replace('tmpl=raw&', '', 'g').replace('tmpl=component&', '', 'g').replace('filter=1&', '', 'g').replace('&tmpl=raw', '', 'g').replace('&tmpl=component', '', 'g').replace('&filter=1', '', 'g');
					window.history.pushState(data, d.title, urlInHistory);

					window.addEventListener('popstate', function(e) {
						if(window.location.href.includes('hikashop_url_reload=1')) {
							window.location.href.replace('&hikashop_url_reload=1','').reload();
						}
					});
				}

				var refreshAreas = document.querySelectorAll('.filter_refresh_div');

				var triggers = o.fireAjax('filters.update', {el: el, refreshAreas : refreshAreas, resp: resp});
				if(triggers !== false && triggers.length > 0)
					return true;

				var refreshUrl = null;
				t.refreshCounter = 0;
				for(let i = 0; i < refreshAreas.length; i++) {
					var currentArea = refreshAreas[i];
					if(skipSelf && currentArea.querySelector('#'+el.id))
						continue;

					if(resp.newURL && currentArea.getAttribute('data-use-url')) {
						refreshUrl = resp.newURL;
					} else {
						refreshUrl = currentArea.getAttribute('data-refresh-url');
						if(resp.params) {
							refreshUrl += '&' + resp.params + '&return_url=' + encodeURIComponent(window.location.href);
						}
					}
					if(!refreshUrl)
						continue;
					t.refreshCounter++;
					var className = currentArea.getAttribute('data-refresh-class');
					if(className) o.addClass(currentArea, className);
					t.refreshOneArea(refreshUrl, currentArea, el, refreshAreas, resp);
				}

				if(scrollToTop) {
					window.hikashop.smoothScroll();
				}
			});
			t.filterRefreshTimer = false;
		}, 300);
		return false;
	},
	smoothScroll: function(target) {
		var target = document.querySelector('div[id^="hikashop_category_information_menu_"]');
		if(!target)
			return;
		var currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
		if (currentScroll > target.offsetTop) {
			window.requestAnimationFrame(window.hikashop.smoothScroll);
			window.scrollTo (target.offsetTop, currentScroll - (currentScroll/5));
		}
	},
	refreshOneArea: function(refreshUrl, currentArea, el, refreshAreas, resp) {
		var d = document, t = this, o = window.Oby;
		o.xRequest(refreshUrl, {mode:'GET'}, function (xhr2) {
			var div = d.createElement('div');
			var scripts = '';
			var text = xhr2.responseText.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(all, code){
				if(all.indexOf('type="application/json"') != -1)
					return '';
				if(all.indexOf('type="application/ld+json"') != -1)
					return '';
				scripts += code + '\n';
				return '';
			});

			var body = /<body.*?>([\s\S]*)<\/body>/.exec(text);
			if(!body)
				body = text;
			else
				body = body[1];
			window.hikashop.updateElem(div, body);
			var newElem = div.querySelector('.filter_refresh_div');

			// to avoid scroll in chrome
			setTimeout(function(){
				if(!currentArea) {
					t.refreshCounter--;
					return;
				}
				var className = currentArea.getAttribute('data-refresh-class');
				if(className) o.removeClass(currentArea, className);
				if(!newElem) {
					t.refreshCounter--;
					return;
				}
				var parentNode = currentArea.parentNode;
				if(!parentNode) {
					t.refreshCounter--;
					return;
				}
				parentNode.replaceChild(newElem, currentArea);
				if( scripts != '' ) {
					new Promise((resolve, reject)=>{
						var script = d.createElement('script');
						script.setAttribute('type', 'text/javascript');

						var windowErrorHandler = (event) =>{
							event.preventDefault();
							var error = event.error;
							error.stack = error.stack + '\n\n' + scripts;
							window.removeEventListener('error', windowErrorHandler);
							reject(error);
						};
						window.addEventListener('error', windowErrorHandler);

						var rejectHandler = (error) =>{
							window.removeEventListener('error', windowErrorHandler);
							reject(error);
						};
						script.addEventListener('error', rejectHandler);
						script.addEventListener('abort', rejectHandler);
						var loadedHandler = ()=>{
							window.removeEventListener('error', windowErrorHandler);
							resolve();
						};

						script.addEventListener('load', loadedHandler);
						script.text = scripts.replaceAll('let jch_', 'jch_').replaceAll('const jch', 'window.hikashop.jch');
						try {
							d.head.appendChild(script);
							d.head.removeChild(script);
						} catch (e) {
							if (e instanceof SyntaxError) {
							}
						}
					})
					.catch(error => {
						console.warn('Could not process JavaScript code:\n', error);
					})
					.then(()=>{
					});
				}

				if(!window.localPage) window.localPage = {};
				window.localPage.infiniteScrollPage = 1;

				setTimeout(function(){
					var elems = parentNode.querySelectorAll('.hikashop_subcontainer');
					if(elems && elems.length)
						window.hikashop.setConsistencyHeight(elems, 'min');

					if(window.hikaVotes)
						initVote(currentArea);
					if(hkjQuery && hkjQuery.hktooltip)
						hkjQuery('[data-toggle="hk-tooltip"]').hktooltip({"html": true,"container": "body"});

					t.refreshCounter--;
					if(t.refreshCounter == 0) {
						o.fireAjax('filters.updated', {el: el, refreshAreas : refreshAreas, resp: resp});
					}
				}, 200);
			}, 0);
		});
	},
	addToCart: function(el, type, container, data) {

		var d = document, t = this, o = window.Oby,
			product_id = 0,
			url = el.getAttribute('href'),
			cart_type = ((type !== 'wishlist') ? 'cart' : 'wishlist'),
			containerName = el.getAttribute('data-addTo-div'),
			extraContainer = el.getAttribute('data-addTo-extra'),
			dest_id = el.getAttribute('data-addTo-cartid');

		product_id = (cart_type == 'cart') ? el.getAttribute('data-addToCart') : el.getAttribute('data-addToWishlist');
		dest_id = (dest_id ? parseInt(dest_id) : 0);
		if(!url)
			url = el.getAttribute('data-href');

		// Avoid bots and crawlers to add products in the cart
		var r = /bot|googlebot|crawler|spider|robot|crawling/i;
		if(navigator && navigator.userAgent && r.test(navigator.userAgent))
			return false;

		// No product ID - fallback mode
		if(!product_id || !url) {
			if(containerName && d.forms[containerName]) {
				d.forms[containerName].submit();
				return false;
			}
			return true;
		}

		if(typeof container !== 'undefined') {
			// container is provided, just use it
		}else if(containerName && product_id) {
			// search for the container on the page
			container = d.forms['hikashop_product_form_' + product_id + '_' + containerName] || d.forms[containerName];
		}

		url += (url.indexOf('?') >= 0 ? '&' : '?') + 'tmpl=raw';
		if(typeof data !== 'undefined') {
		} else if(container) {
			if(window.FormData)
				data = new FormData(container);
			else
				data = o.getFormData(container);
			if(extraContainer) {
				extraContainer = d.forms[extraContainer] || d.getElementById(extraContainer);
				if(window.FormData) {
					extra = o.getFormData(extraContainer, false);
					for(var k in extra) {
						if(!extra.hasOwnProperty(k))
							continue;
						if(k == 'product_id')
							extra[k] = product_id;
						if(typeof(extra[k]) == 'object') {
							for(var i in extra[k]) {
								data.append(k, extra[k][i]);
							}
						} else
							data.append(k, extra[k]);
					}
				} else {
					var extra = o.getFormData(extraContainer);
					if(extra)
						data += '&' + extra;
					data += '&product_id='+product_id;
				}
			}
			if(window.FormData) {
				data.append('cart_type', cart_type);
				if(dest_id)
					data.append('cart_id', dest_id);
			} else {
				data += '&cart_type=' + cart_type;
				if(dest_id)
					data += '&cart_id+' + dest_id;
			}
		} else {
			data = 'cart_type=' + cart_type;
			if(dest_id)
				data += '&cart_id+' + dest_id;
		}

		var className = el.getAttribute('data-addTo-class');
		if(className) o.addClass(el, className);

		if(window.self !== window.top && window.top.hikashop) {
			return window.top.hikashop.addToCart(el, type, container, data);
		}

		o.xRequest(url, {mode:'POST', data: data}, function(xhr) {
			var className = el.getAttribute('data-addTo-class');
			if(className) o.removeClass(el, className);

			var resp = Oby.evalJSON(xhr.responseText);
			var cart_id = (resp && (resp.ret || resp.ret === 0)) ? resp.ret : parseInt(xhr.responseText);
			if(isNaN(cart_id)) {
				console.log('cart_id was not returned in addToCart AJAX call');
				console.log(resp);
				return false;
			}

			var triggers = window.Oby.fireAjax(cart_type+'.updated', {id: cart_id, el: el, product_id: product_id, type: cart_type, resp: resp});
			if(triggers !== false && triggers.length > 0)
				return true;
			if(window.localPage && cart_type == 'cart' && window.localPage.cartRedirect && typeof(window.localPage.cartRedirect) == 'function')
				return window.localPage.cartRedirect(cart_id, product_id, resp);
			if(window.localPage && cart_type == 'wishlist' && window.localPage.wishlistRedirect && typeof(window.localPage.wishlistRedirect) == 'function')
				return window.localPage.wishlistRedirect(cart_id, product_id, resp);
		});
		return false;
	},
	addToWishlist: function(el) {
		return this.addToCart(el, 'wishlist');
	},
	checkQuantity: function(el) {
		var value = parseInt(el.value), old = el.getAttribute('data-hk-qty-old'),
			min = parseInt(el.getAttribute('data-hk-qty-min')),
			max = parseInt(el.getAttribute('data-hk-qty-max')),
			allowZero = el.getAttribute('data-hk-allow-zero') == 'true';
		if(old)
			old = parseInt(old);
		// No values - return
		if(isNaN(value)) {
			el.value = old || (isNaN(min) ? 1 : min);
			return false;
		}
		if(''+value != el.value)
			el.value = value;
		if(isNaN(min) || isNaN(max))
			return false;
		var triggers = window.Oby.fireAjax("quantity.checked", {el:el, value:value, max:max, min:min});
		if(
			(triggers !== false && triggers.length > 0) ||
			(value == 0 && allowZero) ||
			((value <= max || max == 0) && value >= min)
		) {
			// quantity change is ok

			// trigger add to cart if the quantity input is synchronized with the cart
			var isSynch = document.getElementById(el.id + '_synch');
			if(isSynch) {
				isSynch.value = value;
				var addToCartButton = document.getElementById(el.id + '_add_to_cart_button');
				if(addToCartButton) {
					addToCartButton.onclick();
				}
			}
			return true;
		}
		if(max > 0 && value > max) {
			el.value = max;
			if(hkjQuery.notify) {
				this.translate(['QUANTITY_CHANGE_IMPOSSIBLE', 'MAXIMUM_FOR_PRODUCT_IS_X'], function(trans){
					hkjQuery(el).notify({title:trans[0],text:trans[1].replace('%s', max), image:'<i class="fa fa-3x fa-exclamation-circle"></i>'},{style:"metro",className:"warning",arrowShow:true,position:"top left"});
				});
			}
		} else if(value < min) {
			el.value = min;
			if(hkjQuery.notify) {
				this.translate(['QUANTITY_CHANGE_IMPOSSIBLE', 'MINIMUM_FOR_PRODUCT_IS_X'], function(trans){
					hkjQuery(el).notify({title:trans[0],text:trans[1].replace('%s', min), image:'<i class="fa fa-3x fa-exclamation-circle"></i>'},{style:"metro",className:"warning",arrowShow:true,position:"top left"});
				});
			}
		}
		// should not happen ?
		return true;
	},
	translate: function(keys, callback) {
		var t = this, trans = {}, missingKeys = [], o = window.Oby;

		for(var c = 0; c < keys.length; c++) {
			var key = keys[c];
			if(!t.translations[key]) {
				missingKeys.push(key);
			} else {
				trans[c] = t.translations[key];
			}
		}

		if(!missingKeys.length) {
			callback(trans);
			return;
		}

		if(!t.translations_url) {
			console.log('missing translations URL');
			return;
		}
		o.xRequest(t.translations_url, {mode:'POST', data: 'translations=' + missingKeys.join(',')}, function(xhr) {
			var resp = o.evalJSON(xhr.responseText);
			foundKeys = Object.getOwnPropertyNames(resp);
			for(var c = 0; c < foundKeys.length; c++) {
				var key = foundKeys[c];
				trans[keys.indexOf(key)] = resp[key];
				t.translations[key] = resp[key];
			}
			callback(trans);
		});
	},
	addTrans: function(data) {
		for(var k in data) {
			if(!data.hasOwnProperty(k)) continue;
			this.translations[k] = data[k];
		}
	},
	updateQuantity: function(el, dataInput, mod) {
		var d = document, input = el;
		if(!el)
			return false;
		if(dataInput === undefined || !dataInput)
			dataInput = el.getAttribute('data-hk-qty-input');
		if(d.getElementById(dataInput))
			input = d.getElementById(dataInput);
		if(mod === undefined || !mod)
			mod =  parseInt(el.getAttribute('data-hk-qty-mod'));
		if(isNaN(mod) || mod == 0)
			mod = 1;
		var value = parseInt(input.value);
		if(isNaN(value))
			value = 0;
		input.value = (value + mod);
		this.checkQuantity(input);
		if(el.tagName.toLowerCase() == 'a')
			el.blur();
		return false;
	},
	deleteFromCart: function(el, cart_type, container) {
		if(el.processing)
			return false;

		var d = document, t = this, o = window.Oby,
			url = el.getAttribute('href');
		if(!cart_type || cart_type === undefined)
			cart_type = el.getAttribute('data-cart-type');
		if(!cart_type || cart_type == '')
			return true;

		url += (url.indexOf('?') >= 0 ? '&' : '?') + 'tmpl=raw';
		var cart_id = parseInt(el.getAttribute('data-cart-id')),
			cart_product_id = parseInt(el.getAttribute('data-cart-product-id'));
		if(cart_id === NaN || cart_product_id === NaN)
			return true;

		if(container && typeof(container) == 'string')
			container = d.getElementById(container);

		el.processing = true;
		if(container)
			o.addClass(container, "hikashop_checkout_loading");
		var data = 'cart_type=' + cart_type + '&cart_id' + cart_id + '&cart_product_id=' + cart_product_id;

		o.xRequest(url, {mode:'POST', data: data}, function(xhr) {
			el.processing = false;
			if(container)
				o.removeClass(container, "hikashop_checkout_loading");

			var resp = Oby.evalJSON(xhr.responseText);
			cart_id = (resp && resp.ret) ? resp.ret : ((resp && resp.empty && resp.empty == 'true') ? cart_id : parseInt(xhr.responseText));
			if(cart_id === NaN)
				return;
			resp.cart_product_id = cart_product_id;
			resp.quantity = 0;
			var params = {id: cart_id, type: cart_type, resp: resp, notify: false};
			window.Oby.fireAjax(cart_type+'.updated', params);
		});
		return false;
	},
	submitCartModule: function(form, container, cart_type) {
		this.formAjaxSubmit(form, container, function(data, params) {
			var resp = window.Oby.evalJSON(data);
			var cart_id = (resp && resp.ret) ? resp.ret : parseInt(data);
			if(cart_id === NaN)
				return;
			var updatedElements = [];
			for (var key of params.keys()) {
				if(key.startsWith('item[') && key.endsWith('][cart_product_quantity]')) {
					updatedElements.push({ cart_product_id: key.replace('item[', '').replace('][cart_product_quantity]', ''), quantity_requested: params.get(key)});
				}
			}
			window.Oby.fireAjax(cart_type+'.updated', {id: cart_id, type: cart_type, resp: resp, updated_elements: updatedElements, notify: false});
		});
		return false;
	},
	formAjaxSubmit: function(form, container, cb) {
		var d = document, o = window.Oby,
			url = form.action;
		if(form.processing)
			return false;
		if(container && typeof(container) == 'string')
			container = d.getElementById(container);
		if(window.FormData) {
			data = new FormData(form);
			data.append('tmpl', 'raw');
		} else {
			data = o.getFormData(form);
			data += '&tmpl=raw';
		}
		form.processing = true;
		if(container)
			o.addClass(container, "hikashop_checkout_loading");
		o.xRequest(url, {mode:'POST', data: data}, function(xhr) {
			form.processing = false;
			if(container)
				o.removeClass(container, "hikashop_checkout_loading");
			if(!cb)
				o.updateElem(container, xhr.responseText);
			cb(xhr.responseText, data);
		});
		return false;
	},
	syncInit : function() {
		window.hikashop.noChzn();
		window.Oby.registerAjax(['cart.updated'], function(params){
			window.hikashop.refreshSync(params);
		});
		window.Oby.registerAjax(['hkContentChanged'], function(params){
			window.Oby.xRequest(window.hikashop.cartInfoUrl, {}, function(xhr) {
				var resp = Oby.evalJSON(xhr.responseText);
				var params = {resp: resp};
				window.hikashop.refreshSync(params);
			});
		});

		window.Oby.registerAjax(['hkCustomFieldChanged'], function(params){
			if(params.field_type != 'item')
				return;
			window.Oby.xRequest(window.hikashop.cartInfoUrl, {}, function(xhr) {
				var resp = Oby.evalJSON(xhr.responseText);
				var params = {resp: resp};
				window.hikashop.refreshSync(params);
			});
		});

		if(window.hikashop.cartInfo) {
			var params = {resp: window.hikashop.cartInfo};
			window.hikashop.refreshSync(params);
		}
	},
	xRequest: function(url, options, cb, cbError) {
		var t = this, xhr = window.Oby.getXHR();
		if(!options) options = {};
		if(!cb) cb = function(){};
		options.mode = options.mode || 'GET';
		options.update = options.update || false;
		options.replace = options.replace === undefined || options.replace;
		xhr.onreadystatechange = function() {
			if(xhr.readyState != 4)
				return;
			if( xhr.status == 200 || (xhr.status == 0 && xhr.responseText > 0) || !cbError ) {
				if(cb)
					cb(xhr,options.params);
				if(options.update)
					t.updateElem(options.update, xhr.responseText, options.replace);
			} else {
				cbError(xhr,options.params);
			}
		};
		xhr.open(options.mode, url, true);
		if(options.mode.toUpperCase() == 'POST' && typeof(options.data) == 'string') {
			xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		}
		xhr.send( options.data );
	},
	updateElem: function(elem, data, replace = true) {
		var d = document, scripts = '', json = '';
		if( typeof(elem) == 'string' )
			elem = d.getElementById(elem);

		// extract the javascript files and scripts from the HTML
		var files = [];
		var scriptsAlreadyLoaded = document.getElementsByTagName("script");
		var text = data.replace(/<script([^>]*)>([\s\S]*?)<\/script>/gi, function(all, attributes, code){
			if(all.indexOf('type="application/json"') != -1) {
				json += code + '\n';
				return '';
			}
			if(all.indexOf('type="application/ld+json"') != -1)
				return '';
			regex = RegExp('src="([^"]+)"', 'gi');
			result = regex.exec(attributes);
			if(result) {
				result[1] = result[1].replace(/\.js\?[0-9a-f\.]{1,128}/gi, '.js').replace(/&amp;/g, '&');
				for(var i = 0; i < scriptsAlreadyLoaded.length; i++) {
					var src = scriptsAlreadyLoaded[i].getAttribute('src');
					if(src && src.includes(result[1]))
						return '';
				}
				files.push(result[1]);
				return '';
			}
			scripts += code.replace('document.addEventListener(\'DOMContentLoaded\',', 'window.hikashop.ready(').replace('document.addEventListener("DOMContentLoaded",', 'window.hikashop.ready(').replace('$(document).ready(', 'window.hikashop.ready(').replace( 'find("select").chosen(', 'find("select").filter(":visible").chosen(') + '\n';
			return '';
		});

		// add the HTML without the javascript elements to the element on the page
		if(text.indexOf('id="hikashop_main_content"')) {
			var wrapper = document.createElement('div');
			wrapper.innerHTML = text;
			var mainArea = wrapper.querySelector('#hikashop_main_content');
			if(mainArea) {
				if(replace)
					elem.innerHTML = '';
				elem.appendChild(mainArea);
				text = false;
			}
		}
		if(text !== false) {
			if(!replace)
				elem.insertAdjacentHTML('beforeend',text);
			else
				elem.innerHTML = text;
		}

		// add the javascript elements and run them
		if( json != '' && Joomla) {
			var option = JSON.parse(json);
			if (option) {
				Joomla.loadOptions(option);
			}
		}

		if(files.length) {
			this.loadScripts(files, function() {
				window.hikashop.addScript(scripts);
			});
		} else {
			window.hikashop.addScript(scripts);
		}
	},
	addScript: function (code) {
		if(code == '')
			return;
		var oNew = document.createElement("script");
		oNew.type = "text/javascript";
		oNew.textContent = code;
		document.getElementsByTagName("head")[0].appendChild(oNew);
	},	
	loadScripts: function(scripts, complete) {
		var xhr = window.Oby.getXHR();
		var loadScript = function( src ) {
			xhr.open("GET", src , true);
			xhr.send();
		};
		xhr.onreadystatechange = function() {
			if (xhr.readyState != 4)
				return;
			if (xhr.status == 200) {
				try {
					window.hikashop.addScript(xhr.responseText);
				} catch (e) {
					if (e instanceof SyntaxError) {
						console.log(e.message + ' for ' + xhr.responseURL);
						console.log(xhr.responseText);
					}
				}
			}
			var next = scripts.shift();
			if ( next ) {
				loadScript(next);
			} else if ( typeof complete == 'function' ) {
				complete();
			}
		}
		loadScript( scripts.shift() );
	},
	refreshSync : function(params) {
		// the cart is empty so reset all the synched elements and refresh them
		if(params.resp.empty) {
			synchronizedEls = document.querySelectorAll('.synchronized_add_to_cart');
			for(var i=0; i < synchronizedEls.length; i++) {
				var el = synchronizedEls[i];
				el.value = 0;
				el.setAttribute('data-cart-product-id', 0);
				var id = el.getAttribute('data-id');
				window.hikashop.syncQuantity(id, el.value, el.value);
			}
		}
		// the cart is updated from the cart module
		else if(params.resp.products) {
			// loop on all the products on the page
			var els = document.querySelectorAll('.synchronized_add_to_cart');

			if(!els.length)
				return;
			for(var k=0; k < els.length; k++) {
				var el = els[k];
				var id = el.getAttribute('data-id');
				var productId = el.getAttribute('data-product-id');
				var options = [];
				if(el.form) {
					var optionsTable = el.form.querySelector('.hikashop_product_options_table');
					if(optionsTable) {
						options = hikaProductOptions.getOptions();
					}
				}
				var found = false;
				// see if we have the product in the cart
				for(var i=0; i < params.resp.products.length; i++) {
					var result = params.resp.products[i];
					// get the current quantity from the response of the server and update the quantity for the quantity input of the current loop
					if(productId == result.product_id) {
						if(options.length || (result.options && result.options.length)) {
							// no options selected on the product page or no options selected in the product in the cart -> products are different
							if(!options.length || !result.options || !result.options.length)
								continue;
							var match = true;
							// check that both arrays contain the same elements
							for(var j=0; j < options.length; j++) {
								if(!result.options.includes(options[j])) {
									match = false;
									break;
								}
							}
							for(var j=0; j < result.options.length; j++) {
								if(!options.includes(result.options[j])) {
									match = false;
									break;
								}
							}
							if(!match)
								continue;
						}
						found = true;
						el.value = result.quantity;
					}
				}
				// TODO: add custom item fields
				if(!found) {
					el.value = 0;
					el.setAttribute('data-cart-product-id', 0);
				}
				window.hikashop.syncQuantity(id, el.value, el.value);
			}
		}
	},
	syncQuantity : function(id, qtyInCart, qtyInInput) {
		// refresh the quantity in the input field
		if(typeof qtyInInput !== 'undefined') {

			var inputField = document.getElementById(id+'_select');
			if(!inputField)
				inputField = document.getElementById(id);
			if(inputField) {
				if(qtyInInput == 0) {
					var minQty = inputField.getAttribute('data-hk-qty-min');
					if(minQty == 0)
						qtyInInput = 1;
					else
						qtyInInput = minQty;
				}
				inputField.value = qtyInInput;
			}
		}

		// update the display of the quantity input field and the buttons
		var quantityArea = document.getElementById(id + '_area');
		var cartButtonArea = document.getElementById(id + '_add_to_cart_button');
		var wishlistButtonArea = document.getElementById(id + '_add_to_wishlist_button');
		if(!quantityArea || !cartButtonArea)
			return;
		if(qtyInCart > 0) {
			quantityArea.style.display = '';
			cartButtonArea.style.display = 'none';
			if(wishlistButtonArea)
				wishlistButtonArea.style.display = 'none';
		} else {
			quantityArea.style.display = 'none';
			cartButtonArea.style.display = '';
			if(wishlistButtonArea)
				wishlistButtonArea.style.display = '';
		}
	},
	toggleOverlayBlock: function(el, type, state) {
		var t = this, d = document, w = window, o = w.Oby;
		if(typeof(el) == 'string')
			el = d.getElementById(el);
		if(!el)
			return false;
		var open = !!el.toggleOpen; // (el.style.display != 'none');
		if(type != 'hover' && type != 'toggle')
			type = 'click';
		if(type == 'hover' && (!state && open) || (state && !open))
			return;
		if(jQuery) {
			jQuery(el).slideToggle('fast');
		} else {
			el.style.display = (el.style.display == 'none')?'block':'none';
		}
		el.toggleOpen = !el.toggleOpen;

		if(open) {
			if(type == 'hover') {
				o.removeEvent(el, "mouseout", el.toggleFunctionHover);
				el.toggleFunctionHover = null;
			}
			if(el.toggleFunction)
				o.removeEvent(document, "click", el.toggleFunction);
			el.toggleFunction = null;
			return true;
		}
		if(type == 'hover') {
			el.toggleFunctionHover = function(event) {
				if(event.target && this != event.target)
					return false;
				window.hikashop.toggleOverlayBlock(el, 'hover', true);
			};
			if(jQuery) {
				jQuery(el).mouseleave(el.toggleFunctionHover);
			} else {
				o.addEvent(el, "mouseout", el.toggleFunctionHover);
			}
		}
		var f = function(evt) {
			if (!evt) var evt = window.event;
			var trg = (window.event) ? evt.srcElement : evt.target;
			while(trg != null) {
				if(trg == el)
					return;
				trg = trg.parentNode;
			}
			t.toggleOverlayBlock(el);
			o.removeEvent(document, "click", f);
			el.toggleFunction = null;
		};
		el.toggleFunction = f;
		setTimeout(function(){ o.addEvent(document, "click", f); }, 100);
		return true;
	},
	addToCompare: function(el) {
		var t = this, d = document, w = window, o = w.Oby;
		if(!t.compare_list)
			t.compare_list = {};

		if(el.disabled)
			return false;

		var product_id = parseInt(el.getAttribute('data-addToCompare')),
			product_name = el.getAttribute('data-product-name'),
			css = el.getAttribute('data-addTo-class');

		if(isNaN(product_id) || product_id <= 0)
			return false;
		if(!css || css == '')
			css = 'hika-compare';

		var adding = !t.compare_list.hasOwnProperty(product_id);
		if(adding)
			t.compare_list[product_id] = product_name;
		else
			delete t.compare_list[product_id];

		var elems = d.querySelectorAll('[data-addToCompare="'+product_id+'"]');
		if(elems && elems.forEach) {
			elems.forEach(function(e){
				if(e.nodeName.toLowerCase() == 'input' && e.type.toLowerCase() == 'checkbox')
					e.checked = adding;
				if(adding)
					o.addClass(e, css);
				else
					o.removeClass(e, css);
			});
		}
		var size = 0;
		if(Object.keys) {
			size = Object.keys(t.compare_list).length;
		} else {
			for(var k in t.compare_list) {
				if(compare_list.hasOwnProperty(k))
					size++;
			}
		}
		var triggers = window.Oby.fireAjax('compare.updated', {el: el, product_id: product_id, added: adding, list: t.compare_list, size: size});
		if(triggers !== false && triggers.length > 0)
			return false;
		return false;
	},
	toggleOptions: function() {
		var d = document, btnText = d.getElementById('openSearch_btn'),
		tagsDiv = d.getElementById('hikashop_listing_filters_id');
		if(tagsDiv.classList.contains("hidden-features") ) {
			tagsDiv.classList.remove("hidden-features");
			tagsDiv.classList.add("show-features");
			btnText.innerHTML = "<i class='fas fa-chevron-up'></i>";
		} else {
			tagsDiv.classList.remove("show-features");
			tagsDiv.classList.add("hidden-features");
			btnText.innerHTML = "<i class='fas fa-chevron-down'></i>";
		}
		return false;
	},
	clearOptions: function(options, defaults) {
		var d = document, btnText = d.getElementById('openSearch_btn'),
		tagsDiv = d.getElementById('hikashop_listing_filters_id');
		if(!options)
			return false;
		for(var i = options.length - 1; i >= 0; i--) {
			var name = 'filter_' + options[i];
			var el = d.getElementById(name);
			if(!el) {
				console.log('Filter option '+name+' not found');
				continue;
			}
			el.value = defaults[i];
		}
		return true;
	},
	clearSearch: function(el, id, all) {
		if(el.form.limitstart)
			el.form.limitstart.value = 0;
		var search = document.getElementById(id);
		if(search)
			search.value = '';
		if(all) {
			var v, els = el.form.querySelectorAll('[data-search-clear]');
			for(var i = els.length - 1; i >= 0; i--) {
				v = els[i].getAttribute('data-search-clear');
				els[i].value = v;
			}
		} else
			all = false;
		var triggers = window.Oby.fireAjax('search.cleared', {el: el, id: id, all: all});
		if(triggers !== false && triggers.length > 0)
			return false;
		el.form.submit();
		return true;
	},
	compareProducts: function(el, elems) {
		var t = this, params = '',
			url = el.getAttribute('data-compare-href');
		if(!url)
			return false;
		if(!elems)
			elems = t.compare_list;
		if(!elems)
			return false;
		for(var k in elems) {
			if(!elems.hasOwnProperty)
				continue;
			if(params != '') params += '&';
			params += 'cid[]=' + k;
		}
		el.href = url + ((url.indexOf('?') >= 0) ? '&' : '?') + params;
		return true;
	},
	toggleField: function(new_value, namekey, field_type, id, prefix, type) {
		var d = document, checked = 0, size = 0, obj = null, specialField = false,
			checkedGood = [], count = [], el = null,
			arr = d.getElementsByName('data['+field_type+']['+namekey+'][]');

		if(!arr)
			return false;

		if( new_value === null) {
			if(d.getElementById(type + namekey))
				new_value = d.getElementById(type + namekey).value;
			else {
				inputs = d.getElementsByName('data['+field_type+']['+namekey+']');
				for(var i = inputs.length - 1; i >= 0; i--) {
					if(inputs[i].checked)
						new_value = inputs[i].value;
				}
			}
		}

		if(!this.fields_data && window.hikashopFieldsJs) {
			this.fields_data = window.hikashopFieldsJs;
		} else {
			for(var n in window.hikashopFieldsJs) {
				if(!window.hikashopFieldsJs.hasOwnProperty(n)) continue;
				if(this.fields_data[n]) continue;
				this.fields_data[n] = window.hikashopFieldsJs[n];
			}
		}
		if(this.fields_data === undefined || this.fields_data[field_type] === undefined) {

		} else {
			size = (arr[0] && arr[0].length !== undefined) ? arr[0].length : arr.length;

			if(prefix === undefined || !prefix || prefix.length == 0 || prefix.substr(-1) != '_')
				prefix = 'hikashop_';

			var elementName = prefix + field_type + '_' + namekey;
			if(id)
				elementName = elementName + '_' + id;
			el = document.getElementById(elementName);
			var parentHidden = (el && el.style.display && el.style.display == 'none');

			for(var c = 0; c < size; c++) {
				if(arr && arr[0] != undefined && arr[0].length != undefined)
					obj = d.getElementsByName('data['+field_type+']['+namekey+'][]').item(0).item(c);
				else
					obj = d.getElementsByName('data['+field_type+']['+namekey+'][]').item(c);

				if(obj.checked || obj.selected)
					checked++;

				if((obj.type && obj.type == 'checkbox') || obj.selected)
					specialField = true;
			}
			var data = this.fields_data[field_type][namekey];
			for(var k in data) {
				if(typeof data[k] != 'object')
					continue;

				for(var l in data[k]) {
					if(typeof data[k][l] != 'string')
						continue;

					if (typeof count[k] == 'undefined') {
						count[k] = 0;
						checkedGood[k] = 0;
					}
					count[k]++;
					newEl = d.getElementById(namekey + '_' + k);
					if(newEl && (newEl.checked || newEl.selected)) {
						checkedGood[k]++;
						break;
					}
				}
			}

			specialField = specialField || (arr[0] && arr[0].length && count.length > 1);

			for(var j in data) {
				if(typeof data[j] != 'object')
					continue;
				for(var i in data[j]) {
					if(typeof data[j][i] != 'string')
						continue;

					var elementName = prefix + field_type + '_' + data[j][i];
					if(id)
						elementName = elementName + '_' + id;
					el = document.getElementById(elementName);
					if(!el)
						continue;

					if( !parentHidden &&
						(
							(specialField && checkedGood[j] == count[j] && new_value != '')
							||
							(!specialField &&
								(
									j == new_value
									||
									(checkedGood[j] && count[j] && checkedGood[j] == count[j])
								)
							)
						)
					) {
						el.style.display = '';
						this.toggleField(el.value, data[j][i], field_type, id, prefix);
					} else {
						el.style.display = 'none';
						this.toggleField('', data[j][i], field_type, id, prefix);
					}
				}
			}
		}
		if(window.Oby && window.Oby.fireAjax)
			window.Oby.fireAjax("hkCustomFieldChanged", {field_type: field_type, namekey: namekey});
		return false;
	}
};
window.hikashop = hikashop;

if(oldHikaShop && oldHikaShop instanceof Object) {
	for (var attr in oldHikaShop) {
		if (oldHikaShop.hasOwnProperty(attr) && !window.hikashop.hasOwnProperty(attr))
			window.hikashop[attr] = oldHikaShop[attr];
	}
}
})();

function tableOrdering(order, dir, task) {
	var form = document.adminForm;
	form.filter_order.value = order;
	form.filter_order_Dir.value	= dir;
	submitform(task);
}

function submitform(pressbutton) {
	var d = document;
	if(!d.adminForm)
		return false;
	if(pressbutton)
		d.adminForm.task.value = pressbutton;
	if(typeof(CodeMirror) == 'function') {
		for(x in CodeMirror.instances) {
			d.getElementById(x).value = CodeMirror.instances[x].getCode();
		}
	}
	if(typeof(d.adminForm.onsubmit) == "function")
		d.adminForm.onsubmit();
	d.adminForm.submit();
	return false;
}

if(!window.submitbutton) {
	window.submitbutton = function(name) { submitform(name); };
}

function hikashopCheckChangeForm(type, form) {
	if(!form)
		return true;
	var varform = document[form];

	if(typeof(hikashopFieldsJs) == 'undefined' || typeof(hikashopFieldsJs['reqFieldsComp']) == 'undefined' || typeof(hikashopFieldsJs['reqFieldsComp'][type]) == 'undefined' || hikashopFieldsJs['reqFieldsComp'][type].length <= 0)
		return true;

	var d = document;
	for(var i = 0; i < hikashopFieldsJs['reqFieldsComp'][type].length; i++) {
		elementName = 'data['+type+']['+hikashopFieldsJs['reqFieldsComp'][type][i]+']';
		if(typeof(varform.elements[elementName]) == 'undefined')
			elementName = type+'_'+hikashopFieldsJs['reqFieldsComp'][type][i];

		elementToCheck = varform.elements[elementName];
		elementId = 'hikashop_'+type+'_'+ hikashopFieldsJs['reqFieldsComp'][type][i];
		el = d.getElementById(elementId);

		if(elementToCheck && (typeof el == 'undefined' || el == null || typeof el.style == 'undefined' || el.style.display!='none') && !hikashopCheckField(elementToCheck,type,i,elementName,varform.elements)) {
			if(typeof(hikashopFieldsJs['entry_id']) == 'undefined')
				return false;

			for(var j = 1; j <= hikashop['entry_id']; j++) {
				elementName = 'data['+type+'][entry_'+j+']['+hikashopFieldsJs['reqFieldsComp'][type][i]+']';
				elementToCheck = varform.elements[elementName];
				elementId = 'hikashop_'+type+'_'+ hikashopFieldsJs['reqFieldsComp'][type][i] + '_' + j;
				el = d.getElementById(elementId);
				if(elementToCheck && (typeof el == 'undefined' || el == null || typeof el.style == 'undefined' || el.style.display != 'none') && !hikashopCheckField(elementToCheck,type,i,elementName,varform.elements)) {
					return false;
				}
			}
		}
	}

	if(type == 'register') {
		// check the password confirmation field only if we are in selector registration and that the user selected "registration" or "simplified registration", or that the registration is on "all in one page" and that the password confirmation field is there
		var register = d.getElementById('data_register_registration_method0');
		if(!register)
			register = d.getElementById('data[register][registration_method]0');

		var simplified_pwd = d.getElementById('data_register_registration_method3');
		if(!simplified_pwd)
			simplified_pwd = d.getElementById('data[register][registration_method]3');

		if((simplified_pwd && simplified_pwd.checked) || (register && register.checked) || (!simplified_pwd && !register)) {
			// check password
			if(typeof(varform.elements['data[register][password]']) != 'undefined' && typeof(varform.elements['data[register][password2]']) != 'undefined') {
				passwd = varform.elements['data[register][password]'];
				passwd2 = varform.elements['data[register][password2]'];
				if(passwd.value != passwd2.value) {
					alert(hikashopFieldsJs['password_different']);
					return false;
				}
			}
		}

		//check email
		var emailField = varform.elements['data[register][email]'];
		emailField.value = emailField.value.replace(/ /g,"");
		var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,14})+$/i;
		if(!emailField || !filter.test(emailField.value)) {
			alert(hikashopFieldsJs['valid_email']);
			return false;
		}
	} else if(type == 'address' && typeof(varform.elements['data[address][address_telephone]']) != 'undefined') {
		var phoneField = varform.elements['data[address][address_telephone]'], filter = /[0-9]+/i;
		if(phoneField) {
			phoneField.value = phoneField.value.replace(/ /g,"");
			if(phoneField.value.length > 0 && !filter.test(phoneField.value)) {
				alert(hikashopFieldsJs['valid_phone']);
				return false;
			}
		}
	}

	return true;
}

function hikashopCheckField(elementToCheck, type, i, elementName, form) {
	if(!elementToCheck)
		return true;

	var d = document, isValid = false;
	if(typeof(elementToCheck.value) != 'undefined') {
		if(elementToCheck.value == ' ' && typeof(form[elementName+'[]']) != 'undefined') {
			if(form[elementName+'[]'].checked) {
				isValid = true;
			} else {
				for(var a = form[elementName+'[]'].length - 1; a >= 0; a--) {
					if(form[elementName+'[]'][a].checked && form[elementName+'[]'][a].value.length > 0)
						isValid = true;
				}
			}
		} else if(elementToCheck.value.length > 0){
			var found = false;
			for(var j in hikashopFieldsJs['regexFieldsComp'][type]) {
				if(hikashopFieldsJs['regexFieldsComp'][type][j] == hikashopFieldsJs['reqFieldsComp'][type][i]) found = j;
			}
			if(typeof(hikashopFieldsJs['regexFieldsComp']) != 'undefined' && typeof(hikashopFieldsJs['regexFieldsComp'][type]) != 'undefined' && found){
				myregexp = new RegExp(hikashopFieldsJs['regexValueFieldsComp'][type][found]);
				if(myregexp.test(elementToCheck.value)){
					isValid = true;
				}
			}else{
				isValid = true;
			}
		}
	} else {
		for(var a = elementToCheck.length - 1; a >= 0; a--) {
			 if(elementToCheck[a].checked && elementToCheck[a].value.length > 0)
			 	isValid = true;
		}
	}

	// Case for the switcher display, ignore check according to the method selected
	// joomla 3 ids are differents than joomla 1.5...
	var simplified_pwd = d.getElementById('data_register_registration_method3');
	if(!simplified_pwd) simplified_pwd = d.getElementById('data[register][registration_method]3');

	var simplified = d.getElementById('data_register_registration_method1');
	if(!simplified) simplified = d.getElementById('data[register][registration_method]1');

	var guest = d.getElementById('data_register_registration_method2');
	if(!guest) guest = d.getElementById('data[register][registration_method]2');

	if(!isValid && ((simplified && simplified.checked) || (guest && guest.checked) ) && (elementName == 'data[register][password]' || elementName == 'data[register][password2]')){
		window.Oby.addClass(elementToCheck, 'invalid');
		return true;
	}

	if(!isValid && ( (simplified && simplified.checked) || (guest && guest.checked) || (simplified_pwd && simplified_pwd.checked) ) && (elementName == 'data[register][name]' || elementName == 'data[register][username]')) {
		window.Oby.addClass(elementToCheck, 'invalid');
		return true;
	}
	if(!isValid) {
		window.Oby.addClass(elementToCheck, 'invalid');
		alert(hikashopFieldsJs['validFieldsComp'][type][i]);
		return false;
	} else {
		window.Oby.removeClass(elementToCheck, 'invalid');
	}
	return true;
}

window.hikashop.ready(function(){
	if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery) {
		window.hkjQuery = jQuery.noConflict();
	}
	if(window.hikaVotes && typeof(initVote) == 'function')
		initVote();
	window.hikashop.checkConsistency();
});
if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery) {
	window.hkjQuery = jQuery.noConflict();
}
