<template>
  <div id="FormBuilder22" class="container-fluid">
    <div class="row">
      <div class="container col-md-3">
        <div
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

        <form method="post" class="shadow-box">
          <div v-for="group in object_json.Groups" v-bind:key="group.group_id">
            <legend v-if="group.group_showLegend" class="legend">{{group.group_showLegend}}</legend>
            <ul class="fa-ul">
              <draggable
                handle=".handle"
                @start.passive="testastos = ElementList[link.rgt + '_' + group.group_id]"
                :list="testastos"
                @unchoose="ElementList[link.rgt + '_' + group.group_id] = testastos"
                @end="SomethingChange(ElementList[link.rgt + '_' + group.group_id], group.group_id)"
              >
                <li
                  v-for="element in ElementList[link.rgt + '_' + group.group_id]"
                  :key="element.index"
                  v-show="element.hidden === false"
                  class="radient"
                >
                  <span class="fa-li">
                    <em class="fas fa-arrows-alt-v handle"></em>
                  </span>
                  <span class="text mr-auto" v-html="element.label"></span>
                  <BtnModal
                    :IDs="element.id"
                    :element="element"
                    :group="group"
                    @Toupdate="Toupdate"
                    @show="show"
                  />
                </li>
              </draggable>
            </ul>
            <button
              @click="updateOrder(group.group_id, ElementList[link.rgt + '_' + group.group_id])"
              type="button"
              class="btnF reorder"
            >click to reorder</button>
          </div>
        </form>
      </div>
      <FormBuilderViewer
        class="col-md-9"
        :link="link"
        v-if="link"
        :change="this.change"
        :changedElement="this.changedElement"
        :changedGroup="this.changedGroup"
        @ResetChange="ResetChange"
        :update="update"
        @show="show"
      />
    </div>
  </div>
</template>


<script>
import axios from "axios";
import draggable from "vuedraggable";
import FormBuilderViewer from "./FormBuilderViewer";
import BtnModal from "./formBuilderBtnModalElement";

const qs = require("qs");

export default {
  name: "FormBuilder22",
  props: { link: Array },
  components: {
    draggable,
    FormBuilderViewer,
    BtnModal
  },
  data() {
    return {
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
    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    },
    SomethingChange(e, g) {
      this.changedElement = e;
      this.changedGroup = "group_" + g;
      this.change = true;
    },
    Toupdate(e) {
      this.getDataObject();
      console.log(this.update);
      this.update = e;
    },
    ResetChange(e) {
      this.change = e;
    },
    Initialised: function() {
      for (var group in this.Groups) {
        let IndexTable = this.link.rgt + "_" + this.Groups[group].group_id;
        this.ElementList[IndexTable] = Object.values(
          this.Groups[group].elements
        );
        console.log(IndexTable);
        console.log(this.ElementList[IndexTable]);
      }
    },
    echo(à) {
      console.log("heyhety");
      console.log(à);
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
        .then(response => {})
        .catch(e => {
          console.log(e);
        });
    },

    getDataObject: function() {
      console.log("je suis rgt :" + this.link.rgt);
      axios
        .get(this.link.link + "&format=vue_json_custom")
        .then(response => {
          this.object_json = response.data;
          this.Groups = this.object_json.Groups;
          this.Initialised();
        })
        .catch(e => {
          console.log(e);
        });
    }
  },
  created() {
    this.getDataObject();
  },
  watch: {
    link: function() {
      this.getDataObject();
    },
    update: function() {
      if (this.update === true) {
        this.getDataObject();
      }
    }
  }
};
</script>

<style scoped>
.container {
  padding-top: 80px;
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
  background-color: #f14e4e; /* 
  position: absolute;
  left: auto;
  right: 15px; */
}
input {
  width: 12% !important;
  height: 42% !important;
}
.text {
  color: #8a2be2;
  font-style: oblique;
  margin-right: auto;
  cursor: default;
}
.require {
  display: inline-block;
  margin-bottom: 0;
  padding-right: 20px;
}
.checkboxF {
  margin: 0 auto 0 12px;
}
.mr-auto {
  margin: auto;
}
.handle {
  cursor: grab;
}
</style>