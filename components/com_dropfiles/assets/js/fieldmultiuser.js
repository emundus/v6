/**
 * Field user
 */
;(function($){
    'use strict';

    $.fieldMultipleUser = function(container, options){
        // Merge options with defaults
        this.options = $.extend({}, $.fieldMultipleUser.defaults, options);

        // Set up elements
        this.$container = $(container);
        this.$modal = this.$container.find(this.options.modal);
        this.$modalBody = this.$modal.children('.modal-body');
        this.$input = this.$container.find(this.options.input);
        this.$inputName = this.$container.find(this.options.inputName);
        this.$buttonSelect = this.$container.find(this.options.buttonSelect);

        // Bind events
        this.$buttonSelect.on('click', this.modalOpen.bind(this));
        this.$modal.on('hide', this.removeIframe.bind(this));

        // Check for onchange callback,
        var onchangeStr =  this.$input.attr('data-onchange'), onchangeCallback;
        if(onchangeStr) {
            onchangeCallback = new Function(onchangeStr);
            this.$input.on('change', onchangeCallback.bind(this.$input));
        }

    };

    // display modal for select the file
    $.fieldMultipleUser.prototype.modalOpen = function() {
        var $iframe = $('<iframe>', {
            name: 'field-multiple-user-modal',
            src: this.options.url.replace('{field-user-id}', this.$input.attr('id')),
            width: this.options.modalWidth,
            height: this.options.modalHeight
        });
        this.$modalBody.append($iframe);
        this.$modal.modal('show');
        $('body').addClass('modal-open');

        var self = this; // save context
        $iframe.load(function(){
            var content = $(this).contents();

            // handle value select - update on 07-08-2019
            content.on('change', '.user-select', function(){
                if ($(this).is(':checked')) {
                    self.setValue($(this).data('user-value'), $(this).data('user-name'));
                } else {
                    self.unsetValue($(this).data('user-value'), $(this).data('user-name'));
                }
                // self.setValue($(this).data('user-value'), $(this).data('user-name'));
                // self.modalClose();
                // $('body').removeClass('modal-open');
            });
        });
    };

    // close modal
    $.fieldMultipleUser.prototype.modalClose = function() {
        this.$modal.modal('hide');
        this.$modalBody.empty();
        $('body').removeClass('modal-open');
    };

    // close modal
    $.fieldMultipleUser.prototype.removeIframe = function() {
        this.$modalBody.empty();
        $('body').removeClass('modal-open');
    };

    // set the value
    $.fieldMultipleUser.prototype.setValue = function(value, name) {
        var oldValue = this.$input.val();
        var oldName = this.$inputName.val();
        if (oldValue === '0' || oldValue === '') {
            this.$input.val(value).trigger('change');
            this.$inputName.val(name || value).trigger('change');
        } else {
            var newValue = oldValue.split(',');
            var newName = oldName.split(',');
            newValue.push(value);
            newName.push(name);
            this.$input.val(newValue.unique().join(',')).trigger('change');
            this.$inputName.val(newName.unique().join(',')).trigger('change');
        }
        this.updateUrl();
    };

    // unset value - update on 07-08-2019
    $.fieldMultipleUser.prototype.unsetValue = function(value, name) {
        var oldValue = this.$input.val().split(',');
        var oldName = this.$inputName.val().split(',');

        if (oldValue.length === 0) {
            this.$input.val(0).trigger('change');
            this.$inputName.val('').trigger('change');
        } else {
            var newValue = $.grep(oldValue, function(item, index) {
                return item.toString() !== value.toString();
            });
            var newName = $.grep(oldName, function(item, index) {
                return item.toString() !== name.toString();
            });

            this.$input.val(newValue.unique().join(',')).trigger('change');
            this.$inputName.val(newName.unique().join(',')).trigger('change');
        }
        this.updateUrl();
    };

    $.fieldMultipleUser.prototype.updateUrl = function() {
        // Update url with current value
        var url = this.$container.data('url');
        var params = this.extractUrl(url);
        params.selected = $.base64.encode(this.$input.val());
        this.$container.data('url', 'index.php?' + $.param(params));
        this.options.url = 'index.php?' + $.param(params);
    };

    /*
     * https://www.abeautifulsite.net/parsing-urls-in-javascript
     */
    $.fieldMultipleUser.prototype.extractUrl = function (url) {
        var parser = document.createElement('a'),
            searchObject = {},
            queries, split, i;
        // Let the browser do the work
        parser.href = url;
        // Convert query string to object
        queries = parser.search.replace(/^\?/, '').split('&');
        for( i = 0; i < queries.length; i++ ) {
            split = queries[i].split('=');
            searchObject[split[0]] = split[1];
        }
        var newUrl = {
            protocol: parser.protocol,
            host: parser.host,
            hostname: parser.hostname,
            port: parser.port,
            pathname: parser.pathname,
            search: parser.search,
            searchObject: searchObject,
            hash: parser.hash
        };

        return newUrl.searchObject;
    }
    // default options
    $.fieldMultipleUser.defaults = {
        buttonSelect: '.button-select', // selector for button to change the value
        input: '.field-multiple-user-input', // selector for the input for the user id
        inputName: '.field-multiple-user-input-name', // selector for the input for the user name
        modal: '.modal', // modal selector
        url : 'index.php?option=com_users&view=users&layout=modal&tmpl=component',
        modalWidth: '100%', // modal width
        modalHeight: '300px' // modal height
    };

    $.fn.fieldMultipleUser = function(options){
        return this.each(function(){
            var $el = $(this), instance = $el.data('fieldMultipleUser');
            if(!instance){
                var options = options || {},
                    data = $el.data();

                // Check options in the element
                for (var p in data) {
                    if (data.hasOwnProperty(p)) {
                        options[p] = data[p];
                    }
                }

                instance = new $.fieldMultipleUser(this, options);
                $el.data('fieldMultipleUser', instance);
            }
        });
    };

    // Initialise all defaults on load and again when subform rows are added
    $(function($) {
        initMultipleUserField();
        $(document).on('subform-row-add', initMultipleUserField);

        function initMultipleUserField (event, container)
        {
            $(container || document).find('.field-multiple-user-wrapper').fieldMultipleUser();
        }
    });

})(jQuery);

