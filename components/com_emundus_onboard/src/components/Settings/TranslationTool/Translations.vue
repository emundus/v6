<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS') }}</h2>
    <div class="em-flex-row">
      <div class="em-mr-24">
        <multiselect
            v-model="lang"
            label="title_native"
            track-by="lang_code"
            :options="availableLanguages"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :placeholder="translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT_LANGUAGE')"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="false"
            :allow-empty="true"
            @select="getObjects"
        ></multiselect>
      </div>
      <div>
        <multiselect
            v-model="object"
            label="title_native"
            track-by="lang_code"
            :options="objects"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :placeholder="translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT_OBJECT')"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="false"
            :allow-empty="true"
            @select="getTranslations"
        ></multiselect>
      </div>
    </div>
    <hr/>
    <div>
      <div v-if="lang === '' || lang == null" class="text-center em-mt-80">
        <p class="em-h5 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TITLE') }}</p>
        <p class="em-font-size-14 em-text-neutral-600">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TEXT') }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import client from "com_emundus/src/services/axiosClient";
import translationsService from "com_emundus/src/services/translations";
import Multiselect from 'vue-multiselect';

export default {
  name: "Translations",
  components: {
    Multiselect
  },
  data() {
    return {
      availableLanguages: [],
      objects: [],
      lang: null,
      object: null
    }
  },

  created() {
    this.getAllLanguages();
  },

  methods:{
    async getAllLanguages() {
      try {
        const response = await client().get('index.php?option=com_emundus&controller=translations&task=getlanguages');

        this.allLanguages = response.data;
        this.allLanguages.forEach((lang) => {
          if(lang.published == 1){
            this.availableLanguages.push(lang);
          }
        })
      } catch (e) {
        return false;
      }
    },

    async getObjects(){},

    async getTranslations(){}
  }
}
</script>

<style scoped>

</style>
