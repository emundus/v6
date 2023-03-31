/**
 * Radio Button Element
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/elementlist'], function (jQuery, FbElementList) {
    window.FbRadio = new Class({
        Extends: FbElementList,

        options: {
            btnGroup: true
        },

        type: 'radio', // sub element type

        initialize: function (element, options) {
            this.setPlugin('fabrikradiobutton');
            this.parent(element, options);
            this.btnGroup();
        },

        btnGroup: function () {
            // Seems slightly screwy in admin as the j template does the same code
            if (!this.options.btnGroup) {
                return;
            }
            // Turn radios into btn-group
            this.btnGroupRelay();

            var c = this.getContainer();
            if (!c) {
                return;
            }
            c.getElements('.radio.btn-group label').addClass('btn');


            c.getElements(".btn-group input[checked]").each(function (input) {
                var label = input.getParent('label'), v;
                if (typeOf(label) === 'null') {
                    // J3.2 button group markup - label is after input (no longer the case)
                    label = input.getNext();
                }
                v = input.get('value');
                if (v === '') {
                    label.addClass('active btn-primary');
                } else if (v === '0') {
                    label.addClass('active btn-danger');
                } else {
                    label.addClass('active btn-success');
                }
            });
        },

        btnGroupRelay: function () {
            var c = this.getContainer();
            if (!c) {
                return;
            }
            c.getElements('.radio.btn-group label').addClass('btn');
            c.addEvent('click:relay(.btn-group label)', function (e, label) {
                var id = label.get('for'), input;
                if (id !== '') {
                    input = document.id(id);
                }
                if (typeOf(input) === 'null') {
                    input = label.getElement('input');
                }
                this.setButtonGroupCSS(input);
            }.bind(this));
        },

        setButtonGroupCSS: function (input) {
            var label;
            if (input.id !== '') {
                label = document.getElement('label[for=' + input.id + ']');
            }
            if (typeOf(label) === 'null') {
                label = input.getParent('label.btn');
            }
            var v = input.get('value');
            var fabchecked = parseInt(input.get('fabchecked'), 10);

            // Protostar in J3.2 adds its own btn-group js code -
            // need to thus apply this section even after input has been unchecked
            if (!input.get('checked') || fabchecked === 1) {
                if (label) {
                    label.getParent('.btn-group').getElements('label').removeClass('active').removeClass('btn-success')
                        .removeClass('btn-danger').removeClass('btn-primary');
                    if (v === '') {
                        label.addClass('active btn-primary');
                    } else if (v.toInt() === 0) {
                        label.addClass('active btn-danger');
                    } else {
                        label.addClass('active btn-success');
                    }
                }
                input.set('checked', true);

                if (typeOf(fabchecked) === 'null') {
                    input.set('fabchecked', 1);
                }
            }
        },

        watchAddToggle: function () {
            var c = this.getContainer();
            var d = c.getElement('div.addoption');
            var a = c.getElement('.toggle-addoption');
            if (this.mySlider) {
                // Copied in repeating group so need to remove old slider html first
                var clone = d.clone();
                var fe = c.getElement('.fabrikElement');
                d.getParent().destroy();
                fe.adopt(clone);
                d = c.getElement('div.addoption');
                d.setStyle('margin', 0);
            }
            this.mySlider = new Fx.Slide(d, {
                duration: 500
            });
            this.mySlider.hide();
            a.addEvent('click', function (e) {
                e.stop();
                this.mySlider.toggle();
            }.bind(this));
        },

        getValue: function () {
            if (!this.options.editable) {
                return this.options.value;
            }
            var v = '';
            this._getSubElements().each(function (sub) {
                if (sub.checked) {
                    v = sub.get('value');
                    return v;
                }
                return null;
            });
            return v;
        },

        setValue: function (v) {
            if (!this.options.editable) {
                return;
            }
            this._getSubElements().each(function (sub) {
                if (sub.value === v) {
                    sub.set('checked', true);
                }
                else {
                    sub.set('checked', false);
                }
            });
        },

        update: function (val) {
            if (typeOf(val) === 'array')
            {
                val = val.shift();
            }
            this.setValue(val);
            if (!this.options.editable) {
                if (val === '') {
                    this.element.innerHTML = '';
                    return;
                }
                this.element.innerHTML = $H(this.options.data).get(val);
                return;
            } else {
                if (this.options.btnGroup) {
                    var els = this._getSubElements();
                    els.each(function (el) {
                        if (el.value === val) {
                            this.setButtonGroupCSS(el);
                        }
                    }.bind(this));
                }
            }
        },

        cloned: function (c) {
            if (this.options.allowadd === true && this.options.editable !== false) {
                this.watchAddToggle();
                this.watchAdd();
            }
            this._getSubElements().each(function (sub, i) {
                sub.id = this.options.element + '_input_' + i;
                var label = sub.getParent('label');
                if (label) {
                    label.htmlFor = sub.id;
                }
            }.bind(this));
            this.parent(c);
            this.btnGroup();
        },

        getChangeEvent: function () {
            return this.options.changeEvent;
        },

        /**
         * Get the dom selector that events should be attached to, need to include label for button groups
         * (don't think we need this after changing the grid layout to include 'for')
         * @returns {string}
         */
        eventDelegate: function () {
            var str = 'input[type=' + this.type + '][name^=' + this.options.fullName + ']';
            str += ', [class*=fb_el_' + this.options.fullName + '] .fabrikElement label';

            return str;
        },

        setName: function (repeatCount) {
            var element = this.getElement();
            if (typeOf(element) === 'null') {
                return false;
            }

            this._getSubElements().each(function (e) {
                e.name = this._setName(e.name, repeatCount);
                e.id = this._setId(e.id, repeatCount, '_input_\\d+');
                var label = e.getParent('label');
                if (label) {
                    label.htmlFor = e.id;
                }
            }.bind(this));

            if (typeOf(this.element.id) !== 'null') {
                this.element.id = this._setId(this.element.id, repeatCount);
            }
            this.options.repeatCounter = repeatCount;
            return this.element.id;
        }

    });

    return window.FbRadio;
});
