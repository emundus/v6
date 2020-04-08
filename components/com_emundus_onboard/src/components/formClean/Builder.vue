<template>
  <div id="FormBuilder" class="container-fluid">
    <div class="row">
      <div class="container col-md-3">
        <!-- <div
          v-if="object_json.show_page_heading"
          :class="object_json.show_page_heading.class"
          v-html="object_json.show_page_heading.page_heading"
        />
        <h2
          v-if="object_json.show_title"
          class="page_header"
          v-html="object_json.show_title.value"
        />

        <p class="intro" v-if="object_json.intro" v-html="object_json.intro" />
 -->
        <form method="post" class="shadow-box">
          <div v-for="group in object_json.Groups" v-bind:key="group.group_id">
            <legend v-if="group.group_showLegend" class="legend2">
              {{group.group_showLegend}}
             <!--  <span
                class="mr-right dinherit bkcolor"
                type="button"
                @click="addElement(group.group_id, )"
              > 
                <em class="fas fa-plus-circle btnPM"></em>
              </span>-->
            </legend>
            <ul class="fa-ul">
              <draggable
                handle=".handle"
                @start.passive="testastos = ElementList[object.rgt + '_' + group.group_id]"
                :list="testastos"
                @unchoose="ElementList[object.rgt + '_' + group.group_id] = testastos"
                @end="SomethingChange(ElementList[object.rgt + '_' + group.group_id], group.group_id)"
              >
                <li
                  v-for="element in ElementList[object.rgt + '_' + group.group_id]"
                  :key="element.index"
                  v-show="element.hidden === false"
                  class="radient"
                >
                  <span class="fa-li">
                    <em class="fas fa-arrows-alt-v handle"></em>
                  </span>
                  <span
                    v-show="dblckickLabel[element.id]==='false' || !dblckickLabel[element.id]"
                    @dblclick="
                    fdblclick(element)
                    "
                    class="text mr-left"
                    v-html="element.label_raw"
                  ></span>
                  <input
                    v-show="dblckickLabel[element.id]==='true'"
                    @keyup.enter="
                    $set(dblckickLabel, element.id, 'false')  && changeTradLabel(element, newlabel[element.id], group.group_id)"
                    v-model="newlabel[element.id]"
                    class="inputLabelc mr-left"
                  />
                  <BtnModal
                    :IDs="element.id"
                    :element="element"
                    :label="element.label_raw"
                    @show="show"
                    @UpdateLabel="UpdateLabel(element, $event)"
                    @UpdateUx=" UpdateUXT"
                  />
                </li>
              </draggable>
            </ul>
          </div>
        </form>
      </div>
      <BuilderViewer
        class="col-md-9"
        :object="object"
        v-if="object"
        :change="this.change"
        :changedElement="this.changedElement"
        :changedGroup="this.changedGroup"
        @ResetChange="ResetChange"
        @show="show"
        :UpdateUx="UpdateUx"
        @UpdateUxf="UpdateUXF"
      />
    </div>
  </div>
</template>


<script>
import axios from "axios";
import draggable from "vuedraggable";
import BuilderViewer from "./BuilderView";
import BtnModal from "./BtnModal";
import _ from "lodash";

const qs = require("qs");

