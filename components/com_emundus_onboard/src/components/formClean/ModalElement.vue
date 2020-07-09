<template>
  <!-- modalC -->
  <span :id="'modalElement'">
    <modal
      :name="'modalElement'"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="false"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalElement')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{addElement}}
          </h2>
        </div>

        <div class="form-group">
          <label>{{ChooseGroup}}* :</label>
          <select v-model="gid" class="dropdown-toggle">
            <option v-for="(group, index) in groups" :value="group.group_id">{{group.group_showLegend}}</option>
          </select>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="createElement()"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalElement')"
        >{{Retour}}</a>
      </div>
      <div class="loading-form" style="top: 10vh" v-if="submitted">
        <Ring-Loader :color="'#de6339'" />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalElement",
  props: {
    groups: Number,
  },
  data() {
    return {
      submitted: false,
      changes: false,
      gid: 0,
      addElement: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
      ChooseGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_GROUP"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      ElementCreated: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_CREATEDELEMENT"),
      ElementCreating: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_CREATEDELEMENTSUCCES"),
    };
  },
  methods: {
    beforeClose(event) {
      if (this.changes === true) {
        this.$emit(
          "show",
          "foo-velocity",
          "success",
          this.ElementCreated,
          this.ElementCreating
        );
      }
      this.changes = false;
    },
    beforeOpen(event) {
      this.gid = Object.values(this.groups)[0].group_id;
    },
    createElement() {
      this.changes = true;

      this.submitted = true;
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimpleelement",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          gid: this.gid
        })
      }).then((result) => {
        this.submitted = false;
        this.$modal.hide('modalElement');
        this.$emit('GetElement',result.data.scalar,this.gid);
      });
    },
  },

  watch: {
    model_id: function (value) {
      if(value != -1){
        Object.values(this.models).forEach(model => {
          if(model.form_id == this.model_id){
            this.label.fr = model.label.fr;
            this.label.en = model.label.en;

            var divfr = document.createElement("div");
            var diven = document.createElement("div");
            divfr.innerHTML =  model.intro.fr;
            diven.innerHTML =  model.intro.en;
            this.intro.fr = divfr.innerText;
            this.intro.en = diven.innerText;
          }
        });
      } else {
        this.label.fr = '';
        this.intro.fr = '';
      }
    },
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
  align-items: center;
  height: 30px;
}
.titleType {
  font-size: 45%;
  margin-left: 1em;
}
.topright {
  font-size: 25px;
  float: right;
}
.btnCloseModal {
  background-color: inherit;
}
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

.translate-icon{
  height: auto;
  position: absolute;
  right: 2em;
}

.translate-icon-selected{
  margin-bottom: 0;
}
</style>
