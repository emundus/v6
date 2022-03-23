<template>
  <div id="textareaF">
    <div class="row rowmodal">
      <div class="form-group dpflex">
        <input type="checkbox" class="form__input field-general w-input" value="1" v-model="wysiwyg" />
        <label class="ml-10px mb-0">{{UseAdvancedEditor}}</label>
      </div>
      <div class="form-group">
        <label>{{heightext}} :</label>
        <input type="number" class="form__input field-general w-input" v-model="element.params.height" min="3"/>
      </div>
      <div class="form-group">
        <label>{{placeholdertext}} :</label>
        <input
          type="text"
          class="form__input field-general w-input"
          v-model="element.params.textarea_placeholder"
        />
      </div>
      <div class="form-group">
        <label>{{helptext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.rollover" />
      </div>
      <div class="form-group">
        <label>{{maxlength}} :</label>
        <input type="number" min="1" class="form__input field-general w-input" v-model="element.params['textarea-maxlength']" />
      </div>
      <div class="form-group dpflex">
        <input type="checkbox" class="form__input field-general w-input" style="margin: 0 !important;" value="1" v-model="showmax" />
        <label class="ml-10px mb-0">{{DisplayMaxLength}}</label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "textareaF",
  props: { element: Object },
  data() {
    return {
      msg: '',
      path: window.location.protocol + '//' + window.location.host + '/media/com_emundus_onboard/',
      arraySubValues: [],
      wysiwyg: false,
      showmax: false,
      widthtext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_WIDTH"),
      heightext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HEIGHT"),
      helptext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      placeholdertext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER"),
      sizetext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_SIZE"),
      maxlength: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_MAXLENGTH"),
      DisplayMaxLength: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_MAXLENGTH"),
      UseAdvancedEditor: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_USE_ADVANCED_EDITOR"),
      placeholderHelp: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER_HELP"),
    };
  },
  methods: {},
  created(){
    this.msg = '<p style="color: black">' + this.placeholderHelp + '</p>' +
            '<img src="' + this.path + 'placeholder.gif" />'
    if(typeof this.element.params.height == "undefined"){
      this.element.params.height = 6;
    }
    if(typeof this.element.params['textarea-maxlength'] == "undefined"){
      this.element.params['textarea-maxlength'] = 255;
    }
    if(typeof this.element.params.use_wysiwyg == "undefined"){
      this.element.params.use_wysiwyg = 0;
    } else if(this.element.params.use_wysiwyg == '1') {
      this.wysiwyg = true;
    }
    if(typeof this.element.params['textarea-showmax'] == "undefined"){
      this.element.params['textarea-showmax'] = 0;
    } else if(this.element.params['textarea-showmax'] == '1'){
      this.showmax = true;
    }
  },
  watch: {
    wysiwyg: function(value) {
      if(value){
        this.element.params.use_wysiwyg = '1'
      } else {
        this.element.params.use_wysiwyg = '0'
      }
    },
    showmax: function(value) {
      if(value){
        this.element.params['textarea-showmax'] = '1'
      } else {
        this.element.params['textarea-showmax'] = '0'
      }
    },
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

  #textareaF{
    padding: 10px 0;
  }

button{
  background: transparent;
  padding: 0;
}
.inputF{
  margin: 0 !important;
}
</style>
