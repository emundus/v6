<template>
  <!-- modalC -->
  <span :id="typeof widget !== 'undefined' ? 'modalWidgetSettings' + widget.id : 'modalWidgetSettings'">
    <modal
        :name="typeof widget !== 'undefined' ? 'modalWidgetSettings' + widget.id : 'modalWidgetSettings'"
        transition="little-move-left"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="true"
        @before-open="buildWidget"
    >

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4">
          {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_EDIT') }}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="typeof widget !== 'undefined' ? $modal.hide('modalWidgetSettings' + widget.id) : $modal.hide('modalWidgetSettings')">
          <span class="material-icons">close</span>
        </button>
      </div>

      <div>
        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_NAME') }} : </div>
          <input type="text" v-model="selectedWidget.label" class="em-w-100" />
        </div>

        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_TYPE') }} : </div>
          <select v-model="selectedWidget.type" class="em-w-100">
            <option v-for="type in types" :value="type.value">{{ translate(type.label) }}</option>
          </select>
        </div>

        <div class="em-mb-16" v-if="selectedWidget.type === 'chart'">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CHART_TYPE') }} : </div>
          <multiselect
              v-model="selectedWidget.chart_type"
              label="label"
              track-by="value"
              :options="chart_types"
              :multiple="false"
              select-label=""
              selected-label=""
              deselect-label=""
          ></multiselect>
        </div>

        <div class="em-mb-16" v-show="selectedWidget.type === 'chart' || selectedWidget.type === 'other'">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CODE') }} : </div>
          <button class="em-secondary-button em-w-auto" @click="displayCode = true;" v-if="!displayCode">
            {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_DISPLAY_CODE') }}
          </button>
          <button class="em-secondary-button em-w-auto" @click="displayCode = false" v-if="displayCode">
            {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_HIDE_CODE') }}
          </button>
          <prism-editor
              v-show="displayCode"
              class="my-editor em-mt-8"
              v-model="selectedWidget.eval"
              :highlight="highlighter"
          ></prism-editor>
        </div>

        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ACCESS') }} : </div>
          <multiselect
              v-model="selectedWidget.profile"
              label="label"
              track-by="id"
              :options="profiles"
              :multiple="true"
              select-label=""
              selected-label=""
              deselect-label=""
          ></multiselect>
        </div>
      </div>

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <button type="button"
                class="em-secondary-button em-w-auto"
                @click.prevent="typeof widget !== 'undefined' ? $modal.hide('modalWidgetSettings' + widget.id) : $modal.hide('modalWidgetSettings')">
          {{ translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR") }}
        </button>
        <button type="button"
                class="em-primary-button em-w-auto"
                @click.prevent="saveWidget"
        >{{ translate("COM_EMUNDUS_ONBOARD_SAVE") }}</button>
      </div>

      <div class="em-page-loader" v-if="loading"></div>
    </modal>
  </span>
</template>

<script>
import types from '../../../data/dashboard/typeRows'
import Multiselect from 'vue-multiselect';

import { PrismEditor } from "vue-prism-editor";
import "vue-prism-editor/dist/prismeditor.min.css"; // import the styles somewhere

// import highlighting library (you can use any library you want just return html string)
import { highlight, languages } from "prismjs/components/prism-core";
import "prismjs/components/prism-clike";
import "prismjs/components/prism-javascript";

export default {
  name: "WidgetSettings",
  props: {
    widget: Object,
    profiles: Array
  },
  components: {
    Multiselect,
    PrismEditor
  },
  data() {
    return {
      selectedWidget: {},
      types: [
        {
          label: 'COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ARTICLE',
          value: 'article'
        },
        {
          label: 'COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CHART',
          value: 'chart'
        },
        {
          label: 'COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_OTHER',
          value: 'other'
        }
      ],
      chart_types: [],
      profiles: [],

      loading: false,
      displayCode: false,
    };
  },
  methods: {
    saveWidget(){},

    buildWidget() {
      this.chart_types = types['types'];
      this.chart_types.forEach((type) => {
        type.label = this.translate(type.label);
      })

      if(typeof this.widget !== 'undefined') {
        this.selectedWidget = this.widget;

        this.selectedWidget.profile = [];

        if(this.selectedWidget.profiles != null) {
          this.selectedWidget.profiles.split(',').forEach((profile) => {
            let profile_found = this.profiles.findIndex((p, index) => {
              if(p.id === profile)
                return true;
            });
            this.selectedWidget.profile.push(this.profiles[profile_found]);
          })
        }

        if(this.selectedWidget.chart_type != null){
          let chart_type_found = this.chart_types.findIndex((chart, index) => {
            if(chart.value === this.selectedWidget.chart_type)
              return true;
          });
          this.selectedWidget.chart_type = this.chart_types[chart_type_found];
        }
      } else{
        this.selectedWidget = {
          article_id: null,
          chart_type: '',
          class: '',
          eval: 'php|',
          id: null,
          label: '',
          name: 'custom',
          params: null,
          profile: null,
          published: "1",
          size: 10,
          size_small: 12,
          type: ''
        }
        this.displayCode = true;
      }
    },

    highlighter(code) {
      return highlight(code, languages.js, 'js'); //returns html
    },
  },
  watch: {},
};
</script>

<style>
@import "../../../assets/css/modal.scss";
@import "../../../assets/css/code_editor/prism-emundus.scss";

.code-editor{
  padding: 16px;
  border: solid 1px #cecece;
  border-radius: 4px;
}

.my-editor {
  background: #2d2d2d;
  color: #ccc;
  font-family: Fira code, Fira Mono, Consolas, Menlo, Courier, monospace;
  font-size: 14px;
  line-height: 1.5;
  padding: 5px;
  display: flex;
  width: 100%;
  border-radius: 4px;
}
.prism-editor__container textarea{
  background: transparent !important;
  border: unset !important;
  color: white !important;
}
.prism-editor__container textarea:hover,.prism-editor__container textarea:focus{
  box-shadow: unset !important;
}

.prism-editor__editor{
  opacity: 0 !important;
}

.prism-editor__line-numbers{
  padding-top: 10px !important;
  background: transparent !important;
  padding-left: 5px !important;
}

.prism-editor__container{
  width: 100% !important;
}
</style>
