<template>
  <!-- modalC -->
  <span :id="'modalAddUser'">
    <modal
      :name="'modalAddUser'"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddUser')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{addUser}}
          </h2>
        </div>
        <div class="form-group">
          <label>{{Lastname}}* :</label>
          <input v-model="form.lastname" type="text" class="form__input field-general w-input" maxlength="40" :class="{ 'is-invalid': errors.lastname}" />
          <p v-if="errors.lastname" class="error">
            <span class="error">{{LastnameRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label>{{Firstname}}* :</label>
          <input v-model="form.firstname" type="text" class="form__input field-general w-input" maxlength="40" :class="{ 'is-invalid': errors.firstname}" />
          <p v-if="errors.firstname" class="error">
            <span class="error">{{FirstnameRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label class="require col-md-3">{{Email}}* :</label>
          <input v-model="form.email" type="text" class="form__input field-general w-input" maxlength="40" :class="{ 'is-invalid': errors.email}" />
          <p v-if="errors.email" class="error">
            <span class="error">{{EmailRequired}}</span>
          </p>
        </div>
        <div class="form-group" v-if="userManage == 0">
          <label>{{Role}}* :</label>
          <select v-model="form.profile" class="dropdown-toggle" :class="{ 'is-invalid': errors.profile}">
            <option value="5" v-if="coordinatorAccess != 0">{{Administrator}}</option>
            <option value="6">{{Evaluator}}</option>
          </select>
          <p v-if="errors.profile" class="error">
            <span class="error">{{RoleRequired}}</span>
          </p>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="createUser()"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalAddUser')"
        >{{Retour}}</a>
      </div>
      <div class="loading-form" v-if="loading">
        <Ring-Loader :color="'#de6339'" />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalAddUser",
  props: {
    group: Number,
    coordinatorAccess: Number,
    userManage: Number
  },
  data() {
    return {
      errors: {
        firstname: false,
        lastname: false,
        login: false,
        email: false,
      },
      form: {
        login: '',
        firstname: '',
        lastname: '',
        campaigns: '',
        oprofiles: '',
        groups: '',
        profile: 5,
        jgr: 13,
        email: '',
        newsletter: 0,
        university_id: 0,
        ldap: 0
      },
      changes: false,
      loading: false,
      addUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Firstname: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIRSTNAME"),
      FirstnameRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIRSTNAME_REQUIRED"),
      Lastname: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME"),
      LastnameRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME_REQUIRED"),
      Email: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL"),
      EmailRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_REQUIRED"),
      Role: Joomla.JText._("COM_EMUNDUS_ONBOARD_ROLE"),
      RoleRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_ROLE_REQUIRED"),
      Administrator: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATOR"),
      Evaluator: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATOR"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
    },
    generatePseudo() {
      this.form.login = (this.form.firstname.charAt(0) + this.form.lastname.substr(0, 10)).toLowerCase();
    },
    createUser() {
      this.errors = {
        firstname: false,
        lastname: false,
        identifier: false,
        email: false,
      };
      if(this.form.lastname == ''){
        this.errors.lastname = true;
        return 0;
      }
      if(this.form.firstname == ''){
        this.errors.firstname = true;
        return 0;
      }
      if(this.form.email == ''){
        this.errors.email = true;
        return 0;
      }

      this.form.login = this.form.email;
      this.loading = true;
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=users&task=adduser',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(this.form)
      }).then((response) => {
        if(response.data.status == true){
          if(this.userManage == 0) {
            axios({
              method: "post",
              url: 'index.php?option=com_emundus_onboard&controller=program&task=affectusertogroup',
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                profile: this.form.profile,
                group: this.group,
                email: this.form.email
              })
            }).then((rep) => {
              this.loading = false;
              if (this.form.profile == 5) {
                this.$emit("Updatemanager");
              } else {
                this.$emit("Updateevaluator");
              }
              this.$modal.hide('modalAddUser')
            });
          } else {
            this.loading = false;
            this.$modal.hide('modalAddUser');
            this.$emit("UpdateUsers",this.form);
          }
        }
      }).catch((error) =>  {
        console.log(error);
      });
    },
  },
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

  .require{
    margin-bottom: 10px !important;
  }

.inputF{
  margin: 0 0 10px 0 !important;
}

  p .error{
    position: absolute;
    bottom: -10px;
    left: 28%;
  }

  .d-flex{
    display: flex;
    align-items: center;
  }

  .dropdown-custom{
    height: 35px;
  }
</style>
