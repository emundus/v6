define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbPhoneNumber = new Class({
        Extends: FbElement,

        /**
         * Initialize object from Fabrik/Joomla
         *
         * @param element       string      name of the element
         * @param options       array       all options for the element
         */
        initialize: function (element, options) {
            this.setPlugin('emundus_phonenumber');
            this.parent(element, options);

            this.prepareCountryCode();

            this.initChosen();

            this.initValidatorJS();
        },

        /**
         * Called when element cloned in repeatable group
         *
         * @param   c       int         index of the new element
         */
        cloned: function (c) {
            this.element.getElementById('inputValue').value = '';
            this.element.getElementById('countrySelect').setAttribute('selectedValue', this.options.allCountries[0].iso2); // reset with the first one

            this.element.getElementById('countrySelect_chzn').remove();

            this.initChosen();

            this.initValidatorJS(true);

            this.parent(c);
        },

        prepareCountryCode: function () {
            var select = '#'+this.element.id+' #countrySelect';
            var options = jQuery(select+' option');
            for(const option of options) {
                try {
                    var country_code = libphonenumber.getCountryCallingCode(option.value);
                    option.setAttribute('data-countrycode',country_code);
                    option.textContent = option.textContent + ' (+'+country_code+')';
                } catch(e){}
            }
        },

        initChosen: function () {
            Fabrik.buildChosen('#'+this.element.id+' #countrySelect', {
                disable_search_threshold: 10,
                allow_single_deselect: true,
                search_contains: true,
            });

            setTimeout(() => {
                jQuery('#' + this.element.id+' #countrySelect').trigger('liszt:updated');

                this.watchChange();

                document.querySelector('#'+this.element.id+' .chzn-container .chzn-drop').style.minWidth = document.getElementById(this.element.id).offsetWidth+'px';

                jQuery('#'+this.element.id+' #countrySelect').on('change', () => {
                    this.watchChange();
                });
            },100);
        },

        /**
         * Initialise the ValidatorJS object depending on data.
         */
        initValidatorJS: function (cloned = false) {
            const select = this.element.getElementById('countrySelect');
            const input = this.element.getElementById('inputValue');

            if (select !== null && input !== null) { // not in details format
                const allCountries = this.options.allCountries;

                let defaultValue;
                let selectedCountryIndex = this.getSelectedCountryIndex(allCountries, this.options.default_country); // get default country

                if (select.getAttribute('selectedValue') !== '') // already have value
                {
                    defaultValue = input.value;

                    const countryIso2 = select.getAttribute('selectedValue'); // get iso2
                    selectedCountryIndex = this.getSelectedCountryIndex(allCountries, countryIso2); // get index array from iso2
                }

                this.ValidatorJS = new ValidatorJS(this.element, allCountries, selectedCountryIndex, defaultValue, cloned);
            }
        },

        watchChange: function () {
            var select = '#'+this.element.id+' #countrySelect';
            var val = jQuery(select).val();
            var flag = jQuery(select+" option:selected").attr('data-flag');

            jQuery(select+" option[data-value='selected']").attr('value', val);
            jQuery(select+" option[data-value='selected']").html('<img src"images/emundus/'+flag+'" alt="'+val+'"/>');

            jQuery(select).val(val);

            jQuery('#'+this.element.id+' #countrySelect_chzn .chzn-single span').html("<img src='/images/emundus/flags/"+flag+"' alt='"+val+"'/>");
        },

        /**
         * Returns searchCountry index from allCountries array
         *
         * @param   allCountries    html options array      array used for the ValidatorJS object
         * @param   searchCountry   string                  iso2's country
         * @returns {number}        int >=0                 index in the array
         */
        getSelectedCountryIndex: function (allCountries, searchCountry)
        {
            let selectedCountryIndex = 0;
            if (searchCountry !== null && typeof searchCountry !== 'undefined' && searchCountry !== '') {
                selectedCountryIndex = allCountries.findIndex(country => {
                    return country.iso2 === searchCountry;
                });

                selectedCountryIndex = selectedCountryIndex === -1 ? 0 : selectedCountryIndex;
            }
            return selectedCountryIndex;
        },

        getValue: function () {
            return this.element.getElementById('renderCountryCode').value + this.element.getElementById('inputValue').value;
        }
    });
    return window.FbPhoneNumber;
});
