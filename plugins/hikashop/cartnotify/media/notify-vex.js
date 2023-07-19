if(window.Oby) {
window.Oby.registerAjax(["cart.updated","wishlist.updated"],function(params){
	var cart = (params.type == "cart"),
		p = window.cartNotifyParams,
		img_url = p.img_url,
		title = cart ? p.title : p.wishlist_title,
		text = cart ? p.text : p.wishlist_text,
		class_name = "info", success = true;
	if(params.notify === false)
		return;
	if(params.resp.ret == 0) {
		class_name = "warning";
		title = cart ? p.err_title : p.err_wishlist_title;
		text = cart ? p.err_text : p.err_wishlist_text;
		success = false;
		if(!cart && params.resp.err_wishlist_guest && p.err_wishlist_guest) {
			p.redirect_url = p.err_wishlist_guest;
			success = true;
		}
	}else if(params.product_id == 'list' && !params.resp.product_name){
		title = cart ? p.list_title : p.list_wishlist_title;
		text = cart ? p.list_text : p.list_wishlist_text;
	}
	if(params.resp.image)
		img_url = params.resp.image;
	if(params.resp.product_name)
		title = params.resp.product_name;
	if(params.resp.message)
		text = params.resp.message;
	else if(params.resp.messages && params.resp.messages[0] && params.resp.messages[0].msg) {
		text = params.resp.messages[0].msg;
	}

	var content = "";
	if(img_url == null) {
		content = "<div class=\"notifyjs-metro-lite-base\"><div class=\"text-wrapper\"><div class=\"title\">"+title+"</div><div class=\"text\">"+text+"</div></div></div>";
	} else {
		content = "<div class=\"notifyjs-metro-base\"><div class=\"image\"><img src=\""+img_url+"\" width=\"50\" height=\"50\" alt=\"\"/></div><div class=\"text-wrapper\"><div class=\"title\">"+title+"</div><div class=\"text\">"+text+"</div></div></div>";
	}
	var vex_params = {message: content},
		params_key = params.type + (params.resp.ret == 0 ? '_err' : '') + '_params';
	if(p[params_key]) {
		if(Object && Object.assign)
			vex_params = Object.assign(vex_params, p[params_key]);
		else
			vex_params = jQuery.extend(true, vex_params, p[params_key]);
	}
	vex.dialog.alert(vex_params);

	if(success && p.redirect_url) {
		if(!p.redirect_delay)
			p.redirect_delay = 4000;
		setTimeout(function(){ window.location = p.redirect_url; }, p.redirect_delay);
	}

	if(p.hide_delay && p.hide_delay > 0) {
		setTimeout(function(){ if(window.top.vex.closeAll) window.top.vex.closeAll(); }, p.hide_delay);
	}
	return true;
});
}