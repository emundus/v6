<template>
  <!-- modalC -->
  <span :id="'modalC'">
    <modal
      :name="'modalC' + ID"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      width="65%"
      @before-close="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        {{getlabel}}
        <label class="require">require :</label>
        <span :for="element.id">{{ element.FRequire }}</span>
        <input
          type="checkbox"
          class="checkboxF"
          :id="element.id"
          v-model="element.FRequire"
          @change="ChangeRequire(element, group.group_id)"
        />
      </div>
      <div class="col-3 mr-auto">
        <input
          v-model="newlabel[element.id]"
          @change.once="labelchange = true"
          type="text"
          class="inputF"
        />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalC",
  props: { ID: Number, element: Object, group: Object, show: Function },
  components: {},
  data() {
    return {
      newlabel: [],
      done: false,
      labelchange: false
    };
  },
  methods: {
    ChangeRequire(element, group_id) {
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=ChangeRequire",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: element,
          group_id: group_id
        })
      })
        .then(response => {
          this.$emit("Toupdate", true);
        })
        .catch(e => {
          console.log(e);
        });
    },
    changeTradLabel(element, newLabel) {
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
        .then(response => {
          console.log(response);
          this.$emit("Toupdate", true);
        })
        .catch(e => {
          console.log(e);
        });
    },
    beforeClose(event) {
      if (this.labelchange !== false) {
        this.changeTradLabel(this.element, this.newlabel[this.element.id]);
      }
      this.$emit("show", "foo-velocity", "", "je ferme");
    },
    beforeOpen(event) {
      this.$emit(
        "show",
        "foo-velocity",
        "warn",
        "If you didn't save new order it will be lose",
        "Information"
      );
    }
  },
  computed: {
    getlabel: function() {
      if (!this.done) {
        this.newlabel[this.element.id] = this.element.label;
        this.done = true;
      }
      return this.element.label;
    }
  },
  watch: {}
};
</script>

<style scoped>
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
}
</style>
