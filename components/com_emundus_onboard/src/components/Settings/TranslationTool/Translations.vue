<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS') }}</h2>
    <div>
      <!-- Languages -->
      <div class="col-md-3">
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

      <!-- Objects availables -->
      <div class="em-ml-24 col-md-3" v-if="lang">
        <multiselect
            v-model="object"
            label="name"
            track-by="name"
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
        ></multiselect>
      </div>

      <!-- Datas by reference id -->
      <div class="em-ml-24 col-md-3" v-if="object">
        <multiselect
            v-model="data"
            label="label"
            track-by="id"
            :options="datas"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :placeholder="translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT')"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="false"
            :allow-empty="true"
        ></multiselect>
      </div>
    </div>
    <hr class="col-md-12" style="z-index: 0"/>
    <div class="col-md-12">
      <div v-if="lang === '' || lang == null || object === '' || object == null" class="text-center em-mt-80">
        <p class="em-h5 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TITLE') }}</p>
        <p class="em-font-size-14 em-text-neutral-600">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TEXT') }}</p>
      </div>
      <div v-else class="em-neutral-100-box">
        <div v-for="(translation,index) in translations" class="em-mb-32">
          <p>{{ object.fields.IndexedFields[index].Lable.toUpperCase() }}</p>
          <div class="em-flex-row justify-content-between em-mt-16">
            <p class="col-md-5 em-neutral-700-color">{{ translation.default_lang }}</p>
            <input class="col-md-5 mb-0" type="text" :value="translation.lang_to" />
          </div>
        </div>
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
      defaultLang: null,
      availableLanguages: [],
      objects: [],
      datas: [],
      translations: [],
      lang: null,
      object: null,
      data: null
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
        this.allLanguages.forEach((lang) => {
          if(lang.lang_code !== this.defaultLang.lang_code) {
            if (lang.published == 1) {
              this.availableLanguages.push(lang);
            }
          }
        })
      } catch (e) {
        return false;
      }
    },

    async getObjects(){
      translationsService.getObjects().then((response) => {
        this.objects = response.data;
      });
    },
  },

  watch: {
    object: function(value){
      if(value != null) {
        translationsService.getDatas(
            value.table.name,
            value.table.reference,
            value.table.label,
            value.table.filters
        ).then((response) => {
          this.datas = response.data;
        });
      } else {
        this.datas = [];
      }
    },

    data: function(value){
      if(value != null) {
        const fields = Object.keys(this.object.fields.IndexedFields).join(',');
        translationsService.getTranslations(
            this.object.table.type,
            this.defaultLang.lang_code,
            this.lang.lang_code,
            value.id,
            fields,
            this.object.table.name
        ).then((response) => {
          this.translations = response.data;
        })
      } else {}
    }
  }
}
</script>

<style scoped>

</style>
