

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
    input.value = countrySelected.country_code;
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
prepareInput();

select.addEventListener("change", handlerInputChange);

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

const lib = libphonenumber;


const lib2 = lib.isPossibleNumber("oui");

console.log(lib2);
