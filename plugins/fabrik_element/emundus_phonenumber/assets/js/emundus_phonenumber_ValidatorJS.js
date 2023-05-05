

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
        this.input.addEventListener('input', this.handlerInputFocusOut.bind(this));
        this.input.addEventListener('focusin', this.handlerInputFocusIn.bind(this));

        if (this.mustValidate)
        {
            this.inputValidation();
        }
    }

    handlerInputFocusOut(props)
    {
        this.inputValidation();
    }

    inputValidation()
    {
        this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_INVALID');
        this.setInputBorderColor(this.errorColor);
        this.isValid.checked = false; // invalid

        if (this.countrySelected.country_code !== '+' && this.input.value[0] === '+')
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
                this.setInputBorderColor(this.validColor);
                this.input.value = format;
                this.isValid.checked = true; // valid
                this.divError.innerHTML = '';
            }
        }
        else // unsupported country
        {
            this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
            this.isValid.checked = true; // unsupported but still valid
            this.setInputBorderColor(this.unsupportedColor);
        }
    }

    handlerInputFocusIn(props)
    {
        if (this.countrySelected.country_code !== '+')
        {
            this.setInputBorderColor(this.defaultColor);
        }
    }

    handlerSelectChange(props)
    {
        this.mustValidate ? this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_INVALID') : this.divError.innerHTML = '';
        this.isValid.checked = !this.mustValidate;
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
            this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
            this.isValid.checked = true; // unsupported but still valid
            this.setInputBorderColor(this.unsupportedColor);
        }
    }

    prepareInput()
    {
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
