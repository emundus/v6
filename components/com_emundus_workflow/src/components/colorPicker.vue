<template>
  <div id="colorPicker">
    <input id='picker' type="color" :value="colorParam.color" @input="handleChange()" style="" v-if="showColor == true"/>
    <input type="color" :value="colorParam.color" @click="showColor=!showColor" @input="handleChange()" style="" v-if="showColor == false"/>

    <span id='labelColor' v-model="colorParam.color" style="margin-left: 5vh; font-weight: bold" v-bind:style="{ color : colorParam.color }"> {{ colorParam.color || '#000000'}} </span>

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
      showColor: false,
      form: {
        setColor: '',
      },
    }
  },

  created() {
    /// first time or unset --> this.colorParam.color === undefined
    if(this.colorParam.color === undefined) {
      this.showColor = false;
      // this.colorParam.color = '#ffffff';
    }
    else {
      this.form.setColor = this.colorParam.color;
      this.showColor = true;
    }
    this.form = this.element;

  },

  methods: {
    handleChange: function() {
      // this.showColor = true;
      this.form.setColor = $(" #picker ").val();
      this.colorParam.color = $(" #picker ").val();
    }
  }
}
</script>

<style scoped>

</style>