var FbEmundusreferent = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikEmundusreferent';
		this.parent(element, options);
		this.observer = document.id(element);
		this.options = options;
		this.btn = element+'_btn';
		//this.btn_action = document.id(element+'_btn');
		this.response = element+'_response';
		this.error = element+'_error';
		this.loader = element+'_loader';
		this.setOptions(element, this.options);

		if (this.observer && $(this.btn) != null) {
			//new Element('img', {'id': this.loader, 'src': Fabrik.liveSite + 'media/com_fabrik/images/ajax-loader.gif', 'alt': 'loading...', 'styles': {'opacity': '0'}}).inject(this.observer, 'before');
			
			//$(this.btn).addEvent('click', function () {
			//window.addEvent('domready',function () {
			//document.getElementById(this.btn).onclick=function(){alert("button2 clicked");};
			$(this.btn).addEventListener( 'click', function() { 
			//this.observer.addEvent('click', function () {
			//this.btn_action.addEvent('click', function () {
				var v = this.observer.get('value');
				var email = document.getElementById(options['email']).value;
				var attachment_id = this.options.attachment_id;
				//var url = "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=emundusreferent&method=email&email="+email+"&id="+attachment_id;
				
				if (email=="") {
					$(this.options['email']).setStyle('border', '4px solid #ff0000');
					this.endAjax();
				}
				
				this.myAjax = new Request({url: '', method: 'get',
						'data': {
							'option': 'com_fabrik',
							'format': 'raw',
							'task': 'plugin.pluginAjax',
							'plugin': 'emundusreferent',
							'method': 'onAjax_getOptions',
							'attachment_id': attachment_id,
							'email': email, 
							'v' : v,
							'formid': this.options.formid
						},
						onComplete: this.ajaxComplete.bindWithEvent(this)
				});
			
				//alert(this.dump(this.myAjax, 0));
				$(this.btn).disabled = true;
				$(this.btn).value = options['sending'] + " <" + document.getElementById(options['email']).value + ">";
				$(this.loader).setStyle('display', '');
							
				this.myAjax.send();
			}.bind(this));
			
			//v = this.observer.get('value');
		} else {
			fconsole('observer not found ', element);
		}
	},
	
	update: function () {
		if (this.observer) {
			this.myAjax.options.data.v = this.observer.get('value');
			$filterData = eval(this.options.filterobj).getFilterData();
			Object.append(this.myAjax.options.data, $filterData);
			this.myAjax.send();
		}
	},
	
	ajaxComplete: function (json) { 
		json = JSON.decode(json);
		if (json.result == "1") { 
			$(this.observer).value = parseInt($(this.observer).value) + 1;
			$(this.response).innerHTML = json.message; 
			$(this.error).innerHTML = "";
			$(this.options['email']).setStyle('border', '2px solid #B0BB1E');
		} else {
			$(this.error).innerHTML = json.message; 
			$(this.btn).disabled = false;
			$(this.btn).value = this.options['sendmailagain'];
			$(this.options['email']).setStyle('border', '4px solid #ff0000');
		}
		this.endAjax();
	},
	
	endAjax: function ()
	{
		$(this.loader).setStyle('display', 'none');
	},
	
	dump : function (arr, level) {
		var dumped_text = "";
		if(!level) level = 0;
		
		//The padding given at the beginning of the line.
		var level_padding = "";
		for(var j=0;j<level+1;j++) level_padding += "    ";
		
		if(typeof(arr) == 'object') { //Array/Hashes/Objects 
			for(var item in arr) {
				var value = arr[item];
				
				if(typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "'" + item + "' ...\n";
					dumped_text += dump(value,level+1);
				} else {
					dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
				}
			}
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
		}
		return dumped_text;
	}
});