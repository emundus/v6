<template>
  <div class="container-fluid">
    <div class="menu-block">
      <div class="col-md-8 form-viewer-builder" style="margin-bottom: 50%">
        <FormViewer :link="formLinkArray[indexHighlight]" :visibility="this.visibility" v-if="formLinkArray[indexHighlight]" />
      </div>
        <ul class="col-md-3">
          <h3 class="mb-1" style="padding: 0;">{{ FormPage }} :</h3>
          <div class="form-pages">
            <h4 class="ml-10px" style="margin-bottom: 0"><em class="far fa-file-alt mr-1"></em>{{ Form }}</h4>
            <li v-for="(value, index) in formNameArray" :key="index" class="MenuForm">
              <a
                @click="ChangeIndex(index)"
                class="MenuFormItem"
                :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
              >{{value.value}}</a>
            </li>
          </div>
        </ul>
    </div>
  </div>
</template>


<script>
import FormViewer from "../Form/FormViewer";
import axios from "axios";
const qs = require("qs");
import "../../assets/css/formbuilder.scss";

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
      formArray: [],
      FormPage: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_PAGE"),
      Form: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM"),
    };
  },
  methods: {
    ChangeIndex(index) {
      this.indexHighlight = index;
      document.cookie = 'page='+index+'; expires=Session; path=/'
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
