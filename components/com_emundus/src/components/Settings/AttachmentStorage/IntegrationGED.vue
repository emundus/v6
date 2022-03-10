<template>
  <div class="em-mt-32">
    <div class="em-flex-row">
      <span class="material-icons">folder</span>
      <span class="em-ml-8 em-mr-8">/{{site}}</span>
      <v-popover :popoverArrowClass="'custom-popover-arrow'">
        <span class="tooltip-target b3 material-icons">more_horiz</span>
        <template slot="popover">
          <div class="em-font-size-14 em-mb-8 em-pointer">Ajouter un sous-menu</div>
          <div class="em-font-size-14 em-pointer">Supprimer</div>
        </template>
      </v-popover>
    </div>

    <hr/>
  </div>
</template>

<script>

export default {
  name: "IntegrationGED",
  props:{
    site: String
  },
  data() {
    return {
      loading: false,
    }
  },
  created() {}
}
</script>

<style scoped>
</style>
