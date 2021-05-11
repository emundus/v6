<template>
  <div>
    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.email_model_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.emailSelected" class="form-control-select" id="email-selected">
          <option selected disabled>---Email---</option>
          <option v-for = "model in this.$data.emails" :value="model.id"> {{ model.lbl }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.destination_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.destinationSelected" class="form-control-select" id="destination-selected">
          <option selected disabled>---Destination---</option>
          <option v-for="destination in this.$data.destination" :value="destination.id" @click="handleOtherClick"> {{ destination.label }}</option>
          <option @click="handleClick" :value="'other'"> Choisir un utilisateur</option>
        </select>
      </div>
    </div>

<!--    v-if showOtheruser == true -->
    <div class="row mb-3" v-if="showOtherUser==true">
      <input type="checkbox" id="selectAll_" v-if="showOtherUser==true" @click="handleSelect"> {{ selectAllTitle }}
      <div v-for="user in this.userList" v-if="showOtherUser==true">
        <input type="checkbox" :id="'check' + user.id" :value="user.id" v-model="userChecked[user.id]" @click="handleClickUser(user.id)"/>
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

    <div class="row mb-3">
      <b-button variant="success" @click="updateTrigger()">Sauvegarder</b-button>
      <b-button variant="danger">Quitter</b-button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
const qs = require('qs');

export default {
  name: "messageModal",

  props: {
    messageParams: Object,
    stepParams: Object,
    activateParams: Boolean,
    selectOtherUsers: Boolean,
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

      userIdList: [],
      users_selected: '',
    }
  },

  created() {
    this.getAllMessages();
    this.getAllDestinations();
    this.getAllUsers();
    this.getMessageParams(this.messageParams.id);
  },

  methods: {
    handleOtherClick: function() {
      this.showOtherUser = false;
      this.form.usersSelected = null;
      // uncheck all options
      this.userIdList.forEach(elt => {
        this.userChecked[elt] = false;
        $('#check' + elt).prop('checked', false);
      });
    },

    handleClick: function() {
      if(this.showOtherUser === true) {
        /// set variables
        this.form.usersSelected = this.userChecked;
      } else { }
      this.showOtherUser=!this.showOtherUser;
    },

    handleClickUser: function(id) {
      if(this.showOtherUser === true) {
        this.userChecked[id] = true;
        this.form.usersSelected = this.userChecked;
      } else {}
    },

    handleSelect: function() {
      this.selectAll=!this.selectAll;

      this.userIdList.forEach(elt => {
        if(this.selectAll == true) {
          document.getElementById('check' + elt).checked = true;
          this.userChecked[elt] = true;
          this.form.usersSelected = this.userChecked;
        } else {
          this.userChecked[elt] = false;
          document.getElementById('check' + elt).checked = false;
        }
      })
    },

    updateTrigger: function() {
      const selectedUserList = [];
      var selectedIndex = $("#destination-selected option:selected").index();           /// get index of selected option
      var selectedValue = $("#destination-selected option").eq(selectedIndex).val();    /// get the value of selected option

      if(this.showOtherUser === true && selectedValue === 'other') {
        this.userIdList.forEach(id => {
          if(document.getElementById('check' + id).checked === true) {
            /// push
            selectedUserList.push(document.getElementById('check' + id).value);   /// using jquery here
          } else {}
        })
        this.form.usersSelected = selectedUserList.toString();
      }

      let trigger = {
        id: this.messageParams.triggerId,
        step: this.stepParams.outputStatus,
        email_id: this.form.emailSelected,
        to_current_user: this.form.triggerSelected === 'to_current_user' ? 1 : 0,
        to_applicant: this.form.triggerSelected === 'to_applicant' ? 1 : 0,
      }

      let message_div = {
        params: this.form,
        id: this.messageParams.id,      /// update message div
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=common&task=updateelement',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: message_div,
        })
      }).then(response => {
        /// after updating --> emit to parent component all updated values --> how to do???
        let triggerParams = [];
        if( $( "#email-selected option:selected" ).text() !== "" || $( "#email-selected option:selected" ).text() !== undefined || $( "#email-selected option:selected" ).text() !== null) {
          triggerParams['messageTemplate'] = $("#email-selected option:selected").text();
        }
        if( $( "#destination-selected option:selected" ).text() !== "" || $( "#destination-selected option:selected" ).text() !== undefined || $( "#destination-selected option:selected" ).text() !== null) {
          triggerParams['messageDestination'] = $("#destination-selected option:selected").text();
        }

        /// get the selectedIndex, selectedValue -->
        let selectedIndex = $("#destination-selected option:selected").index();
        let selectedValue = $("#destination-selected option").eq(selectedIndex).val();
        let _users= [];

        if(selectedValue === 'other' && this.form.usersSelected !== undefined) {
          let user_selected_split = (this.form.usersSelected).split(',');

          user_selected_split.forEach(user => {
              _users.push(document.getElementById('userName_' + user).innerText);
          })

          triggerParams['messageDestinationList'] = _users.toString();
        }

        if( $( "#trigger-selected option:selected" ).text() !== "" || $( "#trigger-selected option:selected" ).text() !== undefined || $( "#trigger-selected option:selected" ).text() !== null) {
          triggerParams['trigger'] = $("#trigger-selected option:selected").text();
        }

        triggerParams['messageDivId'] = this.messageParams.id;
        this.$emit('updateMessageBlock', triggerParams);

      }).catch(error => { console.log(error); })

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=common&task=updatetrigger',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          trigger: trigger,
          users: selectedUserList.length === 0 ? this.form.destinationSelected :  selectedUserList,
        })
      }).then(response => {})
        .catch(error => { console.log(error); })
    },

    getMessageParams: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=common&task=getelementbyid',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: id,      /// id of this message block
        })
      }).then(response => {
        let json_params = response.data.data.params;
        this.form.emailSelected = JSON.parse(json_params).emailSelected;
        this.form.destinationSelected = JSON.parse(json_params).destinationSelected;
        this.form.triggerSelected = JSON.parse(json_params).triggerSelected;

        if(this.form.destinationSelected === 'other') {
          var raw_users = JSON.parse(json_params).usersSelected;
          this.showOtherUser = true;
          let users_selected = raw_users.split(',');
          users_selected.forEach(user => {
            this.userChecked[user] = true;
          })
        } else {
          this.showOtherUser = false;
          /// do nothing here ...
        }
      }).catch(error => {
        console.log(error);
      })
    },

    getAllMessages: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallmessages').then(response => {this.$data.emails = response.data.data;}).catch(error => {console.log(error);})
    },

    getAllDestinations: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getalldestinations').then(response => {this.$data.destination = response.data.data;}).catch(error => {console.log(error);})
    },

    getAllUsers: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallusers')
          .then(response => {
            this.userList = response.data.data;
            response.data.data.forEach(elt => this.userIdList.push(elt.id));
          })
          .catch(error =>{console.log(error);})
    },
  }
}
</script>

<style>

</style>
