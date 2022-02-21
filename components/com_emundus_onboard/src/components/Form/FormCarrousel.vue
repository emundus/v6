<template>
  <div class="container-fluid">
    <div class="menu-block">
      <div class="col-md-8 form-viewer-builder" style="padding: 30px">
        <FormViewer :link="formLinkArray[indexHighlight]" :visibility="this.visibility" v-if="formLinkArray[indexHighlight]" @editPage="EditPage" />
      </div>
        <ul class="col-md-3 sticky-form-pages">
          <div class="form-pages">
            <h4 class="ml-10px form-title" style="margin: 0;padding: 0 10px;"><img src="/images/emundus/menus/form.png" class="mr-1" :alt="Form">{{ Form }}</h4>
            <li v-for="(value, index) in formNameArray" :key="index" class="MenuForm">
              <a
                class="MenuFormItem"
                @click="ChangeIndex(index)"
                :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
                v-html="value.value"
              >{{value.value}}</a>
            </li>
          </div>
          <div class="form-pages">
            <h4 class="ml-10px form-title" style="margin: 0;padding: 0 10px;"><em class="far fa-folder-open mr-1" :alt="Documents"></em>{{ Documents }}</h4>
            <li v-for="(doc, index) in documentsList" :key="index" class="MenuForm">
              <a class="MenuFormItem"
              >{{doc.label}}</a>
            </li>
          </div>
        </ul>
    </div>
  </div>
</template>


<script>
import FormViewer from "../Form/FormViewer";
import axios from "axios";
import "../../assets/css/formbuilder.scss";

export default {
  name: "FormCarrousel",
  props: {
    formList: Array,
    documentsList: Array,
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
      Documents: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOCUMENTS"),
    };
  },
  methods: {
    splitProfileIdfromLabel(label){
      return (label.split(/-(.+)/))[1];
    },
    ChangeIndex(index) {
      this.indexHighlight = index;
    },
    EditPage() {
      this.$emit("formbuilder", this.indexHighlight);
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
    },
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
.menu-block{
  padding: 0;
}

.form-title{
  display: flex;
  align-items: center;
  padding: 1em;
  color: black !important;
}
.form-title img{
  width: 25px;
}
</style>
