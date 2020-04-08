<template>
  <div id="textareaF">
    <div class="row rowmodal">
      <div class="col-sm-2 flex">
        width :
        <input type="text" class="inputF" v-model="element.params.width" />
      </div>
      <div class="col-sm-2 flex">
        height :
        <input type="text" class="inputF" v-model="element.params.height" />
      </div>
      <div class="col-sm-2 flex">
        Size:
        <span>a définir</span>
      </div>
      <div class="col-sm-3 flex">
        placeholder :
        <input
          type="text"
          class="inputF"
          v-model="element.params.textarea_placeholder"
        />
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
  name: "textareaF",
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