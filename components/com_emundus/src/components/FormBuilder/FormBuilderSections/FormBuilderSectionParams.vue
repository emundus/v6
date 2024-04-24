<template>
  <div v-if="params.length > 0">
    <div v-for="param in displayedParams" class="form-group mb-4">

      <label v-if="(param.name !== 'repeat_min' && param.name !== 'repeat_max')||($props.repetable === true) " >{{ translate(param.label) }}</label>


      <!-- TEXTAREA -->
      <textarea v-if="$props.section && param.type === 'textarea'" v-model="section.params[param.name]" class="em-w-100"></textarea>

      <!-- INPUT (TEXT,NUMBER) -->
      <div v-if="param.name !== 'repeat_min' && param.name !== 'repeat_max'">
        <input v-if="param.type === 'number' || param.type === 'text'" :type="param.type"
             v-model="section.params[param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>
      </div>
      <div v-else-if="$props.repetable === true">
        <input v-if="param.type === 'number' || param.type === 'text'" :type="param.type" min="0" v-model="section.params[param.name]" class="em-w-100" :placeholder="translate(param.placeholder)"/>
      </div>

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
          <div v-if="$props.repetable === true">
            <select v-model="section.params[param.name]" class="em-w-100">
              <option v-for="option in param.options" :value="option.value">{{ translate(option.label) }}</option>
            </select>
          </div>
        </div>
        <div v-if="param.name === 'repeat_group_button'">
        <div v-if="$props.repetable === false" class="py-1 px-3 bg-neutral-300	rounded-md mt-2">
          <span>{{translate('COM_EMUNDUS_ONBOARD_REPEAT_GROUP_WARNING_DISABLE_FILEUPLOAD')}}</span>
          <select disabled>
            <option value="0" >{{translate('JNO')}}</option>
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
      required: false,
      default:
        {
          params: {
            repeat_min: 0,
            repeat_max: 0,
            repeat_group_button: 0,
            intro :"",
            outro :"",
          }
        }
    },
    params: {
      type: Array,
      required: false
    },
    repetable: {
      type: Boolean,
      default: true,
    }
  },
  data: () => ({
    loading: true,
  }),
  mounted() {
    console.log(this.section)
    this.$forceUpdate();
    this.loading = false;
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