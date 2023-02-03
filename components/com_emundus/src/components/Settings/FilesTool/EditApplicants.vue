<template>
  <div class="em-settings-menu">
    <div class="em-w-80">

      <div class="form-group em-flex-center em-w-100 em-mb-16" v-for="(param, index) in params" :key="index">
        <label :for="'param_' + index">{{param.label}}</label>
        <select class="dropdown-toggle w-select" :id="'param_' + index" v-model="param.value" style="margin-bottom: 0" @change="saveEmundusParam(param)">
          <option v-for="option in param.options" :key="option.value"  :value="option.value">{{option.label}}</option>
        </select>
      </div>

    </div>
  </div>
</template>

<script>
import axios from "axios";

import mixin from "com_emundus/src/mixins/mixin";

const qs = require("qs");

export default {
  name: "EditApplicants",

  components: {},

  props: {},

  mixins: [mixin],

  data() {
    return {
      params: {
        applicant_can_renew: {
          label: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_APPLICANT_CAN_RENEW"),
          param: 'applicant_can_renew',
          options: [
            {
              label: this.translate("JNO"),
              value: 0,
            },
            {
              label: this.translate("JYES"),
              value: 1,
            },
            {
              label: this.translate("COM_EMUNDUS_APPLICANT_CAN_RENEW_CAMPAIGN"),
              value: 2,
            },
            {
              label: this.translate("COM_EMUNDUS_APPLICANT_CAN_RENEW_YEAR"),
              value: 3,
            },
          ],
          value: 0,
        },
        can_edit_until_deadline: {
          label: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_APPLICANT_CAN_EDIT_UNTIL_DEADLINE"),
          param: 'can_edit_until_deadline',
          options: [
            {
              label: this.translate("JNO"),
              value: 0,
            },
            {
              label: this.translate("JYES"),
              value: 1,
            },
          ],
          value: 0,
        },
        can_submit_encrypted: {
          label: this.translate("COM_EMUNDUS_ONBOARD_SETTINGS_APPLICANT_CAN_SUBMIT_ENCRYPTED"),
          param: 'can_submit_encrypted',
          options: [
            {
              label: this.translate("JNO"),
              value: 0,
            },
            {
              label: this.translate("JYES"),
              value: 1,
            },
          ],
          value: 0,
        }
      },
      config: {},
    };
  },

  created() {
    this.getEmundusParams();
  },

  methods: {
    getEmundusParams() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemundusparams")
          .then(response => {
            this.config = response.data.config;
            this.params.applicant_can_renew.value = parseInt(this.config.applicant_can_renew);
            this.params.can_edit_until_deadline.value = parseInt(this.config.can_edit_until_deadline);
            this.params.copy_application_form.value = parseInt(this.config.copy_application_form);
            this.params.can_submit_encrypted.value = parseInt(this.config.can_submit_encrypted);
          });
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
          param: param.param,
          value: param.value,
        })
      }).then(() => {
        this.$emit('updateSaving',false);
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
      });
    },
  },
};
</script>
<style scoped>
.form-group label{
  width: 100%;
}
.dropdown-toggle{
  width: 30%;
}
</style>
