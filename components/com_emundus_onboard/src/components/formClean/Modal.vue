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
      :clickToClose="false"
      width="65%"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <h2>
          {{tempEl.label_raw}}
          <span class="titleType">type : {{tempEl.plugin}}</span>
        </h2>
        <div class="topright">
          <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalC' + ID)">
            <em class="fas fa-times-circle"></em>
          </button>
        </div>
        <div class="col-sm-6 inlineflex">
          <label class="require">Name :</label>
          <input v-model="tempLabel" type="text" class="inputF" />
        </div>
        <div class="inlineflex col-sm-6">
          <label class="require">Require :</label>
          <div class="toggle">
            <input type="checkbox" class="check" v-model="tempEl.FRequire" :id="tempEl.id" />
            <strong class="b switch"></strong>
            <strong class="b track"></strong>
          </div>
        </div>
      </div>
      <fieldF v-if="tempEl.plugin === 'field'" :element="tempEl"></fieldF>
      <birthdayF v-if="tempEl.plugin==='birthday'" :element="tempEl"></birthdayF>
      <checkboxF v-if="tempEl.plugin==='checkbox'" :element="tempEl"  @subOptions="subOptions"></checkboxF>
      <dropdownF v-if="tempEl.plugin==='dropdown'" :element="tempEl" @subOptions="subOptions"></dropdownF>
      <radiobtnF v-if="tempEl.plugin=== 'radiobutton'" :element="tempEl"  @subOptions="subOptions"></radiobtnF>
      <textareaF v-if="tempEl.plugin==='textarea'" :element="tempEl"></textareaF>
      <div class="container-evaluation w-clearfix">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="$modal.hide('modalC' + ID) & UpdateParams(tempEl, tempLabel)"
        >Save & Continue</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalC' + ID)"
        >Discard Changes</a>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import fieldF from "./Plugin/field";
import birthdayF from "./Plugin/birthday";
import checkboxF from "./Plugin/checkbox";
import dropdownF from "./Plugin/dropdown";
import radiobtnF from "./Plugin/radiobtn";
import textareaF from "./Plugin/textarea";
const qs = require("qs");

export default {
  name: "modalC",
  props: { ID: Number, element: Object, label: String },
  components: {
    fieldF,
    birthdayF,
    checkboxF,
    dropdownF,
    radiobtnF,
    textareaF
  },
  data() {
    return {
      newlabel: [],
      done: false,
      labelchange: false,
      changes: false,
      tempEl: [],
      tempLabel: "",
      sublabel: ""
    };
  },
  methods: {
    subOptions(sO) {
      this.sublabel = sO;
    },
    UpdateParams(tempEl, tempLabel) {
      this.changes = true;
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=UpdateParams",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: tempEl,
          newLabel: tempLabel
        })
      })
        .then(r => {
          if (this.sublabel !== "") {
            axios({
              method: "post",
              url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=SubLabelsxValues",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                element: tempEl,
                NewSubLabel: this.sublabel
              })
            })
              .then(r => {
                this.$set(this.element, "params", r.data);
                this.sublabel = "";
                this.$emit("UpdateUX");
              })
              .catch(e => {
                console.log(e);
              });
          }
        })
        .then(r => {
          this.element = JSON.parse(JSON.stringify(this.tempEl));
          this.label = this.tempLabel;
          this.$set(this.element, "label_raw", this.label);
          this.initialisation();
          this.$emit("UpdateLabel", this.label);
          this.$emit("UpdateUX");
        })
        .catch(e => {
          console.log(e);
        });
    },
    ChangeRequire(element) {
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=ChangeRequire",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: element
        })
      }).catch(e => {
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
      }).catch(e => {
        console.log(e);
      });
    },
    beforeClose(event) {
      if (this.changes === false) {
        this.initialisation();
        this.$emit(
          "show",
          "foo-velocity",
          "warn",
          "You discared new Data",
          "Information"
        );
      } else {
        this.$emit(
          "show",
          "foo-velocity",
          "warn",
          "New data updated",
          "Information"
        );
      }
      this.changes = false;
    },
    beforeOpen(event) {
      this.initialisation();
    },
    initialisation() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
      this.tempLabel = this.label;
    }
  },
  computed: {
    getlabel: function() {
      return this.tempEl.label_raw;
    }
  },
  watch: {
    element: function() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
      this.tempLabel = this.label;
    }
  },
  created: function() {
    this.initialisation();
  }
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

.b {
  display: block;
}

.toggle {
  vertical-align: middle;
  position: relative;

  left: 20px;
  width: 45px;
  border-radius: 100px;
  background-color: #ddd;
  overflow: hidden;
  box-shadow: inset 0 0 2px 1px rgba(0, 0, 0, 0.05);
}

.check {
  position: absolute;
  display: block;
  cursor: pointer;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  z-index: 6;
}

.check:checked ~ .track {
  box-shadow: inset 0 0 0 20px #4bd863;
}

.check:checked ~ .switch {
  right: 2px;
  left: 22px;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0.05s, 0s;
}

.switch {
  position: absolute;
  left: 2px;
  top: 2px;
  bottom: 2px;
  right: 22px;
  background-color: #fff;
  border-radius: 36px;
  z-index: 1;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  transition-property: left, right;
  transition-delay: 0s, 0.05s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.track {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
  box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.05);
  border-radius: 40px;
}
.inlineflex {
  display: flex;
  align-content: center;
}
.titleType {
  font-size: 45%;
}
.topright {
  position: absolute;
  top: 0;
  left: 97.2%;
  font-size: 25px;
}
.btnCloseModal {
  background-color: inherit;
}
.require {
  margin: 0 !important;
}
</style>
