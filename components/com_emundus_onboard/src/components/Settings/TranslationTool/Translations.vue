<template>
  <div>
    <h2 class="em-h4 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS') }}</h2>
    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="!saving && last_save == null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE') }}</p>
    <div v-if="saving" class="em-mb-24 em-flex-row em-flex-start">
      <div class="em-loader em-mr-8"></div>
      <p class="em-font-size-14 em-flex-row">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS') }}</p>
    </div>
    <p class="em-font-size-14 em-mb-24 em-h-25" v-if="!saving && last_save != null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST') + last_save}}</p>
    <div class="em-grid-4">
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
            @select="getObjects"
        ></multiselect>
      </div>

      <!-- Objects availables -->
      <div v-if="lang">
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
      <div v-if="object">
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

      <!-- Childrens -->
      <div v-if="childrens.length > 0">
        <multiselect
            v-model="children"
            label="label"
            track-by="id"
            :options="childrens"
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
      <div v-if="lang === '' || lang == null || object === '' || object == null || init_translations === false" class="text-center em-mt-80">
        <p class="em-h5 em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TITLE') }}</p>
        <p class="em-font-size-14 em-text-neutral-600">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TEXT') }}</p>
      </div>

      <div v-else>
        <div v-for="section in object.fields.Sections" class="em-mb-32">
          <h4>{{section.Label}}</h4>
          <TranslationRow :section="section" :translations="translations" @saveTranslation="saveTranslation"/>
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
import TranslationRow from "./TranslationRow";

export default {
  name: "Translations",
  components: {
    TranslationRow,
    Multiselect
  },
  mixins: [mixin],
  data() {
    return {
      defaultLang: null,
      availableLanguages: [],

      // Lists
      objects: [],
      datas: [],
      childrens: [],
      translations: {},

      // Values
      lang: null,
      object: null,
      data: null,
      children_type: null,
      children: null,

      loading: false,
      saving: false,
      last_save: null,
      init_translations: false
    }
  },

  created() {
    this.loading = true;
    translationsService.getDefaultLanguage().then((response) => {
      this.defaultLang = response;
      this.getAllLanguages();
      this.loading = false;
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
      this.loading = true;
      this.translations = [];
      this.childrens = [];
      this.datas = [];
      this.objects = [];
      this.object = null;
      this.data = null;
      this.children = null;

      translationsService.getObjects().then((response) => {
        this.objects = response.data;
        this.loading = false;
      });
    },

    async getDatas(value){
      this.loading = true;

      translationsService.getDatas(
          value.table.name,
          value.table.reference,
          value.table.label,
          value.table.filters
      ).then(async (response) => {
        this.datas = response.data;

        if(value.table.load_all === 'true') {
          let fields = [];
          await this.asyncForEach(this.object.fields.Fields, async (field) => {
            fields.push(field.Name);
          })
          fields = fields.join(',');
          const build = async () => {
            for (const data of this.datas) {
              await translationsService.getTranslations(
                  this.object.table.type,
                  this.defaultLang.lang_code,
                  this.lang.lang_code,
                  data.id,
                  fields,
                  this.object.table.name
              ).then(async (rep) => {
                for (const translation of Object.values(rep.data)) {
                  this.translations[data.id] = {};
                  this.object.fields.Fields.forEach((field) => {
                    this.translations[data.id][field.Name] = translation[field.Name];
                  })
                }
              })
            }
            this.init_translations = true;
            this.loading = false;
          }
          await build();
        } else {
          this.loading = false;
        }
      });
    },

    async getTranslations(value){
      let fields = [];
      this.object.fields.Fields.forEach((field) => {
        fields.push(field.Name);
      })
      fields = fields.join(',');

      translationsService.getTranslations(
          this.object.table.type,
          this.defaultLang.lang_code,
          this.lang.lang_code,
          value.id,
          fields,
          this.object.table.name
      ).then((response) => {
        this.translations = response.data;
        this.init_translations = true;
        this.loading = false;
      })
    },

    async saveTranslation({value,translation}){
      this.saving = true;
      //this.translations[index].lang_to = value;
      translationsService.updateTranslations(value,this.object.table.type,this.lang.lang_code,translation.reference_id,translation.tag,translation.reference_table).then((response) => {
        this.last_save = this.formattedDate('','LT');
        this.saving = false;
      });
    }
  },

  watch: {
    object: function(value){
      this.init_translations = false;
      this.translations = {};
      this.childrens = [];
      this.children = null;
      this.datas = [];
      this.data = null;

      if(value != null) {
        this.getDatas(value);
      }
    },

    data: function(value){
      this.loading = true;
      this.init_translations = false;
      this.translations = {};
      this.childrens = [];
      this.children = null;
      this.children_type = null;

      var children_existing = false;

      if(value != null) {
        this.object.fields.Fields.forEach((field) => {
          if (field.Type === 'children') {
            this.children_type = field.Label;
            children_existing = true;
            translationsService.getChildrens(field.Label,this.data.id,field.Name).then((response) => {
              this.childrens = response.data;
              this.loading = false;
            });
          }
        });

        if (!children_existing) {
          this.getTranslations(value);
        }
      } else {
        this.getDatas(this.object);
      }
    },

    children: function(value){
      this.loading = true;
      this.init_translations = false;
      this.translations = {};

      if(value != null) {
        let tables = [];
        this.object.fields.Sections.forEach((section) => {
          const table = {
            table: section.Table,
            join_table: section.TableJoin,
            join_column: section.TableJoinColumn,
            reference_column: section.ReferenceColumn,
            fields: Object.keys(section.indexedFields)
          }
          tables.push(table);
        });

        translationsService.getTranslations(
            this.object.table.type,
            this.defaultLang.lang_code,
            this.lang.lang_code,
            value.id,
            '',
            tables
        ).then((response) => {
          this.translations = response.data;
          this.init_translations = true;
          this.loading = false;
        })
      } else {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>

</style>
