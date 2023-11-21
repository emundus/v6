<template>
  <div class="em-settings-menu">
    <div class="em-w-80" v-if="!loading">

      <div class="form-group em-flex-center em-w-100 em-mb-16" v-for="(param, index) in params" :key="index">
        <label :for="'param_' + index">{{ translate(param.label) }}</label>
        <select class="dropdown-toggle w-select" :id="'param_' + index" v-model="param.value" style="margin-bottom: 0" @change="saveEmundusParam(param)">
          <option v-for="option in param.options" :key="option.value" :value="option.value">{{ translate(option.label) }}</option>
        </select>
      </div>

    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";

import mixin from "com_emundus/src/mixins/mixin";

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
