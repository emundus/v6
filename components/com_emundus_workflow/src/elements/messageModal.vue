<template>
  <div>
    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.email_model_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.email_selected" class="form-control">
          <b-form-select-option selected disabled>--Message--</b-form-select-option>
          <option v-for = "model in this.$data.emails" :value="model.id"> {{ model.lbl }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.input_status_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.status_selected" class="form-control-select">
          <b-form-select-option selected disabled>--Statut--</b-form-select-option>
          <option v-for="statu in this.$data.status" :value="statu.id"> {{ statu.value }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.destination_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.destination_selected" class="form-control-select">
          <b-form-select-option selected disabled>--Destination--</b-form-select-option>
          <option v-for="destination in this.$data.destination" :value="destination.id"> {{ destination.label }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4 col-form-label">{{ this.$data.elementTitle.notes_title }}</label>
      <div class="col-xs-8">
        <textarea v-model="form.notes_provided" placeholder="Supplementaires informations" style="margin: -3px; width: 95%"/>
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
          email_selected: '',
          status_selected: '',
          destination_selected: '',
          notes_provided: '',
        },

        emails: [],
        status: [],
        destination: [],
      }
    },

    created() {
      this.getAllMessages();
      this.getAllStatus();
      this.getAllDestinations();
      this.form = this.element;
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