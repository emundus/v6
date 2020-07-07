<template>
  <!-- modalC -->
  <span :id="'modalAddTrigger' + triggerAction">
    <modal
      :name="'modalAddTrigger' + triggerAction"
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
        <ModalEmailPreview
                :model="this.form.model"
                :models="this.models"
        />
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddTrigger' + triggerAction)">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{addTrigger}}
          </h2>
        </div>
        <div class="form-group">
          <label>{{Model}}* :</label>
          <div class="input-can-translate">
            <select v-model="form.model" class="dropdown-toggle" :class="{ 'is-invalid': errors.model}">
              <option v-for="(model, index) in models" :key="index" :value="model.id">{{model.subject}}</option>
            </select>
            <button class="btnPreview" type="button" v-if="form.model != -1" @click.prevent="$modal.show('modalEmailPreview')">
              <em class="fas fa-eye"></em>
            </button>
          </div>
          <p v-if="errors.model" class="error col-md-12 mb-2">
            <span class="error">{{ModelRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label>{{Status}}* :</label>
          <select v-model="form.status" class="dropdown-toggle" :class="{ 'is-invalid': errors.status}">
            <option v-for="(statu,index) in status" :key="index" :value="statu.step">{{statu.value}}</option>
          </select>
          <p v-if="errors.status" class="error">
            <span class="error">{{StatusRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label>{{Target}}* :</label>
          <select v-model="form.target" class="dropdown-toggle" :class="{ 'is-invalid': errors.target}">
            <option value="5">{{Administrators}}</option>
            <option value="6">{{Evaluators}}</option>
            <option value="1000">{{Candidates}}</option>
            <option value="0">{{DefinedUsers}}</option>
          </select>
          <p v-if="errors.target" class="error">
            <span class="error">{{TargetRequired}}</span>
          </p>
        </div>
        <div class="form-group" v-if="form.target == 0" style="align-items: baseline">
          <label>{{ChooseUsers}}* :</label>
          <div class="wrap">
               <div class="search">
                  <input type="text" class="searchTerm" :placeholder="Search" v-model="searchTerm" @keyup="searchUserByTerm">
                  <button type="button" class="searchButton" @click="searchUserByTerm">
                    <em class="fas fa-search"></em>
                 </button>
               </div>
            </div>
            <div class="select-all">
              <input type="checkbox" class="form-check-input bigbox" @click="selectAllUsers" v-model="selectall">
              <label>
                {{SelectAll}}
              </label>
            </div>
          <div class="users-block" :class="{ 'is-invalid': errors.selectedUsers}">
            <div v-for="(user, index) in users" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="selectedUsers[user.id]">
              <div class="ml-10px">
                  <p>{{user.name}}</p>
                  <p>{{user.email}}</p>
              </div>
            </div>
          </div>
          <p v-if="errors.selectedUsers" class="error">
            <span class="error">{{UsersRequired}}</span>
          </p>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="createTrigger()"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalAddTrigger' + triggerAction)"
        >{{Retour}}</a>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import ModalEmailPreview from "./ModalEmailPreview";
const qs = require("qs");

export default {
  name: "modalAddTrigger",
  components: {ModalEmailPreview},
  props: { prog: Number, trigger: Number, triggerAction: String },
  data() {
    return {
      errors: {
        model: false,
        status: false,
        action_status: false,
        target: false,
        selectedUsers: false,
      },
      form: {
        model: -1,
        status: null,
        action_status: null,
        target: null,
        program: this.prog
      },
      users: [],
      selectedUsers: [],
      models: [],
      status: [],
      searchTerm: '',
      changes: false,
      selectall: false,
      addTrigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_ADDTRIGGER"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Model: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERMODEL"),
      ModelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERMODEL_REQUIRED"),
      Status: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS"),
      StatusRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED"),
      Target: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERTARGET"),
      TargetRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERTARGET_REQUIRED"),
      Administrators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
      Evaluators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
      Candidates: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_CANDIDATES"),
      DefinedUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_DEFINED_USERS"),
      ChooseUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGER_CHOOSE_USERS"),
      UsersRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGER_USERS_REQUIRED"),
      Search: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEARCH_USERS"),
      ChangedActionStatus: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHANGED_ACTION_STATUS"),
      TheCandidate: Joomla.JText._("COM_EMUNDUS_ONBOARD_THE_CANDIDATE"),
      Manual: Joomla.JText._("COM_EMUNDUS_ONBOARD_MANUAL"),
      SelectAll: Joomla.JText._("COM_EMUNDUS_ONBOARD_SELECT_ALL"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
      this.searchTerm = '';
      this.getUsers();
      this.getEmailModels();
      this.getStatus();
      setTimeout(() => {
        if(this.trigger != null) {
          this.getTrigger();
        }
      }, 200);
      if(this.triggerAction === 'candidate'){
        this.form.action_status = 'to_current_user';
      } else {
        this.form.action_status = 'to_applicant';
      }
    },
    createTrigger() {
      this.errors = {
        model: false,
        status: false,
        action_status: false,
        target: false,
        selectedUsers: false,
      };
      if(this.form.model === -1){
        this.errors.model = true;
        return 0;
      }
      if(this.form.status == null){
        this.errors.status = true;
        return 0;
      }
      if(this.form.action_status == null){
        this.errors.action_status = true;
        return 0;
      }
      if(this.form.target == null){
        this.errors.target = true;
        return 0;
      } else if (this.form.target == 0) {
        if(this.selectedUsers.length === 0) {
          this.errors.selectedUsers = true;
        }
      }

      if(this.trigger != null){
        axios({
          method: "post",
          url: 'index.php?option=com_emundus_onboard&controller=email&task=updatetrigger',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            tid: this.trigger,
            trigger: this.form,
            users: this.selectedUsers
          })
        }).then((rep) => {
          this.selectedUsers = [];
          this.$emit("UpdateTriggers");
          this.$modal.hide('modalAddTrigger' + this.triggerAction)
        });
      } else {
        axios({
          method: "post",
          url: 'index.php?option=com_emundus_onboard&controller=email&task=createtrigger',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            trigger: this.form,
            users: this.selectedUsers
          })
        }).then((rep) => {
          this.selectedUsers = [];
          this.$emit("UpdateTriggers");
          this.$modal.hide('modalAddTrigger')
        });
      }
    },
    getUsers() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getuserswithoutapplicants")
              .then(response => {
                this.users = response.data.data;
              });
    },
    searchUserByTerm() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=searchuserbytermwithoutapplicants&term=" + this.searchTerm)
              .then(response => {
                this.users = response.data.data;
              });
    },
    getEmailModels() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=getallemail")
              .then(response => {
                this.models = response.data.data;
              });
    },
    getStatus() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=getstatus")
              .then(response => {
                this.status = response.data.data;
              });
    },
    getTrigger() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=gettriggerbyid&tid=" + this.trigger)
              .then(response => {
                this.form.model = response.data.data.model;
                this.form.status = response.data.data.status;
                if(response.data.data.target == null) {
                  this.form.target = 0;
                  response.data.data.users.forEach(element => {
                    this.selectedUsers[element.user_id] = true;
                  });
                } else {
                  this.form.target = response.data.data.target;
                }
              });
    },
    selectAllUsers() {
      this.users.forEach(element => {
        if(!this.selectall) {
          this.selectedUsers[element.id] = true;
        } else {
          this.selectedUsers[element.id] = false;
        }
      });
      this.$forceUpdate();
    }
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

  .d-flex{
    display: flex;
    align-items: center;
  }

  .dropdown-custom{
    height: 35px;
  }

  .users-block{
    height: 15em;
    overflow: scroll;
  }

.user-item{
  display: flex;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 5px;
  align-items: center;
  margin-bottom: 1em;
}

.bigbox{
  height: 30px !important;
  width: 30px !important;
  cursor: pointer;
}

  .btnPreview{
    margin-bottom: 10px;
    position: relative;
    background: transparent;
  }

  .select-all{
    display: flex;
    align-items: end;
    margin-bottom: 1em;
  }
</style>
