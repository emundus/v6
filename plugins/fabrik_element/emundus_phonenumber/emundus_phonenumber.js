

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
            this.options.countrySelected = this.ValidatorJS.indiceCountry;

            this.initValidatorJS();
            this.parent(c);
        },

        initValidatorJS: function ()
        {
            const select = this.element.getElement("select");
            const input = this.element.getElement("input");
            const allCountry = JSON.parse(atob(select.getAttribute("data-countries"))); // decode base64 + get JSON to array type

            !isNaN(this.options.countrySelected) ? this.ValidatorJS = new ValidatorJS(input, select, allCountry, this.options.countrySelected) : this.ValidatorJS = new ValidatorJS(input, select, allCountry);
            this.ValidatorJS.setColors(validColor, errorColor, unsupportedColor, defaultColor);
        }
    });
    return window.FbPhoneNumber;
});
