<template>
  <div id="app">

    <List />
  </div>
</template>

<script>

import List from './components/List.vue';

export default {
  name: 'App',
  components: {
    List
  }
}
</script>

<style>
@import url("../../../components/com_emundus/src/assets/css/main.scss");

#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin-top: 60px;
}
#g-container-main .g-container{
  width: 90%;
}

</style>
