/**
 * @package    HikaShop for Joomla!
 * @version    3.0.1
 * @author     hikashop.com
 * @copyright  (C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {
	function preventDefault() { this.returnValue = false; }
	function stopPropagation() { this.cancelBubble = true; }

	var Oby = {
		version: 20160630,
		ajaxEvents : {},

		hasClass : function(o,n) {
			if(o.className == '' ) return false;
			var reg = new RegExp("(^|\\s+)"+n+"(\\s+|$)");
			return reg.test(o.className);
		},
		addClass : function(o,n) {
			if( !this.hasClass(o,n) ) {
				if( o.className == '' ) {
					o.className = n;
				} else {
					o.className += ' '+n;
				}
			}
		},
		trim : function(s) {
			return (s ? '' + s : '').replace(/^\s*|\s*$/g, '');
		},
		removeClass : function(e, c) {
			var t = this;
			if( t.hasClass(e,c) ) {
				var cn = ' ' + e.className + ' ';
				e.className = t.trim(cn.replace(' '+c+' ',' '));
			}
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
		removeEvent : function(d,e,f) {
			try {
				if( d.detachEvent )
					d.detachEvent('on' + e, f);
				else if( d.removeEventListener)
					d.removeEventListener(e, f, false);
				else
					d['on' + e] = null;
			} catch(e) {}
		},
		cancelEvent : function(e) {
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
		fireEvent : function(obj,e,data) {
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
		fireAjax : function(name,params) {
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
		registerAjax : function(name, fct) {
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
		unregisterAjax : function(name, id) {
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
		evalJSON : function(text, secure) {
			if( typeof(text) != "string" || !text.length) return null;
			if(JSON !== undefined && typeof(JSON.parse) == 'function') {
				try { var ret = JSON.parse(text); return ret; } catch(e) { }
			}
			if(secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(text.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
			try { var ret = eval('(' + text + ')'); return ret; } catch(e) { }
			return null;
		},
		getXHR : function() {
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
		getFormData : function(target) {
			var d = document, ret = '';
			if( typeof(target) == 'string' )
				target = d.getElementById(target);
			if( target === undefined )
				target = d;
			var typelist = ['input','select','textarea'];
			for(var t in typelist ) {
				t = typelist[t];
				var inputs = target.getElementsByTagName(t);
				for(var i = 0; i < inputs.length; i++) {
					if( inputs[i].name && !inputs[i].disabled ) {
						var evalue = inputs[i].value, etype = '';
						if( t == 'input' )
							etype = inputs[i].type.toLowerCase();
						if( (etype == 'radio' || etype == 'checkbox') && !inputs[i].checked )
							evalue = null;
						if(t == 'select' && inputs[i].multiple) {
							for(var k = inputs[i].options.length - 1; k >= 0; k--) {
								if(inputs[i].options[k].selected) {
									if( ret != '' ) ret += '&';
									ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(inputs[i].options[k].value);
									evalue = null;
								}
							}
						}
						if( (etype != 'file' && etype != 'submit') && evalue != null ) {
							if( ret != '' ) ret += '&';
							ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(evalue);
						}
					}
				}
			}
			return ret;
		},
		encodeFormData : function(data) {
			var ret = '', v = null;
			for(var k in data) {
				if(!data.hasOwnProperty(k))
					continue;
				v = data[k];
				if( ret != '' ) ret += '&';
				ret += encodeURI(k) + '=' + encodeURIComponent(v);
			}
			return ret;
		},
		updateElem : function(elem, data) {
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
		ease : function(v) {
			return 1+Math.pow(v-1,3);
		},
		easeInOut : function(t,s,dt,du) {
			return dt/2 * (1 - Math.cos(Math.PI*t/du)) + s;
		},
		scrollTo : function(name, anim, visible, margin) {
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

	var oldHikaShop = window.hikashop || hikashop;

	var hikashop = {
		submitFct: null,
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
			window.Oby.xRequest(elem.getAttribute('href'), {update: target});
			return false;
		},
		form: function(elem, target) {
			var data = window.Oby.getFormData(target);
			window.Oby.xRequest(elem.getAttribute('href'), {update: target, mode: 'POST', data: data});
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
			if(url !== undefined && url !== null)
				elem.href = url;
			settings = window.Oby.evalJSON(elem.getAttribute('data-vex'));
			if(settings.x && settings.y && elem.href) {
				settings.content = '<iframe style="border:0;margin:0;padding:0;" name="hikashop_popup_iframe" width="'+settings.x+'px" height="'+settings.y+'px" src="'+elem.href+'"></iframe>';
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
			window.Oby.xRequest(url, null, function(xhr){
				var w = window;
				w.Oby.updateElem(id + '_container', xhr.responseText);
				var defaultVal = '', defaultValInput = d.getElementById(id + '_default_value'), stateSelect = d.getElementById(id);
				if(defaultValInput) { defaultVal = defaultValInput.value; }
				if(stateSelect && w.hikashop.optionValueIndexOf(stateSelect.options, defaultVal) >= 0)
					stateSelect.value = defaultVal;
				if(typeof(jQuery) != "undefined" && jQuery().chosen) { jQuery('#'+id).chosen(); }
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
				this.setConsistencyHeight(elems, 'min');
				parents[i].setAttribute('data-consistencyheight-done', s);
				parents[i].removeAttribute('data-consistencyheight');
			}
		},
		setConsistencyHeight: function(elems, mode) {
			if(!elems || !elems.length || elems.length == 0)
				return;
			var maxHeight = 0, cpt = 0;
			for(var i = elems.length - 1; i >= 0; i--) {
				if(maxHeight > 0 && elems[i].clientHeight < maxHeight) {
					cpt++;
				} else if(elems[i].clientHeight > maxHeight) {
					maxHeight = elems[i].clientHeight;
					cpt++;
				}
			}
			if(cpt <= 1)
				return;
			for(var i = elems.length - 1; i >= 0; i--) {
				if(mode !== undefined && mode == 'min')
					elems[i].style.minHeight = maxHeight + 'px';
				else
					elems[i].style.height = maxHeight + 'px';
			}
		},
		addToCart: function(el, type) {
			var d = document, t = this, o = window.Oby,
				product_id = 0, container = null, data = null,
				url = el.getAttribute('href'),
				cart_type = ((type !== 'wishlist') ? 'cart' : 'wishlist'),
				containerName = el.getAttribute('data-addTo-div'),
				dest_id = el.getAttribute('data-addTo-cartid');

			product_id = (cart_type == 'cart') ? el.getAttribute('data-addToCart') : el.getAttribute('data-addToWishlist');
			dest_id = (dest_id ? parseInt(dest_id) : 0);

			// No product ID - fallback mode
			if(!product_id || !url) {
				if(containerName && d.forms[containerName]) {
					d.forms[containerName].submit();
					return false;
				}
				return true;
			}

			if(containerName && product_id)
				container = d.forms['hikashop_product_form_' + product_id + '_' + containerName] || d.forms[containerName];

			url += (url.indexOf('?') >= 0 ? '&' : '?') + 'tmpl=ajax';

			if(container) {
				if(window.FormData) {
					data = new FormData(container);
					data.append('cart_type', cart_type);
					if(dest_id)
						data.append('cart_id', dest_id);
				} else {
					data = o.getFormData(container);
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

			o.xRequest(url, {mode:'POST', data: data}, function(xhr) {
				var className = el.getAttribute('data-addTo-class');
				if(className) o.removeClass(el, className);

				var resp = Oby.evalJSON(xhr.responseText);
				var cart_id = (resp && (resp.ret || resp.ret === 0)) ? resp.ret : parseInt(xhr.responseText);
				if(isNaN(cart_id))
					return;

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
			var value = parseInt(el.value),
				min = parseInt(el.getAttribute('data-hk-qty-min')),
				max = parseInt(el.getAttribute('data-hk-qty-max'));
			// No values - return
			if(isNaN(value)) {
				el.value = isNaN(min) ? 1 : min;
				return false;
			}
			if(isNaN(min) || isNaN(max))
				return false;
			if((value <= max || max == 0) && value >= min)
				return true;
			if(max > 0 && value > max) {
				el.value = max;
			} else if(value < min) {
				el.value = min;
			}
			return true;
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

			url += (url.indexOf('?') >= 0 ? '&' : '?') + 'tmpl=ajax';
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
				var cart_id = (resp && resp.ret) ? resp.ret : parseInt(xhr.responseText);
				if(cart_id === NaN)
					return;
				window.Oby.fireAjax(cart_type+'.updated', {id: cart_id, type: cart_type, resp: resp, notify: false});
			});
			return false;
		},
		submitCartModule: function(form, container, cart_type) {
			this.formAjaxSubmit(form, container, function(data) {
				var resp = window.Oby.evalJSON(data);
				var cart_id = (resp && resp.ret) ? resp.ret : parseInt(data);
				if(cart_id === NaN)
					return;
				window.Oby.fireAjax(cart_type+'.updated', {id: cart_id, type: cart_type, resp: resp, notify: false});
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
				data.append('tmpl', 'ajax');
			} else {
				data = o.getFormData(form);
				data += '&tmpl=ajax';
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
				cb(form, container, xhr.responseText);
			});
			return false;
		},
		toggleOverlayBlock: function(el) {
			var t = this, d = document, w = window, o = w.Oby;
			if(typeof(el) == 'string')
				el = d.getElementById(el);
			if(!el)
				return false;
			var open = el.style.display == 'none';
			if(jQuery) {
				jQuery(el).slideToggle('fast');
			} else {
				el.style.display = (el.style.display == 'none')?'block':'none';
			}
			if(!open) {
				if(el.toggleFunction)
					w.Oby.removeEvent(document, "click", el.toggleFunction);
				el.toggleFunction = null;
				return true;
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
				w.Oby.removeEvent(document, "click", f);
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
		toggleField: function(new_value, namekey, field_type, id, prefix) {
			var d = document, checked = 0, size = 0, obj = null, specialField = false,
				checkedGood = 0, count = 0, el = null,
				arr = d.getElementsByName('data['+field_type+']['+namekey+'][]');

			if(!arr)
				return false;

			if(!this.fields_data && window.hikashopFieldsJs) {
				this.fields_data = window.hikashopFieldsJs;
			} else {
				for(var n in window.hikashopFieldsJs) {
					if(!window.hikashopFieldsJs.hasOwnProperty(n)) continue;
					if(this.fields_data[n]) continue;
					this.fields_data[n] = window.hikashopFieldsJs[n];
				}
			}
			if(this.fields_data === undefined || this.fields_data[field_type] === undefined)
				return false;

			size = (arr[0] && arr[0].length !== undefined) ? arr[0].length : arr.length;

			if(prefix === undefined || !prefix || prefix.length == 0 || prefix.substr(-1) != '_')
				prefix = 'hikashop_';

			for(var c = 0; c < size; c++) {
				if(arr && arr[0] != undefined && arr[0].length != undefined)
					obj = d.getElementsByName('data['+field_type+']['+namekey+'][]').item(0).item(c);
				else
					obj = d.getElementsByName('data['+field_type+']['+namekey+'][]').item(c);

				if(obj.checked || obj.selected)
					checked++;

				if(obj.type && obj.type == 'checkbox')
					specialField = true;
			}

			var data = this.fields_data[field_type][namekey];
			for(var k in data) {
				if(typeof data[k] != 'object')
					continue;

				for(var l in data[k]) {
					if(typeof data[k][l] != 'string')
						continue;

					count++;
					newEl = d.getElementById(namekey + '_' + k);
					if(newEl && (newEl.checked || newEl.selected))
						checkedGood++;
				}
			}

			specialField = specialField || (arr[0] && arr[0].length && count > 1);

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
					if( (specialField && checkedGood == count && checkedGood == checked && new_value != '') || (!specialField && j == new_value) ) {
						el.style.display = '';
						this.toggleField(el.value, data[j][i], field_type, id, prefix);
					} else {
						el.style.display = 'none';
						this.toggleField('', data[j][i], field_type, id, prefix);
					}
				}
			}
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
	window.submitbutton = function(name) {
		submitform(name);
	};
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
	if(window.hikaVotes && typeof(initVote) == 'function')
		initVote();
	window.hikashop.checkConsistency();
});
if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery) {
	window.hkjQuery = jQuery.noConflict();
}
