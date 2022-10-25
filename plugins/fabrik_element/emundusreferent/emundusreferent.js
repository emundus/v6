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

					// first act : get element id of email
					var email_selector = options.email;

					// second act : get parent group of email_selector
					var parent_group = jQuery('#' + email_selector).closest('fieldset').attr('id');

					// third act : from parent_group, we get the firstname, lastname of referent

					var firstname = jQuery('#' + parent_group ).find('[id^=jos_emundus_references___First_Name_]').val();
					var lastname  = jQuery('#' + parent_group ).find('[id^=jos_emundus_references___Last_Name_]').val();
					
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
							'lastname': lastname
						},
						onComplete: response => {
							self.ajaxComplete(response);
						}
					});

					$(this.btn).disabled = false;
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

				// remove the error message (if any) //
				jQuery('.emundusreferent_error').remove();

				// get the parent of this button
				var parent = $(this.btn).closest('div').id;
				jQuery('#messageResponse').last().remove();
				jQuery('#' + parent).append("<div id='messageResponse'>" + json.message + "</div>");
				//jQuery('#' + parent).find('[id=messageResponse]').last().css("background-color", "#dbcb8f");

				/*setTimeout(function() {
					jQuery('#' + parent).find('[id=messageResponse]').last().css("background-color", "");
				}, 1000);*/

				// add bullet to "sollicitation" (if exist) : find "sollicitation_reference_" //
				var fieldset = $(this.btn).closest('fieldset').id;

				var sollicitation = jQuery('#' + fieldset).find('[id*=sollicitation_reference_]').find('div table');

				console.log(sollicitation);

				// if table is not found, create new table
				if(sollicitation.length === 0) {
					// create new label of sollicitation
					//jQuery('#' + fieldset).find('[id*=sollicitation_reference_]').append('<label style="padding-bottom: 20px; font-weight: 500; color:black">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_SOLLICITATION_LABEL') + '</label>');

					// create new table of sollicitation
					/*jQuery('#' + fieldset).find('[id*=sollicitation_reference_]').append('<div><table style="width:100%; border: none !important; display:inline-block">' +
							'<tr>' +
								'<th style="border-bottom: solid;background:unset !important">Email du référent</th>' +
								'<th style="border-bottom: solid;background:unset !important">Sollicité le</th>' +
								'<th style="border-bottom: solid;background:unset !important">À</th>' +
							'</tr>'
					);*/

					jQuery('#' + fieldset).find('[id*=sollicitation_reference_]').append(
						'<div><table style="width:100%; border: none !important; display:inline-block">' +
							'<tr>' +
								'<th style="border-bottom: solid;background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_REFEREE') + '</th>' +
								'<th style="border-bottom: solid;background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_AT_DATE') + '</th>' +
								'<th style="border-bottom: solid;background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_AT_TIME') + '</th>' +
								'<th style="border-bottom: solid;background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_IS_SENT') + '</th>' +
							'</tr>'
					);

					sollicitation = jQuery('#' + fieldset).find('[id*=sollicitation_reference_]').find('div table');
				}
				// append the new record
				sollicitation.append('<tr style="border: none !important; font-size: 15px">' +
						'<td style="border:none !important; width: 300px">' + json.email + '</td>' +
						'<td style="border:none !important">' + new Date().toLocaleDateString("fr-FR") + '</td>' +
						'<td style="border:none !important">' + new Date().toLocaleTimeString() + '</td>' +
						'<td style="border: none !important; text-align:center"><i class="large circle inverted question icon" style="color:darkorange; background-color: none"></i></td>' +
					'</tr>');

				// setTimeout(function() {
				// 	// sollicitation.find('tr i').last().toogleClass("large circle inverted red remove icon");
				// 	sollicitation.find('tr i').last().attr("class", 'large circle inverted red remove icon');
				// 	sollicitation.find('tr i').last().css('background','none');
				// },300);
				
				// get the current date (new Date().toLocaleDateString("fr-FR"))

				// get the current time new Date().toLocaleTimeString()
				/*sollicitation.find('tr').last().css("background-color", "#dbcb8f");

				setTimeout(function () {sollicitation.find('tr').last().css("background-color", "");}, 1000);*/

				//$(this.response).innerHTML = json.message;
				//$(this.error).innerHTML = "";
				//$(this.options.email).setStyle('border', '2px solid #B0BB1E');
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