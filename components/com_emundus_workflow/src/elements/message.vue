<template>
  <div>
    <div class="form-group">
      <label> {{ this.$data.elementTitle.email_model_title }}</label>
      <select v-model="this.$data.email_model">
        <option v-for = "model in this.$props.email_model" :value="model.id"> {{ model.lbl }}</option>
      </select>
    </div>

    <div class="form-group">
      <label> {{ this.$data.elementTitle.input_status_title }}</label>
      <select v-model = "this.$data.input_status">
        <option v-for = "status in this.$props.status_name" :value="status.id"> {{ status.value }}</option>
      </select>
    </div>

    <div class="form-group">
      <label>{{ this.$data.elementTitle.destination_title }}</label>
      <select v-model = "this.$data.destination">
        <option v-for = "destination in this.$props.destination_name" :value="destination.id"> {{ destination.label }}</option>
      </select>
    </div>

    <div class="form-group">
      <label>{{ this.$data.elementTitle.notes_title }}</label>
    </div>

    <div class="message=preview"></div>

    <button class="config-button"> Configurer </button>
    <button class="cancel-button"> Annuler </button>
  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');

  export default {
    name: "message",

    props: {
      email_model: Array,
      status_name: Array,
      destination_name: Array,
    },

    data: function() {
      return {
        elementTitle: {
          email_model_title: "Nom du message",
          input_status_title: "Statut d'entrée",
          destination_title: "Destinataire",
          notes_title: "Notes",
        },
        email_model: '',
        input_status: '',
        destination: '',
        notes: '',
      }
    },

    created() {

    },

    methods: {
      getAllMessages: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallmessages')
          .then(response => {
            this.$props.message_name = response.data.data;
          })
          .catch(error => {
            console.log(error);
          })
      },

      getAllStatus: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus')
            .then(response => {
              this.$props.status = response.data.data;
            })
            .catch(error => {
              console.log(error);
            })
      },

      getAllDestinations: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallassociatedgroup')
            .then(response => {
              this.$props.groups = response.data.data;
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