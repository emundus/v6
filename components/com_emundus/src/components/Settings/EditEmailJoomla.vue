<template>
  <div class="em-settings-menu">




    <!-- TODO: Create a information panel component -->
    <!-- -->

    <div class="w-5/6" v-if="!loading">

      <!-- GLOBAL CONFIGURATION -->
      <div class="mb-4 flex items-center">
        <div class="em-toggle">
          <input type="checkbox"
                 class="em-toggle-check"
                 :id="'published'"
                 v-model="enableEmail"
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <span for="published" class="ml-2">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_ENABLE') }}</span>
      </div>

      <div class="mt-6" v-if="enableEmail">
        <label class="font-medium">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_GLOBAL') }}</label>
        <div class="grid grid-cols-2 gap-6 p-3 bg-[#008A351A] rounded" v-if="enableEmail">
          <div class="form-group w-full" v-for="param in globalInformations"
               :key="param.param">
            <label :for="'param_' + param.param" class="flex items-center font-medium">
              {{ translate(param.label) }}
              <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
            </label>

            <Parameter :parameter="param" @needSaving="updateParameterToSaving"/>
          </div>
        </div>
      </div>

      <!-- CUSTOM CONFIGURATION -->
      <div class="mt-8 mb-4 flex items-center" v-if="enableEmail">
        <div class="em-toggle">
          <input type="checkbox"
                 class="em-toggle-check"
                 :id="'published'"
                 v-model="customConfiguration"
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <span for="published" class="ml-2">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_CUSTOM') }}</span>
      </div>

      <div class="mt-6" v-if="customConfiguration && enableEmail">
        <label class="font-medium">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_CONFIGURATION') }}</label>
        <div class="grid grid-cols-2 gap-6 p-3 bg-[#008A351A] rounded">
          <div class="form-group w-full !ml-0 mr-0 mt-0"
               :class="['smtpsecure','smtpauth'].includes(param.param) ? 'col-span-full' : ''"
               v-for="param in customInformations"
               v-if="checkSmtpAuth(param)"
               :key="param.param">
            <label :for="'param_' + param.param" class="flex items-center font-medium">
              {{ translate(param.label) }}
              <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
            </label>

            <Parameter :parameter="param" @needSaving="updateParameterToSaving"/>
          </div>
        </div>
      </div>
    </div>

    <!-- TODO: Adding buttons to save and test the configuration -->
    <!-- -->

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";

import mixin from "@/mixins/mixin";
import Swal from "sweetalert2";
import Parameter from "@/components/Settings/Files/Parameter.vue";

const qs = require("qs");

export default {
  name: "EditEmailJoomla",
  components: {Parameter},
  props: {
    type: String,
    showValueMail: {
      type: Number,
      default: -1,
      required: false
    },
    customValue: {
      type: Number,
      default: -1,
      required: false
    }
  },

  mixins: [mixin],

  data() {
    return {
      loading: true,
      parametersUpdated: [],

      params: [],
      enableEmail: false,
      customConfiguration: false,
      globalInformations: [],
      customInformations: [],
      config: {},

      CustomConfigServerMail: {},

      //TODO: Move to Parameter component
      emailValidationMessage: [],
      emailValidationColor: [],


      AuthSMTP: false,
      editableParamsServerMail: null,
    };
  },

  created() {
    this.globalInformations = require('../../../data/settings/emails/global.json');
    this.customInformations = require('../../../data/settings/emails/custom.json');
  },
  mounted() {
    this.getEmundusParams();
  },

  methods: {
    getEmundusParams() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemundusparams")
          .then(response => {
            this.config = response.data;

            Object.values(this.params).forEach((param) => {

              param.value = this.config[param.component][param.param];
              if ((param.value === "1") || (param.value === true) || (param.value === "true")) {
                param.value = 1;
              }
              if ((param.value === "0") || (param.value === false) || (param.value === "false")) {
                param.value = 0;
              }
            });
            this.loading = false;

            this.enableEmail = this.config['joomla']['mailonline'];
          });

    },
    saveEmundusParam(param) {
      this.$emit('updateSaving', true);

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=settings&task=updateemundusparam',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          component: param.component,
          param: param.param,
          value: param.value,
        })
      }).then(() => {
        this.$emit('updateSaving', false);
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
        this.$emit('stateOfConfig', this.params);
      });
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

    checkSmtpAuth(param) {
      if (param.param === 'smtpuser' || param.param === 'smtppass') {
        let smtpAuthParameter = this.customInformations.find((element) => element.param === 'smtpauth');

        if(smtpAuthParameter.value == 1) {
          return true;
        } else {
          return false;
        }
      }

      return true;
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

    //TODO: Move to Parameter component
    validateEmail(email) {
      let res = /^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/;
      return res.test(email);
    },
    validate(paramEmail) {
      let paramEmailId = paramEmail.param;
      let email = this.params[paramEmailId].value;
      this.emailValidationMessage[paramEmail.param] = "";
      if (this.validateEmail(email)) {
        this.$set(this.emailValidationMessage, paramEmail.param, email + " is valid");
        this.$set(this.emailValidationColor, paramEmail.param, "green");
        this.saveEmundusParam(paramEmail);
      } else {
        this.$set(this.emailValidationMessage, paramEmail.param, email + " is not valid");
        this.$set(this.emailValidationColor, paramEmail.param, "red");
      }
      return false;
    },
  },
  computed: {},

  watch: {
    parametersUpdated: function (val) {
      this.$emit('needSaving', val.length > 0)
    }
  },

};
</script>
<style scoped>
.form-group label {
  width: 100%;
}
</style>
