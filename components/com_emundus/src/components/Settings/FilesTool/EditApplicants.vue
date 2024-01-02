<template>
  <div class="em-settings-menu">
    <div class="em-w-80" v-if="!loading">

      <div class="form-group em-flex-center em-w-100 em-mb-16" v-for="(param, index) in params" :key="index">
          <label :for="'param_' + index" class="flex items-center">
            {{ translate(param.label) }}
            <span v-if="param.helptext" class="material-icons-outlined ml-2" @click="displayHelp(param.helptext)">help_outline</span>
          </label>


        <select v-if="param.options" class="dropdown-toggle w-select" :id="'param_' + index" v-model="param.value" style="margin-bottom: 0" @change="saveEmundusParam(param)">
          <option v-for="option in param.options" :key="option.value" :value="option.value">{{ translate(option.label) }}</option>
        </select>
        <input v-else type="text" class="form-control" :id="'param_' + index" v-model="param.value" :maxlength="param.maxlength" style="margin-bottom: 0" @change="saveEmundusParam(param)">
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
    }
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
