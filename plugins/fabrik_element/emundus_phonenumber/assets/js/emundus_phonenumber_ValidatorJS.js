

class ValidatorJS {

    constructor(initDiv, initAllCountry, initIndiceCountry = 0, initDefaultValue = "")
    {
        this.select = initDiv.getElement('select');
        this.input = initDiv.getElement('input');
        this.divError = initDiv.parentNode.parentNode.getElementsByClassName("fabrikErrorMessage")[0]; // awfull but necessary
        this.allCountry = initAllCountry;
        this.indiceCountry = initIndiceCountry;
        this.defaultValue = initDefaultValue;
        this.countrySelected = this.allCountry[this.indiceCountry];

        this.newCountry(this.indiceCountry);
        this.setOptionSelected(this.indiceCountry);
        this.prepareInput();
        this.initEventListener();
        this.setColors();
    }

    initEventListener()
    {
        this.select.addEventListener("change", this.handlerSelectChange.bind(this));
        this.input.addEventListener("focusout", this.handlerInputFocusOut.bind(this));
        this.input.addEventListener("focusin", this.handlerInputFocusIn.bind(this));
    }

    handlerInputFocusOut(props)
    {
        if (this.countrySelected.country_code !== "+")
        {
            const number = props.target.value;
            let format;
            this.divError.innerHTML = '';

            try // test number.lengh > 1
            {
                format = libphonenumber.parsePhoneNumber(number.substring(this.countrySelected.country_code.length, number.length), this.countrySelected.iso2).format("E.164")
            }
            catch (e)
            {
                // too short, meh
            }

            if (format && libphonenumber.isValidNumber(format))
            {
                this.setInputBorderColor(this.validColor);
                props.target.value = format;
            }
            else
            {
                this.setInputBorderColor(this.errorColor);
                this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_INVALID');
            }
        }
        //else, unsupported country
    }

    handlerInputFocusIn(props)
    {
        if (this.countrySelected.country_code !== "+")
        {
            this.setInputBorderColor(this.defaultColor);
        }
    }

    handlerSelectChange(props)
    {
        this.divError.innerHTML = '';
        this.newCountry(props.target.options.selectedIndex);
        this.prepareInput();
    }

    newCountry(id)
    {
        this.indiceCountry = id;
        this.countrySelected = this.allCountry[this.indiceCountry];

        this.countrySelected.country_code = "+";
        try
        {
            this.countrySelected.country_code += libphonenumber.parsePhoneNumber("00", this.countrySelected.iso2).countryCallingCode; // +XX format
        }
        catch (e)
        {
            this.divError.innerHTML = Joomla.JText._('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
        }
    }

    prepareInput()
    {
        // unsupported country
        this.input.value = "";
        this.countrySelected.country_code !== "+" ? this.setInputBorderColor(this.defaultColor) : this.setInputBorderColor(this.unsupportedColor);

        if (this.defaultValue !== "")
        {
            this.input.value = this.defaultValue;
            this.defaultValue = "";
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

    setColors(initValidColor = "palegreen", initErrorColor = "lightpink", initUnsupportedColor = "lightsalmon", initDefaultColor = "black")
    {
        this.validColor = initValidColor;
        this.errorColor = initErrorColor;
        this.unsupportedColor = initUnsupportedColor;
        this.defaultColor = initDefaultColor;

    }
}
