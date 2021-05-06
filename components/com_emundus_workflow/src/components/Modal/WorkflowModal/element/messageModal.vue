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

    <div v-if="showOtherUser==true"> Other users </div>
    <div v-if="showOtherUser==false"></div>

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
      },

      form: {
          emailSelected: '',
          inputStatus: '',
          destinationSelected: '',
          messageNotes: '',
      },

      emails: [],
      status: [],
      destination: [],
      isDisable: true,
      showOtherUser: false,
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
    }
  },

  methods: {
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
  }
}
</script>

<style>

</style>
