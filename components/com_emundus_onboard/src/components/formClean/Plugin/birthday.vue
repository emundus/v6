<template>
  <div id="birthdayF">
    <div class="rowmodal">
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
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT")
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
