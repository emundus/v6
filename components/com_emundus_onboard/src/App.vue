<template>
  <div>
    <transition name="slide-right">
      <router-view/>
    </transition>
      <div class="loading-form" v-if="this.loading">
        <RingLoader :color="'#12DB42'" />
      </div>
  </div>
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
  data: () => ({
    loading: false,
  }),

  created() {
    global.commit("initDatas", this.$props.datas);
    this.$router.push({
      name: this.$props.component
    }).catch(()=>{});
  },

  watch: {
    loading: function (value) {
      this.loading = value;
    }
  }
}
</script>

<style scoped>

</style>
