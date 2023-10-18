<template>
  <div v-if="params.length > 0">
    <div v-for="param in displayedParams" class="form-group mb-4">
      <label>{{ translate(param.label) }}</label>

      <!-- DROPDOWN -->
      <div v-if="param.type === 'dropdown'">
        <select v-model="section.params[param.name]" class="em-w-100">
          <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
        </select>
      </div>

      <!-- TEXTAREA -->
      <textarea v-else-if="param.type === 'textarea'" v-model="section.params[param.name]" class="em-w-100"></textarea>

      <!-- INPUT (TEXT,NUMBER) -->
      <input v-else :type="param.type" v-model="section.params[param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>

      <!-- HELPTEXT -->
      <label v-if="param.helptext !== ''" style="font-size: small">{{ translate(param.helptext) }}</label>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
export default {
  name: "FormBuilderSectionParams",
  props: {
    section: {
      type: Object,
      required: false
    },
    params: {
      type: Array,
      required: false
    }
  },
  data: () => ({
    loading: false,
  }),
  computed: {
    sysadmin: function() {
      return parseInt(this.$store.state.global.sysadminAccess);
    },
	  displayedParams() {
			return this.params.filter((param) => {
				return (param.published && !param.sysadmin_only) || (this.sysadmin && param.sysadmin_only && param.published)
			});
	  }
  }
}
</script>

<style scoped>

</style>
