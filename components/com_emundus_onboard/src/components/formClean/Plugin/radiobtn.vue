<template>
  <div id="radiobtnF">
    <p>Je suis le radiobtn {{element.id}}</p>
    <div class="flex">
      Hover help:
      <input type="text" class="inputF" v-model="element.params.rollover" />
    </div>
  </div>
</template>

<script>
export default {
  name: "radiobtnF",
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