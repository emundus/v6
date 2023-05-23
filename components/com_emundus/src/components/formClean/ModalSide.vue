<template>
  <!-- modalC -->
  <span :id="'modalSide'">
    <modal
      :name="'modalSide' + ID"
      height="auto"
      transition="little-move-left"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="true"
      @closed="beforeClose"
      @before-open="beforeOpen">

	    <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4">
          {{translations.editMenu}}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalSide' + ID)">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div>
        <div class="em-mb-16">
          <label>{{translations.Name}} :</label>
          <input v-model="label[actualLanguage]" type="text" maxlength="40" class="em-w-100" style="margin: 0" :class="{ 'is-invalid': errors}"/>
        </div>
        <p v-if="errors" class="em-red-500-color">
          <span class="em-red-500-color">{{translations.LabelRequired}}</span>
        </p>

        <div class="em-mb-16" :class="{'mb-0': can_translate.intro}">
          <label>{{translations.Intro}}</label>
          <editor
              v-if="intro.hasOwnProperty(selectedLanguage)"
              :height="'30em'"
              :text="intro[selectedLanguage]"
              :lang="actualLanguage"
              :enable_variables="false"
              :id="'editor_' + selectedLanguage"
              :key="dynamicComponent"
              v-model="intro[selectedLanguage]"></editor>
        </div>

        <div class="em-mb-16 em-flex-row" id="template_checkbox">
          <input type="checkbox" v-model="template">
          <label class="em-ml-8">{{translations.SaveAsTemplate}}</label>
        </div>

        <div class="em-flex-row em-flex-space-between em-mb-16">
          <div class="em-flex-row">
            <button
                class="em-secondary-button em-w-auto"
                @click.prevent="$modal.hide('modalSide' + ID)">
              {{translations.Retour}}
            </button>
            <button class="em-tertiary-button em-w-auto"
                    @click.prevent="deleteMenu()"
                    v-if="menus.length > 1 && files == 0">
              {{translations.Delete}}
            </button>
          </div>
          <button
              class="em-primary-button em-w-auto"
              @click.prevent="$modal.hide('modalSide' + ID) & UpdateParams()">
              {{translations.Continuer}}
          </button>
        </div>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Translation from "@/components/translation"
import Editor from "../editor";

const qs = require("qs");

export default {
  name: "modalSide",
  components: {
    List,
    Editor,
    Translation
  },
  props: { ID: String, element: Object, index: Number, menus: Array, files: Number, link: String, manyLanguages: String, actualLanguage: String,  languages: Array },
  data() {
    return {
      tempEl: [],
      can_translate: {
        label: false,
        intro: false
      },
      label: {},
      intro: {},
      dynamicComponent: 0,
      template: false,
      errors: false,
      changes: false,
      selectedLanguage: this.actualLanguage,
      translations: {
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Continuer: "COM_EMUNDUS_ONBOARD_ADD_CONTINUER",
        dataSaved: "COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED",
        informations: "COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS",
        orderingMenu: "COM_EMUNDUS_ONBOARD_BUILDER_MENUORDERING",
        editMenu: "COM_EMUNDUS_ONBOARD_BUILDER_EDITMENU",
        Name: "COM_EMUNDUS_ONBOARD_FIELD_NAME",
        Intro: "COM_EMUNDUS_ONBOARD_FIELD_INTRO",
        Delete: "COM_EMUNDUS_ONBOARD_ACTION_DELETE",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        LabelRequired: "COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME",
        SaveAsTemplate: "COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE",
        TranslateIn: "COM_EMUNDUS_ONBOARD_TRANSLATE_IN",
      }
    };
  },
  methods: {
    async UpdateParams() {
      this.changes = true;

      await this.axioschange(this.intro, this.tempEl.intro_raw);
      await this.axioschange(this.label, this.tempEl.show_title.titleraw);
      await this.updatefalang(this.label);

      await this.saveAsTemplate();

      this.element = JSON.parse(JSON.stringify(this.tempEl));
      this.$emit("UpdateName", this.index, this.label[this.actualLanguage]);
      this.$emit("UpdateIntro", this.index, this.intro[this.actualLanguage]);
    },

    /*
      Events
     */
    beforeClose(event) {
      if (this.changes !== false) {
        this.$emit(
          "show",
          "foo-velocity",
          "success",
          this.dataSaved,
          this.informations
        );
        this.changes = false;
      }
      this.$emit("modalClosed");
    },
    beforeOpen(event) {
      this.initialisation();
    },

    /*
      Update methods
     */
    axioschange(label, labelraw) {
      return new Promise(resolve => {
          axios({
          method: "post",
          url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            labelTofind: labelraw,
            NewSubLabel: label
          })
        }).then((response) => {
          resolve(response.data.status);
        }).catch(e => {
          console.log(e);
        });
    })
    },
    updatefalang(label){
      return new Promise(resolve => {
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=formbuilder&task=updatemenulabel",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            label: label,
            pid: this.element.id
          })
        }).then((result) => {

          resolve(result.data.status);
        });
      })
    },
    saveAsTemplate() {
      return new Promise(resolve => {
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=savemenuastemplate",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            menu: this.element,
            template: this.template,
          })
        }).then((response) => {

          resolve(response.data.scalar)
        });
      })
    },
    //

    /*
      Get translations
     */
    async axiostrad(totrad) {
      return await axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getalltranslations",
        params: {
          toJTEXT: totrad ,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then((rep) => {
        return rep.data;
      });
    },
    //

    deleteMenu() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENU"),
        text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
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
                    "index.php?option=com_emundus&controller=formbuilder&task=deletemenu",
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

    checkIfTemplate(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getPagesModel"
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
        .then((intro_translated) => {
          this.intro = intro_translated
        })
        .catch(function(intro_translated) {
          console.log(intro_translated);
        });
      this.axiostrad(this.tempEl.show_title.titleraw)
        .then((label_translated) => {
          this.label = label_translated
        })
        .catch(function(label_translated) {
          console.log(label_translated);
        });
      this.checkIfTemplate();
    },
  },
  watch: {
    element: function() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
    }
  },
  created: function() {}
};
</script>

<style scoped>
#template_checkbox input{
  margin: 0 !important;
}
</style>
