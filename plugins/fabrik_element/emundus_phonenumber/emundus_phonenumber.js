
const select = document.getElementById("div_emundus_select_phone_code");
const input = document.getElementById("div_emundus_phone");
const allCountry = JSON.parse(atob(select.getAttribute("data-countries"))); // décode base64 + récupération du JSON sous format d'array


const js = new ValidatorJS(input, select, allCountry, 0);

const allColor =  getComputedStyle(document.querySelector(':root'));
const errorColor = allColor.getPropertyValue("--red-600");
const validColor = allColor.getPropertyValue("--secondary-main-400");
const defaultColor = allColor.getPropertyValue("--neutral-900");
const unsupportedColor = allColor.getPropertyValue("--orange-400")
js.setColors(validColor, errorColor, unsupportedColor, defaultColor);

//const lib = libphonenumber;

//const lib2 = lib.isPossibleNumber("oui");

//const number = libphonenumber.parsePhoneNumber("46771093", 'FR').format("E.164");
//console.log(number);
//console.log(libphonenumber.isValidNumber(number));

//console.log(number.metadata);
//console.log(number.format("E.164"));


//console.log(lib2);
