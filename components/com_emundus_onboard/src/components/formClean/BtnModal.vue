<template>
  <!-- modalBTN 
  -->
  <span id="BtnModal">
    <span
      class="BtnModal"
      @click="$modal.show('modalC' + ID)"
      data-toggle="tooltip"
      data-placement="top"
      :title="elementhover"
    >
      <em class="fas fa-cog"></em>
    </span>
    <modalC
      :ID="ID"
      :element="element"
      @show="show"
      @UpdateUX="UpdateUX"
      :label="label"
      @UpdateLabel="UpdateLabel"
    />
  </span>
</template>

<script>
import modalC from "./Modal";

export default {
  name: "BtnModal",
  props: {
    IDs: Number,
    element: Object,
    group: Object,
    label: String
  },
  components: { modalC },
  data() {
    return { 
      showModal: false,
      elementhover: Joomla.JText._("COM_EMUNDUS_ONBOARD_ELEMENTTOOLTIPS"),
      ID: this.IDs 
    };
  },
  methods: {
    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    },
    UpdateLabel(label) {
      this.$emit("UpdateLabel", label);
    },
    UpdateUX() {
      this.$emit("UpdateUx");
    }
  }
};
</script>
<style scoped>
</style>
