<template>
  <!-- modalC -->
  <span :id="'modalDuplicateElement'">
    <modal
      :name="'modalDuplicateElement' + ID"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalDuplicateElement' + ID)">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{DuplicateElement}}
          </h2>
          <p>{{Target}}</p>
        </div>
        <div class="form-group mb-2">
          <label>{{Page}}* :</label>
          <select id="select_page" class="dropdown-toggle" v-model="page" :class="{ 'is-invalid': errors.page}">
            <option v-for="(page, index) in pages" :key="index" :value="page.id">
              {{page.label}}
            </option>
          </select>
        </div>
        <p v-if="errors.page" class="error col-md-12 mb-2">
          <span class="error">{{PageRequired}}</span>
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
          <span class="error">{{GroupRequired}}</span>
        </p>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="duplicate()"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalDuplicateElement' + ID)"
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
      DuplicateElement: Joomla.JText._("COM_EMUNDUS_ONBOARD_DUPLICATE_ELEMENT"),
      Page: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDMENU"),
      Group: Joomla.JText._("COM_EMUNDUS_ONBOARD_GROUP"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      PageRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_FORM"),
      GroupRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_GROUP"),
      Target: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_TARGET"),
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
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=duplicateelement",
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
        //this.$modal.hide('modalDuplicateElement' + this.ID);
      });
    },

    getFormPages(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
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
        url: "index.php?option=com_emundus_onboard&controller=form&task=getgroupsbyform",
        params: {
          form_id: this.page
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.groups = response.data.data;
        console.log(this.groups.length)
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
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
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

@media (max-width: 991px) {
  .top-responsive {
    margin-top: 5em;
  }
}
</style>
