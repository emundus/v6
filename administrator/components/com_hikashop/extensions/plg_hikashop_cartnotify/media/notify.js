window.cartNotifyParams = {
	img_url: null,
	title:"Product added to cart",
	text:"Product successfully added to the cart",
	wishlist_title:"Product added to wishlist",
	wishlist_text:"Product successfully added to the wishlist",
	err_title:"Error",
	err_text:"The product cannot be added to the cart",
	err_wishlist_title:"Error",
	err_wishlist_text:"The product cannot be added to the wishlist"
};
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

	if(params.resp.messages && params.resp.messages[0] && params.resp.messages[0].type)
		class_name = params.resp.messages[0].type;
	if(!hkjQuery.notify) {
		console.error('jQuery has been reInitialized after the notify plugin was added to it and thus it is missing. This leads to the add to cart notification box not appearing. To fix that, you\'ll probably have to use the extension jQuery Easy.');
	}
	try {
		if(p && p.reference && p.reference == 'button' && params.el) {
			hkjQuery(params.el).notify({title:title,text:text,image:"<img src=\""+img_url+"\" width=\"50\" height=\"50\" alt=\"\"/>"},{style:"metro",className:class_name,arrowShow:true});
		}else if(img_url == null) {
			hkjQuery.notify({title:title,text:text},{style:"metro-lite",className:class_name});
		} else {
			hkjQuery.notify({title:title,text:text,image:"<img src=\""+img_url+"\" alt=\"\"/>"},{style:"metro",className:class_name});
		}
	} catch (error) {
		console.error(error);
	}

	if(success && p.redirect_url) {
		if(!p.redirect_delay)
			p.redirect_delay = 4000;
		setTimeout(function(){ window.location = p.redirect_url; }, p.redirect_delay);
	}

	return true;
});
}