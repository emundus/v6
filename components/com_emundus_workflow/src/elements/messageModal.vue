<template>
  <div>
    <div class="form-group">

      <label> {{ this.$data.elementTitle.email_model_title }}</label>

      <select v-model="this.$data.email_selected">
        <option v-for = "model in this.$data.email_model" :value="model.id"> {{ model.lbl }}</option>
      </select>
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

        email_model: [],
        input_status: [],
        destination: [],
        notes: '',
      }
    },

    created() {
      this.getAllMessages();
    },

    methods: {
      getAllMessages: function() {
        axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallmessages')
          .then(response => {
            this.$data.email_model = response.data.data;
            console.log(response);
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