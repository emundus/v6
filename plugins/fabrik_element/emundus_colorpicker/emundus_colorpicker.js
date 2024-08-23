define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbEmundusColorpicker = new Class({
        Extends: FbElement,

        initialize: function (element, options) {
            this.setPlugin('emundus_colorpicker');
            this.parent(element, options);

            var swatches = document.getElementsByClassName('js-color-swatches');
            if( swatches.length > 0 ) {
                for( var i = 0; i < swatches.length; i++) {
                    new ColorSwatches(swatches[i]);
                }
            }
        },
    });

    return window.FbEmundusColorpicker;
});
