<template>
  <div id="app">
    <List/>
  </div>
</template>

<script>
import List from './components/List.vue'

export default {
  name: 'App',
  props: {
    currentUser: {
      type: String,
      required: true
    },
    module: {
      type: String,
      required: true
    },
    readonly: {
      type: String,
      required: true
    }
  },
  components: {
    List
  },
  created() {
    this.$store.dispatch('user/setCurrentUser', this.currentUser);
  }
}
</script>

<style>
@import url("../../../components/com_emundus/src/assets/css/main.scss");

#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin: 20px 20px 0 0;
}

#g-container-main .g-container {
  width: 90%;
}
</style>
