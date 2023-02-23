<template>
  <div class="control-group fabrikElementContainer">
	  <label class="fabrikLabel" v-html="element.label"></label>
      <div v-if="!readonly" class="fabrikElement">
        <div class="fabrikSubElementContainer">
          <div class="row-fluid">
          <div class="fabrikgrid_radio" v-for="(sub_label, index) in options.sub_labels">
            <label  class="radio">
            <input class="fabrikinput" type="radio" :id="options.sub_values[index]" :name="element.name" :value="options.sub_values[index]" v-model="value" />
            <span :for="options.sub_values[index]">{{ sub_label }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <p v-else>{{ value }}</p>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */

/* IMPORT YOUR SERVICES */

export default {
  name: "radiobutton",
  components: {},
  mixins: [],
  props: {
    element: {
      type: Object,
      required: true
    },
    value: {
      type: String,
      default: ''
    }
  },
  data: () => ({
    params: {},
    options: [],
  }),
  created() {
    this.params = JSON.parse(this.element.params)
    this.options = this.params.sub_options
  },
  mounted() {},
  methods: {},
  computed: {
    readonly: function(){
      return parseInt(this.params.readonly) === 1
    },
  },
  watch: {
    value: function(value) {
      this.$emit('input', {value: value, name: this.element.name})
    }
  }
}
</script>

<style scoped>
input.em-input.em-h-40{
  height: 40px;
}
</style>
