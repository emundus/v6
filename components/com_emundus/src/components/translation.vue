<template>
  <div class="translation">
    <transition :name="'slide-down'" type="transition">
      <div class="inlineflex" style="margin: 25px">
        <label class="translate-label">{{ TranslateIn }}</label>
        <select v-model="currentLangTranslation" class="dropdown-toggle ml-10px">
          <option v-for="language in languages" :key="language.sef" :value="language.sef">{{ language.title_native }}
          </option>
        </select>
      </div>
    </transition>
    <transition :name="'slide-down'" type="transition">
      <input type="text"
             class="form__input field-general w-input"
             v-model="label[currentLangTranslation]"
             :id="'label_' + currentLangTranslation"
      />
    </transition>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "translation",

  props: {
    label: Object,
    actualLanguage: String,
  },

  data() {
    return {
      currentLangTranslation: 'en',
      languages: [],
      TranslateIn: this.translate("COM_EMUNDUS_ONBOARD_TRANSLATE_IN")
    };
  },

  created() {
    this.getLanguages();
  },

  methods: {
    getLanguages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=getactivelanguages"
      }).then(response => {
        this.languages = response.data.data;
        this.languages.forEach((element, index) => {
          if (element.sef == this.actualLanguage) {
            this.languages.splice(index, 1);
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
