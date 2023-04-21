<template>
  <div id="FormBuilder" class="em-mt-16 applicant-form">
    <div>
      <BuilderViewer
        :object="object"
        :groups="GroupList"
        v-if="object_json"
        :change="this.change"
        :changedElement="this.changedElement"
        :changedGroup="this.changedGroup"
        @show="show"
        @modalClosed="$emit('modalClosed')"
        @modalOpen="$emit('modalOpen')"
        @createGroup="$emit('createGroup')"
        :UpdateUx="UpdateUx"
        @UpdateUxf="UpdateUXF"
        :key="builderViewKey"
        :files="files"
        :prid="prid"
        :eval="eval"
        :actualLanguage="actualLanguage"
        :manyLanguages="manyLanguages"
        ref="builder_viewer"
      />
    </div>
  </div>
</template>


<script>
import axios from "axios";
import draggable from "vuedraggable";
import BuilderViewer from "./BuilderView";
import Swal from "sweetalert2";
import _ from 'lodash';

const qs = require("qs");

export default {
  name: "Builder",
  props: { object: Object, UpdateUx: Boolean, rgt: Number, files: Number, prid: String, eval: Number, actualLanguage: String, manyLanguages: Number  },
  components: {
    draggable,
    BuilderViewer,
  },
  data() {
    return {
      newlabel: [],
      dblckickLabel: [],
      testastos: [],
      ElementList: [],
      GroupList: [],
      object_json: Object,
      GroupsObject: Object,
      change: false,
      changedElement: "",
      changedGroup: "",
      update: false,
      builderViewKey: 0,
      addGroup: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP"),
      addItem: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
      editGroup: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_EDITGROUP"),
      deleteGroup: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGROUP"),
      deleteMenu: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENU"),
    };
  },
  methods: {
    UpdateUXT() {
      this.UpdateUx = true;
    },
    UpdateUXF() {
      this.UpdateUx = false;
    },
    UpdateLabel(element, label) {
      element.label_raw = label;
    },
    UpdateGroupName(group, label) {
      this.object_json.Groups[group].group_showLegend = label;
    },
    UpdateBuilderView() {
      this.$emit("UpdateFormBuilder");
    },
    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    },
    Initialised: function() {
      for (var group in this.GroupsObject) {
        let IndexTable = this.object.rgt + "_" + this.GroupsObject[group].group_id;
        this.ElementList[IndexTable] = Object.values(
          this.GroupsObject[group].elements
        );
      }
    },
    deleteAMenu(mid){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENU"),
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENUWARNING"),
        type: "warning",
        showCancelButton: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus&controller=formbuilder&task=deleteMenu",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              mid: mid,
            })
          }).then(() => {
            this.$modal.hide('modalSide' + this.ID)
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_MENUDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.$emit("UpdateFormBuilder");
            });
          }).catch(e => {
            console.log(e);
          });
        }
      });
    },
    async updateOrder(gid,elts){
      await this.$refs.builder_viewer.updateElementsOrder(gid,elts);
    },

    getDataObject: function() {
      this.object_json = this.object.object;
      this.GroupList = Object.values(this.object_json.Groups);
      this.GroupsObject = this.object_json.Groups;
      this.Initialised();
    }
  },
  created() {
    if(!_.isEmpty(this.object.object)){
      this.getDataObject();
    }
  },
  watch: {
    object: function() {
      this.getDataObject();
    },
    update: function() {
      if (this.update === true) {
        this.getDataObject();
      }
    },
    UpdateUx: function() {
      if (this.UpdateUx === true) {
        this.UpdateUXT();
      }
    },
  }
};
</script>

<style scoped>
  #FormBuilder{
    margin-bottom: unset !important;
  }
</style>
