<template>
  <div class="em-settings-menu">

    <div class="em-w-80">
      <label class="mb-1">{{ translate("COM_EMUNDUS_ONBOARD_HOME_CONTENT") }}</label>

      <div class="em-grid-3 em-mb-16">
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

      <div class="form-group controls" v-if="this.form.content != null">
        <editor :height="'30em'" :text="form.content" :lang="actualLanguage" :enable_variables="false" :id="'editor'" :key="dynamicComponent" v-model="form.content" @focusout="saveContent"></editor>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
/* COMPONENTS */
import Editor from "@/components/editor";
import Multiselect from 'vue-multiselect';

/* MIXINS */
import mixin from "com_emundus/src/mixins/mixin";

/* SERVICES */
import client from "com_emundus/src/services/axiosClient";
import translationsService from "com_emundus/src/services/translations";
import axios from "axios";
import qs from "qs";

export default {
  name: "editHomepage",

  components: {
    Editor,
    Multiselect
  },

  props: {
    actualLanguage: String,
    manyLanguages: Number
  },
  mixins: [mixin],

  data() {
    return {
      defaultLang: null,
      availableLanguages: [],

      lang: null,
      loading: false,
      dynamicComponent: 0,

      form: {
        content: ''
      },
    };
  },

  created() {
    this.loading = true;
    translationsService.getDefaultLanguage().then((response) => {
      this.defaultLang = response;
      this.getAllLanguages();
      this.loading = false;
    });
  },

  methods: {
    async getArticle() {
      await client().get("index.php?option=com_emundus_onboard&controller=settings&task=gethomepagearticle", {
        params: {
          lang: this.lang.lang_code,
        }
      }).then(response => {
        this.form.content = response.data.data.introtext;
        this.dynamicComponent++;
      });
    },

    async saveContent() {
      this.$emit('updateSaving',true);

      const formData = new FormData();
      formData.append('content', this.form.content);
      formData.append('lang', this.lang.lang_code);

      await client().post(`index.php?option=com_emundus_onboard&controller=settings&task=updatehomepage`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },

    async getAllLanguages() {
      try {
        const response = await client().get('index.php?option=com_emundus&controller=translations&task=getlanguages');
        this.availableLanguages = response.data;
        this.lang = this.defaultLang;
      } catch (e) {
        return false;
      }
    },
  },

  watch: {
    lang: function() {
      this.getArticle();
    },
  }
};
</script>

<style scoped>
</style>
