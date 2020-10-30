<template xmlns="http://www.w3.org/1999/html">
  <div id="birthdayF">
    <div class="rowmodal">
      <div class="form-group dpflex">
<!--        <input type="checkbox" class="form__input field-general w-input" value="0" v-model="datepicker" />-->
<!--        <label class="ml-10px mb-0">{{displaycalendar}}</label>-->
      <!-- 3 radio buttons pour 3 options -->
        <input type="radio" id = 'radio_default' value='1' v-model="datepicker">{{birthdaySelect}}</input>
        <input type="radio" id = 'radio_years' value='2' v-model="datepicker">{{yearSelect}}</input>
        <input type="radio" id = 'radio_dayMonth' value='3' v-model="datepicker">{{dateSelect}}</input>

      </div>
      <div class="form-group">
<!--        <label>{{helptext}} :</label>-->
<!--        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />-->
      </div>
    </div>
  </div>
  </template>

<script>
export default {
  name: "birthdayF",
  components:{},
  props: { element: Object },
  data() {
    return {
      datepicker: 1,
      birthdaySelect: "Plein",
      yearSelect: "Annee",
      dateSelect: "Jour-Mois",
      // calendarShow:false,
    }
  },

  created() {
    setTimeout(() => {
      if(this.element.plugin == 'birthday'){
        this.datepicker = 1;
      }
      else if(this.element.plugin == 'date') {
        this.datepicker = 3;
        // this.calendarShow = true;
      }
      else {
        this.datepicker = 2; //year
      }
    },1000);
  },
  watch:{
    datepicker: function(value) {
      // check radio button i is selected
      if(value == 3) {
        this.element.plugin = 'date';
      }
      else if(value == 2) {
        this.element.plugin = 'years';
      }
      // else if(document.getElementsById("jos_emundus_1002_00___e_349_7674_cal_cal_img").clicked == true) {
      //   this.element.plugin = 'years';
      // }
      else{
        this.element.plugin = 'birthday';
      }

    }
  }
};
</script>
<style scoped>
  .flex {
    display: flex;
    align-items: center;
    margin-bottom: 1em;
    height: 30px;
  }
  .rowmodal {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
  }
  birthdayF {
    padding: 10px;
  }

</style>