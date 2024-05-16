<template>
  <div class="em-settings-menu">

    <div class="w-full">
      <div class="w-4/5" v-if="!loading">
        <div class="form-group flex flex-col mb-6 w-full" v-for="(param, indexParam) in displayedParams"
             :key="param.param">
          <label :for="'param_' + param.param" class="flex items-center font-medium">
            {{ translate(param.label) }}
            <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
          </label>

          <Parameter :parameter="param" @needSaving="updateParameterToSaving"/>
        </div>

        <Global v-if="displayLanguage === true"/>
      </div>

      <button class="btn btn-primary float-right" v-if="parametersUpdated.length > 0" @click="saveSiteSettings">
        {{ translate("COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_SAVE") }}
      </button>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import Parameter from "@/components/Settings/Parameter.vue";
import axios from "axios";
import Swal from "sweetalert2";
import Global from "@/components/Settings/Translation/Global.vue";
import settingsService from "../../services/settings";


export default {
  name: "SiteSettings",
  components: {Global, Parameter},
  props: {
    json_source: {
      type: String,
      required: true,
    },
    displayLanguage: {
      type: Boolean,
      default: false
    },
  },

  mixins: [],

  data() {
    return {
      parameters: [],
      parametersUpdated: [],

      loading: true,
      config: {},
    }
  },
  created() {
    this.parameters = require('../../../data/settings/' + this.$props.json_source);

    this.getEmundusParams();
  },
  mounted() {
  },
  methods: {
    getEmundusParams() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemundusparams")
          .then(response => {
            this.config = response.data;

            Object.values(this.parameters).forEach((parameter) => {

              if (parameter.type === 'keywords') {
                let keywords = this.config[parameter.component][parameter.param].split(',');
                parameter.value = keywords.map((keyword) => {
                  return {
                    name: keyword,
                    code: keyword
                  }
                });
              } else {
                parameter.value = this.config[parameter.component][parameter.param];
              }

              if ((parameter.value === "1") || (parameter.value === true) || (parameter.value === "true")) {
                parameter.value = 1;
              }
              if ((parameter.value === "0") || (parameter.value === false) || (parameter.value === "false")) {
                parameter.value = 0;
              }
            });

            this.loading = false;
          });
    },

    updateParameterToSaving(needSaving, parameter) {
      if (needSaving) {
        let checkExisting = this.parametersUpdated.find((param) => param.param === parameter.param);
        if (!checkExisting) {
          this.parametersUpdated.push(parameter);
        }
      } else {
        this.parametersUpdated = this.parametersUpdated.filter((param) => param.param !== parameter.param);
      }
    },

    displayHelp(message) {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_SWAL_HELP_TITLE"),
        text: this.translate(message),
        showCancelButton: false,
        confirmButtonText: this.translate("COM_EMUNDUS_SWAL_OK_BUTTON"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          confirmButton: 'em-swal-confirm-button',
          actions: "em-swal-single-action",
        },
      });
    },

    async saveSiteSettings() {
      let params = [];
      this.parametersUpdated.forEach((param) => {
        params.push({
          component: param.component,
          param: param.param,
          value: param.value
        });
      });

      settingsService.saveParams(params)
          .then(() => {
            this.parametersUpdated = [];
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_SUCCESS"),
              text: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_SAVE_SUCCESS"),
              showCancelButton: false,
              showConfirmButton: false,
              customClass: {
                title: 'em-swal-title'
              },
              timer: 2000,
            });
          })
          .catch(() => {
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ERROR"),
              text: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_SAVE_ERROR"),
              showCancelButton: false,
              confirmButtonText: this.translate("COM_EMUNDUS_SWAL_OK_BUTTON"),
              reverseButtons: true,
              customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                actions: "em-swal-single-action",
              },
            });
          });
    },

    async saveMethod() {
      await this.saveSiteSettings();
      return true;
    }
  },
  computed: {
    displayedParams() {
      return this.parameters.filter(param => param.displayed === true);
    }
  },
  watch: {
    activeSection: function (val) {
      this.$emit('sectionSelected', this.sections[val])
    },
    parametersUpdated: function (val) {
      this.$emit('needSaving', val.length > 0)
    }
  },
}
</script>

<style scoped>

</style>