

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
        },

        cloned: function (c)
        {
            this.HTMLInputElement = this.element.getElementById('currency_inputValue');
            this.HTMLRowInputElement = this.element.getElementById('currency_rowInputValue');
            this.HTMLSelectElement = this.element.getElementById('currency_selectValue');

            this.HTMLInputElement.removeAttribute('value');
            this.mask = null;
            this.initSelect();
            this.addMask();

            this.parent(c);
        },

        initSelect: function()
        {
            if (this.HTMLSelectElement.options.length === 1)
            {
                this.HTMLSelectElement.setAttribute('tabindex', -1)
                this.HTMLSelectElement.style.pointerEvents = 'none';
                this.HTMLSelectElement.style.backgroundImage = 'none';
                this.HTMLSelectElement.style.textAlign = 'end';

                this.changeElement(this.HTMLSelectElement.options[0]);
            }
            else
            {
                Fabrik.buildChosen(this.HTMLSelectElement, {
                    disable_search_threshold: 5,
                    allow_single_deselect: true,
                    search_contains: true,
                });

                const element = document.querySelector('#' + this.element.id + ' .chzn-single span');
                this.changeElement(element);

                jQuery(this.HTMLSelectElement).on('change', () => { // sadly mandatory
                    this.handlerSelectChange();
                });
            }
        },

        handlerSelectChange: function()
        {
            const oldIdSelectedCurrency = this.idSelectedCurrency;
            const newIdSelectedCurrency = this.idSelectedCurrency = this.HTMLSelectElement.selectedIndex;

            const newInputValue = this.getNewInputValue(this.HTMLInputElement.value, oldIdSelectedCurrency, newIdSelectedCurrency);
            this.addMask();
            this.mask.value = newInputValue;

            const element = document.querySelector('#'+this.element.id+' .chzn-single span');
            this.changeElement(element);
        },

        changeElement: function(element)
        {
            const select = this.HTMLSelectElement;
            const val = select.value;
            const symbol = this.getSymbolFromIso3(val);
            const displayiso3 =this.element.getElementById('currency_displayiso3').value;
            if(displayiso3 == 1) {
                element.textContent = symbol + ' (' + val + ')';
            } else {
                element.textContent = symbol;
            }
        },

        getNewInputValue: function(oldInput, oldIdSelectedCurrency, newIdSelectedCurrency)
        {
            const oldDecimalSep = this.allSelectedCurrencies[oldIdSelectedCurrency].decimal_separator;  // fractional delimiter
            const oldThousandSep =this.allSelectedCurrencies[oldIdSelectedCurrency].thousand_separator;

            const newDecimalSep = this.allSelectedCurrencies[newIdSelectedCurrency].decimal_separator;  // fractional delimiter
            const newThousandSep = this.allSelectedCurrencies[newIdSelectedCurrency].thousand_separator;

            return oldInput.replace(oldDecimalSep, newDecimalSep).replace(oldThousandSep, newThousandSep);
        },

        getSymbolFromIso3: function(iso3)
        {
            let symbol;
            for(const currency of this.options.allCurrency)
            {
                if (currency.iso3 === iso3)
                {
                    symbol = currency.symbol;
                }
            }
            return symbol;
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

        update: function(e)
        {
            if (typeOf(this.element) === 'null') {
                return;
            }
            this.setValue(e);
        },

        getValue: function ()
        {
            return this.mask.unmaskedValue;
        },

        setValue: function (val)
        {
            const decimalSep = this.allSelectedCurrencies[this.idSelectedCurrency].decimal_separator;
            this.mask.value = val.toString().replace('.', decimalSep);
        },


        /**
         * When a form/details view is updating its own data, then should we use the raw data or the html?
         * Raw is used for cdd/db join elements
         *
         * @returns {boolean}
         */
        updateUsingRaw: function () {
            return true;
        },

        onsubmit: function (c)
        {
            this.HTMLRowInputElement.value = this.mask.unmaskedValue;
            this.parent(c);
        }
    });

    return window.FbCurrency;
});
