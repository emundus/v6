<template>
  <div class="em-flex-row em-gap-8">
    <div v-for="tab in $props.tabs" class="em-tabs em-pointer" :class="selected === tab.name ? 'em-selected-tab' : ''"
         @click="selected = tab.name;$emit('updateTab',tab.name)">
      <p>{{ translate(tab.label) }} ({{ tab.total }})</p>
    </div>
  </div>

</template>

<script>
export default {
  name: "Tabs",
  props: {
    tabs: Array
  },
  data: () => ({
    selected: '',
  }),
  created() {
    this.$props.tabs.forEach((tab) => {
      if (tab.selected) {
        this.selected = tab.name;
      }
    })
  }
}
</script>

<style scoped>

</style>