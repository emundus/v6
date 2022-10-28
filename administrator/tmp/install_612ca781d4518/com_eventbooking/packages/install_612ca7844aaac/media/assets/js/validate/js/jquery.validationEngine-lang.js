Eb.jQuery(function ($) {
    $.fn.validationEngineLanguage = function () {
    };

    $.validationEngineLanguage = {
        newLang: function () {
            var rootUri = Joomla.getOptions('rootUri');
            var eventId = Joomla.getOptions('eventId');

            $.validationEngineLanguage.allRules = {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_FIELD_REQUIRED'),
                    "alertTextCheckboxMultiple": Joomla.JText._('EB_VALIDATION_PLEASE_SELECT_AN_OPTION'),
                    "alertTextCheckboxe": Joomla.JText._('EB_VALIDATION_CHECKBOX_REQUIRED'),
                    "alertTextDateRange": Joomla.JText._('EB_VALIDATION_BOTH_DATE_RANGE_FIELD_REQUIRED')
                },
                "requiredInFunction": {
                    "func": function (field, rules, i, options) {
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": Joomla.JText._('EB_VALIDATION_FIELD_MUST_EQUAL_TEST')
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID'),
                    "alertText2": "Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID'),
                    "alertText2": Joomla.JText._('EB_VALIDATION_DATE_TIME_RANGE')
                },
                "minSize": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_MINIMUM'),
                    "alertText2": Joomla.JText._('EB_CHARACTERS_REQUIRED')
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_MAXIMUM'),
                    "alertText2": Joomla.JText._('EB_VALIDATION_CHACTERS_ALLOWED'),
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_GROUP_REQUIRED')
                },
                "min": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_MIN')
                },
                "max": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_MAX')
                },
                "past": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_DATE_PRIOR_TO')
                },
                "future": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_DATE_PAST')
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_MAXIMUM'),
                    "alertText2": Joomla.JText._('EB_VALIDATION_OPTION_ALLOW')
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_PLEASE_SELECT'),
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_FIELDS_DO_NOT_MATCH')
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_CREDIT_CARD_NUMBER')
                },
                "phone": {
                    // credit: jquery.h5validate.js / orefalo
                    "regex": /^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_PHONE_NUMBER')
                },
                "email": {
                    // HTML5 compatible email regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    "regex": /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_EMAIL_ADDRESS')
                },
                "integer": {
                    "regex": /^[\-\+]?\d+$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_NOT_A_VALID_INTEGER')
                },
                "number": {
                    // Number, including positive, negative, and floating decimal. credit: orefalo
                    "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_FLOATING_DECIMAL_NUMBER')
                },
                "date": {
                    //	Check if date is valid by leap year
                    "func": function (field) {
                        var match = pattern.exec(field.val());
                        if (match == null)
                            return false;

                        var year = match[yearPartIndex + 1];
                        var month = match[monthPartIndex + 1] * 1;
                        var day = match[dayPartIndex + 1] * 1;
                        var date = new Date(year, month - 1, day); // because months starts from 0.

                        return (date.getFullYear() == year && date.getMonth() == (month - 1) && date.getDate() == day);
                    },

                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_DATE').replace('YYYY-MM-DD', Joomla.getOptions('humanFormat'))
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_IP_ADDRESS')
                },
                "url": {
                    "regex": /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_URL')
                },
                "onlyNumberSp": {
                    "regex": /^[0-9\ ]+$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_NUMBER_ONLY')
                },
                "onlyLetterSp": {
                    "regex": /^[a-zA-Z\ \']+$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_LETTERS_ONLY')
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_NO_SPECIAL_CHACTERS_ALLOWED')
                },
                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
                "ajaxUserCall": {
                    "url": rootUri + "/index.php?option=com_eventbooking&task=validate_username",
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_USERNAME')
                },
                "ajaxEmailCall": {
                    "url": rootUri + "/index.php?option=com_eventbooking&task=validate_email&event_id=" + eventId,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_EMAIL')
                },
                //tls warning:homegrown not fielded
                "dateFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
                    "alertText": Joomla.JText._('EB_VALIDATION_INVALID_DATE')
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "* Invalid Date or Date Format",
                    "alertText2": Joomla.JText._('EB_VALIDATION_EXPECTED_FORMAT'),
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
                    "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
                }
            };
        }
    };
    $.validationEngineLanguage.newLang();
});

