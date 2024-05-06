<template>
  <div class="em-settings-menu">

    <div class="em-w-80" v-if="!loading">
      <div class="form-group em-flex-center em-w-100 em-mb-16" v-for="(param, indexParam) in displayedParams"
           :key="param.param">
        <label :for="'param_' + param.param" class="flex items-center font-medium"
               v-if="visibility(param) && param.type_field !== 'authentification'">
          {{ translate(param.label) }}
          <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
        </label>

        <div v-if="(param.type_field === 'toggle')&&(visibility(param))">
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" :v-model="param.value" v-model="param.value"
                   @click="toggle(param)">
            <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"></span>
          </label>
        </div>


        <div v-if="(param.type_field === 'yesno')&&(visibility(param))">
          <div class="flex-row flex items-center">
            <button v-for="(option, indexOfOptions) in param.options" type="button"
                    :id="'BtYN'+indexParam+'_'+indexOfOptions" :name="'YNbuttton'+param.name"
                    :class="['YesNobutton'+option.value ,{'active': param.value ===1} , {'click':param.value === 0},
                    {'disabled-element':editableParamsServerMail !== null && !editableParamsServerMail || ( param.editable===false)}]"
                    :disabled="editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)"
                    class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
                    v-model="param.value" @click="clickYN(param,indexParam, indexOfOptions)">{{
                translate(option.label)
              }}
            </button>
          </div>
        </div>

        <select v-if="(param.type_field === 'select')&&(visibility(param)) " class="dropdown-toggle w-select"
                :id="'param_' + param.param" v-model="param.value" style="margin-bottom: 0"
                @focusout="saveEmundusParam(param)"
                :class="{'disabled-element':editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)}"
                :disabled="editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)">
          <option v-for="option in param.options" :key="option.value" :value="option.value">{{
              translate(option.label)
            }}
          </option>
        </select>

        <input v-if="(param.type_field ==='text')&&(visibility(param))" :type="param.type" class="form-control"
               :placeholder="param.placeholder" :id="'param_' + param.param" v-model="param.value"
               :maxlength="param.maxlength" style="margin-bottom: 0" @focusout="handleInput(param)"
               @pressEnter="handleInput(param)"
               :class="{'disabled-element':editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)}"
               :readonly="editableParamsServerMail !== null && !editableParamsServerMail || ( param.editable===false)"
        >

        <div v-if="param.type==='email' && (param.editable!==undefined && param.editable===true)" :id="'emailCheck-'+param.param"
             :style="{ color: emailValidationColor[param.param] }">
          {{
            emailValidationMessage[param.param]
          }}
        </div>

        <textarea v-if="param.type_field === 'textarea'" :id="'param_' + param.param" v-model="param.value"
                  :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)"
                  :class="{'disabled-element':editableParamsServerMail !== null && !editableParamsServerMail || ( param.editable===false)}"
                  :readonly="editableParamsServerMail !== null && !editableParamsServerMail || ( param.editable===false)">
        </textarea>

        <div v-if="(param.type_field ==='authentification')&&(visibility(param)&&AuthSMTP)">
          <label :for="'param_' + param.param" class="flex items-center font-medium">
            {{ translate(param.label) }}
            <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
          </label>
          <input :type="param.type" class="form-control"
                 :placeholder="param.placeholder" :id="'param_' + param.param" v-model="param.value"
                 :maxlength="param.maxlength" style="margin-bottom: 0" @focusout="saveEmundusParam(param)"
                 :class="{'disabled-element':editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)}"
                 :readonly="editableParamsServerMail !== null && !editableParamsServerMail || (param.editable===false)">
        </div>


      </div>

    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";

import mixin from "com_emundus/src/mixins/mixin";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "EditEmailJoomla",
  components: {},
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
      params: {},
      config: {},

      CustomConfigServerMail: {},

      emailValidationMessage: [],
      emailValidationColor: [],
      YNButtons: Array(30).fill(false),
      AuthSMTP: false,
      editableParamsServerMail: null,
    };
  },

  created() {
    this.params = require('../../../../data/settings-' + this.$props.type + '.json');
    let firstWord = this.$props.type.split('-')[0];
    if (firstWord === 'mail') {
      setTimeout(() => {
        if (this.params['smtpauth']) {
          this.AuthSMTP = this.params['smtpauth'].value
        }
      }, 1000);
      let secondWord = this.$props.type.split('-')[1];
      if (secondWord === 'SERVER') {
        let thirdWord = this.$props.type.split('-')[2];
        if (thirdWord === 'custom') {
          this.editableParamsServerMail = this.$props.customValue;
        }
      }
    }
  },
  mounted() {
    this.$parent.$on('changeMailOnline', this.handleSignalParent);
    this.getEmundusParams();
  },

  methods: {
    handleSignalParent(value) {
      if (this.params['mailonline']) {
        if (value.value !== undefined) {
          this.params['mailonline'].value = value.value;
          if (this.params['mailonline'].value !== undefined) {
            this.saveEmundusParam(this.params['mailonline']);
          }
        }

      }
    },
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
            this.$emit('stateOfConfig', this.params);
          });

    },
    visibility(param) {
      if (param.section && param.section[0] === 'mail') {
        return this.config['joomla'].mailonline === '1';
      }
      return true;
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
    toggle(param) {
      param.value = !param.value;
      param.value = param.value ? 1 : 0;
      this.saveEmundusParam(param);
    },
    handleInput(param) {
      if (param.type === 'email') {
        this.validate(param);
      } else {
        this.saveEmundusParam(param);
      }
    },
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
    clickYN(param, index, indexOfOptions) {
      param.value = indexOfOptions;
      param.value = param.value ? 1 : 0;
      if (param.param === 'smtpauth') {
        this.AuthSMTP = indexOfOptions;
      }
      this.saveEmundusParam(param)

      this.YNButtons[index] = indexOfOptions;
    },
    goTo(url, newTab) {
      if (newTab) {
        window.open(url, '_blank');
      } else {
        window.location.href = url;
      }
    }
  },
  computed: {
    displayedParams() {
      return Object.values(this.params).filter((param) => {
        return param.displayed;
      });
    },
  },

  watch: {
    '$props.customValue': function (newVal) {
      this.editableParamsServerMail = newVal;
    }
  },

};
</script>
<style scoped>
.form-group label {
  width: 100%;
}

.dropdown-toggle {
  width: 30%;
}

.YesNobutton1 {
  border: 1px solid #008A35;
  background-color: white;
  color: #008A35;
}

.YesNobutton1:hover {
  background-color: #008A35;
  color: black;
}

.YesNobutton1.active {
  background-color: #008A35;
  color: white;
}

.YesNobutton0 {
  border: 1px solid #FF0000;
  background-color: white;
  color: #FF0000;
}

.YesNobutton0:hover {
  background-color: #FF0000;
  color: white;
}

.YesNobutton0.click {
  background-color: #FF0000;
  color: white;
}

.disabled-element {
  cursor: not-allowed;
}
</style>
