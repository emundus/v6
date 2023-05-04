<template>
  <div>
    <h1 class="em-h1 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHELINS') }}</h1>
    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="!saving && last_save == null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE') }}</p>
    <div v-if="saving" class="em-mb-24 em-flex-row em-flex-start">
      <div class="em-loader em-mr-8"></div>
      <p class="em-font-size-14 em-flex-row">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS') }}</p>
    </div>
    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="!saving && last_save != null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST') + last_save}}</p>

    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="availableLanguages.length === 0 && !loading">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_NO_LANGUAGES_AVAILABLE') }}</p>

    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="translations.length === 0 && !loading">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHANS_CONGRATULATIONS') }}</p>

    <div class="em-grid-4" v-else>
      <!-- Languages -->
      <div>
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
        ></multiselect>
      </div>
    </div>

    <hr class="col-md-12" style="z-index: 0"/>

    <div class="col-md-12">
      <div v-if="lang === '' || lang == null || translations.length === 0" class="text-center em-mt-80">
        <p class="em-h5 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_ORPHELINS_TITLE') }}</p>
        <p class="em-font-size-14 em-text-neutral-600" v-if="lang === '' || lang == null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_ORPHELINS_TEXT') }}</p>
      </div>

      <div v-else>
        <div v-for="translation in translations" :key="translation.id">
          <div class="em-mb-32 em-neutral-100-box em-p-24">
            <div class="em-flex-space-between em-mt-16 em-grid-50">
              <p class="em-neutral-700-color">{{ translation.override }}</p>
              <div>
                <input class="mb-0 em-input em-w-100" type="text" :value="translation.override" :ref="'translation-' + translation.id + ''"/>
                <a class="em-pointer em-blue-500-color em-mt-16 em-font-size-12 em-hover-blue-500" @click="saveTranslation(translation)">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHELIN_CONFIRM_TRANSLATION') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import client from "com_emundus/src/services/axiosClient";
import translationsService from "com_emundus/src/services/translations";
import mixin from "com_emundus/src/mixins/mixin";
import Multiselect from 'vue-multiselect';

export default {
  name: "Orphelins",
  components: {
    Multiselect
  },
  mixins: [mixin],
  data() {
    return {
      defaultLang: null,
      availableLanguages: [],

      // Lists
      translations: [],

      // Values
      lang: null,

      loading: true,
      saving: false,
      last_save: null
    }
  },

  created() {
    translationsService.getDefaultLanguage().then((response) => {
      this.defaultLang = response;
      this.getAllLanguages();
    });
  },

  methods:{
    async getAllLanguages() {
      try {
        const response = await client().get('index.php?option=com_emundus&controller=translations&task=getlanguages');

        this.allLanguages = response.data;
        for(const lang of this.allLanguages){
          if (lang.lang_code !== this.defaultLang.lang_code) {
            if (lang.published == 1) {
              this.availableLanguages.push(lang);
            }
          }
        }
        if(this.availableLanguages.length === 1){
          this.lang = this.availableLanguages[0];
        } else {
          this.loading = false;
        }
      } catch (e) {
        this.loading = false;
        return false;
      }
    },

    async saveTranslation(translation) {
      this.saving = true;
      const value = this.$refs['translation-' + translation.id][0].value;

      if (value) {
        translationsService.insertTranslation(value,'override', this.lang.lang_code, translation.reference_id, translation.tag, translation.reference_table).then((response) => {
          this.last_save = this.formattedDate('','LT');
          this.saving = false;
          this.translations = this.translations.filter(function(item) {
             return item.id !== translation.id;
          });
        });
      }
    }
  },

  watch: {
    lang: function(value){
      if (value === null || typeof value === undefined) {
        return;
      }

      this.loading = true;
      this.translations = [];

      translationsService.getOrphelins(this.defaultLang.lang_code,value.lang_code).then((response) => {
        this.translations = response.data;
        this.loading = false;
      })
    }
  }
}
</script>

<style scoped>

</style>
