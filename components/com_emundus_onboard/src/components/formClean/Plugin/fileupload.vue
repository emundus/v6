<template xmlns="http://www.w3.org/1999/html">

  <div id="fileF">
      <div class="row rowmodal">
         <div class="form-group">
           <label class="file-select">
             <div class="select-button">
               <span v-if="value">Selected File: {{value.name}}</span>
               <span v-else>Select File</span>
             </div>
           </label>
             <input type="file" @change="handleFileChange" class="form__input field-general w-input"/>

         </div>
      </div>
  </div>

</template>

<script>
export default {
  name: "fileF",
  props: {
    value: File
  },
  data() {
    return {
      msg: '',
      path: window.location.protocol + '//' + window.location.host + '/media/com_emundus_onboard/',


      heightext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HEIGHT"),
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),

      placeholderHelp: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER_HELP"),
    }
  },
  methods: {
    handleFileChange(e) {
      this.$emit('input', e.target.files[0])
    },

  },
  created() {
    this.msg = '<p style="color: black">' + this.placeholderHelp + '</p>' +
        '<img src="' + this.path + 'placeholder.gif" />'
  }
}
</script>

<style scoped>
  .file-select > .select-button {
      padding: 1rem;
      color: red;
      background-color: #2EA169;
      border-radius: .3rem;
      text-align: center;
      font-weight: bold;
  }

  .file-select > input[type="file"] {
      display: none;
  }
  .rowmodal {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
  }
  #fileF{
    padding: 10px 0;
  }
</style>