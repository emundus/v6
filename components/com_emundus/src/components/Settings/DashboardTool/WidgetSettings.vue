<template>
  <!-- modalC -->
  <span :id="'modalWidgetSettings' + widget.id">
    <modal
        :name="'modalWidgetSettings' + widget.id"
        transition="little-move-left"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="true"
    >

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4">
          {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_EDIT') }}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalWidgetSettings' + widget.id)">
          <span class="material-icons">close</span>
        </button>
      </div>

      <div>
        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_NAME') }} : </div>
          <input type="text" class="em-w-100" />
        </div>

        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_TYPE') }} : </div>
          <select v-model="selectedWidget.type">
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

        <div class="em-mb-16" v-if="selectedWidget.type === 'chart' || selectedWidget.type === 'other'">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CODE') }} : </div>
        </div>

        <div class="em-mb-16">
          <div class="em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ACCESS') }} : </div>
        </div>
      </div>

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <button type="button"
                class="em-secondary-button em-w-auto"
                @click.prevent="$modal.hide('modalWidgetSettings' + widget.id)">
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

export default {
  name: "WidgetSettings",
  props: {
    widget: Object
  },
  components: {
    Multiselect
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

      loading: false
    };
  },
  created() {
    this.selectedWidget = this.widget;
    this.chart_types = types['types'];
    this.chart_types.forEach((type) => {
      type.label = this.translate(type.label);
    })
  },
  methods: {
    saveWidget(){}
  },
  watch: {},
};
</script>

<style scoped>
@import "../../../assets/css/modal.scss";
</style>
