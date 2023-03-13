<template>
  <div class="control-group fabrikElementContainer">
    <label class="fabrikLabel" :for="'input_' + element.id" v-html="element.label"></label>
    <div class="fabrikElement">
      <span v-if="tip.length > 0" class="em-tip">{{ tip }}</span>
      <input v-if="!readonly" :type="type" class="fabrikinput em-w-100" :id="'input_' + element.id" :value="value" :name="element.name" v-model="value" />
      <p v-else>{{ value }}</p>
    </div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import Inputmask from 'inputmask';
/* IMPORT YOUR SERVICES */

export default {
  name: "field",
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
  }),
  created() {
    this.params = JSON.parse(this.element.params);
  },
  mounted() {
    if(this.params.text_input_mask !== ''){
      let selector = document.getElementById("input_" + this.element.id);

      let im = new Inputmask(this.params.text_input_mask);
      im.mask(selector);
    }
  },
  methods: {},
  computed: {
    readonly: function(){
      return parseInt(this.params.readonly) === 1;
    },
    tip: function(){
      return this.params.rollover;
    },
    type: function(){
      switch (this.params.password) {
        case '1':
          return 'password';
        case '2':
          return 'tel';
        case '3':
          return 'email';
        case '6':
          return 'number';
        default:
          return 'text';
      }
    }
  },
  watch: {
    value: function(value) {
      this.$emit('input', {value: value, name: this.element.name})
    }
  }
}
</script>

