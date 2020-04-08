<template>
  <!-- modalC -->
  <span :id="'modalSide'">
    <modal
      :name="'modalSide' + ID"
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
        <div class="topright">
          <button
            type="button"
            class="btnCloseModal"
            @click.prevent="$modal.hide('modalSide' + ID)"
          >
            <em class="fas fa-times-circle"></em>
          </button>
        </div>

        <h2 v-if="tempEl.show_title" class="page_header" v-html="title" />

        <textarea v-model="title" class="centepercent"></textarea>

        <p class="intro" v-if="tempEl.intro" v-html="intro" />

        <textarea v-model="intro" class="centepercent"></textarea>

        <div class="container-evaluation w-clearfix">
          <a
            class="bouton-sauvergarder-et-continuer-3"
            @click.prevent="$modal.hide('modalSide' + ID) & UpdateParams(tempEl)"
          >{{Continuer}}</a>
          <a
            class="bouton-sauvergarder-et-continuer-3 w-retour"
            @click.prevent="$modal.hide('modalSide' + ID)"
          >{{Retour}}</a>
        </div>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "modalSide",
  props: { ID: Number, element: Object, index: Number },
  data() {
    return {
      changes: false,
      tempEl: [],
      title: "",
      intro: "",
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER")
    };
  },
  methods: {
    UpdateParams(tempEl) {
      this.changes = true;
      this.axioschange(this.intro, this.tempEl.introraw);
      this.axioschange(this.title, this.tempEl.show_title.titleraw);
      this.element = JSON.parse(JSON.stringify(this.tempEl));
      this.$emit("UpdateUx");
      this.$emit("UpdateName", this.index, this.title);
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
          "success",
          "New data updated",
          "Information"
        );
      }
      this.changes = false;
    },
    beforeOpen(event) {
      this.initialisation();
    },
    axioschange(label, labelraw) {
      axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          labelTofind: labelraw,
          NewSubLabel: label
        })
      }).catch(e => {
        console.log(e);
      });
    },
    axiostrad: function(totrad) {
      return axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXT",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          toJTEXT: totrad
        })
      });
    },
    initialisation() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
      this.axiostrad(this.tempEl.introraw)
        .then(response => {
          this.intro = response.data;
        })
        .catch(function(response) {
          console.log(response);
        });
      this.axiostrad(this.tempEl.show_title.titleraw)
        .then(response => {
          this.title = response.data;
        })
        .catch(function(response) {
          console.log(response);
        });
    }
  },
  watch: {
    element: function() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
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
.centepercent {
  width: 100%;
  max-width: 100%;
  min-height: 100px;
  margin-bottom: 2.5%;
}
</style>
