define(['jquery', 'fab/element'], function (jQuery, FbElement) {

    window.FbEmundusreferent_telecomparis = new Class({

        Extends: FbElement,
        initialize: function (element, options) {

            var self = this;

            this.parent(element, options);
            this.setPlugin = 'fabrikEmundusreferent_telecomparis';
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
                    var email_selector = options.email;

                    // parent group of email_selector
                    var parent_group = jQuery('#' + email_selector).closest('fieldset').attr('id');

                    // get the firstname, lastname of referent for each group

                    var firstname = jQuery('#' + parent_group ).find('[id^=jos_emundus_references___First_Name_]').val();
                    var lastname  = jQuery('#' + parent_group ).find('[id^=jos_emundus_references___Last_Name_]').val();

                    if (email == "") {
                        $(this.options.email).setStyle('border', '4px solid #ff0000');
                        this.endAjax();
                    }

                    this.myAjax = new Request({
                        url: 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundusreferent_telecomparis&method=onAjax_getOptions&v='+v,
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

                // get the fieldset id (e.g: group507)
                var fieldset = $(this.btn).closest('fieldset').id;

                // find sollicitation element if exists
                var sollicitationDiv = jQuery('#' + fieldset).find('div[class*=sollicitation_reference_]');

                // find sent emails table if exists
                var sentEmailTbl = jQuery('#' + fieldset).find('table[id=sollicitation_table]');

                // find the last element of the current fieldset
                var lastElt = jQuery('#' + fieldset).last();

                // set table header
                var sentEmailTblHeader =
                    '<div>' +
                    '<table id="sollicitation_table" class="table-striped" style="width:100%; border: none !important; display:inline-block">' +
                    '<tr>' +
                    '<th style="border-bottom: solid; background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_REFERENCE') + '</th>' +
                    '<th style="border-bottom: solid; background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_DATE')                 + '</th>' +
                    '<th style="border-bottom: solid; background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT_TO')                   + '</th>' +
                    '<th style="border-bottom: solid; background:unset !important">' + Joomla.JText._('PLG_ELEMENT_EMUNDUSREFERENT_SOLLICITATION_SEND_STATUS')       + '</th>' +
                    '</tr>' +
                    '</table>' +
                    '</div>';

                if(sollicitationDiv.length > 0) {
                    if(sentEmailTbl.length === 0) { sollicitationDiv.append(sentEmailTblHeader); }
                } else {
                    // append table after the last element
                    if(sentEmailTbl.length === 0) { lastElt.append(sentEmailTblHeader); }
                }

                // reupdate table
                sentEmailTbl = jQuery('#' + fieldset).find('table[id=sollicitation_table]');
                sentEmailTbl.append(
                    '<tr style="border: none !important; font-size: 15px">' +
                    '<td style="border:none !important; width: 300px">' + json.email + '</td>' +
                    '<td style="border:none !important">' + new Date().toLocaleDateString("fr-FR") + '</td>' +
                    '<td style="border:none !important">' + new Date().toLocaleTimeString() + '</td>' +
                    '<td style="border: none !important; text-align:center"><i class="large circle inverted question icon" style="color:darkorange; background-color: none"></i></td>' +
                    '</tr>'
                );
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

    return window.FbEmundusreferent_telecomparis;
});