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
      birthdaySelect: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMAT_BIRTHDAY"),
      yearSelect: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMAT_YEAR"),
      dateSelect: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_CALENDAR"),
      Format: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATE_FORMATTING"),
    }
  },

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
