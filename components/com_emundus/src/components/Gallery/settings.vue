<template>
  <div id="gallery-settings">
    <div>
      <h2>Paramètres</h2>
    </div>

    <div class="mt-2">
      <div class="mb-4 mt-2">
        <label>Titre</label>
        <input type="text" class="w-full" />
      </div>

      <div class="mb-4">
        <label>Introduction</label>
        <textarea class="w-full" rows="5"></textarea>
      </div>

      <div class="mb-4">
        <label>Activer le vote</label>
      </div>

      <div class="mb-4">
        <label>Nombre de votes maximum par personne</label>
      </div>

      <div class="mb-4">
        <label>Date de début</label>
      </div>

      <div class="mb-4">
        <label>Date de fin</label>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";

export default {
  name: "settings",

  components: {},

  directives: {},

  props: {
    gallery: Object,
  },

  data: () => ({}),

  created() {},
  methods: {},

  watch: {}
};
</script>

<style scoped>
</style>