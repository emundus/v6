/**
 * Panel Element
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbPanel = new Class({
        Extends   : FbElement,
        initialize: function (element, options) {
            this.setPlugin('panel');
            this.parent(element, options);
        },

        update: function (val) {
            if (this.getElement()) {
                let valueElt = this.element.querySelector('span[id*="-value"]');
                if (valueElt) {
                    valueElt.innerHTML = val;
                }
            }
        },

        cloneUpdateIds: function (id) {
            this.element = document.id(id);
            this.options.element = id;

            let contentElt = document.querySelector('#' + id + ' div[id*="-content"]');
            if (contentElt) {
                contentElt.id = id + '-content';
            }

            let valueElt = document.querySelector('#' + id + ' span[id*="-value"]');
            if (valueElt) {
                valueElt.id = id + '-value';
            }
        },
    });

    return window.FbPanel;
});