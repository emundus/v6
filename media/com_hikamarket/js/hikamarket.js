/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {

function preventDefault() { this.returnValue = false; }
function stopPropagation() { this.cancelBubble = true; }

var Oby = {
	version: 20170309,
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
	getFormData: function(target) {
		var d = document, ret = '';
		if( typeof(target) == 'string' )
			target = d.getElementById(target);
		if( target === undefined )
			target = d;
		var typelist = ['input','select','textarea'];
		for(var t in typelist) {
			t = typelist[t];
			var inputs = target.getElementsByTagName(t);
			for(var i = 0; i < inputs.length; i++) {
				if( !inputs[i].name || inputs[i].disabled )
					continue;
				var evalue = inputs[i].value, etype = '';
				if( t == 'input' )
					etype = inputs[i].type.toLowerCase();
				if( (etype == 'radio' || etype == 'checkbox') && !inputs[i].checked )
					evalue = null;
				if(t == 'select' && inputs[i].multiple) {
					for(var k = inputs[i].options.length - 1; k >= 0; k--) {
						if(!inputs[i].options[k].selected)
							continue;
						if( ret != '' ) ret += '&';
						ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(inputs[i].options[k].value);
						evalue = null;
					}
				}
				if( (etype != 'file' && etype != 'submit') && evalue != null ) {
					if( ret != '' ) ret += '&';
					ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(evalue);
				}
			}
		}
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
			if( ret != '' ) ret += '&';
			ret += encodeURI(k) + '=' + encodeURIComponent(v);
		}
		return ret;
	},
	updateElem: function(elem, data) {
		var d = document, scripts = '';
		if( typeof(elem) == 'string' )
			elem = d.getElementById(elem);
		var text = data.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(all, code){
			scripts += code + '\n';
			return '';
		});
		elem.innerHTML = text;
		if( scripts != '' ) {
			var script = d.createElement('script');
			script.setAttribute('type', 'text/javascript');
			script.text = scripts;
			d.head.appendChild(script);
			d.head.removeChild(script);
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
		t.anim = { timer:null, s:null, dt:0, du:500, t:0, inc:10 };
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

var hikamarket = {
	submitFct: null,
	submitBox: function(data) {
		var t = this;
		if( t.submitFct ) {
			try {
				t.submitFct(data);
			} catch(err) {}
		}
		t.closeBox();
	},
	deleteId: function(id) { return window.hikashop.deleteId(id); },
	dup: function(tplName, htmlblocks, id, extraData, appendTo) { return window.hikashop.dup(tplName, htmlblocks, id, extraData, appendTo); },
	deleteRow: function(id) { return window.hikashop.deleteRow(id); },
	dupRow: function(tplName, htmlblocks, id, extraData) { return window.hikashop.dupRow(tplName, htmlblocks, id, extraData); },
	cleanTableRows: function(id) { return window.hikashop.cleanTableRows(id); },
	checkRow: function(id) { return window.hikashop.checkRow(id); },
	isChecked: function(id,cancel) { return window.hikashop.isChecked(id,cancel); },
	checkAll: function(checkbox, stub) {
		stub = stub || 'cb';
		if(!checkbox.form)
			return false;
		var o = window.Oby, cb = checkbox.form, c = 0;
		for(var i = 0, n = cb.elements.length; i < n; i++) {
			var e = cb.elements[i];
			if(e != checkbox && e.type == checkbox.type && ((stub && e.id.indexOf(stub) == 0) || !stub)) {
				e.checked = checkbox.checked;
				o.fireEvent(e, 'change');
				c += (e.checked == true ? 1 : 0);
			}
		}
		if(cb.boxchecked)
			cb.boxchecked.value = c;
		return true;
	},
	submitform: function(task, form, extra) { return window.hikashop.submitform(task, form, extra); },
	get: function(elem, target) { return window.hikashop.get(elem, target); },
	form: function(elem, target) { return window.hikashop.form(elem, target); },
	openBox: function(elem, url, jqmodal) { return window.hikashop.openBox(elem, url, jqmodal); },
	closeBox: function(parent) { return window.hikashop.closeBox(parent); },
	tabSelect: function(m,c,id) { return window.hikashop.tabSelect(mc,id); },
	getOffset: function(el) { return window.hikashop.getOffset(el); },
	switchBlock: function(el, values, name) {
		var dest = document.getElementById(name);
		if(!dest) return;
		if(typeof(values) == 'number') values = [values];
		for(var i = values.length - 1; i >= 0; i--) {
			if(values[i] == el.value) { dest.style.display = ''; return; }
		}
		dest.style.display = 'none';
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
	dlTitle: function(parent) {
		var t = this, d = document;
		if(parent && typeof(parent) == 'string')
			parent = d.getElementById(parent);
		if(!parent)
			parent = d;
		var dt = parent.getElementsByTagName('dt'), val = null,
			hkTip = (typeof(hkjQuery) != "undefined" && hkjQuery().hktooltip);
		for(var i = 0; i < dt.length; i++) {
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
	collapseTitles: function() {
		var t = this, d = document, w = window, o = w.Oby,
			collapsedBlocks = t.dataGet('hkm_section_toggle');
		collapsedBlocks = collapsedBlocks ? o.evalJSON(collapsedBlocks) : [];
		if(!collapsedBlocks) collapsedBlocks = [];

		d.querySelectorAll('[data-section-toggle]').forEach(function(el){
			if(el.collapseTitleInit) return;
			o.addEvent(el, 'click', t.collapseTitle);

			var section = t._collapseGetSection(el);
			if(section) {
				if(collapsedBlocks.indexOf(section.name) >= 0) {
					el.collapsed = true;
					o.addClass(el, 'hk_closed');
				} else
					o.addClass(section.elem, 'open');
				o.addClass(section.elem, 'hk_collapsing');
			}
			el.collapseTitleInit = true;
		});
	},
	_collapseGetSection: function(el) {
		if(!el) return false;
		var d = document,
			sectionName = el.getAttribute('data-section-toggle');
		if(!sectionName) return false;

		var section = d.getElementById('hikamarket_section_' + sectionName);
		if(!section) section = d.getElementById(sectionName);
		return {name: sectionName, elem: section};
	},
	collapseTitle: function(evt) {
		if(!evt) {
			evt = window.event;
			if(!evt) return false;
		}
		var w = window, t = w.hikamarket, o = w.Oby,
			el = evt.target;
		if(!el) return false;

		var section = t._collapseGetSection(el);
		if(!section) return false;

		if(el.collapsed) {
			o.addClass(section.elem, 'open');
			o.removeClass(el, 'hk_closed');
		} else {
			o.removeClass(section.elem, 'open');
			o.addClass(el, 'hk_closed');
		}

		var collapsedBlocks = t.dataGet('hkm_section_toggle');
		collapsedBlocks = collapsedBlocks ? o.evalJSON(collapsedBlocks) : [];
		if(!collapsedBlocks) collapsedBlocks = [];

		var p = collapsedBlocks.indexOf(section.name);
		if(p >= 0 && el.collapsed) {
			collapsedBlocks.splice(p, 1);
		}
		if(p === -1 && !el.collapsed) {
			collapsedBlocks.push(section.name);
		}
		t.dataStore('hkm_section_toggle', JSON.stringify(collapsedBlocks));

		el.collapsed = !el.collapsed;
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
			if(!lr || lr.substring(0,4) != 'tab:') continue;
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
	callbackFct: function(t,url,keyword,tree,node,ev) {
		return treeCallbackFct(t,url,keyword,tree,node,ev);
	},
	searchSubmit:function(el) {
		if(el.form.limitstart)
			el.form.limitstart.value=0;
		el.form.submit();
	},
	searchReset:function(el) {
		if(el.form.limitstart) el.form.limitstart.value=0;
		if(el.form.search) el.form.search.value='';
		var els = el.form.querySelectorAll("[data-search-reset]");
		if(els) { for(var i = els.length - 1; i >= 0; i--) {
			els[i].value = els[i].getAttribute('data-search-reset');
		} }
		el.form.submit();
	},
	searchFilters:function(el, id) {
		var block = document.getElementById(id);
		if(!block) return false;
		block.style.display = (block.style.display == "none") ? "" : "none";
		return false;
	}
};
window.hikamarket = hikamarket;

})();
