define(['jquery', 'fab/element'], function (jQuery, FbElement) {

	window.FbEmundusreferent = new Class({

		Extends: FbElement,
		initialize: function (element, options) {

			var self = this;

			this.parent(element, options);
			this.setPlugin = 'fabrikEmundusreferent';
			this.container = jQuery(this.container);

			this.observer = document.id(element);
			this.options = options;

			this.btn = element + '_btn';
			this.response = element + '_response';
			this.error = element + '_error';
			this.loader = element + '_loader';
			this.setOptions(element, this.options);

			if (this.observer && $(this.btn) != null) {
				$(this.btn).addEventListener('click', function () {

					var v = this.observer.get('value');
					var email = document.getElementById(options.email).value;
					var attachment_id = this.options.attachment_id;
					var fnum = document.querySelector('[id$="___fnum"]').value;

					// element id of email
					var email_selector = this.options.email;

					// parent group of email_selector
					var parent_group = document.querySelector('#' + email_selector).closest('fieldset').getAttribute('id');

					// get the firstname, lastname of referent for each group
					var firstname = document.querySelector('#' + parent_group + ' [id^=jos_emundus_references___First_Name_]').value;
					var lastname  = document.querySelector('#' + parent_group + ' [id^=jos_emundus_references___Last_Name_]').value;

					if (email == "") {
						$(this.options.email).setStyle('border', '4px solid #ff0000');
						this.endAjax();
					}

					this.myAjax = new Request({
						url: 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundusreferent&method=onAjax_getOptions&v='+v,
						method: 'post',
						'data': {
							'attachment_id': attachment_id,
							'email': email,
							'formid': this.options.formid,
							'fnum': fnum,
							'firstname': firstname,
							'lastname': lastname,
							'form_recommend': this.options.form_recommend
						},
						onComplete: response => {
							self.ajaxComplete(response);
						}
					});

					$(this.btn).disabled = true;
					$(this.btn).value = options.sending + " <" + email + ">";
					$(this.loader).setStyle('display', '');

					this.myAjax.send();
				}.bind(this));

			} else {
				fconsole('observer not found ', element);
			}
		},

		update: function () {
			if (this.observer) {
				this.myAjax.options.data.v = this.observer.get('value');
				var $filterData = eval(this.options.filterobj).getFilterData();
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
				$(this.options.email).setStyle('border', '2px solid #B0BB1E');
			} else {
				$(this.error).innerHTML = json.message;
				$(this.btn).disabled = false;
				$(this.btn).value = this.options.sendmailagain;
				$(this.options.email).setStyle('border', '4px solid #ff0000');
			}
			this.endAjax();
		},

		endAjax: function () {
			$(this.loader).setStyle('display', 'none');
		},

		dump: function (arr, level) {
			var dumped_text = "";
			if (!level) level = 0;

			//The padding given at the beginning of the line.
			var level_padding = "";
			for (var j = 0; j < level + 1; j++) {
				level_padding += "    ";
			}

			if (typeof (arr) == 'object') {
				for (var item in arr) {
					var value = arr[item];

					if (typeof (value) == 'object') {
						dumped_text += level_padding + "'" + item + "' ...\n";
						dumped_text += dump(value, level + 1);
					} else {
						dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
					}
				}
			} else {
				dumped_text = "===>" + arr + "<===(" + typeof (arr) + ")";
			}
			return dumped_text;
		}
	});

	return window.FbEmundusreferent;
});