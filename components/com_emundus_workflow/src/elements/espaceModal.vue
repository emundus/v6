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
<!--      <div class="col-xs-8">-->
<!--        <select v-model="form.editedStatusSelected" class="form-control-select" @change="updateOutStatus(form.editedStatusSelected)">-->
<!--          <b-form-select-option selected disabled>&#45;&#45;Statut d'édition&#45;&#45;</b-form-select-option>-->
<!--          <option v-for="(item, index) in this.$data.inStatus" :value="item.step" :disabled="item.disabled"> {{ item.value }}</option>-->
<!--        </select>-->

      <div v-for="item in this.$data.inStatus">
        <input type="checkbox" :id="item.step" :value="item.step" v-model="checked[item.step]" @click="updateOutStatus(item.step)"/>
        <label class="form-check-label" :for="item.step">{{item.value}}</label>
      </div>
<!--      </div>-->

    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.output_status_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.outputStatusSelected" class="form-control-select" @change="updateInStatus(form.outputStatusSelected)">
          <b-form-select-option selected disabled>--Statut de sortie--</b-form-select-option>
          <option v-for="(item, index) in this.$data.outStatus" :value="item.step" :disabled="item.disabled"> {{ item.value }}</option>
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
import Swal from "sweetalert2";
const qs = require('qs');
let _all = [];
export default {
  name: "espaceModal",
  props: {
    element: Object,
    isChecked: Array,
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
        editedStatusSelected: [],
        outputStatusSelected: '',
        notes: '',
        color: "#0f4c81",
      },
      forms: [],
      status: [],
      inStatus: [],
      outStatus: [],
      disabled: false,
      checked: [],
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
        // this.$data.inStatus = response.data.data;
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

    // updateParams: function() {
    //   axios({
    //     method: 'post',
    //     url: 'index.php?option=com_emundus_workflow&controller=item&task=updateparams',
    //     headers: {
    //       "Content-Type": "application/x-www-form-urlencoded"
    //     },
    //     data: qs.stringify({
    //       params: this.form,
    //     })
    //   }).then(response => {
    //   }).catch(error => {
    //     console.log(error);
    //   })
    // },

    updateInStatus: async function(outStatus) {
      var _rawAll = await axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus');
      var _rawIn = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getin', { params: {wid:this.getWorkflowIdFromURL()} });

      var as =_rawAll.data.data;
      var bs = _rawIn.data.data;

      //in intersections + differences
      const _iintersection = as.filter(item1 => bs.some(item2 => item1.step === item2.step));
      const _idifference = as.filter(({ step: id1 }) => !bs.some(({ step: id2 }) => id2 === id1));

      //set _difference --> disabled = true
      //set _intersection --> disabled = false

      _idifference.forEach(elt => elt['disabled']=true);
      _iintersection.forEach(elt => elt['disabled']=false);

      (_idifference.concat(_iintersection)).forEach(elt => { if(elt.step == outStatus) {elt['disabled'] = true;}});

      this.$data.inStatus = _idifference.concat(_iintersection);
    },

    updateOutStatus: async function(inStatus) {
      var _rawAll = await axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus');
      var _rawOut = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getout', { params: {wid:this.getWorkflowIdFromURL()} });

      var as =_rawAll.data.data;
      var cs = _rawOut.data.data;

      const _ointersection = as.filter(item1 => cs.some(item2 => item1.step === item2.step));
      const _odifference = as.filter(({ step: id1 }) => !cs.some(({ step: id2 }) => id2 === id1));

      _odifference.forEach(elt => elt['disabled']=true);
      _ointersection.forEach(elt => elt['disabled']=false);

      console.log(this.checked);

      // (_odifference.concat(_ointersection)).forEach(elt => { if(elt.step == inStatus) {elt['disabled'] = true;}});

      // console.log(_odifference.concat(_ointersection));

      this.$data.outStatus = _odifference.concat(_ointersection);
    },

    /*checkStatus: function() {
        if(this.form.editedStatusSelected !== null && this.form.outputStatusSelected !== null && this.form.editedStatusSelected == this.form.outputStatusSelected) {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            html: 'Configuration du status n\'est pas correcte',
            timer: 1200,
            showConfirmButton:false,
          })
        }

        else if(this.form.editedStatusSelected == null && this.form.outputStatusSelected !== null) {}

        else if(this.form.editedStatusSelected !== null && this.form.outputStatusSelected == null) {}

        else {
          this.updateParams();
        }
    },*/

    // getTable(e) {
    //   console.log(e);
    // }

  },
  created() {
    this.getAllFormType();
    this.updateInStatus();
    this.updateOutStatus();
    this.form = this.element;

    this.form.editedStatusSelected = this.checked;
  },
  watch() {
  }
}
</script>
<style>
</style>