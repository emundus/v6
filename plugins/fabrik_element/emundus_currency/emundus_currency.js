

define(['jquery', 'fab/element', 'components/com_fabrik/libs/masked_input/jquery.maskedinput'],
    function (jQuery, FbElement, Mask) {

    window.FbCurrency = new Class({
        Extends: FbElement,

        options: {
            use_input_mask         : false,
            input_mask_definitions : '',
            input_mask_autoclear   : false,
            geocomplete            : false,
            mapKey                 : false,
            language               : ''
        },

        initialize: function (element, options) {

            this.parent(element, options);
        },

        select: function () {
            var element = this.getElement();
            if (element) {
                this.getElement().select();
            }
        },

        focus: function () {
            var element = this.getElement();
            if (element) {
                this.getElement().focus();
            }
            this.parent();
        },

        cloned: function (c) {
            this.parent(c);
        }

    });

    return window.FbCurrency;
});
