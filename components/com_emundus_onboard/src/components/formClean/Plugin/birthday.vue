<template>
  <div id="birthdayF">
    <div class="rowmodal">
      <div class="form-group dpflex">
        <input type="checkbox" class="form__input field-general w-input" value="0" v-model="datepicker" />
        <label class="ml-10px mb-0">{{displaycalendar}}</label>
      </div>
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "birthdayF",
  props: { element: Object },
  data() {
    return {
      datepicker: false,
      displaycalendar: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_CALENDAR"),
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT")
    }
  },
  created() {
    setTimeout(() => {
      if(this.element.plugin == 'date'){
        this.datepicker = true;
      }
    },1000);
  },
  watch:{
    datepicker: function(value) {
      if(value == 1){
        this.element.plugin = 'date';
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
  #birthdayF{
    padding: 10px;
  }
</style>
