<template>
  <div v-if="params.length > 0">
    <div v-for="param in displayedParams" class="form-group mb-4">
      <label>{{ translate(param.label) }}</label>


      <!-- TEXTAREA -->
      <textarea v-if="param.type === 'textarea'" v-model="section.params[param.name]" class="em-w-100"></textarea>

      <!-- INPUT (TEXT,NUMBER) -->
      <input v-if="param.type === 'number' || param.type === 'text'" :type="param.type"
             v-model="section.params[param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>

      <!-- HELPTEXT -->
      <label v-if="param.helptext !== ''" style="font-size: small">{{ translate(param.helptext) }}</label>

      <!-- DROPDOWN -->
      <div v-if="param.type === 'dropdown'">
        <div v-if="param.name !== 'repeat_group_button'">
            <select v-model="section.params[param.name]" class="em-w-100">
              <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
            </select>
        </div>
        <div v-if="param.name === 'repeat_group_button'">
          <div v-if="$data.repetable === true">
            <select v-model="section.params[param.name]" class="em-w-100">
              <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
            </select>
          </div>
        </div>
        <div v-if="param.name === 'repeat_group_button'">
        <div v-if="$data.repetable === false">
          <select disabled>
            <option>jojo</option>
          </select>
        </div>
        </div>


      </div>
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
    },
    repetable: {
      type: Boolean,
      required: true,
    }
  },
  data: () => ({
    loading: false,
    repetable: true,
  }),
  created() {
    console.log('section', this.section);
    console.log('params', this.params);
    console.log('repetable', this.repetable);

    this.$data.repetable = this.$props.repetable;

  },
  mounted() {
    this.$forceUpdate();
  },
  computed: {
    sysadmin: function () {
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