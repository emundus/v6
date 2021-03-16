<template>
  <div>
    <div class="form-group">
      <label> {{ this.$data.elementTitle.form_name_title }}</label>

      <select v-model="this.$data.formNameSelected" @change="getValue($event)">
        <option v-for="form in this.$data.forms" :value="form.id"> {{ form.label }}</option>
      </select>

      <div class="form-group">
        <label> {{ this.$data.elementTitle.edited_status_title }}</label>
        <select v-model="this.$data.editedStatusSelected">
          <option v-for="instatus in this.$data.status" :value="instatus.id"> {{ instatus.value }}</option>
        </select>
      </div>

      <div class="form-group">
        <label> {{ this.$data.elementTitle.output_status_title }}</label>
        <select v-model="this.$data.outputStatusSelected">
          <option v-for="outstatus in this.$data.status" :value="outstatus.id"> {{ outstatus.value }}</option>
        </select>
      </div>

      <div class="form-group">
        <label> {{ this.$data.elementTitle.notes_title }}</label>
        <textarea v-model="notes" placeholder="Supplémentaire informations"></textarea>
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

  },

  data: function() {
    return {
      elementTitle: {
        form_name_title: "Nom du formulaire",
        edited_status_title: "Statut d'édition",
        output_status_title: "Statut de sortie",
        notes_title: "Notes",
      },
      formNameSelected: '',
      editedStatusSelected: '',
      outputStatusSelected: '',

      forms: [],
      status: [],
      notes: '',
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
  },

  watch() {

  }
}
</script>

<style>

</style>