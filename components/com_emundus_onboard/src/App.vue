<template>
  <transition name="slide-right">
    <router-view/>
  </transition>
</template>

<script>
import {global} from "./store/global";

export default {
  name: "App",
  props: {
    component: String,
    datas: Object
  },
  components: {},

  created() {
    global.commit("initDatas", this.$props.datas);
    this.$router.push({
      name: this.$props.component
    }).catch(()=>{});
  }
}
</script>

<style scoped>

</style>
