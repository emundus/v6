<?php
defined('JPATH_BASE') or die;



 //$disp=new PlgFabrik_ElementField();




$d = (object)($displayData);
echo $d->id;




//echo 2;

//echo JHTML::_('select.genericlist', $d->options, $d->name, $d->attributes, 'value', 'text', $d->default, $d->id);


?>
<!--<input type='text' name="jos_emundus_users___tel" id="jos_emundus_users___tel"/>-->
<!--<div id="div_<?php echo $d->attributes['name']; ?>">
    <input list="$d->id" name=<?=$d->name?> value=<?=($d->default)[0]?> <?=$d->attributes?>>
  <datalist id="$d->id">
      <?php foreach ($d->options as $key ) :?>
    <option value=<?=$key->value?>>

    <?php endforeach;?>
    <option value="Firefox">
    <option value="Chrome">
    <option value="Opera">
    <option value="Safari">
  </datalist>
</div>-->

<select id=<?=$d->id?> name=<?=$d->name?> class="fabrikinput form-control inputbox input advancedSelect input-mini chzn-done" size="1" style="display: none;"  onchange="filterChanged();">
    <option value="FR" selected="selected" style="color: black;">FR</option>
    <?php foreach ($d->options as $key ) :?>
    <option value="<?=$key->value?>" style="color: black;"><?=$key->value?></option>
    <?php endforeach;?>

</select>
<input type='text' name="jos_emundus_users___tel" id="jos_emundus_users___tel" class="fabrikinput form-control input-medium" onkeyup="phoneTyping();"/>

<script src="https://cdnjs.cloudflare.com/ajax/libs/libphonenumber-js/1.7.25/libphonenumber-js.min.js"></script>
<script>


    var form_id=(document.getElementsByTagName('form'))[1].id;

    function filterChanged(){

        //var val = this.get("value");

       val=document.getElementById("<?=$d->id?>").value

        var Url = 'https://www.countryflags.io/'+val+'/flat/16.png';

        var element = document.getElementById("<?=$d->id?>");

        element.style.backgroundImage = "url("+Url+")";
        document.querySelector('.chzn-single b').style.backgroundImage = "url("+Url+")";







    }
    function phoneTyping(){

        let myPromise = new Promise(function(resolve, reject) {

            setTimeout(function() {

                var cont =Array(this.Fabrik.getBlock(form_id).formElements.get('jos_emundus_1008_00___tel'),
                    this.Fabrik.getBlock(form_id).formElements.get("<?=$d->id?>"),
                    this.Fabrik.getBlock(form_id).formElements.get('jos_emundus_1008_00___number_regex'),
                    this.Fabrik.getBlock(form_id).formElements.get('jos_emundus_1008_00___national_number'),
                );
                resolve(cont);
                // this.Fabrik.getBlock('form_379_1').formElements.get('jos_emundus_1008_00___phone_area').getContainer()
            }, 1000);
        });

        myPromise.then(function(value) {

            var num=value[0];
            var sel=value[1]




            //var oldnum=value[0];

            var oldnum=document.getElementById('jos_emundus_users___tel')

            var code = sel.get('value');
            var regex = value[2];

            var national_number =value[3];

            var libphone = new libphonenumber.AsYouType(code);

            var val_old = oldnum.value;

            //console.log(document.querySelector('#jos_emundus_users___tel').events);
            var newString = libphone.input(val_old);


            oldnum.value=libphone.getNumber().formatInternational()

            regex.set(libphone.getNumber().metadata.countries[code][2]);
            national_number.set(libphone.getNumber().nationalNumber);

            if(!libphone.getNumber().isValid()) {
                oldnum.style.color = 'red';
            }
            else {
                oldnum.style.color = 'black';
            }

        });


    }


    window.addEventListener('load', () => {

            let myPromise = new Promise(function(resolve, reject) {

                setTimeout(function() {

                       var cont =Array(this.Fabrik.getBlock(form_id).formElements.get('jos_emundus_1008_00___tel').getContainer(),
                            this.Fabrik.getBlock(form_id).formElements.get('<?=$d->id?>')
                        );
                       resolve(cont);
                       // this.Fabrik.getBlock('form_379_1').formElements.get('jos_emundus_1008_00___phone_area').getContainer()
                 }, 1000);
            });

            myPromise.then(function(value) {

                var num=value[0];
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


    },

    );


   var target = document.getElementById('jos_emundus_1008_00___phone_area');
   var targets = document.getElementsByTagName('form');
   console.log(targets[1].id);












</script>

<!--<script>

    target.addEventListener("click", function() {
        alert("clik");
    });

    // wrapped in a function

</script>-->
