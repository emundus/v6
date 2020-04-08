<template>
  <div id="fieldF">
    <div class="row rowmodal">
      <div class="col-sm-3 flex">
        type de champ :
        <select v-model="element.params.password">
          <option value="0">Texte</option>
          <option value="2">Phone</option>
          <option value="3">Email</option>
          <option value="6">Nombre</option>
        </select>
      </div>
      <div class="col-sm-3 flex">
        placeholder :
        <input type="text" class="inputF" v-model="element.params.placeholder" />
      </div>
      <div class="col-sm-3 flex">
        Size:
        <span>a définir</span>
      </div>
      <div class="col-sm-3 flex">
        Hover help:
        <input type="text" class="inputF" v-model="element.params.rollover" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "fieldF",
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