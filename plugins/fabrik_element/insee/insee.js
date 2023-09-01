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
                baseUrl: '',
                propertyToCheck: ''
            },

            initialize: function (element, options) {
                this.setPlugin('fabrikinsee');
                this.parent(element, options);

                const htmlElement = document.querySelector('#' + this.element.id);
                htmlElement.addEventListener('paste', this.handlerPaste.bind(this));
                htmlElement.addEventListener('keyup', this.handlerKeyup.bind(this));

                if (this.options.bearerToken.status === 200) {
                    htmlElement.addEventListener('focusout', this.handlerFocusOut.bind(this));
                }

                if (this.options.bearerToken.status !== 200) {
                    console.log(this.options.bearerToken.message);
                }

                this.options.mapping = Object.entries(this.options.mapping);

                let value = document.querySelector('#' + this.element.id + ' input').value;
                if (value !== '') {
                    this.callInsee(value);
                }
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

            handlerPaste: function (event) {
                event.preventDefault();

                let value = event.clipboardData.getData('Text');
                event.target.value = value.replace(/\s/g, '');
            },

            handlerKeyup: function (event) {
                event.target.value = event.target.value.replace(/\s/g, '');
            },

            handlerFocusOut: function (event) {
                if (event.target.value !== '') {
                    this.callInsee(event.target.value);
                } else {
                    this.resetFields();
                }
            },

            resetFields: function () {
                let repeatNum = this.getRepeatNum();

                this.options.mapping.forEach((item) => {
                    let item_to_reset = item[1].insee_fabrik_element;
                    if (repeatNum !== false) {
                        item_to_reset = item_to_reset + '_' + repeatNum;
                    }

                    let element_to_reset = this.form.elements.get(item_to_reset);
                    element_to_reset.set('');
                });
            },

            callInsee: function (value) {
                let repeatNum = this.getRepeatNum();
                const divError = this.element.parentNode.parentNode.getElementsByClassName('fabrikErrorMessage')[0];
                divError.innerHTML = '';

                fetch(this.options.baseUrl + '/entreprises/sirene/V3/' + this.options.propertyToCheck + '/' + value, {
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
                            const properties = data.etablissement;

                            this.options.mapping.forEach((item) => {
                                let data_to_insert = [];

                                let attributes_to_search = item[1].insee_property;
                                attributes_to_search = attributes_to_search.split(';');

                                // We loop on each attribute to search
                                attributes_to_search.forEach((attribute_to_search) => {
                                    attribute_to_search = attribute_to_search.split(':');

                                    let property = properties[attribute_to_search[0]];
                                    if (attribute_to_search.length > 1) {
                                        property = property[attribute_to_search[1]];
                                    }

                                    // If the property is undefined it's a separator
                                    if (property === undefined) {
                                        data_to_insert.push(attribute_to_search[0]);
                                    } else {
                                        data_to_insert.push(property);
                                    }
                                });

                                    // We search the element to fill
                                    let item_to_fill = item[1].insee_fabrik_element;
                                    if (repeatNum !== false) {
                                        item_to_fill = item_to_fill + '_' + repeatNum;
                                    }

                                    // We get the element from Fabrik
                                    let element_to_fill = this.form.elements.get(item_to_fill);

                                // We prepare the data to prefill the field
                                let empty_datas = data_to_insert.every(element => element == null)

                                if (data_to_insert.length > 0 && !data_to_insert.includes('[ND]') && !empty_datas) {
                                    // We check if the element is a date or a birthday
                                    if (element_to_fill.plugin === 'birthday' || item[1].insee_property_type === 'date') {
                                        let date = new Date(data_to_insert.join(''));
                                        data_to_insert = [];

                                        if (item[1].insee_property_type === 'date') {
                                            let date_format = item[1].insee_property_date_format.split('/');

                                            date_format.forEach((format) => {
                                                switch (format) {
                                                    case 'd':
                                                        if (date.getDate() < 10) {
                                                            data_to_insert.push('0' + date.getDate());
                                                        } else {
                                                            data_to_insert.push(date.getDate());
                                                        }
                                                        break;
                                                    case 'm':
                                                        if ((date.getMonth() + 1) < 10) {
                                                            data_to_insert.push('0' + (date.getMonth() + 1));
                                                        } else {
                                                            data_to_insert.push(date.getMonth() + 1);
                                                        }
                                                        break;
                                                    case 'Y':
                                                        data_to_insert.push(date.getFullYear());
                                                        break;
                                                }
                                            });
                                        } else {
                                            if (date.getDate() < 10) {
                                                data_to_insert.push('0' + date.getDate());
                                            } else {
                                                data_to_insert.push(date.getDate());
                                            }

                                            if ((date.getMonth() + 1) < 10) {
                                                data_to_insert.push('0' + (date.getMonth() + 1));
                                            } else {
                                                data_to_insert.push(date.getMonth() + 1);
                                            }

                                            data_to_insert.push(date.getFullYear());
                                        }

                                        data_to_insert = data_to_insert.join('/');

                                        element_to_fill.set(data_to_insert);
                                    } else if (item[1].insee_property_type === 'tva') {
                                        let tva_number = '';
                                        let code_pays = 'FR';
                                        let siren = parseInt(properties['siren']);
                                        if (properties['adresseEtablissement']['codePaysEtrangerEtablissement'] !== null) {
                                            code_pays = properties['adresseEtablissement']['codePaysEtrangerEtablissement'];
                                        }

                                        if (code_pays !== '' && siren !== 0) {
                                            let tva_key = [12 + 3 * (siren % 97)] % 97;

                                            if (tva_key !== 0) {
                                                tva_number = code_pays + tva_key + siren;
                                            }
                                        }

                                        element_to_fill.set(tva_number);
                                    } else {
                                        element_to_fill.set(data_to_insert.join(''));
                                    }
                                } else {
                                    let htmlElement = document.querySelector('#' + element_to_fill.baseElementId);
                                    if(htmlElement.hasAttribute('readonly')){
                                        htmlElement.removeAttribute('readonly')
                                        htmlElement.classList.remove('readonly')
                                    }
                                }
                            });

                            break;
                        case 404:
                            divError.innerHTML = Joomla.JText._('PLG_ELEMENT_INSEE_SIRET_NOT_FOUND');
                            this.resetFields();

                            break;
                        case 400:
                            divError.innerHTML = data.header.message;
                            this.resetFields();

                            break;
                        default:
                            divError.innerHTML = Joomla.JText._('PLG_ELEMENT_INSEE_ERROR');
                    }
                });
            },
        });

        return window.FbInsee;
    });