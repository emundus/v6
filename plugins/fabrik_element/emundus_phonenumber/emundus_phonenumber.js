

const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-400");
const unsupportedColor = allColor.getPropertyValue("--orange-400")

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbPhoneNumber = new Class({
        Extends: FbElement,

        initialize: function (element, options)
        {
            this.setPlugin('emundus_phonenumber');
            this.parent(element, options);
            this.initValidatorJS();
        },

        cloned: function (c)
        {
            this.element.getElement("input").value = "";
            this.initValidatorJS();
            this.parent(c);
        },

        initValidatorJS: function ()
        {
            const select = this.element.getElement("select");
            const input = this.element.getElement("input");

            if (select !== null && input !== null) { // not in details format
                const allCountries = JSON.parse(atob(select.getAttribute("data-countries"))); // decode base64 + get JSON to array type

                let defaultValue; // use if already have value in input
                let selectedCountryIndex = this.getSelectedCountryIndex(allCountries, this.options.countrySelected); // get default country

                if (input.value.length > 4) // already have value
                {
                    defaultValue = input.value;

                    const countryIso2 = select.getAttribute("selectedValue"); // get iso2
                    selectedCountryIndex = this.getSelectedCountryIndex(allCountries, countryIso2); // get index array from iso2
                }

                this.ValidatorJS = new ValidatorJS(input, select, allCountries, selectedCountryIndex, defaultValue);
                this.ValidatorJS.setColors(validColor, errorColor, unsupportedColor, defaultColor);
            }
        },

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

        onsubmit: function(c)
        {
            const input = this.ValidatorJS.input;
            const iso2 = this.ValidatorJS.allCountry[this.ValidatorJS.indiceCountry].iso2;

            input.value = iso2 + input.value // +XXYYYY format to FR+XXYYYY

            this.parent(c);
        },
    });
    return window.FbPhoneNumber;
});
