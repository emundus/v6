

define(['jquery', 'fab/element'],
    function (jQuery, FbElement) {

    window.FbCurrency = new Class({
        Extends: FbElement,

        initialize: function (element, options)
        {

            this.setPlugin('currency');
            this.parent(element, options);

            this.input = this.element.getElementById('currency_inputValue');
            this.select = this.element.getElementById('currency_selectValue');

            this.initSelect(this.select);
            this.initMask(this.input);

        },

        cloned: function (c)
        {
            this.input = this.element.getElementById('currency_inputValue');
            this.select = this.element.getElementById('currency_selectValue');

            this.initSelect(this.select);
            this.initMask(this.input);

            this.parent(c);
        },


        initSelect: function(selectElement)
        {
            const selectedIso3 = this.options.selectedIso3

            this.options.allCurrency.forEach((currency) =>
            {
                if (currency.iso3 === selectedIso3)
                {
                    const option = document.createElement('option');
                    option.value = currency.iso3;
                    option.text = currency.symbol + ' ('+currency.iso3+')';
                    option.selected = true;
                    selectElement.add(option);
                }
            });
        },

        initMask: function (inputElement)
        {

            if(this.mask) {
                this.mask.destroy();
            }

            this.mask = IMask(
                inputElement,
                {
                    mask: Number,
                    // other options are optional with defaults below
                    scale: 2,  // digits after point, 0 for integers
                    signed: false,  // disallow negative
                    thousandsSeparator: this.options.thousand_separator,  // any single char
                    padFractionalZeros: false,  // if true, then pads zeros at end to the length of scale
                    normalizeZeros: true,  // appends or removes zeros at ends
                    radix: this.options.decimal_separator,  // fractional delimiter

                    // additional number interval options (e.g.)
                    min: this.options.min_value,
                    max: this.options.max_value,
                });
        },

        onsubmit: function (c)
        {
            this.input.value = this.mask.unmaskedValue;
            this.parent(c);
        }

    });

    return window.FbCurrency;
});
