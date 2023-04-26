

class ValidatorJS {

    constructor(initInput, initSelect, initAllCountry, initIndiceCountry = 0)
    {
        this.input = initInput;
        this.select = initSelect;
        this.allCountry = initAllCountry;
        this.indiceCountry = initIndiceCountry;
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
        if (this.countrySelected.country_code !== undefined)
        {
            const number = props.target.value;
            let format;

            try // test if number.lengh > 1
            {
                format = libphonenumber.parsePhoneNumber(number.substring(this.countrySelected.country_code.length, number.length), this.countrySelected.iso2).format("E.164")
            }
            catch (e)
            {
                // the number isn't long enough, meh
            }

            if (format && libphonenumber.isValidNumber(format))
            {
                this.setInputBorderColor(this.validColor);
                props.target.value = format;
            }
            else
            {
                this.setInputBorderColor(this.errorColor);
            }
        }
        //else, unsupported country
    }

    handlerInputFocusIn(props)
    {
        if (this.countrySelected.country_code !== undefined)
        {
            this.setInputBorderColor(this.defaultColor);
        }
    }

    handlerSelectChange(props)
    {
        this.newCountry(props.target.options.selectedIndex);
        this.prepareInput();

        if (this.countrySelected.country_code)
        {
            this.setInputBorderColor(this.defaultColor);
        }
        else
        {
            this.setInputBorderColor(this.unsupportedColor);
        }
    }

    newCountry(id)
    {
        this.indiceCountry = id;
        this.countrySelected = this.allCountry[this.indiceCountry];

        try
        {
            this.countrySelected.country_code = "+" + libphonenumber.parsePhoneNumber("00", this.countrySelected.iso2).countryCallingCode;
        }
        catch (e)
        {
            // unsupported country
        }
    }

    prepareInput()
    {
        if (this.countrySelected.country_code)
        {
            this.input.value = this.countrySelected.country_code;
        }
        else // unsupported country
        {
            this.input.value = "";
            this.setInputBorderColor(this.unsupportedColor)
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
