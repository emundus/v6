<template>
  <div id="colorPicker">
    <input id='picker' type="color" :value="colorParam.color" @input="handleChange()" style=""/>
    <span id='labelColor' v-model="colorParam.color" style="margin-left: 5vh; font-weight: bold" v-bind:style="{ color : colorParam.color }"> {{ colorParam.color }} </span>

  </div>
</template>

<script>
import $ from 'jquery';

export default {
  name: "colorPicker",

  props: {
    element: Object,
    colorParam: Object,
  },

  data: function() {
    return {
      hotColor: '#f5f5f5',
      form: {
        setColor: '',
      },
    }
  },

  created() {
    /// first time or unset --> this.colorParam.color === undefined

    if(this.colorParam.color === undefined) {
      this.colorParam.color = '#f5f5f5';
    }

    this.form = this.element;
    this.form.setColor = this.colorParam.color;
  },

  methods: {
    handleChange: function() {
      this.form.setColor = $(" #picker ").val();
      this.colorParam.color = $(" #picker ").val();
    }
  }
}
</script>

<style scoped>

</style>