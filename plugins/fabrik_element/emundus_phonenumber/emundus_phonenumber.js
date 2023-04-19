

const prepareMaskFormat = () =>
{
    return "\\"+countrySelected.country_code+countrySelected.area_code+countrySelected.subscriber_number;
};

const newCountry = (id) =>
{
    indiceCountry = id;
    countrySelected = allCountry[indiceCountry];
};

const prepareInput = () =>
{
    input.pattern = prepareMaskFormat();
    input.required = true;
    input.value = "";
};

const handlerInputChange = (props) => {
    newCountry(props.target.options.selectedIndex);
    prepareInput();
};


const select = document.getElementById("div_emundus_select_phone_code");
const input = document.getElementById("div_emundus_phone");
const allCountry = JSON.parse(atob(select.getAttribute("data-countries"))); // décode base64 + récupération du JSON sous format d'array
let indiceCountry; let countrySelected;


newCountry(0);

select.addEventListener("change", handlerInputChange);