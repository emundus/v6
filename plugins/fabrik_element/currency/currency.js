

define(['jquery', 'fab/element'],
    function (jQuery, FbElement) {

    window.FbCurrency = new Class({
        Extends: FbElement,

        initialize: function (element, options) {

            this.setPlugin('currency');
            this.parent(element, options);
        },

        cloned: function (c) {
            this.parent(c);
        }

    });

    return window.FbCurrency;
});