export default {
  name: "FormBuilder",
  props: { object: Object, UpdateUx: Boolean  },
  components: {
    draggable,
    BuilderViewer,
    BtnModal
  },
  data() {
    return {
      newlabel: [],
      dblckickLabel: [],
      testastos: [],
      ElementList: [],
      object_json: Object,
      Groups: Object,
      change: false,
      changedElement: "",
      changedGroup: "",
      update: false
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
    fdblclick(element) {
      this.$set(this.dblckickLabel, element.id, "true");
      this.newlabel[element.id] = element.label_raw;
    },
    changeTradLabel(element, newLabel, gpId, elementlist) {
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=changeTradLabel",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: element,
          newLabel: newLabel
        })
      })
        .then(r => {
          this.$set(element, "label_raw", newLabel);
          this.$emit(
            "show",
            "foo-velocity",
            "success",
            "Label change successfull",
            "Label change"
          );
          this.UpdateUx = true;
        })
        .catch(e => {
          this.$emit(
            "show",
            "foo-velocity",
            "error",
            "Label change failed",
            "Label change"
          );
          console.log(e);
        });
    },
    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    },
    SomethingChange(e, g) {
      this.changedElement = e;
      this.changedGroup = "group_" + g;
      this.change = true;
      this.updateOrder(g, e);
    },
    Toupdate(e) {
      this.update = e;
    },

    ResetChange(e) {
      this.change = e;
    },
    Initialised: function() {
      for (var group in this.Groups) {
        let IndexTable = this.object.rgt + "_" + this.Groups[group].group_id;
        this.ElementList[IndexTable] = Object.values(
          this.Groups[group].elements
        );
      }
    },
    updateOrder(group, Reorderlist) {
      var elements = Reorderlist.map((element, index) => {
        return { id: element.id, order: index + 1 };
      });
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=updateOrder",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          elements: elements,
          group_id: group
        })
      })
        .then(response => {
          this.$emit(
            "show",
            "foo-velocity",
            "success",
            "Order change successfull",
            "Order change"
          );
        })
        .catch(e => {
          this.$emit(
            "show",
            "foo-velocity",
            "error",
            "Order change failed",
            "Order change"
          );

          console.log(e);
        });
    },

    getDataObject: function() {
      this.object_json = this.object.object;
      this.Groups = this.object_json.Groups;
      this.Initialised();
    }
  },
  created() {
    this.getDataObject();
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
    }
  }
};
</script>

<style scoped>
.container {
  font-family: "Open Sans", sans-serif;
  color: #1f1f1f;
  font-size: 14px;
  line-height: 20px;
}
.page_header {
  text-align: center;
  font-weight: 700;
}
.radient {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  height: 60px;
  margin-bottom: 10px;
  padding-left: 15px;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  border-radius: 4px;
  background-image: -webkit-gradient(
    linear,
    left top,
    right top,
    from(transparent),
    to(#f1f1f1)
  );
  background-image: linear-gradient(90deg, transparent, #f1f1f1);
  color: #696969;

  -webkit-box-shadow: 7px 7px 12px 0px #c0c0c0;
  box-shadow: 7px 7px 12px 0px #c0c0c0;
  -webkit-border-radius: 12px;
  border-radius: 12px;
  margin: 12px 15px 12px 0;
  /* margin: 0 12px 20px 0; */
}
.shadow-box {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  padding-bottom: 8px;
}
.btnF {
  display: block;
  padding: 0.3em 1.2em;
  margin: 0 0.1em 0.1em 0;
  border: 0.16em solid rgba(255, 255, 255, 0);
  border-radius: 2em;
  box-sizing: border-box;
  text-decoration: none;
  font-family: "Roboto", sans-serif;
  font-weight: 300;
  color: #ffffff;
  text-shadow: 0 0.04em 0.04em rgba(0, 0, 0, 0.35);
  text-align: center;
  transition: all 0.2s;
  margin-left: auto;
  margin-right: 15px;
}
.btnLabel:hover {
  border-color: rgba(255, 255, 255, 1);
}
.reorder {
  background-color: #9a4ef1;
  margin-right: 15%;
}
.name {
  background-color: #f14e4e;
}

.text {
  color: #1b1f3c;
  font-style: oblique;
  margin-right: auto;
}
.require {
  display: inline-block;
  margin-bottom: 0;
  padding-right: 20px;
}
.checkboxF {
  margin: 0 auto 0 12px;
}
.mr-left {
  margin: auto auto auto 0;
}
.mr-right {
  margin: auto 0 auto auto;
}

.handle {
  cursor: grab;
}
.inputLabelc {
  height: 42%;
  width: 72%;
}
.legend {
  padding: 10px 20px;
  display: flex;
}
.bkcolor {
  background-color: inherit;
}
.intro {
  margin-bottom: 25px;
}

</style>