!function(){
'use strict';

if(window.stripeConnect)
	return;

var stripeConnect = {
	eventRegister: null,
	data: {},
	formSubmit: false,

	init: function(id, opt) {
		var instance = {
			id: id,
			s: Stripe(opt.authData.pub),
			mode: (opt.mode || 'token'),
			additional: opt.additional
		};
		instance.elements = instance.s.elements();

		if(opt.notify)
			instance.notify = opt.notify;
		if(!opt.style)
			opt.style = stripeConnect.getDefaultStyle();
		instance.card = instance.elements.create("card", {style: opt.style});
		instance.card.mount("#hk_co_p_c_STRIPEC_elements_" + id);
		instance.card.addEventListener("change", function(event) {
			var displayError = document.getElementById("hk_co_p_c_STRIPEC_errors_" + id);
			if (event.error) {
				displayError.textContent = event.error.message;
			} else {
				displayError.textContent = "";
			}
		});

		stripeConnect.registerEvent();

		stripeConnect.data[id] = instance;
		return instance;
	},
	registerEvent: function() {
		if(stripeConnect.eventRegister) return;
		var d = document, w = window, o = w.Oby, t = stripeConnect;
		o.registerAjax("custompayment.needsubmit", function(params){
			if(!t.data[params.payment_id]) return;
			if(t.data[params.payment_id].hasData) return;
			t.formSubmit = true;
			return 'stripeconnect';
		});
		o.registerAjax("custompayment.submit", function(params){
			if(!params || params.method != "stripeconnect") return;
			if(!t.data[params.payment_id]) return;
			var instance = t.data[params.payment_id],
				step = params.step, pos = params.pos, payment_id = params.payment_id,
				container = d.getElementById("hk_co_p_c_STRIPEC_container_" + payment_id),
				el = d.getElementById("hikashop_checkout_payment_"+step+"_"+pos);;
			if(!container || !instance.s)
				return;
			w.checkout.setLoading(el, true);
			if(instance.mode == 'token')
				t._handleToken(instance, el, step, pos);
			if(instance.mode == 'source')
				t._handleCardSource(instance, el, step, pos);
			if(instance.mode == 'method')
				t._handlePaymentMethod(instance, el, step, pos);
			return true;
		});
		stripeConnect.eventRegister = true;
	},
	registerFormEvent: function(id) {
		var t = this, d = document,
			form = d.getElementById("stripe-payment-form");
		form.addEventListener("submit", function(event) {
			event.preventDefault();
			if(form.tokenCreation) return;
			form.tokenCreation = true;
			var container = d.getElementById('stripe-payment-container');
			if(container) container.className = "hikashop_checkout_loading";
			var instance = t.data[id];
			instance.form = form;
			t._handlePaymentMethod(instance, null, null, null);
		});
	},
	_handleToken: function(instance, el, step, pos) {
		var d = document, w = window, o = w.Oby, t = stripeConnect;
		instance.s.createToken(instance.card, instance.additional).then(function(result) {
			if (result.error) {
				// Inform the user if there was an error
				var errorElement = d.getElementById("hk_co_p_c_STRIPEC_errors_" + instance.id);
				errorElement.textContent = result.error.message;
				if(instance.form)
					instance.form.tokenCreation = false;
				var container = d.getElementById('stripe-payment-container');
				if(container) container.className = "";
			} else {
				// Push the token
				var input = d.getElementById("hk_co_p_c_STRIPEC_TOK_" + instance.id);
				input.value = result.token.id;
				if(result.token && result.token.card && result.token.card.last4) {
					input = d.getElementById("hk_co_p_c_STRIPEC_L4_" + instance.id);
					if(input)
						input.value = result.token.card.last4;
				}
				// Submit the form
				w.checkout.submitPayment(step, pos);
			}
			w.checkout.setLoading(el, false);
		});
	},
	_handleCardSource: function(instance, el, step, pos) {
		var d = document, w = window, o = w.Oby, t = stripeConnect;
		instance.s.createSource(instance.card, instance.additional).then(function(result) {
			if (result.error) {
				// Inform the user if there was an error
				var errorElement = d.getElementById("hk_co_p_c_STRIPEC_errors_" + instance.id);
				errorElement.textContent = result.error.message;
				if(instance.form)
					instance.form.tokenCreation = false;
				var container = d.getElementById('stripe-payment-container');
				if(container) container.className = "";
			} else {
				// Push the source
				var input = d.getElementById("hk_co_p_c_STRIPEC_SRC_" + instance.id);
				input.value = result.source.id;
				if(result.source && result.source.card && result.source.card.three_d_secure) {
					input = d.getElementById("hk_co_p_c_STRIPEC_3DS_" + instance.id);
					if(input) input.value = result.source.card.three_d_secure;
				}
				if(result.source && result.source.card && result.source.card.last4) {
					input = d.getElementById("hk_co_p_c_STRIPEC_L4_" + instance.id);
					if(input) input.value = result.source.card.last4;
				}
				// Submit the form
				w.checkout.submitPayment(step, pos);
			}
			w.checkout.setLoading(el, false);
		});
	},
	_handlePaymentMethod: function(instance, el, step, pos) {
		var d = document, w = window, o = w.Oby, t = stripeConnect;
		instance.s.createPaymentMethod('card', instance.card, instance.additional).then(function(result) {
			if (result.error) {
				// Inform the user if there was an error
				var errorElement = d.getElementById("hk_co_p_c_STRIPEC_errors_" + instance.id);
				errorElement.textContent = result.error.message;
				if(instance.form)
					instance.form.tokenCreation = false;
				var container = d.getElementById('stripe-payment-container');
				if(container) container.className = "";
			} else {
				// Push the method id
				var input = d.getElementById("hk_co_p_c_STRIPEC_MET_" + instance.id);
				input.value = result.paymentMethod.id;
				if(result.paymentMethod && result.paymentMethod.card && result.paymentMethod.card.three_d_secure_usage) {
					input = d.getElementById("hk_co_p_c_STRIPEC_3DS_" + instance.id);
					if(input) input.value = result.paymentMethod.card.three_d_secure_usage.supported;
				}
				if(result.paymentMethod && result.paymentMethod.card && result.paymentMethod.card.last4) {
					input = d.getElementById("hk_co_p_c_STRIPEC_L4_" + instance.id);
					if(input) input.value = result.paymentMethod.card.last4;
				}
				// Submit the form
				if(w.checkout && step !== null)
					w.checkout.submitPayment(step, pos);
				if(instance.notify)
					t._notifyPaymentMethod(instance, result);
			}
			if(w.checkout && el !== null)
				w.checkout.setLoading(el, false);
		});
	},
	_handleNotifyResponse: function(instance, xhr) {
		var o = window.Oby, t = stripeConnect,
			response = o.evalJSON(xhr.responseText),
			displayError = document.getElementById("hk_co_p_c_STRIPEC_errors_" + instance.id);
		if(displayError)
			displayError.textContent = "";
		if(response===null) {
			console.log("Stripe error with: " + xhr.responseText);
			if(displayError)
				displayError.textContent = "Unknown error";
			else
				alert("Unknown error");
			return;
		}
		if(response.error) {
			if(displayError)
				displayError.textContent = response.error;
			else
				alert(response.error);
			if(instance.form)
				instance.form.tokenCreation = false;
			var container = document.getElementById('stripe-payment-container');
			if(container) container.className = "";
		} else if(response.requires_action) {
			instance.s.handleCardAction(response.payment_intent_client_secret).then(function(res) {
				if (res.error) {
					displayError.textContent = res.error.message || res.error.code;
					if(instance.form)
						instance.form.tokenCreation = false;
					var container = document.getElementById('stripe-payment-container');
					if(container) container.className = "";
				} else {
					t._notifyPaymentIntent(instance, res);
				}
			});
		} else if(response.success) {
			if(instance.form) {
				instance.form.style.display = 'none';
				var d = document, thanksBlock = d.getElementById('hikashop_stripeconnect_thankyou');
				if(thanksBlock) thanksBlock.style.display = '';
			}
			if(instance.success && typeof(instance.success) == 'function')
				instance.success();
			if(response.url)
				window.location = response.url;
		}
	},
	_notifyPaymentMethod: function(instance, result) {
		var o = window.Oby, t = stripeConnect,
			data = o.encodeFormData({ paymentMethod: result.paymentMethod.id });
		o.xRequest(instance.notify, {mode:"POST",data:data}, function(x,p){
			t._handleNotifyResponse(instance, x);
		});
	},
	_notifyPaymentIntent: function(instance, result) {
		var o = window.Oby, t = stripeConnect,
			data = o.encodeFormData({ paymentIntent: result.paymentIntent.id });
		o.xRequest(instance.notify, {mode:"POST",data:data}, function(x,p) {
			t._handleNotifyResponse(instance, x);
		});
	},
	getDefaultStyle: function() {
		return {
			base: {
				color: "#32325d",
				lineHeight: "18px",
				fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
				fontSmoothing: "antialiased",
				fontSize: "16px",
				"::placeholder": {
					color: "#aab7c4"
				}
			},
			invalid: {
				color: "#fa755a",
				iconColor: "#fa755a"
			}
		};
	}
};
window.stripeConnect = stripeConnect;

}();