<template>
  <div id="gallery-details">
    <div>
      <h2>Affichage des détails</h2>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";

export default {
  name: "details",

  components: {},

  directives: { focus: {
      inserted: function (el) {
        el.focus()
      }
    }
  },

  props: {
    gallery: Number,
  },

  data: () => ({}),

  created() {},
  methods: {},

  watch: {}
};
</script>

<style scoped>
</style>