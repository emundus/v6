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
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button
              type="button"
              class="btnCloseModal"
              @click.prevent="$modal.hide('modalSide' + ID)"
            >
              <em class="fas fa-times-circle"></em>
            </button>
          </div>

          <h2 class="update-title-header">
             {{editMenu}}
          </h2>
        </div>

        <div class="form-group" :class="{ 'mb-0': translate.label}">
            <label>{{Name}} :</label>
          <div class="input-can-translate">
            <input v-model="label.fr" type="text" maxlength="40" class="form__input field-general w-input" style="margin: 0" :class="{ 'is-invalid': errors}"/>
            <button class="translate-icon" :class="{'translate-icon-selected': translate.label}" type="button" @click="translate.label = !translate.label"></button>
          </div>
        </div>
        <transition :name="'slide-down'" type="transition">
        <div class="inlineflex" v-if="translate.label">
          <label class="translate-label">
            {{TranslateEnglish}}
          </label>
          <em class="fas fa-sort-down"></em>
        </div>
        </transition>
        <transition :name="'slide-down'" type="transition">
        <div class="form-group mb-1" v-if="translate.label">
          <input v-model="label.en" type="text" maxlength="40" class="form__input field-general w-input"/>
        </div>
        </transition>
        <p v-if="errors" class="error col-md-12 mb-2">
          <span class="error">{{LabelRequired}}</span>
        </p>

        <div class="form-group mt-1" :class="{'mb-0': translate.intro}">
          <label>{{Intro}} :</label>
          <div class="input-can-translate">
            <textarea v-model="intro.fr" class="form__input field-general w-input" rows="3" maxlength="300" style="margin: 0"></textarea>
            <button class="translate-icon" :class="{'translate-icon-selected': translate.intro}" type="button" @click="translate.intro = !translate.intro"></button>
          </div>
        </div>
        <transition :name="'slide-down'" type="transition">
        <div class="inlineflex" v-if="translate.intro">
          <label class="translate-label">
            {{TranslateEnglish}}
          </label>
          <em class="fas fa-sort-down"></em>
        </div>
        </transition>
        <transition :name="'slide-down'" type="transition">
        <div class="form-group mb-1" v-if="translate.intro">
          <textarea v-model="intro.en" rows="3" class="form__input field-general w-input" maxlength="300"></textarea>
        </div>
        </transition>

        <div class="col-md-12 d-flex mb-1" style="align-items: center">
          <input type="checkbox" v-model="template">
          <label class="ml-10px">{{SaveAsTemplate}}</label>
        </div>

        <div class="col-md-12 mb-1">
          <a
            class="bouton-sauvergarder-et-continuer-3"
            @click.prevent="$modal.hide('modalSide' + ID) & UpdateParams()"
          >{{Continuer}}</a>
          <a class="bouton-sauvergarder-et-continuer-3 w-delete"
             @click.prevent="deleteMenu()"
             v-if="menus.length > 1 && files == 0">
            {{Delete}}
          </a>
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
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "modalSide",
  props: { ID: Number, element: Object, index: Number, menus: Array, files: Number, link: String },
  components: {},
  data() {
    return {
      tempEl: [],
      translate: {
        label: false,
        intro: false
      },
      label: {
        fr: '',
        en: ''
      },
      intro: {
        fr: '',
        en: ''
      },
      template: false,
      errors: false,
      changes: false,
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      dataSaved: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED"),
      informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS"),
      orderingMenu: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_MENUORDERING"),
      editMenu: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_EDITMENU"),
      Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_NAME"),
      Intro: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_INTRO"),
      Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
      TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
      LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
      SaveAsTemplate: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE"),
    };
  },
  methods: {
    UpdateParams() {
      this.changes = true;
      if(this.label.en == '') {
        this.label.en = this.label.fr;
      }
      if(this.intro.en == '') {
        this.intro.en = this.intro.fr;
      }
      this.axioschange(this.intro, this.tempEl.intro_raw);
      this.axioschange(this.label, this.tempEl.show_title.titleraw);
      this.updatefalang(this.label);
      this.saveAsTemplate();
      this.element = JSON.parse(JSON.stringify(this.tempEl));
      this.$emit("UpdateName", this.index, this.label.fr);
      this.$emit("UpdateUx");
    },
    beforeClose(event) {
      if (this.changes != false) {
        this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.dataSaved,
                this.informations
        );
        this.changes = false;
      }
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
    updatefalang(label){
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updatemenulabel",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          label: label,
          link: this.link
        })
      }).then((result) => {});
    },
    axiostrad: function(totrad) {
      return axios({
        method: "post",
        url:
          "index.php?option=com_emundus_onboard&controller=formbuilder&task=getalltranslations",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          toJTEXT: totrad
        })
      });
    },
    deleteMenu() {
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENU"),
        text: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus_onboard&controller=formbuilder&task=deletemenu",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              mid: this.element.id,
            })
          }).then((response) => {
            this.$emit('removeMenu', this.element.id);
            this.$modal.hide('modalSide' + this.ID);
          });
        }
      });
    },
    saveAsTemplate() {
      return axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=savemenuastemplate",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          menu: this.element,
          template: this.template,
        })
      });
    },
    checkIfTemplate(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getPagesModel"
      }).then(response => {
        var BreakException = {};
        try {
          Object.values(response.data).forEach((model, index) => {
            if (model.form_id == this.element.id) {
              this.template = true;
              throw BreakException;
            }
          });
        } catch (e) {
          if (e !== BreakException) throw this.template = false;
        }
      });
    },
    initialisation() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
      this.axiostrad(this.tempEl.intro_raw)
        .then(response => {
          this.intro.fr = response.data.fr;
          this.intro.en = response.data.en;
        })
        .catch(function(response) {
          console.log(response);
        });
      this.axiostrad(this.tempEl.show_title.titleraw)
        .then(response => {
          this.label.fr = response.data.fr;
          this.label.en = response.data.en;
        })
        .catch(function(response) {
          console.log(response);
        });
      this.checkIfTemplate();
    },
  },
  watch: {
    element: function() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
    }
  },
  created: function() {
    //this.initialisation();
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
  float: right;
  font-size: 25px;
}
.btnCloseModal {
  background-color: inherit;
}
.centepercent {
  width: 100%;
  max-width: 100%;
  min-height: 100px;
  margin-bottom: 1em;
}

.intro{
  margin-top: 2em;
}

  textarea{
    padding: 0.5em;
  }

  .menu-list{
    padding: 1em;
  }

.inlineflex {
  display: flex;
  align-content: center;
  align-items: center;
  height: 30px;
}

.handle {
  cursor: grab;
}
  .icon-handle{
    position: relative;
  }

.update-field-header{
  margin-bottom: 1em;
}

.update-title-header{
  margin-top: 0;
  display: flex;
  align-items: center;
}

</style>
