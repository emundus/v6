<template>
  <div id="app">
    <FilterBuilder></FilterBuilder>
  </div>
</template>

<script>
import FilterBuilder from './components/FilterBuilder.vue';

export default {
  name: 'App',
  components: {
    FilterBuilder
  },
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
