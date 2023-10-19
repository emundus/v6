/**
 * @package    HikaShop for Joomla!
 * @version    4.7.4
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {

var oldCheckout = window.checkout || null;

var hikashopCheckout = {
	token: "",
	urls: {
		show: "",
		submit: "",
		submitstep: "",
	},
	urlParams: {
		type: 'HIKATYPE',
		cid: 'HIKACID',
		pos: 'HIKAPOS',
		token: 'HIKATOKEN',
	},
	loading: false,

	refreshBlock: function(type, step, id) {
		if((id === null || id === undefined) && typeof(step) == 'object') {
			info = this.getBlockPos(step);
			if(!info) return false;
			step = info.step;
			id = info.pos;
		}
		var type_clean = type.replace(/\./g,'-'),
			el_name = "hikashop_checkout_" + type_clean + "_" + step + "_" + id,
			t = this, d = document, w = window, o = w.Oby,
			el = d.getElementById(el_name);
		if(!el || !window.checkout.urls.show)
			return false;
		t.setLoading(el, true);

		var url = window.checkout.urls.show,
			params = {};
		url = t.handleParams({'type': type, 'cid': step, 'pos': id }, url, params);

		o.xRequest(url, params, function(x,p) {
			el = d.getElementById("hikashop_checkout_" + type_clean + "_" + step + "_" + id);
			t.setLoading(el, false);
			window.hikashop.updateElem(el, x.responseText);
			t.handleEnter(type_clean, step, id);
			t.checkScroll();
			o.fireAjax('checkoutBlockRefresh', {'type': type_clean, 'cid': step, 'pos': id});
		});
		return false;
	},
	submitStep: function(el) {
		if(window.checkout.onFormSubmit && !window.checkout.onFormSubmit(el.form, el))
			return false;
		var mainDiv = document.getElementById('hikashop_checkout');
		if(mainDiv)
			this.setLoading(mainDiv, true);
		el.form.submit();
		return false;
	},
	submitBlock: function(type, step, id, data) {
		if((id === null || id === undefined) && typeof(step) == 'object') {
			info = this.getBlockPos(step);
			if(!info) return false;
			step = info.step;
			id = info.pos;
		}
		var type_clean = type.replace(/\./g,'-'), el_name = "hikashop_checkout_" + type_clean + "_" + step + "_" + id, url = null, formData = null,
			t = this, d = document, w = window, o = w.Oby,
			el = d.getElementById(el_name);

		if(!el)
			return false;

		if(!window.checkout.urls.submit || !window.checkout.token) {
			var f = d.getElementById('hikashop_checkout_form');
			if(!f) return false;
			f.submit();
			return false;
		}

		var triggers = o.fireAjax('checkoutBlockSubmit', {'type': type, 'cid': step, 'pos': id, 'element': el, 'data': data});
		if(triggers !== false && triggers.length > 0)
			return true;

		if(data === undefined || !data) {
			formData = o.getFormData(el);
		} else if(typeof(data) == "string") {
			formData = data;
		} else {
			formData = "";
			for(var k in data) {
				if( formData != "" ) formData += "&";
				formData += encodeURI(k) + "=" + encodeURIComponent(data[k]);
			}
		}

		t.setLoading(el, true, true);

		var url = window.checkout.urls.submit,
			params = {mode:"POST", data: formData};
		url = t.handleParams({'type': type, 'cid': step, 'pos': id, 'token': 1 }, url, params);

		o.xRequest(url, params, function(x,p) {
			if(x.responseText == '401')
				window.location.reload(true);
			if(x.status == 303 || x.status == 301) {
				console.log('[HikaShop Checkout Error] Something on the server side requested a redirect to "' + x.getResponseHeader('Location') + '". It\'s probably a third party plugin which shouldn\'t do that. The page was reload to avoid any issue.');
				window.location.reload(true);
			}
			el = d.getElementById("hikashop_checkout_" + type_clean + "_" + step + "_" + id);
			t.setLoading(el, false);
			window.hikashop.updateElem(el, x.responseText);
			t.handleEnter(type_clean, step, id);
			t.checkScroll();
			o.fireAjax('checkoutBlockRefresh', {'type': type_clean, 'cid': step, 'pos': id});
		});
		return false;
	},
	getBlockPos: function(el) {
		var ret = false;
		while(el && ret === false) {
			var pos = el.getAttribute("data-checkout-pos"), step = el.getAttribute("data-checkout-step");
			if(pos && step)
				ret = {'pos':pos,'step':step};
			el = el.parentNode;
			if(el && el.id && el.id == "hikashop_checkout_form")
				el = null;
		}
		return ret;
	},
	setLoading: function(el, load) {
		var w = window, o = w.Oby, t = this, d = document, btn = d.getElementById('hikabtn_checkout_next');
		if(el) {
			if(load)
				o.addClass(el, "hikashop_checkout_loading");
			else
				o.removeClass(el, "hikashop_checkout_loading");
		}
		if(load)
			t.loading++;
		else if(t.loading > 0)
			t.loading--;
		// we block the next button while blocks are being submitted to avoid wrong actions to be validated while finishing the checkout
		if(btn) {
			if(t.loading) {
				btn.disabled = true;
				o.addClass(btn, 'next_button_disabled');
			} else  {
				btn.disabled = false;
				o.removeClass(btn, 'next_button_disabled');
			}
		}
	},
	handleParams: function(data, url, req) {
		var t = this, fields = {type: 'blocktask', cid: 'cid', pos: 'blockpos', token: window.checkout.token};
		for(var f in fields) {
			if(!fields.hasOwnProperty(f) || !data[f]) continue;
			url = t.handleParam(fields[f], t.urlParams[f], data[f], url, req);
		}
		return url;
	},
	handleParam: function(key, param, data, url, req) {
		var t = this;
		if(param && param != "" && url.indexOf(param) >= 0) {
			url = url.replace(param, data);
			return url;
		}
		if(!req.data) req.data = "";
		if(req.data != "") req.data += "&";
		req.data += encodeURI(key) + "=" + encodeURIComponent(data);
		req.mode = "POST";
		return url;
	},
	onFormSubmit: function(el, btn) {
		if(el === null)
			el = document.getElementById('hikashop_checkout_form');
		if(el.submit_in_progress)
			return false;
		el.submit_in_progress = true;
		var triggers = window.Oby.fireAjax('checkoutFormSubmit', {'element': el, 'button': btn});
		if(triggers !== false && triggers.length > 0) {
			el.submit_in_progress = false;
			return false;
		}
		return true;
	},
	isSource: function(params, step, pos) {
		return (params && params.src && typeof(params.src.step) != "undefined" && params.src.step == step && typeof(params.src.pos) != "undefined" && params.src.pos == pos);
	},
	processEvents: function(evts, src) {
		for(var i = 0; i < evts.length; i++) {
			var evt = evts[i], params = null;
			if(evt && typeof(evt) != "string" && evt[0]) {
				params = evt[1];
				evt = evt[0];
			}
			if(src && (!params || !params.src)) {
				if(!params) params = {};
				params.src = src;
			}
			window.Oby.fireAjax(evt, params);
		}
	},
	handleEnter: function(task, step, pos) {
		var t = this, d = document;

		block = d.getElementById('hikashop_checkout_' + task + '_' + step + '_' + pos);
		if(!block)
			return true;

		els = block.querySelectorAll('input[type=text], input[type=checkbox], input[type=password]');
		if(!els.length)
			return true;

		for(var idx = 0 ; idx < els.length ; idx++) {
			if(els[idx].parentElement.className == 'chzn-search' || els[idx].className == 'hk-no-submit')
				continue;
			els[idx].addEventListener('keydown', function(e) {
				if(e.key === undefined && e.keyCode === undefined && e.which === undefined)
					return;
				if((e.key !== undefined && e.key != "Enter") || (e.keyCode !== undefined && e.keyCode != 13) || (e.which !== undefined && e.which != 13))
					return;
				e.preventDefault();
				t.submitBlock(task, step, pos);
			});
		}
	},
	checkScroll: function (fullReload) {
		var els = document.getElementsByClassName("hikashop_error");
		var height =  (window.innerHeight || document.documentElement.clientHeight);
		for (var i = 0; i < els.length; i++) {
			var bounding = els[i].getBoundingClientRect();
			// for submitblock
			if(!fullReload && bounding.top < 0)
				els[i].scrollIntoView('{block: "start"}');
			// for submitstep
			if(fullReload && bounding.bottom > height) {
				els[i].scrollIntoView('{block: "start"}');
				break;
			}
		}
	}
};

window.checkout = hikashopCheckout;

if(oldCheckout && oldCheckout instanceof Object) {
	for(var attr in oldCheckout) {
		if(oldCheckout.hasOwnProperty(attr) && !window.checkout.hasOwnProperty(attr))
			window.checkout[attr] = oldCheckout[attr];
	}
}

window.hikashop.ready(function(){
	window.checkout.checkScroll(true);
});

})();
