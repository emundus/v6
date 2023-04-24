
const select = document.getElementById("div_emundus_select_phone_code");
const input = document.getElementById("div_emundus_phone");
const allCountry = JSON.parse(atob(select.getAttribute("data-countries"))); // décode base64 + récupération du JSON sous format d'array

class ValidatorJS {

    constructor(initInput, initSelect, initAllCountry, initIndiceCountry)
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
        this.countrySelected = this.allCountry[id];

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

const js = new ValidatorJS(input, select, allCountry, 0);

const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-900");
const unsupporttedColor = allColor.getPropertyValue("--orange-400")

js.setColors(validColor, errorColor, unsupporttedColor, defaultColor);

//const lib = libphonenumber;

//const lib2 = lib.isPossibleNumber("oui");

//const number = libphonenumber.parsePhoneNumber("46771093", 'FR').format("E.164");
//console.log(number);
//console.log(libphonenumber.isValidNumber(number));

//console.log(number.metadata);
//console.log(number.format("E.164"));


//console.log(lib2);
