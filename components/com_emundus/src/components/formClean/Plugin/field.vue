<template>
  <div id="fieldF">
    <div class="row rowmodal">
      <!--<div class="form-group">
        <label>{{fieldtype}} :</label>
        <select v-model="element.params.password" id='selectIdTest' class="dropdown-toggle" :disabled="files != 0 && element.params.password == 6">
          <option value="0">{{textfield}}</option>
          <option value="2">{{phonefield}}</option>
          <option value="3">{{emailfield}}</option>
          <option value="6" v-if="files == 0 || (files != 0 && element.params.password == 6)">{{numberfield}}</option>
        </select>
      </div>-->
      <div class="form-group">
        <label>{{placeholdertext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.placeholder" />
      </div>
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="form-group">
        <label>{{maxlength}} :</label>
        <input type="number" min="1" class="form__input field-general w-input" v-model="element.params.maxlength" />
      </div>

<!--      v-show - shox this div when option 4 is checked-->
      <div class="form-group" v-if="element.params.password == 0">
        <label>{{inputmasktitle}}</label>
        <input type="text" id="format" class="form__input field-general w-input" v-model="element.params.text_input_mask" :placeholder="[[ inputmaskplaceholder ]]"/>
        <label style="font-size: small">{{tipData}}</label>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "fieldF",
  props: { element: Object, files: Number },
  data() {

    return {
      msg: '',
      path: window.location.protocol + '//' + window.location.host + '/media/com_emundus_onboard/',
      helptext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      placeholdertext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER"),
      sizetext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_SIZE"),
      maxlength: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_MAXLENGTH"),
      fieldtype: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_FIELDTYPE"),
      textfield: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_FIELDTEXT"),
      phonefield: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_FIELDPHONE"),
      emailfield: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_EMAIL"),
      numberfield: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_NUMBER"),
      placeholderHelp: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER_HELP"),
      text_input_mask: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_INPUT_MASK"),
      inputmasktitle: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_INPUT_MASK_TITLE"),
      inputmaskplaceholder: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_INPUT_MASK_PLACEHOLDER"),
      tipData: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_INPUT_MASK_HINT"),
    };
  },
  methods: {

  },
  created(){
    this.msg =
            '<p style="color: white">' + this.placeholderHelp + '</p>' +
            '<img src="' + this.path + 'placeholder.gif" />';
    if(typeof this.element.params.password == 'undefined'){
      this.element.params.password = 0;
    }
    if(typeof this.element.params.maxlength == 'undefined'){
      this.element.params.maxlength = 255;
    }

    if(typeof this.element.params.text_input_mask == 'undefined'){
      this.element.params.text_input_mask = ''
    }

    if(typeof this.element.params.placeholder == 'undefined'){
      this.element.params.placeholder = ''
    }
  },
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
  button{
    background: transparent;
  }
#fieldF{
  padding: 10px 0;
}
</style>
