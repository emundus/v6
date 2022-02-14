<template>
  <div class="translation">
    <transition :name="'slide-down'" type="transition">
      <div class="inlineflex" style="margin: 25px">
        <label class="translate-label">{{TranslateIn}}</label>
        <select v-model="currentLangTranslation" class="dropdown-toggle ml-10px">
          <option v-for="language in languages" :value="language.sef">{{language.title_native}}</option>
        </select>
      </div>
    </transition>
    <transition :name="'slide-down'" type="transition">
      <input type="text"
             class="form__input field-general w-input"
             v-model="label[currentLangTranslation]"
             :id="'label_' + currentLangTranslation"
             v-if="inputType=='text'"
      />
      <editor :height="'20em'" :text="label[currentLangTranslation]" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="label[currentLangTranslation]" v-if="inputType=='wysiwygs'"></editor>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import Editor from "./editor";

export default {
  name: "translation",

  props: {
    label: Object,
    actualLanguage: String,
    inputType:{
      type: String,
      default: "text"
    }
  },
  components: {

    Editor,
  },

  data() {
    return {
      currentLangTranslation: 'en',
      dynamicComponent:0,
      languages: [],
      TranslateIn: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_IN")
    };
  },

  created() {
    this.getLanguages();
  },

  methods: {
    getLanguages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=getactivelanguages"
      }).then(response => {
        this.languages = response.data.data;
        this.languages.forEach((element,index) => {
          if(element.sef == this.actualLanguage){
            this.languages.splice(index,1);
          }
        });
        this.currentLangTranslation = this.languages[0].sef;
      });
    }
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  destroyed() {
    document.removeEventListener("click", this.handleClickOutside);
  }
};
</script>

<style scoped>
</style>
