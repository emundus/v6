<template>
  <div id="FormViewer" class="FormViewer container-fluid">
    <div
      v-if="object_json.show_page_heading"
      :class="object_json.show_page_heading.class"
      v-html="object_json.show_page_heading.page_heading"
    />
    <div v-if="object_json.show_title" class="page_header" v-html="object_json.show_title.value" />

    <p v-if="object_json.intro" v-html="object_json.intro" />

    <form method="post" object_json.attribs>
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>
      <div v-for="group in object_json.Groups" v-bind:key="group.index">
        <fieldset :class="group.group_class" :id="'group'+group.group_id" :style="group.group_css">
          <legend v-if="group.group_showLegend" class="legend">{{group.group_showLegend}}</legend>
          <div v-if="group.group_intro" class="groupintro">{{group.group_intro}}</div>

          <div
            v-for="element in group.elements"
            v-bind:key="element.index"
            v-show="element.hidden === false"
          >
            <span v-if="element.label" v-html="element.label" v-show="element.labelsAbove != 2"></span>
            <div v-if="element.labelsAbove == 0" class="controls">
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
export default {
  name: "FormBuilderViewer",
  props: {
    link: String,
    change: Boolean,
    changedElement: Array,
    changedGroup: String,
    update: Boolean
  },
  data() {
    return {
      object_json: ""
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
      axios
        .get(this.link.link + "&format=vue_json")
        .then(response => {
          this.object_json = response.data;
        })
        .then(() => {
          this.$emit("show", "foo-velocity", "success", "", "Interface load");
        })
        .catch(e => {
          console.log(e);
        });
    }, 250)
  },
  watch: {
    link: function() {
      this.getDataObject();
    },
    change: function() {
      if (this.change === true) {
        this.reorderViewer();
      }
    },
    update: function() {
      if (this.update === true) {
        this.getDataObject();
        this.update = false;
      }
    }
  }
};
</script>

<style scoped>
.hidden {
  display: none;
}
.FormViewer {
  padding: 1% 1%;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  border-radius: 5px;
}
.fabrikgrid_radio label.radio input {
  display: inline-block !important;
  vertical-align: top !important;
  padding: 0 !important;
  margin: 0 !important;
}
fieldset {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  margin: 0% 2% 0.5%;
  padding: 2.5%;
}
.legend {
  padding-top: 3%;
}
</style>