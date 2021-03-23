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
          <b-form-select-option selected disabled>--Statut d'édition--</b-form-select-option>
          <option v-for="(item, index) in this.$data.inStatus" :value="item.step"> {{ item.value }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.output_status_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.outputStatusSelected" class="form-control-select">
          <b-form-select-option selected disabled>--Statut de sortie--</b-form-select-option>
          <option v-for="(item, index) in this.$data.outStatus" :value="item.step"> {{ item.value }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.notes_title }}</label>
      <div class="col-xs-8">
        <textarea id="notes_form" rows="3" v-model="form.notes" placeholder="Notes" style="margin: -5px; width: 102%"></textarea>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <label>Choisir couleur</label>
      </div>
      <div class="col-sm-4" style="padding-left: 0">
        <v-input-colorpicker  v-model="form.color" style="width:140px"/>
      </div>
      <div class="col-sm-2" id="hex_color">
        <p style="padding: 3px; margin:0px -15px" v-bind:style="{ color: this.form.color }">{{ this.form.color }}</p>
      </div>
    </div>
  </div>
</template>
<script>
import axios from 'axios';
const qs = require('qs');
export default {
  name: "espaceModal",
  props: {
    element: Object,
  },
  data: function() {
    return {
      elementTitle: {
        form_name_title: "Nom du frmulaire",
        edited_status_title: "Statut d'édition",
        output_status_title: "Statut de sortie",
        notes_title: "Notes",
      },
      form: {
        formNameSelected: '',
        editedStatusSelected: '',
        outputStatusSelected: '',
        notes: '',
        color: "#0f4c81",
      },
      forms: [],
      status: [],
      inStatus: [],
      outStatus: [],
      disabled: false,
    }
  },
  methods: {
    getAllFormType: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallpublishedforms')
          .then(response => {
            // console.log(response);
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
    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },


    getIn: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getin',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          wid: this.getWorkflowIdFromURL(),
        })
      }).then(response => {
        this.$data.inStatus = response.data.data;
      }).catch(error => {
        console.log(error);
      })
    },

    getOut: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getout',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          wid: this.getWorkflowIdFromURL(),
        })
      }).then(response => {
        this.$data.outStatus = response.data.data;
      }).catch(error => {
        console.log(error);
      })
    },

    updateParams: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=updateparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          params: this.form,
        })
      }).then(response => {
      }).catch(error => {
        console.log(error);
      })
    },
    onChange: function(event) {
      console.log(event);
    }
  },
  created() {
    this.getAllFormType();
    this.getAllStatus();
    this.getIn();
    this.getOut();
    this.form = this.element;
  },
  watch() {
  }
}
</script>
<style>
</style>