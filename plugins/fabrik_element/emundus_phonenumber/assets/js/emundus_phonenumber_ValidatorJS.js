

class ValidatorJS {

    constructor(initDiv, initAllCountry, initIndiceCountry = 0, initDefaultValue = '')
    {
        this.select = initDiv.getElementById('countrySelect');
        this.input = initDiv.getElementById('inputValue');
        this.divError = initDiv.parentNode.parentNode.getElementsByClassName('fabrikErrorMessage')[0]; // awfull but necessary
        this.isValid = initDiv.getElementById('validationValue');

        this.allCountry = initAllCountry;
        this.indiceCountry = initIndiceCountry;
        this.defaultValue = initDefaultValue;
        this.countrySelected = this.allCountry[this.indiceCountry];

        !this.isValid.checked ?  this.mustValidate = true : this.mustValidate = false;

        this.newCountry(this.indiceCountry);
        this.setOptionSelected(this.indiceCountry);
        this.prepareInput();
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

    inputValidation()
    {
        this.frontMessage('invalid');
        if (this.countrySelected.country_code !== '+')
        {
            const number = this.input.value;
            let format;

            try // test number.lengh > 1
            {
                format = libphonenumber.parsePhoneNumber(number.substring(this.countrySelected.country_code.length, number.length), this.countrySelected.iso2).format('E.164')
            }
            catch (e)
            {
                // too short, meh
            }

            if (format && libphonenumber.isValidNumber(format))
            {
                this.input.value = format;
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
        this.prepareInput();
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

    prepareInput()
    {
        if(typeof this.mask !== 'undefined') {
            this.mask.destroy();
        }
        // unsupported country
        this.input.value = '';

        if (this.defaultValue !== '')
        {
            this.input.value = this.defaultValue;
            this.defaultValue = '';
        }
        else if (this.countrySelected.country_code)
        {
            this.input.value = this.countrySelected.country_code;
            this.mask = IMask(
                document.getElementById(this.input.id),
                {
                    mask: 'country_code`'+'num',
                    blocks: {
                        country_code:{
                            mask: '{'+this.countrySelected.country_code+'}'
                        },
                        num: {
                            // nested masks are available!
                            mask: Number,
                        }
                    }
                   ,
                });
        }
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
