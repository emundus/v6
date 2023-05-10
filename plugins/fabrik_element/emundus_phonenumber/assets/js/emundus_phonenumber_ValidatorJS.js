

class ValidatorJS {

    constructor(initDiv, initAllCountry, initIndiceCountry = 0, initDefaultValue = '')
    {
        this.select = initDiv.getElementById('countrySelect');
        this.input = initDiv.getElementById('inputValue');
        this.renderCountryCode = initDiv.getElementById('renderCountryCode');
        this.divError = initDiv.parentNode.parentNode.getElementsByClassName('fabrikErrorMessage')[0]; // awfull but necessary
        this.isValid = initDiv.getElementById('validationValue');

        this.allCountry = initAllCountry;
        this.indiceCountry = initIndiceCountry;
        this.defaultValue = initDefaultValue;
        this.countrySelected = this.allCountry[this.indiceCountry];

        !this.isValid.checked ?  this.mustValidate = true : this.mustValidate = false;

        this.newCountry(this.indiceCountry);
        this.setOptionSelected(this.indiceCountry);
        this.changeRenderCountryCode();
        this.initEventListener();
        this.setColors();
    }

    initEventListener()
    {
        this.select.addEventListener('change', this.handlerSelectChange.bind(this));
        this.input.addEventListener('input', this.inputValidation.bind(this));
        this.input.addEventListener('focusout', this.inputValidation.bind(this));
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

    handlerSelectChange(props)
    {
        this.mustValidate ? this.frontMessage('invalid') : this.frontMessage('default');
        this.newCountry(props.target.options.selectedIndex);
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
    }

    setOptionSelected(id)
    {
        this.select.options[id].selected = true;
    }

    setColors(initValidColor = 'palegreen', initErrorColor = 'lightpink', initUnsupportedColor = 'lightsalmon', initDefaultColor = 'black')
    {
        this.validColor = initValidColor;
        this.errorColor = initErrorColor;
        this.unsupportedColor = initUnsupportedColor;
        this.defaultColor = initDefaultColor;

    }
}