// Compatibility with mootools modal layout
function jSelectMultiUser(element) {
    var $el = jQuery(element),
        value = $el.data('user-value'),
        name  = $el.data('user-name'),
        fieldId = $el.data('user-field'),
        $inputValue = jQuery('#' + fieldId + '_id'),
        $inputName  = jQuery('#' + fieldId);

    if (!$inputValue.length) {
        // The input not found
        return;
    }

    // Update the value
    $inputValue.val(value).trigger('change');
    $inputName.val(name || value).trigger('change');

    // Check for onchange callback,
    var onchangeStr = $inputValue.attr('data-onchange'), onchangeCallback;
    if(onchangeStr) {
        onchangeCallback = new Function(onchangeStr);
        onchangeCallback.call($inputValue[0]);
    }
    jModalClose();
}

/**
 * Array unique function from
 * Thanks to ShAkKiR from https://stackoverflow.com/a/44376705
 * @returns {Array}
 */
Array.prototype.unique = function() {
    var a = [];
    for (i = 0; i < this.length; i++) {
        var current = this[i];
        if (a.indexOf(current) < 0) a.push(current);
    }
    return a;
}
/* jQuery base64 decode/encode

 * Original code (c) 2010 Nick Galbreath
 * http://code.google.com/p/stringencoders/source/browse/#svn/trunk/javascript
 *
 * jQuery port (c) 2010 Carlo Zottmann
 * http://github.com/carlo/jquery-base64
 */
jQuery.base64=function(r){var t="=",e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";function n(r,t){var n=e.indexOf(r.charAt(t));if(-1===n)throw"Cannot decode base64";return n}function h(r,t){var e=r.charCodeAt(t);if(e>255)throw"INVALID_CHARACTER_ERR: DOM Exception 5";return e}return{decode:function(r){var e,h,a=0,c=r.length,o=[];if(r=String(r),0===c)return r;if(c%4!=0)throw"Cannot decode base64";for(r.charAt(c-1)===t&&(a=1,r.charAt(c-2)===t&&(a=2),c-=4),e=0;e<c;e+=4)h=n(r,e)<<18|n(r,e+1)<<12|n(r,e+2)<<6|n(r,e+3),o.push(String.fromCharCode(h>>16,h>>8&255,255&h));switch(a){case 1:h=n(r,e)<<18|n(r,e+1)<<12|n(r,e+2)<<6,o.push(String.fromCharCode(h>>16,h>>8&255));break;case 2:h=n(r,e)<<18|n(r,e+1)<<12,o.push(String.fromCharCode(h>>16))}return o.join("")},encode:function(r){if(1!==arguments.length)throw"SyntaxError: exactly one argument required";var n,a,c=[],o=(r=String(r)).length-r.length%3;if(0===r.length)return r;for(n=0;n<o;n+=3)a=h(r,n)<<16|h(r,n+1)<<8|h(r,n+2),c.push(e.charAt(a>>18)),c.push(e.charAt(a>>12&63)),c.push(e.charAt(a>>6&63)),c.push(e.charAt(63&a));switch(r.length-o){case 1:a=h(r,n)<<16,c.push(e.charAt(a>>18)+e.charAt(a>>12&63)+t+t);break;case 2:a=h(r,n)<<16|h(r,n+1)<<8,c.push(e.charAt(a>>18)+e.charAt(a>>12&63)+e.charAt(a>>6&63)+t)}return c.join("")},VERSION:"1.0"}}(jQuery);
