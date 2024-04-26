<template>
  <div class="em-settings-menu">
    <div class="em-w-80" v-if="!loading">

      <div class="form-group em-flex-center em-w-100 em-mb-16" v-for="(param, indexParam) in displayedParams" :key="param.param">
          <label :for="'param_' + param.param" class="flex items-center font-medium" v-if="(param.param !== 'smtpuser')&&(param.param !== 'smtppass')">
            {{ translate(param.label) }}
            <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
          </label>

        <div v-if="param.type === 'toggle'">
          <label class="inline-flex items-center cursor-pointer">

            <input type="checkbox" class="sr-only peer" v-model="param.value"  @change="toggle(param)">
            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"></span>
          </label>
          </div>



        <div v-if="param.type === 'yesno'">
          <div class="flex-row flex items-center">
            <button type="button" :id="'BtN'+indexParam" @click="clickYN(false, indexParam , param)"
                    :class="{'red-YesNobutton': true, 'active': param.value === '0'}"
                    class="red-YesNobutton  focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
              Non
            </button>
            <button type="button" :id="'BtY'+indexParam" @click="clickYN(true, indexParam, param)"
                    :class="{'green-YesNobutton': true, 'active': param.value === '1'}"
                    class="focus:ring-neutral-50 focus:outline-none  font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
              Oui
            </button>
          </div>
        </div>

        <select v-if="(param.type !== 'yesno') && (param.options)" class="dropdown-toggle w-select" :id="'param_' + param.param" v-model="param.value" style="margin-bottom: 0" @focusout="saveEmundusParam(param)">
          <option v-for="option in param.options" :key="option.value" :value="option.value">{{ translate(option.label) }}</option>
        </select>

        <div v-else-if="param.type === 'email'">
        <input  type="email" class="form-control" :id="'param_' + param.param" v-model="param.value" style="margin-bottom: 0" @change="validate(param)">
          <div id="emailCheck" :style="{ color: emailValidationColor }">{{ emailValidationMessage }}</div>
        </div>
        <textarea v-if="param.type === 'textarea'" :id="'param_' + param.param" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)">
        </textarea>

        <input v-if="param.type==='text'" type="text"  class="form-control" :placeholder="param.placeholder" :id="'param_' + param.param" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)">
        <input v-if="param.type==='number'" type="number" class="form-control" :placeholder="param.placeholder" :id="'param_' + param.param" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)">

        <div v-if="(AuthSMTP===true )">
          <label :for="'param_' + param.param" class="flex items-center" v-if="(param.param === 'smtpuser')&&(param.param === 'smtppass')">
            {{ translate(param.label) }}
            <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
          </label>
        <input v-if="param.type==='login'" type="text" class="form-control" :id="'param_' + param.param" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)">
        <input v-if="param.type==='password'" type="password" class="form-control" :id="'param_' + param.param" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)"></div>
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
  name: "EditApplicants",
  components: {},
  props: {
    type: String
  },

  mixins: [mixin],

  data() {
    return {
      loading: true,
      params: {},
      config: {},

      emailValidationMessage: "",
      emailValidationColor: "",
      YNButtons: [],
      AuthSMTP: true,
      showAllEmailparams: true,
    };
  },

  created() {
    this.params = require('../../../../data/settings-'+this.$props.type+'.json');
    this.getEmundusParams();
  },

  methods: {
    getEmundusParams() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemundusparams")
          .then(response => {
            this.config = response.data;

            Object.values(this.params).forEach((param) => {
              param.value = this.config[param.component][param.param];
            });

            this.loading = false;
          });
      console.log(this.params);
    },

    saveEmundusParam(param) {
      this.$emit('updateSaving',true);

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
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
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
      this.showAllEmailparams = param.value;
      this.saveEmundusParam(param);
    },
    validateEmail(email) {
  let res = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
  return res.test(email);
},
    validate(paramEmail) {
      let paramEmailId = paramEmail.param;
      let email = this.params[paramEmailId].value;
      this.emailValidationMessage = "";
      if(this.validateEmail(email)) {
        this.emailValidationMessage = email + " is valid";
        this.emailValidationColor = "green";
        this.saveEmundusParam(paramEmail);
      } else {
        this.emailValidationMessage = email + " is not valid";
        this.emailValidationColor = "red";
      }
      return false;
    },
    clickYN(bool, index, param) {
      if(param.param === 'smtpauth') {
        this.AuthSMTP = bool;
      }
      param.value = bool ? 1 : 0;
      this.saveEmundusParam(param)
      this.YNButtons[index] = bool;
      if (bool) {
        document.getElementById('BtY' + index).classList.add('active');
        document.getElementById('BtN' + index).classList.remove('active');
      } else {
        document.getElementById('BtN' + index).classList.add('active');
        document.getElementById('BtY' + index).classList.remove('active');
      }
    },
  },
	computed: {
		displayedParams() {
			return Object.values(this.params).filter((param) => {
				return param.displayed;
			});
		}
	}
};
</script>
<style scoped>
.form-group label{
  width: 100%;
}
.dropdown-toggle{
  width: 30%;
}

.green-YesNobutton {
  border: 1px solid #008A35;
  background-color: white;
  color: #008A35;
}

.green-YesNobutton:hover {
  background-color: #008A35;
  color: black;
}

.green-YesNobutton.active {
  background-color: #008A35;
  color: white;
}

.red-YesNobutton {
  border: 1px solid #FF0000;
  background-color: white;
  color: #FF0000;
}

.red-YesNobutton:hover {
  background-color: #FF0000;
  color: white;
}

.red-YesNobutton.active {
  background-color: #FF0000;
  color: white;
}
</style>
