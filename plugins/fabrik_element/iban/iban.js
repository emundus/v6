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
                AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
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
                    country: 'France'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'AD',
                    lazy: false,
                    country: 'Andorre'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'AT',
                    lazy: false,
                    country: 'Autriche'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'BA',
                    lazy: false,
                    country: 'Bosnie Herzégovine'
                },
                {
                    mask: 'aa00 **** **** ****',
                    startsWith: 'BE',
                    lazy: false,
                    country: 'Belgique'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'BG',
                    lazy: false,
                    country: 'Bulgarie'
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'CH',
                    lazy: false,
                    country: 'Suisse'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'CY',
                    lazy: false,
                    country: 'Chypre'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'CZ',
                    lazy: false,
                    country: 'République thèque'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'DE',
                    lazy: false,
                    country: 'Allemagne'
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'DK',
                    lazy: false,
                    country: 'Danemark'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'EE',
                    lazy: false,
                    country: 'Estonie'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'ES',
                    lazy: false,
                    country: 'Espagne'
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'FI',
                    lazy: false,
                    country: 'Finlande'
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'FO',
                    lazy: false,
                    country: 'Îles Féroé'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'GB',
                    lazy: false,
                    country: 'Grande-Bretagne'
                },
                {
                    mask: 'aa00 **** **** **** **** ***',
                    startsWith: 'GI',
                    lazy: false,
                    country: 'Gibraltar'
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'GL',
                    lazy: false,
                    country: 'Gröenland'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'GR',
                    lazy: false,
                    country: 'Grèce'
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'HR',
                    lazy: false,
                    country: 'Croatie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'HU',
                    lazy: false,
                    country: 'Hongrie'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'IE',
                    lazy: false,
                    country: 'Irlande'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **',
                    startsWith: 'IS',
                    lazy: false,
                    country: 'Islande'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'IT',
                    lazy: false,
                    country: 'Italie'
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'LI',
                    lazy: false,
                    country: 'Liechtenstein'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'LT',
                    lazy: false,
                    country: 'Lituanie'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'LU',
                    lazy: false,
                    country: 'Luxembourg'
                },
                {
                    mask: 'aa00 **** **** **** **** *',
                    startsWith: 'LV',
                    lazy: false,
                    country: 'Lettonie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'MC',
                    lazy: false,
                    country: 'Monaco'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'MD',
                    lazy: false,
                    country: 'Moldavie'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'ME',
                    lazy: false,
                    country: 'Monténégro'
                },
                {
                    mask: 'aa00 **** **** **** ***',
                    startsWith: 'MK',
                    lazy: false,
                    country: 'Macédoine'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'MR',
                    lazy: false,
                    country: 'Mauritanie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ***',
                    startsWith: 'MT',
                    lazy: false,
                    country: 'Malte'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** **',
                    startsWith: 'MU',
                    lazy: false,
                    country: 'Île Maurice'
                },
                {
                    mask: 'aa00 **** **** **** **',
                    startsWith: 'NL',
                    lazy: false,
                    country: 'Pays-Bas'
                },
                {
                    mask: 'aa00 **** **** ***',
                    startsWith: 'NO',
                    lazy: false,
                    country: 'Norvège'
                },
                {
                    mask: 'aa00 **** **** ***',
                    startsWith: 'NO',
                    lazy: false,
                    country: 'Norvège'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'PK',
                    lazy: false,
                    country: 'Pakistan'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'PL',
                    lazy: false,
                    country: 'Pologne'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'PS',
                    lazy: false,
                    country: 'Palestine'
                },
                {
                    mask: 'aa00 **** **** **** **** **** *',
                    startsWith: 'PT',
                    lazy: false,
                    country: 'Portugal'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'QA',
                    lazy: false,
                    country: 'Qatar'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'RO',
                    lazy: false,
                    country: 'Roumanie'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'RS',
                    lazy: false,
                    country: 'Serbie'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SA',
                    lazy: false,
                    country: 'Arabie Saoudite'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SE',
                    lazy: false,
                    country: 'Suède'
                },
                {
                    mask: 'aa00 **** **** **** ***',
                    startsWith: 'SI',
                    lazy: false,
                    country: 'Slovénie'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'SK',
                    lazy: false,
                    country: 'Slovaquie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ***',
                    startsWith: 'SM',
                    lazy: false,
                    country: 'Saint-Marin'
                },
                {
                    mask: 'aa00 **** **** **** **** ****',
                    startsWith: 'TN',
                    lazy: false,
                    country: 'Tunisie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **',
                    startsWith: 'TR',
                    lazy: false,
                    country: 'Turquie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'AL',
                    lazy: false,
                    country: 'Albanie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** ****',
                    startsWith: 'BY',
                    lazy: false,
                    country: 'Biélorussie'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'CR',
                    lazy: false,
                    country: 'Costa Rica'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'EG',
                    lazy: false,
                    country: 'Égypte'
                },
                {
                    mask: 'aa00 **** **** **** **** **',
                    startsWith: 'GE',
                    lazy: false,
                    country: 'Géorgie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ****',
                    startsWith: 'LC',
                    lazy: false,
                    country: 'Sainte-Lucie'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** ***',
                    startsWith: 'SC',
                    lazy: false,
                    country: 'Seychelles'
                },
                {
                    mask: 'aa00 **** **** **** **** **** **** *',
                    startsWith: 'UA',
                    lazy: false,
                    country: 'Ukraine'
                },
                {
                    mask: 'aa00 **** **** **** ****',
                    startsWith: 'XK',
                    lazy: false,
                    country: 'Kosovo'
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
