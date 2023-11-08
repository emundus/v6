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
            :allow-empty="false"
        ></multiselect>
      </div>

      <div class="mb-4 flex items-center" v-if="category === 'rgpd'">
        <div class="em-toggle">
          <input type="checkbox"
                 true-value="1"
                 false-value="0"
                 class="em-toggle-check"
                 id="published"
                 name="published"
                 v-model="form.published"
                 @change="publishArticle()"
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <span for="published" class="ml-2">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_CONTENT_PUBLISH') }}</span>
      </div>

      <div class="form-group controls">
        <editor-quill :height="'30em'" :text="form.content" :enable_variables="false" :id="'editor'"
                      :key="dynamicComponent" v-model="form.content" @focusout="saveContent"></editor-quill>
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
import EditorQuill from "@/components/editorQuill.vue";

export default {
  name: "editArticle",

  components: {
    EditorQuill,
    Editor,
    Multiselect
  },

  props: {
    actualLanguage: {
      type: String,
      default: "fr"
    },
    article_alias: {
      type: String,
      default: null
    },
    article_id: {
      type: Number,
      default: 0
    },
    category: {
      type: String,
      default: null
    },
    published: {
      type: Number,
      default: 1
    }
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
        published: this.$props.published,
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
      if (this.$props.article_alias !== null) {
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
      this.$emit('updateSaving', true);

      const formData = new FormData();
      formData.append('content', this.form.content);
      formData.append('lang', this.lang.lang_code);
      if (this.$props.article_alias !== null) {
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
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
      });
    },

    async getAllLanguages() {
      await translationsService.getAllLanguages().then((response) => {
        this.availableLanguages = response;
        this.lang = this.defaultLang;
      })
    },

    async publishArticle() {
      this.$emit('updateSaving', true);

      const formData = new FormData();
      formData.append('publish', this.form.published);
      if (this.$props.article_alias !== null) {
        formData.append('article_alias', this.$props.article_alias);
      } else {
        formData.append('article_id', this.$props.article_id);
      }

      await client().post(`index.php?option=com_emundus&controller=settings&task=publisharticle`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
        this.$emit('updatePublished', this.form.published);
      });
    },
  },

  watch: {
    lang: function () {
      if (this.lang !== null) {
        this.getArticle();
      } else {
        this.form.content = '';
        this.dynamicComponent++;
      }
    },
  }
};
</script>
<style scoped>
</style>
