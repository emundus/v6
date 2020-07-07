<template>
  <div id="textareaF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{widthtext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.width" />
      </div>
      <div class="form-group">
        <label>{{heightext}} :</label>
        <input type="text" class="form__input field-general w-input" v-model="element.params.height" />
      </div>
      <div class="form-group">
        <label>{{placeholdertext}}
          <button v-tooltip="{
          content: msg,
          placement: 'top-center',
          offset: 5,
          delay: {
            show: 500,
            hide: 300,
            },
          }"><em class="fas fa-question-circle"></em></button> :</label>
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
        <input type="number" max="255" min="1" class="form__input field-general w-input" v-model="element.params.maxlength" />
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
      widthtext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_WIDTH"),
      heightext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HEIGHT"),
      helptext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      placeholdertext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER"),
      sizetext: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_SIZE"),
      maxlength: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_MAXLENGTH"),
      placeholderHelp: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER_HELP"),
    };
  },
  methods: {},
  created(){
    if(window.location.host == 'localhost:8888'){
      this.path = window.location.protocol + '//' + window.location.host + '/v6/media/com_emundus_onboard/'
    }
    this.msg = '<p style="color: black">' + this.placeholderHelp + '</p>' +
            '<img src="' + this.path + 'placeholder.gif" />'
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
    padding: 10px;
  }

button{
  background: transparent;
  padding: 0;
}
.inputF{
  margin: 0 !important;
}
</style>
