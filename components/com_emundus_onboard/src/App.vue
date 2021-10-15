<template>
  <router-view/>
</template>

<script>
export default {
  name: "App",
  props: {
    component: String,
    datas: Object
  },
  components: {},

  created() {
    this.$router.push({
      name: this.$props.component,
      params: { datas: this.$props.datas }
    }).catch(()=>{});
  }
}
</script>

<style scoped>

</style>
