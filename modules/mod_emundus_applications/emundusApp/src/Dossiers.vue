<template>
  <div id="dossier">
    <DossiersListing
      :dossiers="dossiers"
      :forms="forms"
      :attachements="attachements"
      :firstpage="firstpage"
    />
  </div>
</template>

<script>
import DossiersListing from "./components/DossiersListing";

export default {
  name: "Dossiers",
  props: {
    dossiers: Object,
    forms: Object,
    attachements: Object,
    firstpage: Object
  },
  components: {
    DossiersListing
  }
};
</script>

<style>
#add {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  display: flex;
}
</style>
