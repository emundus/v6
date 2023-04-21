<template>
  <div class="control-group fabrikElementContainer">
	  <label class="fabrikLabel" v-html="element.label"></label>
	  <div v-if="!readonly" class="fabrikElement">
        <div class="fabrikSubElementContainer">
          <div class="row-fluid">
          <div class="fabrikgrid_checkbox" v-for="(sub_label, index) in options.sub_labels">
            <label class="checkbox">
              <input class="fabrikinput" type="checkbox" :id="options.sub_values[index]" :name="element.name+'['+[index]+']'" :value="options.sub_values[index]" v-model="values_selected[index]" />
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
  name: "checkbox",
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
    values_selected: [],
  }),
  created() {
    this.params = JSON.parse(this.element.params)
    this.options = this.params.sub_options
    let tmp_value = this.value.replace('[', '').replace(']', '').split(',');
    tmp_value.forEach((val) =>{
      this.values_selected[parseInt(val.replace('"', ''))] = true;
    });
  },
  methods: {},
  computed: {
    readonly: function(){
      return parseInt(this.params.readonly) === 1
    },
  },
  watch: {
    value: function(value) {
      this.$emit('input', {value: value, name: this.element.name})
    },
    values_selected: function(values) {
      let string_values = '[';
      let array_values = [];
      values.forEach((value,index) => {
        if(value === true) {
          array_values.push('"' + index + '"');
        }
      });
      array_values = array_values.join(',');
      string_values += array_values + ']';
      this.value = string_values;
    }
  }
}
</script>

<style scoped>
input.em-input.em-h-40{
  height: 40px;
}
</style>
