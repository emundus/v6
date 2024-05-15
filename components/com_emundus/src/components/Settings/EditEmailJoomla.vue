<template>
  <div class="em-settings-menu">
    <div class="w-5/6" v-if="!loading">

      <!-- GLOBAL CONFIGURATION -->
      <div class="mb-4 flex items-center">
        <div class="em-toggle">
          <input type="checkbox"
                 class="em-toggle-check"
                 :id="'published'"
                 v-model=" computedEnableEmail "
          />
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
        <span for="published" class="ml-2">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_ENABLE') }}</span>
      </div>

      <div class="mt-6" v-if="enableEmail  && computedEnableEmail">
        <label class="font-medium">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_EMAIL_GLOBAL') }}</label>
        <div class="grid grid-cols-2 gap-6 p-3 bg-[#008A351A] rounded">
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
      <div class="mt-8 mb-4 flex items-center" v-if="enableEmail && computedEnableEmail">
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

    <div v-if=" !computedEnableEmail" class="bg-orange-300 rounded flex items-center pb-2">
        <span class="material-icons-outlined scale-150 ml-2 mt-2">warning</span>
        <p class="ml-2 mt-2">{{ translate(warning) }}</p>
      </div>

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
    },
    warning: {
      type: String,
      default: "",
      required: false
    },
  },

  mixins: [mixin],

  data() {
    return {
      loading: true,
      parametersUpdated: [],

      params: [],
      enableEmail: 0,
      customConfiguration: null,
      globalInformations: [],
      customInformations: [],
      config: {},

      CustomConfigServerMail: {},
      ParamJoomlaEmundusExtensions: {},

      //TODO: Move to Parameter component
      emailValidationMessage: [],
      emailValidationColor: [],

      AuthSMTP: false,
      editableParamsServerMail: null,
    };
  },

  created() {
    this.globalInformations = require('../../../data/settings/elementInSection/emails/global.json');
    this.customInformations = require('../../../data/settings/elementInSection/emails/custom.json');
  },
  mounted() {
    this.getEmundusParamsJoomlaConfiguration()
    this.getEmundusParamsJoomlaExtensions()
  },

  methods: {
    getEmundusParamsJoomlaConfiguration() {
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

    getEmundusParamsJoomlaExtensions() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemundusparamExtensions")
          .then(response => {
            this.CustomConfigServerMail = response.data;
            this.filterEmundusParamsJoomlaExtensions('email_')
          });
    },
    filterEmundusParamsJoomlaExtensions(filter) {
      let result = [];
      for (let index in this.CustomConfigServerMail) {
        if (index.includes(filter)) {
          let obj = {};
          obj[index] = this.CustomConfigServerMail[index];
          result.push(obj);
        }
      }
      this.ParamJoomlaEmundusExtensions = result;
     this.customConfiguration =  this.getEmundusparamsEmailValue('custom_email_conf'  , 'boolean');
    },
    getEmundusparamsEmailValue(specificValue , type){
      let variableInput = null;
      for (let index in this.ParamJoomlaEmundusExtensions) {
        if (this.ParamJoomlaEmundusExtensions[index][specificValue]) {
          if (type === 'boolean') {
            if (this.ParamJoomlaEmundusExtensions[index][specificValue] ==  1 || this.ParamJoomlaEmundusExtensions[index][specificValue] == true || this.ParamJoomlaEmundusExtensions[index][specificValue] == "true") {
              variableInput = true;}
            else{
              variableInput = false;
            }
            return variableInput;
          }
        }
      }
    },
    updateValueParamsEmundusExtensions(variable, value) {
      console.log(this.ParamJoomlaEmundusExtensions)
      console.log("update start")
      console.log(this.CustomConfigServerMail[variable]);
      for (let index in this.ParamJoomlaEmundusExtensions) {
        if (this.ParamJoomlaEmundusExtensions[index][variable]) {
          this.ParamJoomlaEmundusExtensions[index][variable] = value;
          this.CustomConfigServerMail[variable] = value;
        }
      }
      console.log(this.ParamJoomlaEmundusExtensions)
      console.log("update end");
      this.saveEmundusParamsExtensions();
    },
    saveEmundusParamsExtensions(){
      //this.$emit('updateSaving', true);
      console.log(this.CustomConfigServerMail);
      axios.post("index.php?option=com_emundus&controller=settings&task=saveemundusparamExtensions&params=" + JSON.stringify(this.CustomConfigServerMail))
          .then(() => {
            //this.$emit('updateSaving', false);
            //this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
            //this.$emit('stateOfConfig', this.params);
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

    /*
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
*/


  },
  computed: {
    computedEnableEmail: {
      get() {
        return this.enableEmail == 1 ? true : false;
      },
      set(value) {
        this.enableEmail = value ? "1" : "0";
      },
    },
  },

  watch: {
    parametersUpdated: function (val) {
      this.$emit('needSaving', val.length > 0)
    },
    enableEmail: function (val , oldVal) {
      if (val == 1)
      {
        val=1 ;
      }
      else if (val == 0)
      {
        val=0 ;
      }
      if  (val != oldVal)
      {
        this.saveEmundusParam({
          component: 'joomla',
          param: 'mailonline',
          value: val,
        });
      }

    },
    customConfiguration: function (val , oldVal) {
      if (val == 1)
      {
        val="1" ;
      }
      else if (val == 0)
      {
        val="0" ;
      }
      if (oldVal != null)
      {
        this.updateValueParamsEmundusExtensions('custom_email_conf', val);
      }
    },
  },


};
</script>
<style scoped>
.form-group label {
  width: 100%;
}
</style>
