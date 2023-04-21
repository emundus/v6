<template>
  <div class="em-settings-menu">

    <div class="em-w-80">

      <div class="em-grid-3 em-mb-16">
        <multiselect
            v-model="selectedColumn"
            label="label"
            track-by="index"
            :options="columns"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :placeholder="translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT_COLUMN')"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="false"
            :allow-empty="true"
        ></multiselect>
      </div>

      <div class="form-group controls" v-if="selectedColumn.index === 0 && this.form.content.col1 != null">
        <editor :height="'30em'" :text="form.content.col1" :lang="actualLanguage" :enable_variables="false" :id="'editor_1'" :key="dynamicComponent" v-model="form.content.col1" @focusout="saveFooter"></editor>
      </div>
      <div class="form-group controls" v-if="selectedColumn.index === 1 && this.form.content.col2 != null">
        <editor :height="'30em'" :text="form.content.col2" :lang="actualLanguage" :enable_variables="false" :id="'editor_2'" :key="dynamicComponent" v-model="form.content.col2" @focusout="saveFooter"></editor>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
/* COMPONENTS */
import Editor from "../../editor";
import Multiselect from 'vue-multiselect';

/* SERVICES */
import client from "com_emundus/src/services/axiosClient";
import mixin from "com_emundus/src/mixins/mixin";

export default {
  name: "EditFooter",

  components: {
    Editor,
    Multiselect
  },

  props: {
    actualLanguage: String
  },

  mixins: [mixin],

  data() {
    return {
      loading: false,
      dynamicComponent: 0,
      selectedColumn: 0,

      form: {
        content: {
          col1: null,
          col2: null
        }
      },
      columns: [
        {
          index: 0,
          label: this.translate("COM_EMUNDUS_ONBOARD_COLUMN") + ' 1',
        },
        {
          index: 1,
          label: this.translate("COM_EMUNDUS_ONBOARD_COLUMN") + ' 2',
        },
      ],
    };
  },

  created() {
    this.loading = true;
    this.getArticles();
    this.selectedColumn = this.columns[0];
  },

  methods: {
    async getArticles() {
      await client().get("index.php?option=com_emundus&controller=settings&task=getfooterarticles")
          .then(response => {
            this.form.content.col1 = response.data.data.column1;
            this.form.content.col2 = response.data.data.column2;
            this.loading = false;
          });
    },

    async saveFooter() {
      this.$emit('updateSaving',true);

      const formData = new FormData();
      formData.append('col1', this.form.content.col1);
      formData.append('col2', this.form.content.col2);

      await client().post(`index.php?option=com_emundus&controller=settings&task=updatefooter`,
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
  },

  watch: {
    selectedColumn: function() {
      this.dynamicComponent++;
    }
  }
};
</script>
<style scoped>
</style>
