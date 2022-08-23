<template xmlns="http://www.w3.org/1999/html">
  <div id="birthdayF">
    <div class="rowmodal">
      <div class="form-group">
        <label>{{ Format }}</label>

        <div class="flex mr-2">
          <input type="radio" id = 'radio_default' value='1' v-model="datepicker"/>
          <span class="ml-10px">{{birthdaySelect}}</span>
        </div>

        <div class="flex mr-2">
          <input type="radio" id = 'radio_years' value='2' v-model="datepicker"/>
          <span class="ml-10px">{{yearSelect}}</span>
        </div>

        <div class="flex mr-2">
          <input type="radio" id = 'radio_dayMonth' value='3' v-model="datepicker"/>
          <span class="ml-10px">{{dateSelect}}</span>
        </div>

      </div>

      <div class="form-group" v-if="datepicker == 1 || datepicker == 2">
        <label>{{yearrange}}</label>
        <input type="text" id="format" class="em-w-100" v-model="element.params.birthday_forward"/>
        <label style="font-size: small">{{yeararrangetip}}</label>
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
      datepicker: null,
      birthdaySelect: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMAT_BIRTHDAY"),
      yearSelect: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMAT_YEAR"),
      dateSelect: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_CALENDAR"),
      Format: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMATTING"),
      birthday_forward: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_FUTURE_YEARS"),    //FUTURE_YEARS_SELECTED
      yearrange: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_YEAR_RANGE"),
      yeararrangetip: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_TIP_YEARS_FUTURE"),
    }
  },

  // use element "birthday_forward"

  created() {
      if(this.element.plugin == 'birthday'){
        this.datepicker = 1;
      } else if(this.element.plugin == 'date') {
        this.datepicker = 3;
        // this.calendarShow = true;
      } else {
        this.datepicker = 2; //year
      }
  },
  watch:{
    datepicker: function(value) {
      // check radio button i is selected
      if(value == 3) {
        this.element.plugin = 'date';
      } else if(value == 2) {
        this.element.plugin = 'years';
      } else {
        this.element.plugin = 'birthday';
        //this.element.params = 'birthday_forward';
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

  input[type='radio']{
    width: auto;
  }

</style>
