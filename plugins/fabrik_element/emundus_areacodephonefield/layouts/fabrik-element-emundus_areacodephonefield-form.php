<?php
defined('JPATH_BASE') or die;



 //$disp=new PlgFabrik_ElementField();




$d = (object)($displayData);

//echo substr($d->id,0,-1);


$user_tel="tel_".$d->id;



//echo 2;

//echo JHTML::_('select.genericlist', $d->options, $d->name, $d->attributes, 'value', 'text', $d->default, $d->id);


?>

<select id=<?=$d->id?> name=<?=$d->name?> class="fabrikinput form-control inputbox input advancedSelect input-mini chzn-done " size="1" style="display: none;"  onchange="filterChanged('<?=$d->id?>')">
    <option value="FR" selected="selected" style="color: black;">FR</option>
    <?php foreach ($d->options as $key ) :?>
    <option value="<?=$key->value?>" style="color: black;"><?=$key->value?></option>
    <?php endforeach;?>

</select>
<!--<input type='text' name=<?=$user_tel?> id=<?=$user_tel?> class="fabrikinput form-control input-large" onkeyup="phoneTyping('<?=$d->id?>');"/>
-->



<script src="https://cdnjs.cloudflare.com/ajax/libs/libphonenumber-js/1.7.25/libphonenumber-js.min.js"></script>
<script>


    var form_id=(document.getElementsByTagName('form'))[1].id;

    function filterChanged(params){

        //var val = this.get("value");

       val=document.getElementById(params).value


        var Url = 'https://www.countryflags.io/'+val+'/flat/16.png';

        var element = document.getElementById(params);

        element.style.backgroundImage = "url("+Url+")";
        document.querySelector('#'+params+'_chzn .chzn-single b').style.backgroundImage = "url("+Url+")";


    }
    function retrieveElement(params){
        return Array(this.Fabrik.getBlock(form_id).formElements.get(params.substring(0, params.length-1)+'tel_0'),
            this.Fabrik.getBlock(form_id).formElements.get(params),
            this.Fabrik.getBlock(form_id).formElements.get(params.substring(0, params.length-1)+'number_regex_0'),
            this.Fabrik.getBlock(form_id).formElements.get(params.substring(0, params.length-1)+'national_number_0'),
        );
    }


    function phoneTyping(params){


        let myPromise = new Promise(function(resolve, reject) {

            setTimeout(function() {


                resolve(retrieveElement(params));

            }, 1000);
        });

        myPromise.then(function(value) {

            var num=value[0];
            var sel=value[1];

            var oldnum=value[0];

            //var oldnum=document.getElementById("<?=$user_tel?>")

            var code = sel.get('value');
            var regex = value[2];


            var national_number =value[3];

            var libphone = new libphonenumber.AsYouType(code);

            //var val_old = oldnum.value;
            var val_old = oldnum.get('value');


            var newString = libphone.input(val_old);


            oldnum.value=libphone.getNumber().formatInternational()


            regex.set(libphone.getNumber().metadata.countries[code][2]);
            national_number.set(libphone.getNumber().nationalNumber);
            num.set(oldnum.value);
            if(!libphone.getNumber().isValid()) {
                oldnum.element.style.color = 'red';
            }
            else {
                oldnum.element.style.color = 'black';
            }

        });


    }


    window.addEventListener('load', () => {

            let myPromise = new Promise(function(resolve, reject) {

                setTimeout(function() {


                       resolve(
                           Array(this.Fabrik.getBlock(form_id).formElements.get('<?=substr($d->id,0,-1)?>tel_0'),
                               this.Fabrik.getBlock(form_id).formElements.get('<?=$d->id?>')
                           )
                       );
                       // this.Fabrik.getBlock('form_379_1').formElements.get('jos_emundus_1008_00___phone_area').getContainer()
                 }, 2000);
            });

            myPromise.then(function(value) {

                var num=value[0].getContainer();
                var sel=value[1]


                sel.getContainer().parentNode.append(num);
                sel.getContainer().style.width = '100px';
                sel.getContainer().parentNode.style.display = 'flex';
                sel.getContainer().parentNode.setAttribute('style', 'flex-direction: row');

            });

            jQuery('<?=$d->id?>').chosen();
            jQuery('.chzn-single').on('click', function (e) {
                jQuery('.active-result').each(function (li) {
                    var img = document.createElement('img');
                    var val = this.innerHTML;
                    var url = 'https://www.countryflags.io/' + val + '/flat/16.png';


                    img.setAttribute('src', url);
                    jQuery(this).prepend(img);
                });


            });


            (document.getElementById('<?=substr($d->id,0,-1)?>tel_0')).onkeyup=function (){phoneTyping('<?=$d->id?>')}


        },

    );














</script>

<!--<script>

    target.addEventListener("click", function() {
        alert("clik");
    });

    // wrapped in a function

</script>-->
