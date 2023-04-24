
const setInputBGColor = (color) =>
{
    input.style.backgroundColor = color;
}

const newCountry = (id) =>
{
    countrySelected = allCountry[id];

    try
    {
        countrySelected.country_code = "+" + libphonenumber.parsePhoneNumber("00", countrySelected.iso2).countryCallingCode;
    }
    catch (e)
    {
        // unsupported country
    }

};

const prepareInput = () =>
{

    if (countrySelected.country_code)
    {
        input.value = countrySelected.country_code;
    }
    else // unsupported country
    {
        input.value = "";
        setInputBGColor(unsupportedNumberColor)
    }
};

const handlerSelectChange = (props) =>
{
    newCountry(props.target.options.selectedIndex);
    prepareInput();

    if (countrySelected.country_code)
    {
        setInputBGColor(defaultInputColor);
    }
    else
    {
        setInputBGColor(unsupportedNumberColor);
    }
};

const handlerInputFocusOut = (props) =>
{

    if (countrySelected.country_code)
    {
        const number = input.value;
        let format;

        try // test if number.lengh > 1
        {
            format = libphonenumber.parsePhoneNumber(number.substring(countrySelected.country_code.length, number.length), countrySelected.iso2).format("E.164")
        }
        catch (e)
        {
            alert("Veuillez entrer un numéro !")
        }


        if (format && libphonenumber.isValidNumber(format))
        {
            setInputBGColor(validNumberColor);
            input.value = format;
        }
        else
        {
            setInputBGColor(errorNumberColor);
        }
    }
    //else, unsupported country

}


const handlerInputFocuIn = (props) =>
{

    if (countrySelected.country_code)
    {
        setInputBGColor(defaultInputColor);
    }
}

const validNumberColor = "palegreen";
const errorNumberColor = "lightpink";
const unsupportedNumberColor = "lightsalmon";
const defaultInputColor = "white";

const select = document.getElementById("div_emundus_select_phone_code");
const input = document.getElementById("div_emundus_phone");
const allCountry = JSON.parse(atob(select.getAttribute("data-countries"))); // décode base64 + récupération du JSON sous format d'array
let indiceCountry; let countrySelected;


newCountry(0);
prepareInput();

select.addEventListener("change", handlerSelectChange);
input.addEventListener("focusout", handlerInputFocusOut);
input.addEventListener("focusin", handlerInputFocuIn);

/*
const sendAjax = {

    initialize: () =>
    {
        const urlphp = window.location.href;
        const indice = JSON.encode(indiceCountry);


        let httpRequest = new XMLHttpRequest();

        httpRequest.open('POST', urlphp, true);
        httpRequest.setRequestHeader('Content-Type', 'test/plain');
        httpRequest.send('jesuisla='+indice);



        const packagesRequest = new Request.HTML({

            url: urlphp,
            header: '\'Content-Type\', \'test/plain\'',
            method: 'post',
            data: '{"JE SUIS LAAAAAAAAAAAAAAAAAAAA": indice}',


            onSuccess: () =>
            {
                //alert("gros coup de chance");

            },

            onFailure: () =>
            {
                //alert("pas réussi noob");
            }

        });
        packagesRequest.send();


    }
}

const buttonSubmit = document.getElementsByName("Submit")[0];
select.addEventListener("click", () =>
{

    sendAjax.initialize();
});
*/

//const lib = libphonenumber;

//const lib2 = lib.isPossibleNumber("oui");

//const number = libphonenumber.parsePhoneNumber("46771093", 'FR').format("E.164");
//console.log(number);
//console.log(libphonenumber.isValidNumber(number));

//console.log(number.metadata);
//console.log(number.format("E.164"));


//console.log(lib2);
