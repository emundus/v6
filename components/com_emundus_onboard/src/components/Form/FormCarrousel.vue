<template>
  <div class="container-fluid">
    <div class="row card">
      <div class="col-md-2 coldmd2left">
        <ul>
          <li v-for="(value, index) in formNameArray" :key="index" class="MenuForm">
            <a
              @click="ChangeIndex(index)"
              class="MenuFormItem"
              :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
            >{{value.value}}</a>
          </li>
        </ul>
      </div>
      <div class="col-md-10 card-body">
        <FormViewer :link="formLinkArray[indexHighlight]" v-if="formLinkArray[indexHighlight]" />
      </div>
    </div>
  </div>
</template>


<script>
import _ from "lodash";
import FormViewer from "../Form/FormViewer";
import axios from "axios";
const qs = require("qs");
import "../../assets/css/formbuilder.css";

export default {
  name: "FormCarrousel",
  props: {
    formList: Object
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
        axios
          .get(element.link + "&format=vue_json")
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
.MenuForm {
  list-style: none;
  text-decoration: none;
  margin: 12px 0 0;
}

.MenuForm:hover {
  text-decoration: underline;
  color: salmon;
}
.MenuFormItem {
  text-decoration: none;
  color: black;
  cursor: pointer;
}
.MenuFormItem:hover {
  color: grey;
}
.MenuFormItem_current {
  color: #de633d;
  padding: 0 5px;
  cursor: pointer;
}
</style>