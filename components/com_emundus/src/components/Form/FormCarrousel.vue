<template>
  <div>
    <div class="em-flex-row em-flex-space-between em-align-start">
      <div class="em-w-100">
        <FormViewer :link="formLinkArray[indexHighlight]" :visibility="this.visibility"
                    v-if="formLinkArray[indexHighlight]" @editPage="EditPage"/>
      </div>

      <hr class="vertical-divider">

      <div class="em-flex-column em-align-start em-mt-48">
        <div class="em-mb-16">
          <h4>
            <span class="material-icons-outlined">article</span>
            {{ Form }}
          </h4>
          <div v-for="(value, index) in formNameArray" :key="index" class="MenuForm em-mb-8">
            <a @click="ChangeIndex(index)"
               class="em-pointer em-text-neutral-900"
               :class="indexHighlight == index ? 'MenuFormItem_current' : ''"
               v-html="value.value"
            >{{ value.value }}</a>
          </div>
        </div>

        <div>
          <h4>
            <span class="material-icons-outlined">folder</span>
            {{ Documents }}
          </h4>
          <div v-for="(doc, index) in documentsList" :key="index" class="MenuForm em-mb-8">
            <p class="em-text-neutral-900">{{ doc.label }}</p>
          </div>
        </div>
      </div>
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
      FormPage: this.translate("COM_EMUNDUS_ONBOARD_FORM_PAGE"),
      Form: this.translate("COM_EMUNDUS_ONBOARD_FORM"),
      Documents: this.translate("COM_EMUNDUS_ONBOARD_DOCUMENTS"),
    };
  },
  methods: {
    splitProfileIdfromLabel(label) {
      return (label.split(/-(.+)/))[1];
    },
    ChangeIndex(index) {
      this.indexHighlight = index;
    },
    EditPage() {
      this.$emit("formbuilder", this.indexHighlight);
    },
    getDataObject: function () {
      this.formList.forEach(element => {
        let ellink = element.link.replace("fabrik", "emundus");
        axios
            .get(ellink + "&format=vue_jsonclean")
            .then(response => {
              this.formNameArray.push({
                value: response.data.show_title.value,
                rgt: element.rgt
              });
              this.formLinkArray.push({link: element.link, rgt: element.rgt});
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
.MenuFormItem_current {
  color: var(--main-500);
}

.MenuForm a:hover {
  color: #1C6EF2;
}

.vertical-divider {
  height: 50vh;
  border-right: solid 1px #cecece;
  margin: 12px 16px !important;
}
</style>
