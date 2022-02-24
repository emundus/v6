<template>
  <div class="em-settings-menu">
    <div class="em-w-80">

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

      <div class="form-group controls">
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

/* SERVICES */
import client from "com_emundus/src/services/axiosClient";
import translationsService from "com_emundus/src/services/translations";
import mixin from "com_emundus/src/mixins/mixin";

export default {
  name: "editArticle",

  components: {
    Editor,
    Multiselect
  },

  props: {
    actualLanguage: String,
    manyLanguages: Number,
    article_alias: String,
    article_id: Number
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
      let params = {
        article_id: this.$props.article_id,
        lang: this.lang.lang_code,
        field: 'introtext',
      }
      if(typeof this.$props.article_alias !== 'undefined'){
        params = {
          article_alias: this.$props.article_alias,
          lang: this.lang.lang_code,
          field: 'introtext',
        }
      }

      await client().get("index.php?option=com_emundus&controller=settings&task=getarticle", {
        params: params
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
      if(typeof this.$props.article_alias !== 'undefined') {
        formData.append('article_alias', this.$props.article_alias);
      } else {
        formData.append('article_id', this.$props.article_id);
      }
      formData.append('field', 'introtext');

      await client().post(`index.php?option=com_emundus&controller=settings&task=updatearticle`,
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
      await translationsService.getAllLanguages().then((response) => {
        this.availableLanguages = response;
        this.lang = this.defaultLang;
      })
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
