<template>
  <!-- modalBTN 
  -->
  <span id="BtnModal">
    <span class="btn btn-success" @click="$modal.show('modalC' + ID)">
      <em class="fas fa-cog"></em>
    </span>
    <modalC :ID="ID" :element="element" :group="group" @Toupdate="Toupdate" @show="show" />
  </span>
</template>

<script>
import modalC from "./formBuilderElementModal";

export default {
  name: "BtnModal",
  props: {
    IDs: Number,
    element: Object,
    group: Object
  },
  components: { modalC },
  data() {
    return { showModal: false, ID: this.IDs };
  },
  methods: {
    Toupdate(e) {
      this.$emit("Toupdate", e);
    },
    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    }
  }
};
</script>
<style></style>
