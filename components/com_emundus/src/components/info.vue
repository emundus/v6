<template>
  <div  class="bg-blue-300 rounded flex items-center pb-2">
    <span class="material-icons-outlined scale-150 ml-2 mt-2">info</span>
    <p class="ml-2 mt-2">{{translate (text) }}</p>
  </div>


</template>

<script>
export default {
  name: "info",
  components: {},
  props: {
    text: {
      type: String,
      required: true,
    },
  },

  mixins: [],

  data() {
    return {
    }
  },
  created() {},
  mounted() {},
  methods: {},
  watch: {},
}
</script>

<style scoped>

</style>