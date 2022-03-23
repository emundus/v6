<template>
  <div id="displayF">
    <div class="row rowmodal">
      <div class="form-group">
        <label>{{Text}} :</label>
        <editor :height="'30em'" :text="element.default" :lang="'fr'" :enable_variables="false" :id="'element_' + element.id" v-model="element.default"></editor>
      </div>
    </div>
  </div>
</template>

<script>
import Editor from "../../editor";

export default {
  name: "displayF",
  props: { element: Object, files: Number },
  components: {
    Editor
  },
  data() {
    return {
      Text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_TEXT"),
      dynamicComponent: false,
    };
  },
  methods: {},
  created(){}
};
</script>
<style scoped>
.flex {
  display: flex;
  align-items: center;
  margin-bottom: 1em;
  height: 30px;
}
.rowmodal {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
  button{
    background: transparent;
  }
#fieldF{
  padding: 10px;
}
</style>
