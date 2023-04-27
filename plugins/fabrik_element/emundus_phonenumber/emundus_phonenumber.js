

const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-900");
const unsupportedColor = allColor.getPropertyValue("--orange-400")

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbPhoneNumber = new Class({
        Extends: FbElement,

        initialize: function (element, options)
        {
            this.setPlugin('emundus_phonenumber');
            this.parent(element, options);

            this.options.countrySelected = parseInt(this.options.countrySelected) - 1;
            this.initValidatorJS();
        },

        cloned: function (c)
        {
            this.options.countrySelected = 0;

            this.initValidatorJS();
            this.parent(c);
        },

        initValidatorJS: function ()
        {
            const select = this.element.getElement("select");
            const input = this.element.getElement("input");
            const allCountries = JSON.parse(atob(select.getAttribute("data-countries"))); // decode base64 + get JSON to array type

            if (isNaN(this.options.countrySelected)) // if the default country isn't set
            {
                this.options.countrySelected = this.getCountryIndexFromIso2(allCountries, navigator.language.substring(3,navigator.language.length));
                // try to get index from country from navigator's language
                // work only language format : "en-US" so we get "US"
            }

            this.ValidatorJS = new ValidatorJS(input, select, allCountries, this.options.countrySelected);
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
