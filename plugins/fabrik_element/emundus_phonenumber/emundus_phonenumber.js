

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
            this.initValidatorJS();
            this.parent(c);
        },

        initValidatorJS: function ()
        {
            const select = this.element.getElement("select");
            const input = this.element.getElement("input");
            const allCountries = JSON.parse(atob(select.getAttribute("data-countries"))); // decode base64 + get JSON to array type

            let selectedCountryIndex = 0;
            if (this.options.countrySelected !== null && typeof this.options.countrySelected !== 'undefined' && this.options.countrySelected !== '') {
                selectedCountryIndex = allCountries.findIndex(country => {
                    return country.iso2 === this.options.countrySelected;
                });

                selectedCountryIndex = selectedCountryIndex === -1 ? 0 : selectedCountryIndex;
            }

            this.ValidatorJS = new ValidatorJS(input, select, allCountries, selectedCountryIndex);
            this.ValidatorJS.setColors(validColor, errorColor, unsupportedColor, defaultColor);
        },

        getCountryIndexFromIso2: function (countries, iso2Search)
        {
            let index = 0;
            countries.forEach((element, key) =>
            {
                if (element.iso2 === iso2Search)
                {
                    index = key;
                }
            });
            return index;
        },

    });
    return window.FbPhoneNumber;
});
