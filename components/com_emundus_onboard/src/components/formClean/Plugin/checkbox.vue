<template>
  <div id="checkboxF">
    <p>Je suis le checkbox {{element.id}}</p>
    <div class="flex">
      Hover help:
      <input type="text" class="inputF" v-model="element.params.rollover" />
    </div>
  </div>
</template>

<script>
export default {
  name: "checkboxF",
  props: { element: Object },
  methods: {}
};
</script>
<style scoped>
.flex {
  display: flex;
}
.rowmodal {
  margin-bottom: 45px;
}
</style>