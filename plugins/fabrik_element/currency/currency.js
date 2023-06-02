
const currency_hoverColor = allColor.getPropertyValue('--neutral-600');
const currency_defaultColor = allColor.getPropertyValue('--neutral-400');
const currency_focusColor = allColor.getPropertyValue('--blue-500');

define(['jquery', 'fab/element'],
    function (jQuery, FbElement) {

    window.FbCurrency = new Class({
        Extends: FbElement,

        initialize: function (element, options)
        {

            this.setPlugin('currency');
            this.parent(element, options);

            this.HTMLInputElement = this.element.getElementById('currency_inputValue');
            this.HTMLRowInputElement = this.element.getElementById('currency_rowInputValue');
            this.HTMLSelectElement = this.element.getElementById('currency_selectValue');

            this.idSelectedCurrency = this.options.idSelectedCurrency ? this.options.idSelectedCurrency : 0;
            this.allSelectedCurrencies = this.options.selectedCurrencies;

            this.initSelect();
            this.addMask();
            this.initDivEvent();
        },

        cloned: function (c)
        {
            this.HTMLInputElement = this.element.getElementById('currency_inputValue');
            this.HTMLRowInputElement = this.element.getElementById('currency_rowInputValue');
            this.HTMLSelectElement = this.element.getElementById('currency_selectValue');

            this.HTMLInputElement.value = null;
            this.mask = null;
            this.initSelect();
            this.addMask();

            this.parent(c);
        },

        initDivEvent: function()
        {
            this.element.addEventListener('mouseenter', this.mouseenterDivHandler.bind(this));
            this.element.addEventListener('mouseleave', this.mouseleaveDivHandler.bind(this));
            this.element.addEventListener('focusin', this.focusInDivHandler.bind(this));
            this.element.addEventListener('focusout', this.focusOutDivHandler.bind(this));
        },

        mouseenterDivHandler: function()
        {
            this.HTMLInputElement.style.borderColor = currency_hoverColor;
            this.HTMLSelectElement.style.borderColor = currency_hoverColor;
        },

        mouseleaveDivHandler: function()
        {
            this.HTMLInputElement.style.borderColor = currency_defaultColor;
            this.HTMLSelectElement.style.borderColor = currency_defaultColor;
        },

        focusInDivHandler: function()
        {
            this.HTMLInputElement.style.borderColor = currency_focusColor;
            this.HTMLSelectElement.style.borderColor = currency_focusColor;
        },

        focusOutDivHandler: function ()
        {
            this.HTMLInputElement.style.borderColor = currency_defaultColor;
            this.HTMLSelectElement.style.borderColor = currency_defaultColor;
        },

        initSelect: function()
        {
            this.HTMLSelectElement.options.length === 1 ? this.HTMLSelectElement.setAttribute('tabindex', -1) : null;
            this.HTMLSelectElement.addEventListener('change', this.handlerSelectChange.bind(this));
        },

        handlerSelectChange: function(e)
        {
            this.HTMLInputElement.value = null;
            this.idSelectedCurrency = e.target.selectedIndex;
            this.addMask();
        },

        addMask: function ()
        {
            if(this.mask) {
                this.mask.destroy();
            }

            this.mask = IMask(
                this.HTMLInputElement,
                {
                    mask: Number,
                    // other options are optional with defaults below
                    scale: this.allSelectedCurrencies[this.idSelectedCurrency].decimal_numbers,  // digits after point, 0 for integers
                    signed: false,  // disallow negative
                    thousandsSeparator: this.allSelectedCurrencies[this.idSelectedCurrency].thousand_separator,
                    padFractionalZeros: false,  // if true, then pads zeros at end to the length of scale
                    normalizeZeros: true,  // appends or removes zeros at ends
                    radix: this.allSelectedCurrencies[this.idSelectedCurrency].decimal_separator,  // fractional delimiter

                    // additional number interval options (e.g.)
                    min: this.allSelectedCurrencies[this.idSelectedCurrency].minimal_value,
                    max: this.allSelectedCurrencies[this.idSelectedCurrency].maximal_value,
                });
        },

        onsubmit: function (c)
        {
            this.HTMLRowInputElement.value = this.mask.unmaskedValue === ''
                ? this.mask.unmaskedValue
                : this.mask.unmaskedValue + ' '+ this.HTMLSelectElement.value;

            this.parent(c);
        }
    });

    return window.FbCurrency;
});
