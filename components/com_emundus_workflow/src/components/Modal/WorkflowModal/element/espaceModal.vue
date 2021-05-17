<template>
  <div>
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ espaceModal_Title.label }}</label>
      <div class="col-xs-8">
        <textarea :id="'step_label'+element.id" rows="3" v-model="form.itemLabel" :placeholder="espaceModal_PlaceHolder.label" style="width: 95%; height: 35px !important"></textarea>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ espaceModal_Title.profile }}</label>
      <div class="col-xs-8">
        <select v-model="form.formNameSelected" class="form-control-select">
          <option selected disabled>{{ espaceModal_PlaceHolder.profile }}</option>
          <option v-for="form in this.$data.forms" :value="form.id"> {{ form.label }}</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ espaceModal_Title.supplementary_information }}</label>
      <div class="col-xs-8">
        <textarea id="notes_form" rows="3" v-model="form.notes" :placeholder="espaceModal_PlaceHolder.supplementary_information" style="margin: -5px; width: 102%"></textarea>
      </div>
    </div>

  </div>
</template>
<script>
import axios from 'axios';

const qs = require('qs');
let _all = [];
export default {
  name: "espaceModal",
  props: {
    element: Object,
  },
  data: function() {
    return {
      espaceModal_PlaceHolder: {
        label: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_PLACEHOLDER_LABEL"),
        profile: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_PLACEHOLDER_PROFILE"),
        supplementary_information: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_PLACEHOLDER_SUPPLEMENTARY_INFORMATION"),
      },

      espaceModal_Title: {
        label: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_TITLE_LABEL"),
        profile: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_TITLE_PROFILE"),
        supplementary_information: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_ESPACE_MODAL_TITLE_SUPPLEMENTARY_INFORMATION"),
      },

      form: {
        itemLabel: "",
        formNameSelected: '',
        notes: '',
      },
      forms: [],
      status: [],
      inStatus: [],
      outStatus: [],
      isDisabled: true,
      checked: [],

      in: [],
      out: [],
    }
  },
  methods: {
    getAllFormType: function() {
      axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallpublishedforms')
          .then(response => {
            this.$data.forms = response.data.data;
          })
          .catch(error => {
            console.log(error);
          })
    },

    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    getCurrentStatus: function(id) {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getcurrentstatusbyitem',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        //match dataIn and dataOut
        if(response.data.dataIn !== null && response.data.dataOut !== null) {
          response.data.dataIn.forEach(elt => { this.checked[elt.step] = true; })
          this.form.outputStatus = (response.data.dataOut)[0].step;
        }

        else if (response.data.dataIn == null && response.data.dataOut !== null) {
          this.form.outputStatus = (response.data.dataOut)[0].step;
        }

        else if (response.data.dataIn !== null && response.data.dataOut == null) {
          response.data.dataIn.forEach(elt => { this.checked[elt.step] = true; })
        }

        else {}
      }).catch(error => {
        console.log(error);
      })
    },

    getNonStatusParams: function(id) {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getnonstatusparamsbyitem',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        if(response.data.data !== null) {
          this.form.formNameSelected = (response.data.data).profile;
          this.form.notes = (response.data.data).notes;
          this.form.itemLabel = (response.data.data).label;
        }
        else {}
      })
    },
  },
  created() {
    this.getAllFormType();

    this.form = this.element;
    // this.form.inputStatus = this.checked;
    // this.getCurrentStatus(this.form.id);
    //
    // var data = {
    //   wid:this.getWorkflowIdFromURL(),
    //   id: this.form.id,
    // }
    //
    // this.getAvailableInStatus(data);
    // this.getAvailableOutStatus(data);

    this.getNonStatusParams(this.form.id);
  },

}
</script>
<style>
</style>
