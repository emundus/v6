<template>
  <div id="colorPicker">
    <input id='picker' type="color" :value="form.setColor" @input="handleChange()" style=""/>
    <span id='labelColor' v-model="form.setColor" style="margin-left: 5vh; font-weight: bold" v-bind:style="{ color : hotColor }"> {{ hotColor }} </span>
  </div>
</template>

<script>
import $ from 'jquery';

export default {
  name: "colorPicker",

  props: {
    element: Object,
  },

  data: function() {
    return {
      form: {
        setColor: '',
      },
      hotColor: '#000000'
    }
  },

  created() {
    this.form = this.element;
  },

  methods: {
    handleChange: function() {
      this.form.setColor = $(" #picker ").val();
      this.hotColor = $(" #picker ").val();
      // using emit to pass color
      this.$emit('updateColor', $(" #picker ").val());
    }
  }
}
</script>

<style scoped>

</style>