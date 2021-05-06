<template>
  <div>
    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.email_model_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.emailSelected" class="form-control-select" id="email-selected">
          <option selected disabled>---Email---</option>
          <option v-for = "model in this.$data.emails" :value="model.id" :disabled="isDisable"> {{ model.lbl }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.destination_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.destinationSelected" class="form-control-select" id="destination-selected">
          <option selected disabled>---Destination---</option>
          <option v-for="destination in this.$data.destination" :value="destination.id" :disabled="isDisable" @click="showOtherUser=false"> {{ destination.label }}</option>
          <option @click="showOtherUser=!showOtherUser"> Choisir un utilisateur</option>
        </select>
      </div>
    </div>

<!--    v-if showOtheruser == true -->
    <div class="row mb-3" v-if="showOtherUser==true">
      <input type="checkbox" id="selectAll_" v-if="showOtherUser==true" @click="handleSelect"> {{ selectAllTitle }}
      <div v-for="user in this.userList" v-if="showOtherUser==true">
        <input type="checkbox" :id="user.id" :value="user.id" v-model="userChecked[user.id]"/>
        <label class="form-check-label" :id="'userName_' + user.id"> {{ user.firstname }} {{ user.lastname }}</label>
        <label class="form-check-label" :id="'userEmail_' + user.id"> {{ '[' + user.email + ']'}}</label>
      </div>
    </div>

    <div v-if="showOtherUser==false"></div>

    <!--  TRIGGER PART -->
    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.trigger_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.triggerSelected" class="form-control-select" id="trigger-selected">
          <option value="to_current_user">{{ TO_CURRENT_USER }}</option>
          <option value="to_applicant">{{ TO_APPLICANT }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.notes_title }}</label>
      <div class="col-xs-8">
        <textarea v-model="form.messageNotes" placeholder="Supplementaires informations" style="margin: -3px; width: 95%"/>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');

export default {
  name: "messageModal",

  props: {
    element: Object,
    stepParams: Object,
    activateParams: Boolean,
  },

  data: function() {
    return {
      elementTitle: {
        email_model_title: "Message",
        input_status_title: "Statut d'entrée",
        destination_title: "Destinataire",
        notes_title: "Notes",
        trigger_title: "Trigger",
      },

      form: {
          emailSelected: '',
          inputStatus: '',
          destinationSelected: '',
          messageNotes: '',
          usersSelected: [],
          triggerSelected: [],
      },

      emails: [],
      status: [],
      destination: [],
      isDisable: true,
      showOtherUser: false,

      userList: [],

      userChecked: [],
      triggerChecked: [],

      selectAllTitle: "Choisir tous",
      selectAll: false,

      TO_CURRENT_USER: 'Current User',
      TO_APPLICANT: 'Applicant',
    }
  },

  created() {

    if(this.$props.activateParams === undefined) {
      this.isDisable = true;
    } else {
      this.isDisable = false;
    }
    this.getAllMessages();
    this.getAllDestinations();
    this.form = this.element;
    if(this.$props.stepParams !== undefined) {
      this.form.emailSelected = this.$props.stepParams.email;
      this.form.destinationSelected = this.$props.stepParams.destination;

      if(this.$props.stepParams.destination === 'Choisir un utilisateur') {
        this.showOtherUser = true;
      }
    }
    this.getAllUsers();
    this.form.usersSelected = this.userChecked;
    this.form.triggerSelected = this.triggerChecked;
  },

  methods: {
    handleSelect: function() {
      var checkboxes = document.getElementById('userName_');
      console.log(checkboxes);        //// checkbox + id

    },

    createTrigger: function() {
      axios({
        method: 'post',
        url: '',      /// create trigger --> to applicant / to current user
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {

      }).catch(error => {
        console.log(error);
      })
    },

    getAllMessages: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallmessages')
          .then(response => {
            this.$data.emails = response.data.data;
          })
          .catch(error => {
            console.log(error);
          })
    },

    getAllDestinations: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getalldestinations')
          .then(response => {
            this.$data.destination = response.data.data;
          })
          .catch(error => {
            console.log(error);
          })
    },

    getAllUsers: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallusers')
          .then(response => {
            this.userList = response.data.data;
          })
          .catch(error =>{console.log(error);})
    },
  }
}
</script>

<style>

</style>
