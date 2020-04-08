<template>
  <div id="birthdayF">
    <div class="rowmodal">
      <div class="flex">
        Hover help:
        <input type="text" class="inputF" v-model="element.params.rollover" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "birthdayF",
  props: { element: Object }
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
