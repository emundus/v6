<template>
  <div>
    <div class="form-group">
      <label> {{ this.$data.elementTitle.email_model_title }}</label>

      <select v-model="this.$data.email_selected">
        <option v-for = "model in this.$data.emails" :value="model.id"> {{ model.lbl }}</option>
      </select>
    </div>

    <div class="form-group">
      <label> {{ this.$data.elementTitle.input_status_title }}</label>
      <select v-model="this.$data.status_selected">
        <option v-for="statu in this.$data.status" :value="statu.id"> {{ statu.value }}</option>
      </select>
    </div>

    <div class="form-group">
      <label> {{ this.$data.elementTitle.destination_title }}</label>
      <select v-model="this.$data.destination_selected">
        <option v-for="destination in this.$data.destination" :value="destination.id"> {{ destination.label }}</option>
      </select>
    </div>

    <div class="form-group">
      <label> {{ this.$data.elementTitle.notes_title }}</label>
      <textarea v-model="this.$data.notes_provided" placeholder="Supplementaires informations"/>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');

  export default {
    name: "messageModal",

    props: {

    },

    data: function() {
      return {
        elementTitle: {
          email_model_title: "Modèle du message",
          input_status_title: "Statut d'entrée",
          destination_title: "Destinataire",
          notes_title: "Notes",
        },

        email_selected: '',
        status_selected: '',
        destination_selected: '',
        notes_provided: '',

        emails: [],
        status: [],
        destination: [],
      }
    },

    created() {
      this.getAllMessages();
      this.getAllStatus();
      this.getAllDestinations();
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

      getAllStatus: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus')
            .then(response => {
              this.$data.status = response.data.data;
            })
            .catch(error => {
              console.log(error);
            })
      },

      getAllDestinations: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getalldestinations')
            .then(response => {
              this.$data.destination = response.data.data;
              console.log(response);
            })
            .catch(error => {
              console.log(error);
            })
      },

      configureMessage: function() {

      }
    }
  }
</script>

<style>

</style>