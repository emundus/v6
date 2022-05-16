<template>
  <!-- modalC -->
  <span :id="'modalDuplicateElement'">
    <modal
      :name="'modalDuplicateElement' + ID"
      height="auto"
      transition="little-move-left"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="true"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
        <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalDuplicateElement' + ID)">
              <em class="fas fa-times"></em>
            </button>
          </div>
        <div class="update-field-header">
          <h2 class="update-title-header">
             {{translations.DuplicateElement}}
          </h2>
          <p>{{translations.Target}}</p>
        </div>
      </div>
      <div class="modalC-content">
        <div class="form-group mb-2">
          <label>{{translations.Page}}* :</label>
          <select id="select_page" class="dropdown-toggle" v-model="page" :class="{ 'is-invalid': errors.page}">
            <option v-for="(page, index) in pages" :key="index" :value="page.id">
              {{page.label}}
            </option>
          </select>
        </div>
        <p v-if="errors.page" class="error col-md-12 mb-2">
          <span class="error">{{translations.PageRequired}}</span>
        </p>
        <transition :name="'slide-down'" type="transition">
          <div class="form-group mb-2" v-if="page !== -1 && groups.length > 1">
            <label>{{Group}}* :</label>
            <select id="select_group" class="dropdown-toggle" v-model="group" :class="{ 'is-invalid': errors.group}">
              <option v-for="(group, index) in groups" :key="index" :value="group.id">
                {{group.label}}
              </option>
            </select>
          </div>
        </transition>
        <p v-if="errors.group" class="error col-md-12 mb-2">
          <span class="error">{{ translations.GroupRequired }}</span>
        </p>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button"
                class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalDuplicateElement' + ID)">
          {{ translations.Retour }}
        </button>
        <button type="button"
          class="bouton-sauvergarder-et-continuer"
          @click.prevent="duplicate()"
        >{{ translations.Continuer }}</button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalDuplicateElement",
  props: {
    ID: Number,
    prid: String,
    currentGroup: Number,
    currentPage: Number,
  },
  data() {
    return {
      page: -1,
      group: -1,
      pages: [],
      groups: [],
      errors: {
        page: false,
        group: false,
      },
      translations: {
        DuplicateElement: "COM_EMUNDUS_ONBOARD_DUPLICATE_ELEMENT",
        Page: "COM_EMUNDUS_ONBOARD_BUILDMENU",
        Group: "COM_EMUNDUS_ONBOARD_GROUP",
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Continuer: "COM_EMUNDUS_ONBOARD_SAVE",
        PageRequired: "COM_EMUNDUS_ONBOARD_REQUIRED_FORM",
        GroupRequired: "COM_EMUNDUS_ONBOARD_REQUIRED_GROUP",
        Target: "COM_EMUNDUS_ONBOARD_CHOOSE_TARGET",
      }
    };
  },
  methods: {
    beforeClose(event) {
      if (this.changes === true) {
        this.$emit(
          "show",
          "foo-velocity",
          "warn",
          this.dataSaved,
          this.informations
        );
      }
      this.$emit("modalClosed");
      this.changes = false;
    },
    beforeOpen(event) {
      this.page = -1;
      this.group = -1;
      this.errors = {
        page: false,
        group: false,
      };
      this.getFormPages();
    },

    duplicate() {
      this.errors = {
        page: false,
        group: false
      };

      if(this.page == -1) {
        this.errors.page = true;
        return 0;
      }

      if(this.group == -1) {
        this.errors.group = true;
        return 0;
      }

      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=formbuilder&task=duplicateelement",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: this.ID,
          group: this.group,
          old_group: this.currentGroup,
          form_id: this.page,
        })
      }).then((result) => {
        window.location.reload();
      });
    },

    getFormPages(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getFormsByProfileId",
        params: {
          profile_id: this.prid
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
          this.pages = response.data.data;
          this.page = this.currentPage;
      });
    },

    getGroupsByForm(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getgroupsbyform",
        params: {
          form_id: this.page
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.groups = response.data.data;
        if(this.groups.length <= 1){
          this.group = this.groups[0].id;
        }
      });
    }
  },

  watch: {
    page: function() {
      this.getGroupsByForm();
    }
  }
};
</script>

<style scoped>
</style>
