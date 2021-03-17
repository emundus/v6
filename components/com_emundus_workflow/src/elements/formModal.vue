<template>
  <div>
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.form_name_title }}</label>
        <div class="col-xs-8">
          <select v-model="form.formNameSelected" class="form-control">
            <b-form-select-option selected disabled>--Formulaire--</b-form-select-option>
            <option v-for="form in this.$data.forms" :value="form.id"> {{ form.label }}</option>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.edited_status_title }}</label>
        <div class="col-xs-8">
          <select v-model="form.editedStatusSelected" class="form-control-select">
            <b-form-select-option selected disabled>--Statut--</b-form-select-option>
            <option v-for="instatus in this.$data.status" :value="instatus.id"> {{ instatus.value }}</option>
          </select>
        </div>
      </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.output_status_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.outputStatusSelected" class="form-control-select">
          <b-form-select-option selected disabled>--Statut--</b-form-select-option>
          <option v-for="outstatus in this.$data.status" :value="outstatus.id"> {{ outstatus.value }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.notes_title }}</label>
      <div class="col-xs-8">
        <textarea id="exampleFormControlTextarea1" rows="3" v-model="form.notes" placeholder="Informations Supplémentaires" style="margin: -5px; width: 105%"></textarea>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');

export default {
  name: "formModal",

  props: {
    element: Object,
  },

  data: function() {
    return {
        types: [
          'color'
        ],
      elementTitle: {
        form_name_title: "Nom du formulaire",
        edited_status_title: "Statut d'édition",
        output_status_title: "Statut de sortie",
        notes_title: "Notes",
      },
      form: {
        formNameSelected: '',
        editedStatusSelected: '',
        outputStatusSelected: '',
        notes: '',
      },

      forms: [],
      status: [],
    }
  },

  methods: {
    getAllFormType: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallpublishedforms')
          .then(response => {
            console.log(response);
            this.$data.forms = response.data.data;
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

  },

  created() {
    this.getAllFormType();
    this.getAllStatus();
    this.form = this.element;
  },

  watch() {

  }
}
</script>

<style>
</style>