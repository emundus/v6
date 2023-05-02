

const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-400");
const unsupportedColor = allColor.getPropertyValue("--orange-400")

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbPhoneNumber = new Class({
        Extends: FbElement,

        /**
         * Initialize object from Fabrik/Joomla
         *
         * @param element       string      name of the element
         * @param options       array       all options for the element
         */
        initialize: function (element, options)
        {
            this.setPlugin('emundus_phonenumber');
            this.parent(element, options);
            this.initValidatorJS();
        },

        /**
         * Called when element cloned in repeatable group
         *
         * @param   c       int         index of the new element
         */
        cloned: function (c)
        {
            this.element.getElement('input').value = '';
            this.initValidatorJS();
            this.parent(c);
        },

        /**
         * Initialise the ValidatorJS object depending on data.
         */
        initValidatorJS: function ()
        {
            const select = this.element.getElement('select');
            const input = this.element.getElement('input');

            if (select !== null && input !== null) { // not in details format
                const allCountries = JSON.parse(atob(select.getAttribute('data-countries'))); // decode base64 + get JSON to array type

                let defaultValue;
                let selectedCountryIndex = this.getSelectedCountryIndex(allCountries, this.options.countrySelected); // get default country

                if (input.value.length > 4) // already have value
                {
                    defaultValue = input.value;

                    const countryIso2 = select.getAttribute('selectedValue'); // get iso2
                    selectedCountryIndex = this.getSelectedCountryIndex(allCountries, countryIso2); // get index array from iso2
                }

                this.ValidatorJS = new ValidatorJS(input, select, allCountries, selectedCountryIndex, defaultValue);
                this.ValidatorJS.setColors(validColor, errorColor, unsupportedColor, defaultColor);
            }
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

        /**
         * Called before sending in back-end,
         * Change input's value to DB format
         *
         * @param c
         */
        onsubmit: function(c)
        {
            const input = this.ValidatorJS.input;
            const iso2 = this.ValidatorJS.allCountry[this.ValidatorJS.indiceCountry].iso2;

            input.value = iso2 + input.value // +XXYYYY format to ZZ+XXYYYY

            this.parent(c);
        },
    });
    return window.FbPhoneNumber;
});
