var FbAutocomplete = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'autocomplete';
		this.parent(element, options);
		options = JSON.parse(options);

		document.getElementById(element).onkeydown = function(e) {
			//console.log("Callbackid :" + options.callbackid + "options['infoid'] :" + options.infoid)		
			this.getParent().getElement('.loader').setStyle('display', '');
			
			
			if (options['callbackid'] != "null" && options['infoid'] != "null") {
				var TABKEY = 9;
				if(navigator.appName === "Microsoft Internet Explorer") { 
					if( e.keyCode != TABKEY) {
						document.getElementById(options['callbackid']).value="";
						document.getElementById(options['infoid']).value="";
					}
				} else {
					if( e.which != TABKEY) {
						document.getElementById(options['callbackid']).value="";
						document.getElementById(options['infoid']).value="";
					}
				}
			}
		}
		
			document.getElementById(element).onblur = function(e) {
			this.getParent().getElement('.loader').setStyle('display', 'none');
		}

		this.ac_options = {
				varname:"input",
				script: function(obj){
				if (options['search_value_name'] != 'null') {
					search_value = document.getElementById(options['search_value_name']).value;
					url = "index.php?option=com_fabrik&format=raw&view=plugin&task=plugin.pluginAjax&plugin=autocomplete&method=json_get&r1="+options['r1']+"&r2="+options['r2']+"&r3="+options['r3']+"&search_field="+options['search_field']+"&info_field="+options['info_field']+"&search_value_name="+options['search_value_name']+"&search_value="+search_value+"&input="+obj; 
				} else {
					url = "index.php?option=com_fabrik&format=raw&view=plugin&task=plugin.pluginAjax&plugin=autocomplete&method=json_get&r1="+options['r1']+"&r2="+options['r2']+"&r3="+options['r3']+"&search_field="+options['search_field']+"&info_field="+options['info_field']+"&search_value_name=&search_value=&input="+obj;
				}
				//parent.showLoader();
				return url;
				},
				json:true,
				timeout:10000,
				shownoresults:true,
				maxresults:6,
				callback: function (obj) { 
					if (options['callbackid'] != 'null') 
						document.getElementById(options['callbackid']).value = obj.id;
					
					var reg=new RegExp("[,]+", "g");
					val = obj.value;
					var cname=val.split(reg);
					document.getElementById(options['id']).value = cname[0];
					
					var reg=new RegExp("[___]+", "g");
					vid = options['id'];
					var elt_name=vid.split(reg);
					
					if(elt_name[3] == "Last") {
						firstname_id = "jos_emundus_references___First_Name_"+elt_name[5];
						document.getElementById(firstname_id).value = cname[1];
					}

					if ( options['infoid'] != 'null' ) 
						document.getElementById(options['infoid']).value = obj.info;
					
					//element.getParent().getElement('.loader').setStyle('display', 'none');
					//this.hideLoader();
				}
		};
			
		var as_json = new bsn.AutoSuggest(options.id, this.ac_options);
		//console.log(options.id);
	
	},
	
	showLoader: function() {
		this.element.getParent().getElement('.loader').setStyle('display', '');
	},
	hideLoader: function() {
		this.element.getParent().getElement('.loader').setStyle('display', 'none');
	},
});


