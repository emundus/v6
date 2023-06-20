/**
 * @package    HikaShop for Joomla!
 * @version    4.7.3
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function(){
//
//
if(!window.checkoutBlocks)
	window.checkoutBlocks = {};
//
//
if(!window.checkoutWorkflows)
	window.checkoutWorkflows = {};
/**
 *
 */
window.checkoutWorkflowEditor = {
	/**
	 *
	 */
	init:function(id) {
		var el = document.getElementById(id);
		if(!el)
			return false;
		window.checkoutWorkflows[id] = JSON.parse(el.value);
		this.setShowOn(id + '_container');
		this.setPlaceholder(id + '_container');

		// properly init the last step
		var steps = document.querySelectorAll('[data-checkout="container"][data-checkout-id="'+id+'"] [data-checkout="step"]');
		if(!steps)
			return false;
		var lastStep = steps[ steps.length - 1];
		lastStep.querySelector('[class="checkout_content_step_delete"]').style.display = 'none';
		var input = lastStep.querySelector('[data-checkout-step-name]');
		input.setAttribute('placeholder', input.getAttribute('data-placeholder'));
		return true;
	},
	/**
	 *
	 */
	getId:function(el) {
		var id = el.getAttribute("data-checkout-id");
		if(id) return id;
		var p = el;
		while(p) {
			if(p.hasAttribute("data-checkout-id"))
				return p.getAttribute("data-checkout-id");
			p = p.parentNode;
		}
		return false;
	},
	/**
	 *
	 */
	save:function(id, refresh) {
		if(typeof(id) !== "string")
			id = this.getId(id);
		if(!id) return false;
		var d = document, w = window,
			el = document.getElementById(id);
		if(!el || !w.checkoutWorkflows[id]) return false;
		if(refresh) this.serialize(id);
		el.value = JSON.stringify(w.checkoutWorkflows[id]);
		return true;
	},
	/**
	 *
	 */
	serialize:function(id) {
		if(typeof(id) !== "string")
			id = this.getId(id);
		if(!id) return false;
		var d = document, w = window,
			el = document.getElementById(id);
		if(!el) return false;
		if(!w.checkoutWorkflows[id])
			w.checkoutWorkflows[id] = {};

		var wfs = [],
			steps = d.querySelectorAll('[data-checkout="container"][data-checkout-id="'+id+'"] [data-checkout="step"]');
		for(var i = 0; i < steps.length; i++) {
			var s = {},
				step = steps[i],
				input = step.querySelector("[data-checkout-step-name]");

			s.name = input ? input.value : "";

			// Special behavior for the last step
			if(i == steps.length - 1) {
				s.content = [{"task":"end"}];
				wfs.push( s );
				continue;
			}
			s.content = this.getStepContent(id, step);
			if(i == steps.length - 2)
				s.content.push = {"task":"confirm"};
			if(s.content.length == 0)
				continue;
			wfs.push( s );
		}
		w.checkoutWorkflows[id].steps = wfs;
		return true;
	},
	/**
	 *
	 */
	onChange:function(el) {
		if(typeof(el) == "string")
			el = document.getElementById(el);
		var p = el, id = false;
		while(p) {
			if(p.hasAttribute("data-checkout-id")) {
				id = p.getAttribute("data-checkout-id");
				p = false;
			} else
				p = p.parentNode;
		}

		this.checkShowOn(el);

		if(id)
			this.save(id, true);
		return true;
	},
	/**
	 *
	 */
	setPlaceholder:function(el) {
		if(typeof(el) == "string")
			el = document.getElementById(el);
		var steps = el.querySelectorAll('[data-checkout="step"]');
		if(!steps.length)
			return;
		for(var i = steps.length - 1; i >= 0; i--) {
			this.checkPlaceholder(steps[i]);
		}
	},
	/**
	 *
	 */
	checkPlaceholder:function(el) {
		if(typeof(el) == "string")
			el = document.getElementById(el);
		var step = null;
		if(el.getAttribute('data-checkout') == '')
			step = el;
		else
			step = this.getContainerBlock(el, "step");
		var input = step.querySelector('[data-checkout-step-name]');
		var block = step.querySelector('[data-checkout="content"]');
		if(!block){
			input.setAttribute('placeholder','');
			return;
		}
		var title = block.querySelector('[class="checkout_content_block_title"]');
		if(title.innerHTML)
			input.setAttribute('placeholder',title.innerHTML);
	},
	/**
	 *
	 */
	setShowOn:function(el) {
		if(typeof(el) == "string")
			el = document.getElementById(el);
		var params = el.querySelectorAll("[data-checkout-param]");
		for(var i = params.length - 1; i >= 0; i--) {
			this.checkShowOn(params[i]);
		}
	},
	/**
	 *
	 */
	checkShowOn:function(el) {
		if(typeof(el) == "string")
			el = document.getElementById(el);
		var container = this.getContainerBlock(el),
			val = this.getValue(el);
		if(!container || !val || !val.param)
			return;
		var els = container.querySelectorAll('[data-showon-key="' + val.param + '"]');
		for(var i = els.length - 1; i >= 0; i--) {
			values = els[i].getAttribute('data-showon-values').split(',');
			// TODO : Support val as array
			if(values.indexOf(val.value) > -1)
				els[i].style.display = '';
			else
				els[i].style.display = 'none';
		}
		var step = this.getContainerBlock(el, "step");
		this.resetConsistency( step );
	},
	/**
	 *
	 */
	getStepContent: function(id, el) {
		var ret = [],
			blocks = el.querySelectorAll('[data-checkout="content"]');
		for(var i = 0; i < blocks.length; i++) {
			var b = {
				"task": blocks[i].getAttribute("data-checkout-content")
			};
			var params = blocks[i].querySelectorAll("[data-checkout-param]");
			if(params.length == 0) {
				ret.push(b);
				continue;
			}
			b.params = {};
			for(var j = 0; j < params.length; j++) {
				var v = this.getValue(params[j]);
				if( v === null)
					continue;
				if(b.params.hasOwnProperty(v.param)) {
					if(typeof(b.params[v.param]) == "object")
						b.params[v.param] = [ b.params[v.param] ];
					b.params[v.param].push(v.value);
				} else
					b.params[v.param] = v.value;
			}
			ret.push(b);
		}
		return ret;
	},
	/**
	 * @param el - the source node that we want to get the value of.
	 */
	getValue:function(el) {
		var param = el.getAttribute("data-checkout-param"),
			nodeType = el.nodeName.toLowerCase(),
			checkoutType = el.getAttribute("data-checkout-type"),
			val = null;
		if((nodeType == "input" && ["text","hidden"].indexOf(el.type) >= 0) || nodeType == "textarea")
			return {"param": param, "value": el.value};
		if(nodeType == "input" && ["radio","checkbox"].indexOf(el.type) >= 0) {
			if(!el.checked) return null;
			return {"param": param, "value": el.value};
		}
		if(nodeType == "select") {
			if(!el.multiple)
				return {"param": param, "value": el.value};
			// Get all selected options of the select
			val = [];
			for(var i = 0; i < el.options.length; i++) { if(el.options[i].selected) val.push(el.options[i].value); }
			return {"param": param, "value": val};
		}
		if(checkoutType == "namebox") {
			if(!window.oNameboxes[el.id])
				return null;
			var nv = window.oNameboxes[el.id].get();
			if(nv.value)
				return {"param": param, "value": nv.value};
			// Translate the namebox value object into a simplier array
			val = [];
			for(var i = 0; i < nv.length; i++) { val.push(nv[i].value); }
			return {"param": param, "value": val};
		}
		return null;
	},
	/**
	 * @param el - the source element in the workflow. We will determine the "id" thanks to it.
	 * @param block [optional] a content block that we want to move to the new created step.
	 */
	addStep:function(el, block) {
		if(!window.checkoutWorflowUrls || !window.checkoutWorflowUrls.addstep)
			return false;
		var t = this,
			id = this.getId(el),
			steps = document.querySelectorAll('[data-checkout="container"][data-checkout-id="'+id+'"] [data-checkout="step"]');
		if(!steps)
			return false;
		var lastStep = steps[ steps.length - 1],
			num = parseInt(lastStep.getAttribute('data-checkout-step'));
		if(isNaN(num))
			return false;
		window.Oby.xRequest(window.checkoutWorflowUrls.addstep, {mode:'POST', data:'num='+num}, function(xhr){
			var div = document.createElement('div');
			scripts = t.updateElem(div, window.Oby.trim(xhr.responseText));
			lastStep.parentNode.insertBefore(div.childNodes[0], lastStep);
			lastStep.setAttribute('data-checkout-step', num + 1);
			var cpt = lastStep.querySelector('[data-checkout="num"]');
			if(cpt)
				cpt.innerHTML = (num + 2);
			if(scripts)
				t.processScript(scripts);
			if(block)
				t.blockNext(block);
		});
		return false;
	},
	stepDelete:function(el) {
		if(!el) return false;
		var step = this.getContainerBlock(el, "step");
		if(!step) return false;
		var id = this.getId(el);
		step.parentNode.removeChild(step);

		steps = document.querySelectorAll('[data-checkout="container"][data-checkout-id="'+id+'"] [data-checkout="step"]');
		for(var i = 0; i < steps.length; i++) {
			steps[i].setAttribute('data-checkout-step', i);
			var cpt = steps[i].querySelector('[data-checkout="num"]');
			if(cpt)
				cpt.innerHTML = (i + 1);
		}
		//
		this.save(id, true);
		return false;
	},
	/**
	 *
	 */
	addBlock:function(el) {
		if(!window.checkoutWorflowUrls || !window.checkoutWorflowUrls.addblock)
			return false;
		el = this.getContainerBlock(el, "add");
		if(!el)
			return false;
		var t = this,
			list = el.querySelector('[data-checkout="addlist"]');
		if(!list)
			return false;

		window.Oby.xRequest(window.checkoutWorflowUrls.addblock, {mode:'POST', data:'name='+list.value}, function(xhr){
			var div = document.createElement('div');
			scripts = window.hikashop.updateElem(div, window.Oby.trim(xhr.responseText));
			el.parentNode.insertBefore(div.childNodes[0], el);
			t.resetConsistency( el.parentNode );
			t.setShowOn(el);

			if(scripts)
				t.processScript(scripts);
			t.save(el, true);
			t.checkPlaceholder(el);
		});

		return false;
	},
	/**
	 * Get Parent Container Block.
	 * @param el - the source node which is in the block we want to find.
	 * @param type [optional] indicate the type of block we want to retrieve, "content" by default.
	 */
	getContainerBlock:function(el, type) {
		var p = el, type = type || "content";
		while(p) {
			if(p && p.getAttribute && p.getAttribute("data-checkout") == type)
				return p;
			p = p.parentNode;
		}
		return false;
	},
	/**
	 *
	 */
	resetConsistency:function(el) {
		el.setAttribute('data-consistencyheight', '.checkout_content_block');
		el.removeAttribute('data-consistencyheight-done');
		var blocks = el.querySelectorAll('.checkout_content_block');
		for(var i = blocks.length - 1; i >= 0; i--) {
			if(blocks[i].style.minHeight)
				blocks[i].style.minHeight = null;
		}
		window.hikashop.checkConsistency();
	},
	/**
	 *
	 */
	updateElem:function(elem, data) {
		var d = document, scripts = '';
		if( typeof(elem) == 'string' )
			elem = d.getElementById(elem);
		var text = data.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(all, code){
			scripts += code + '\n';
			return '';
		});
		elem.innerHTML = text;
		return scripts;
	},
	processScript:function(data) {
		if(!data || data == '')
			return;
		var d = document, script = d.createElement('script');
		script.setAttribute('type', 'text/javascript');
		script.text = data;
		d.head.appendChild(script);
		d.head.removeChild(script);
	},
	/**
	 *
	 */
	blockUp:function(el) {
		el = this.getContainerBlock(el);
		if(!el) return false;
		var prev = el.previousElementSibling;
		if(!prev)
			return false;
		el.parentNode.insertBefore(el, prev);
		this.save(el, true);
		this.checkPlaceholder(el);
		return false;
	},
	blockDown:function(el) {
		el = this.getContainerBlock(el);
		if(!el) return false;
		var next = el.nextElementSibling;
		if(!next || next.getAttribute('data-checkout') == 'add')
			return false;
		el.parentNode.insertBefore(next, el);
		this.save(el, true);
		this.checkPlaceholder(el);
		return false;
	},
	/**
	 *
	 */
	blockPrevious:function(el) {
		el = this.getContainerBlock(el);
		if(!el) return false;
		var step = this.getContainerBlock(el, "step");
		if(!step || !step.previousElementSibling)
			return false;
		var previousStep = step.previousElementSibling,
			addBlock = previousStep.querySelector('[data-checkout="add"]');
		if(!addBlock)
			return false;
		addBlock.parentNode.insertBefore(el, addBlock);
		//
		this.save(el, true);
		//
		this.resetConsistency(step);
		this.checkPlaceholder(step);
		this.resetConsistency(previousStep);
		this.checkPlaceholder(previousStep);
		return false;
	},
	blockNext:function(el) {
		el = this.getContainerBlock(el);
		if(!el) return false;
		var step = this.getContainerBlock(el, "step");
		if(!step || !step.nextElementSibling)
			return false;
		var nextStep = step.nextElementSibling;
			addBlock = nextStep.querySelector('[data-checkout="add"]');
		if(!addBlock)
			return this.addStep(step, el);
		addBlock.parentNode.insertBefore(el, addBlock);
		//
		this.save(el, true);
		//
		this.resetConsistency(step);
		this.checkPlaceholder(step);
		this.resetConsistency(nextStep);
		this.checkPlaceholder(nextStep);
		return false;
	},
	/**
	 *
	 */
	blockDelete:function(el) {
		el = this.getContainerBlock(el);
		if(!el) return false;
		var id = this.getId(el),
			step = this.getContainerBlock(el, "step");
		el.parentNode.removeChild(el);
		//
		this.save(id, true);
		//
		this.resetConsistency(step);
		this.checkPlaceholder(step);
		return false;
	}
};

})();
