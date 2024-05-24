define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbEmundusIban = new Class({
        Extends: FbElement,

        initialize: function (element, options) {
            this.setPlugin('iban');
            this.parent(element, options);

            this.addMask();
            this.initEvents();
        },

        cloned: function (c) {
            this.mask = null;
            this.addMask();
            this.initEvents();

            this.parent(c);
        },

        initEvents: function () {
            jQuery(this.element).on('change', () => {
                if (this.element.get('value') === '') {
                    this.setError('');
                    return;
                }

                var is_valid = this.isValidIBANNumber();
                if (!is_valid) {
                    this.setError(Joomla.JText._('PLG_ELEMENT_IBAN_INVALID'));
                } else {
                    this.setError('');
                }
            });
        },

        addMask: function () {
            if (this.mask) {
                this.mask.destroy();
            }

            this.mask = IMask(
                this.element,
                {
                    mask: this.initDynamicMask(),
                    dispatch: (appended, dynamicMasked) => {
                        const number = (dynamicMasked.value + appended);
                        const mask = dynamicMasked.compiledMasks.find(m => number.indexOf(m.startsWith) === 0)

                        if(mask) {
                            this.element.parentNode.getElementsByClassName('localization-block')[0].getElementsByClassName('localization')[0].innerHTML = mask.country;
                        } else {
                            this.element.parentNode.getElementsByClassName('localization-block')[0].getElementsByClassName('localization')[0].innerHTML = '';
                        }

                        return mask;
                    }
                }
            );
        },

        isValidIBANNumber: function () {
            var input = this.element.get('value');

            var CODE_LENGTHS = {
                AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BJ: 28, BR: 29,
                CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
                FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
                HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
                LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
                MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
                RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26,
                AL: 28, BY: 28, CR: 22, EG: 29, GE: 22, IQ: 23, LC: 32, SC: 31, ST: 25,
                SV: 28, TL: 23, UA: 29, VA: 22, VG: 24, XK: 20
            };
            var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''), // keep only alphanumeric characters
                code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/), // match and capture (1) the country code, (2) the check digits, and (3) the rest
                digits;
            // check syntax and length
            if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
                return false;
            }
            // rearrange country code and check digits, and convert chars to ints
            digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
                return letter.charCodeAt(0) - 55;
            });

            // final check
            return this.mod97(digits) === 1;
        },

        mod97: function (string) {
            var checksum = string.slice(0, 2), fragment;
            for (var offset = 2; offset < string.length; offset += 7) {
                fragment = String(checksum) + string.substring(offset, offset + 7);
                checksum = parseInt(fragment, 10) % 97;
            }
            return checksum;
        },

        setError: function (msg) {
            var parentofSelected = this.element.parentNode.parentNode.parentNode; // gives the parent DIV

            var children = parentofSelected.childNodes;
            for (var i = 0; i < children.length; i++) {
                if (children[i].classList) {
                    if (children[i].classList.contains('fabrikErrorMessage')) {
                        children[i].innerHTML = msg;
                        break;
                    }
                }
            }
        },

        initDynamicMask: function () {
            return [
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'FR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_FRANCE')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'AD',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ANDORRA')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'AT',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_AUSTRIA')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'BA',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_BOSNIE_HERZEGOVINE')
                },
                {
                    mask: 'aa00 **** **** ****',
                    startsWith: 'BE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_BELGIUM')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'BG',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_BULGARIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'BJ',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_BENIN')
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'CH',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SWITZERLAND')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'CY',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_CYPRUS')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'CZ',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_CZECH_REPUBLIC')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'DE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_GERMANY')
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'DK',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_DENMARK')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'EE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ESTONIA')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'ES',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SPAIN')
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'FI',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_FINLAND')
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'FO',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_FEROE_ISLANDS')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'GB',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_UNITED_KINGDOM')
                },
                {
                    mask: 'aa00 **** **** **** **** ***',
                    startsWith: 'GI',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_GIBRALTAR')
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'GL',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_GREENLAND')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'GR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_GREECE')
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'HR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_CROATIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'HU',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_HUNGARY')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'IE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_IRELAND')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **',
                    startsWith: 'IS',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ICELAND')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'IT',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ITALY')
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'LI',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_LIECHTENSTEIN')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'LT',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_LITHUANIA')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'LU',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_LUXEMBOURG')
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'LV',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_LATVIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'MC',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MONACO')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'MD',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_REPUBLIC_OF_MOLDOVA')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'ME',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MONTENEGRO')
                },
                {
                    mask: 'aa00 **** **** **** ***',
                    startsWith: 'MK',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MACEDONIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'MR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MOROCCO')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ***',
                    startsWith: 'MT',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MALTA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** **',
                    startsWith: 'MU',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_MAURITIUS')
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'NL',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_NETHERLANDS')
                },
                {
                    mask: 'aa00 **** **** ***',
                    startsWith: 'NO',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_NORWAY')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'PK',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_PAKISTAN')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'PL',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_POLAND')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'PS',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_PALESTINE')
                },
                {
                    mask: 'aa00 **** **** **** **** **** *',
                    startsWith: 'PT',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_PORTUGAL')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'QA',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_QATAR')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'RO',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ROMANIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'RS',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SERBIA')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SA',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SAUDI_ARABIA')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SWEDEN')
                },
                {
                    mask: 'aa00 **** **** **** ***',
                    startsWith: 'SI',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SLOVENIA')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SK',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SLOVAKIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'SM',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SAN_MARINO')
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'TN',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_TUNISIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **',
                    startsWith: 'TR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_TURKEY')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'AL',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_ALBANIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'BY',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_BELARUS')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'CR',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_COSTA_RICA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'EG',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_EGYPT')
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'GE',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_GEORGIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ****',
                    startsWith: 'LC',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SAINT_LUCIA')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ***',
                    startsWith: 'SC',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_SEYCHELLES')
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'UA',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_UKRAINE')
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'XK',
                    lazy: false,
                    country: Joomla.JText._('PLG_ELEMENT_IBAN_KOSOVO')
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: '',
                    country: ''
                }
            ]
        },
    });

    return window.FbEmundusIban;
});
