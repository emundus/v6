/**
 * Field Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Wrap in require js to ensure we always load the same version of jQuery
// Multiple instances can be loaded an ajax pages are added and removed. However we always want
// to get the same version as plugins are only assigned to this jQuery instance
define(['jquery', 'fab/element'],
    function (jQuery, FbElement) {

        window.FbInsee = new Class({
            Extends: FbElement,

            options: {
                bearerToken: {},
                mapping: [],
                baseUrl: ''
            },

            initialize: function (element, options) {
                this.setPlugin('fabrikinsee');
                this.parent(element, options);

                const htmlElement = document.querySelector('#' + this.element.id);

                if (this.options.bearerToken.status === 200) {
                    htmlElement.addEventListener('focusout', this.handlerFocusOut.bind(this));
                }

                if (this.options.bearerToken.status !== 200) {
                    console.log(this.options.bearerToken.message);
                }

                this.options.mapping = Object.entries(this.options.mapping);
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
                var element = this.getElement();

                this.parent(c);

                const htmlElement = document.querySelector('#' + this.element.id);

                if (this.options.bearerToken.status === 200) {
                    htmlElement.addEventListener('focusout', this.handlerFocusOut.bind(this));
                }
            },

            handlerFocusOut: function (event) {
                let repeatNum = this.getRepeatNum();
                const divError = this.element.parentNode.parentNode.getElementsByClassName('fabrikErrorMessage')[0];

                if(event.target.value !== '') {
                    fetch(this.options.baseUrl + '/entreprises/sirene/V3/siret?q=siret:' + event.target.value, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': this.options.bearerToken.data
                        }
                    }).then((response) => {
                        return response.json();
                    }).then((data) => {

                        switch (data.header.statut) {
                            case 200:
                                // Populate the fields with the data
                                const properties = data.etablissements[0];

                                this.options.mapping.forEach((item) => {
                                    let data_to_insert = [];
                                    let attributes_to_search = item[1].insee_property;
                                    attributes_to_search = attributes_to_search.split(',');
                                    attributes_to_search.forEach((attribute_to_search) => {
                                        attribute_to_search = attribute_to_search.split(':');

                                        if (attribute_to_search.length > 1) {
                                            data_to_insert.push(properties[attribute_to_search[0]][attribute_to_search[1]]);
                                        } else {
                                            data_to_insert.push(properties[attribute_to_search[0]]);
                                        }
                                    });

                                    if (data_to_insert !== '' && data_to_insert.length > 0) {
                                        let item_to_fill = item[1].insee_fabrik_element;
                                        if(repeatNum !== false) {
                                            item_to_fill = item_to_fill + '_' + repeatNum;
                                        }
                                        console.log(item_to_fill);
                                        this.form.elements.get(item_to_fill).set(data_to_insert.join(' '));
                                    }
                                });

                                break;
                            case 404:
                                divError.innerHTML = Joomla.JText._('PLG_ELEMENT_INSEE_SIRET_NOT_FOUND');

                                break;
                            default:
                                divError.innerHTML = Joomla.JText._('PLG_ELEMENT_INSEE_ERROR');
                        }
                    });
                }
            }

        });

        return window.FbInsee;
    });