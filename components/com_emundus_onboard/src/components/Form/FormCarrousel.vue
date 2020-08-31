<template>
  <div class="container-fluid">
    <div class="row card">
      <div class="col-md-12">
        <ul class="menus-row">
          <li v-for="(value, index) in formNameArray" :key="index" class="MenuForm">
            <a
              @click="ChangeIndex(index)"
              class="MenuFormItem"
              :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
            >{{value.value}}</a>
          </li>
        </ul>
      </div>
      <div class="col-md-12 card-body" style="margin-bottom: 50%">
        <FormViewer :link="formLinkArray[indexHighlight]" :visibility="this.visibility" v-if="formLinkArray[indexHighlight]" />
      </div>
    </div>
  </div>
</template>


<script>
import FormViewer from "../Form/FormViewer";
import axios from "axios";
const qs = require("qs");
import "../../assets/css/formbuilder.css";

export default {
  name: "FormCarrousel",
  props: {
    formList: Object,
    visibility: Number
  },
  components: {
    FormViewer
  },
  data() {
    return {
      indexHighlight: "0",
      formNameArray: [],
      formLinkArray: [],
      formArray: []
    };
  },
  methods: {
    ChangeIndex(index) {
      this.indexHighlight = index;
      this.$emit("getEmitIndex", this.indexHighlight);
    },
    getDataObject: function() {
      this.formList.forEach(element => {
        let ellink = element.link.replace("fabrik","emundus_onboard");
        axios
          .get(ellink + "&format=vue_jsonclean")
          .then(response => {
            this.formNameArray.push({
              value: response.data.show_title.value,
              rgt: element.rgt
            });
            this.formLinkArray.push({ link: element.link, rgt: element.rgt });
          })
          .then(r => {
            this.formNameArray.sort((a, b) => a.rgt - b.rgt);
            this.formLinkArray.sort((a, b) => a.rgt - b.rgt);
          })
          .catch(e => {
            console.log(e);
          });
      });
    }
  },
  created() {
    this.getDataObject();
  }
};
</script>

<style scoped>
.container-fluid {
  margin-bottom: 5%;
}
</style>
