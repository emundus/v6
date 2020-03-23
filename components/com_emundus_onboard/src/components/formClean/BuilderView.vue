<template>
  <div id="BuilderViewer" class="BuilderViewer container">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"
    />
    <div
      v-if="object_json.show_page_heading"
      :class="object_json.show_page_heading.class"
      v-html="object_json.show_page_heading.page_heading"
    />
    <h2 v-if="object_json.show_title" class="page_header" v-html="object_json.show_title.value" />

    <p v-if="object_json.intro" class="introP" v-html="object_json.intro" />

    <form method="post" object_json.attribs>
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>
      <div v-for="group in object_json.Groups" v-bind:key="group.index">
        <fieldset :class="group.group_class" :id="'group'+group.group_id" :style="group.group_css">
          <legend
            v-if="group.group_showLegend"
            class="legend ViewerLegend"
          >{{group.group_showLegend}}</legend>
          <div v-if="group.group_intro" class="groupintro">{{group.group_intro}}</div>

          <div
            v-for="element in group.elements"
            v-bind:key="element.index"
            v-show="element.hidden === false"
          >
            <span v-if="element.label" v-html="element.label" v-show="element.labelsAbove != 2"></span>
            <div v-if="element.params.date_table_format">
              <date-picker v-model="date" :config="options"></date-picker>
            </div>
            <div v-else-if="element.labelsAbove == 0" class="controls">
              <div v-if="element.error" class="fabrikElement" v-html="element.error"></div>
              <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
              <span v-if="element.tipSide" v-html="element.tipSide"></span>
            </div>
            <span v-else>
              <div v-if="element.element" class="fabrikElement" v-html="element.error"></div>
              <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
              <span v-if="element.tipSide" v-html="element.tipSide"></span>
            </span>
            <span v-if="element.tipBelow" v-html="element.tipBelow"></span>
          </div>
          <div class="groupoutro" v-if="group.group_outro" v-html="group_outro"></div>
        </fieldset>
      </div>
      <div v-if="object_json.pluginbottom" v-html="object_json.pluginbottom"></div>
    </form>
  </div>
</template>


<script>
import _ from "lodash";
import axios from "axios";
import datePicker from "vue-bootstrap-datetimepicker";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

export default {
  name: "BuilderViewer",
  props: {
    object: Object,
    change: Boolean,
    changedElement: Array,
    changedGroup: String,
    UpdateUx: Boolean
  },
  components: {
    datePicker
  },
  data() {
    return {
      object_json: "",
      date: new Date(),
      options: {
        format: "DD/MM/YYYY",
        useCurrent: false
      }
    };
  },
  methods: {
    reorderViewer: function() {
      var table = Object.values(
        this.object_json.Groups[this.changedGroup].elements
      );
      var chel = this.changedElement;
      chel = chel.map(p => p.id);
      this.object_json.Groups[this.changedGroup].elements = this.mapOrder(
        table,
        chel,
        "id"
      );
      this.$emit("ResetChange", false);
    },
    mapOrder: function(array, order, key) {
      array.sort(function(a, b) {
        var A = a[key],
          B = b[key];

        if (order.indexOf(A) > order.indexOf(B)) {
          return 1;
        } else {
          return -1;
        }
      });

      return array;
    },
    getDataObject: _.debounce(function() {
      this.object_json = this.object.object;
    }, 500),
    getApiData: _.debounce(function() {
      this.$emit(
        "show",
        "foo-velocity",
        "",
        "Wait changes load",
        "Load changes"
      );
      axios.get(this.object.link + "&format=vue_jsonClean").then(r => {
        this.object_json = r.data;
        this.$emit("UpdateUxf");
        this.$emit(
          "show",
          "foo-velocity",
          "success",
          "Changes loaded",
          "Load changes"
        );
      });
    }, 1000)
  },
  watch: {
    object: function() {
      this.getDataObject();
    },
    change: function() {
      if (this.change === true) {
        this.reorderViewer();
      }
    },
    UpdateUx: function() {
      if (this.UpdateUx === true) {
        this.getApiData();
      }
    }
  }
};
</script>





<style scoped>
.hidden {
  display: none;
}
.BuilderViewer {
  padding: 0 1%;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  border-radius: 5px;
}
fieldset {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  margin: 0% 2% 0.5%;
  padding: 2.5%;
}
</style>

