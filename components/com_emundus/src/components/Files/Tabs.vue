<template>
  <div class="em-flex-row em-gap-8">
    <div v-for="tab in tabs" class="em-tabs em-pointer" :class="selected === tab.name ? 'em-selected-tab' : ''" @click="selected = tab.name;$emit('updateTab',tab.name)">
      <p>{{ translate(tab.label) }} ({{ $props.counts[tab.name] }})</p>
    </div>
  </div>

</template>

<script>
export default {
  name: "Tabs",
  props: {
    counts: Object
  },
  data: () => ({
    selected: 'to_evaluate',
    tabs: [
      {
        label: 'COM_EMUNDUS_FILES_TO_EVALUATE',
        name: 'to_evaluate'
      },
      {
        label: 'COM_EMUNDUS_FILES_EVALUATED',
        name: 'evaluated'
      },
    ]
  })
}
</script>

<style scoped>

</style>