
const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-400");
const unsupportedColor = allColor.getPropertyValue("--orange-400")

class ValidatorJS {

    constructor(initDiv, initAllCountry, initIndiceCountry = 0, initDefaultValue = '', cloned = false)
    {
        this.select = initDiv.getElementById('countrySelect');
        this.select_chosen = '#'+initDiv.id+' #countrySelect';
        this.select_chosen_block = '#'+initDiv.id+' #countrySelect_chzn .chzn-single';
        this.input = initDiv.getElementById('inputValue');
        this.renderCountryCode = initDiv.getElementById('renderCountryCode');
        this.divError = initDiv.parentNode.parentNode.getElementsByClassName('fabrikErrorMessage')[0]; // awfull but necessary
        this.isValid = initDiv.getElementById('validationValue');

        this.validColor = validColor;
        this.errorColor = errorColor;
        this.unsupportedColor = unsupportedColor;
        this.defaultColor = defaultColor;
        this.cloned = cloned;

        this.allCountry = initAllCountry;
        this.indiceCountry = initIndiceCountry;
        this.defaultValue = initDefaultValue;
        this.countrySelected = this.allCountry[this.indiceCountry];

        this.mustValidate = initDiv.getElementById('hasValidation').checked; // does he have validation ?
        this.isValid.checked = !this.mustValidate; // if yes, set false, if no, set true

        if (this.cloned) // awfull
        {
            this.frontMessage('default');
        }
        this.newCountry(this.indiceCountry);
        this.setOptionSelected(this.indiceCountry);
        this.changeRenderCountryCode();
        this.initEventListener();
    }

    initEventListener()
    {
        //this.select.addEventListener('change', this.handlerSelectChange.bind(this));
        jQuery(this.select_chosen).on('change', () => {
            this.handlerSelectChange();
        });
        this.input.addEventListener('input', this.inputValidation.bind(this));
        this.input.addEventListener('focusout', this.handlerFocusOut.bind(this));
        this.input.addEventListener('focusin', this.handlerInputFocusIn.bind(this));

        if (this.mustValidate)
        {
            this.inputValidation();
        }
    }

    inputValidation(e)
    {
        const countryCode = this.countrySelected.country_code;
        this.frontMessage('invalid');

        if (countryCode !== '+')
        {
            const number = this.renderCountryCode.value + this.input.value;
            let format;

            try // test number.lengh > 1
            {
                format = libphonenumber.parsePhoneNumber(number.substring(countryCode.length, number.length), this.countrySelected.iso2).format('E.164')
            }
            catch (e)
            {
                // too short, meh
            }

            if (format && libphonenumber.isValidNumber(format))
            {
                this.input.value = format.substring(this.renderCountryCode.value.length, format.length);
                this.frontMessage('valid');
            }
        }
        else // unsupported country
        {
            this.frontMessage('unsupported');
        }
    }

    handlerInputFocusIn(props)
    {
        if (this.countrySelected.country_code !== '+')
        {
            this.frontMessage('default');
        }
    }

    handlerFocusOut(props)
    {

        this.frontMessage('default'); // we consider its good everytime

        if(this.mustValidate) // mandatory so we validate everytime
        {
            this.inputValidation(props);
        }
        else if(this.input.value.length !== 0) // not mandatory but valid only if numbers in it
        {
            this.inputValidation(props);
        }
    }

    handlerSelectChange()
    {
        this.mustValidate ? this.frontMessage('invalid') : this.frontMessage('default');
        this.newCountry(document.querySelector(this.select_chosen).selectedIndex);
        this.changeRenderCountryCode();
    }

    newCountry(id)
    {
        this.indiceCountry = id;
        this.countrySelected = this.allCountry[this.indiceCountry];

        this.countrySelected.country_code = '+';
        try
        {
            this.countrySelected.country_code += libphonenumber.parsePhoneNumber("00", this.countrySelected.iso2).countryCallingCode; // +XX format
        }
        catch (e)
        {
            this.frontMessage('unsupported');
        }
    }

    changeRenderCountryCode()
    {
        // unsupported country
        this.input.value = '';

        if (this.defaultValue !== '')
        {
            this.input.value = this.defaultValue.substring(this.countrySelected.country_code.length, this.defaultValue.length);
            this.renderCountryCode.value = this.defaultValue.substring(0, this.countrySelected.country_code.length);
            this.defaultValue = '';
        }
        else if (this.countrySelected.country_code) {

            this.renderCountryCode.value = this.countrySelected.country_code;
            this.setMaskToInput();
        }
    }

    setMaskToInput()
    {
        if(this.mask) {
            this.mask.destroy();
        }

        this.mask = IMask(
            this.input,
            {
                mask: 'num',
                blocks: {
                    num: {
                        mask: Number,
                    }
                },
            });
    }

    frontMessage(message)
    {
        switch (message)
        {
            case 'invalid':
                this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_INVALID');
                this.isValid.checked = false; // invalid
                this.setInputBorderColor(this.errorColor);
                break;

            case 'default':
                this.divError.innerHTML = '';
                this.isValid.checked = !this.mustValidate;
                this.setInputBorderColor(this.defaultColor);
                break;

            case 'unsupported':
                this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
                this.isValid.checked = true; // unsupported but still valid
                this.setInputBorderColor(this.unsupportedColor);
                break;

            case 'valid':
                this.divError.innerHTML = '';
                this.isValid.checked = true; // valid
                this.setInputBorderColor(this.validColor);
                break;
        }
    }

    setInputBorderColor(color)
    {
        this.input.style.borderColor = color;
        this.renderCountryCode.style.borderColor = color;
        document.querySelector(this.select_chosen_block).style.borderColor = color;
    }

    setOptionSelected(id)
    {
        this.select.options[id].selected = true;
    }

}
