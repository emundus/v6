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

      <div v-for="item in this.$data.inStatus" v-if="!item.disabled">
        <input type="checkbox" :id="item.step" :value="item.step" v-model="checked[item.step]"/>
        <label class="form-check-label" :for="item.step">{{item.value}}</label>
      </div>
<!--      </div>-->

    </div>

    <div class="row mb-3">
      <label class="col-sm-6 col-form-label">{{ this.$data.elementTitle.output_status_title }}</label>
      <div class="col-xs-8">
        <select v-model="form.outputStatus" class="form-control-select">
          <b-form-select-option selected disabled>--Statut de sortie--</b-form-select-option>
          <option v-for="(item, index) in this.$data.outStatus" :value="item.step" :disabled="item.disabled" v-if="!item.disabled"> {{ item.value }}</option>
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
  },
  data: function() {
    return {
      elementTitle: {
        form_name_title: "Nom du formulaire",
        edited_status_title: "Statut d'édition",
        output_status_title: "Statut de sortie",
        notes_title: "Notes",
      },
      form: {
        formNameSelected: '',
        inputStatus: [],
        outputStatus: '',
        notes: '',
        color: "#0f4c81",
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

    getCurrentStatus: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getcurrentinputstatusbyitem',
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        if(response.data.data !== null) {
          response.data.data.forEach(elt => { this.checked[elt.step] = true; })
        }
        else {}
      })

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getcurrentoutputstatusbyitem',
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        // console.log(response);
        if(response.data.data !== null) {
          this.form.outputStatus = (response.data.data)[0].step;
        }
        else {}
      })
    },

    getAvailableInStatus: async function(data) {
      var _rawA2 = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getavailableinputstatus', { params: { data : data }});
      var _rawAll = await axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus');

      var _a2 = _rawA2.data.data;
      var _all = _rawAll.data.data;

      let _idiff = _all.filter(({ step: id1 }) => !_a2.some(({ step: id2 }) => id2 === id1));
      let _iintersect = _all.filter(item1 => _a2.some(item2 => item1.step === item2.step));

      if(_idiff.length !== 0 && _iintersect.length !== 0) {
        _idiff.forEach(elt => elt['disabled'] = true);
        _iintersect.forEach(elt => elt['disabled'] = false);
        var _merge1 = _idiff.concat(_iintersect);
      }

      else {
        if(_idiff.length !== 0 && _iintersect.length == 0) {
          _all.forEach(elt => elt['disabled'] = true);
          var _merge1 = _all;
        }
        else {
          _all.forEach(elt => elt['disabled'] = false);
          var _merge1 = _all;
        }
      }

      this.$data.inStatus = _merge1;
    },

    getAvailableOutStatus: async function(data) {
      var _rawB2 = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getavailableoutputstatus', { params: { data : data }});
      var _rawAll = await axios.get('index.php?option=com_emundus_workflow&controller=common&task=getallstatus');

      var _b2 = _rawB2.data.data;
      var _all = _rawAll.data.data;

      let _odiff = _all.filter(({ step: id1 }) => !_b2.some(({ step: id2 }) => id2 === id1));
      let _ointersect = _all.filter(item1 => _b2.some(item2 => item1.step === item2.step));

      if(_odiff.length !== 0 && _ointersect.length !== 0) {
        _odiff.forEach(elt => elt['disabled'] = true);
        _ointersect.forEach(elt => elt['disabled'] = false);
        var _merge2 = _odiff.concat(_ointersect);
      }

      else {
        _all.forEach(elt => elt['disabled'] = false);
        var _merge2 = _all;
      }

      this.$data.outStatus = _merge2;
    },

    getNonStatusParams: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getnonstatusparamsbyitem',
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        if(response.data.data !== null) {
          this.form.formNameSelected = (response.data.data).profile;
          this.form.notes = (response.data.data).notes;
        }
        else {}
      })
    }

  },
  created() {
    this.form = this.element;
    this.form.inputStatus = this.checked;

    var data = {
      wid:this.getWorkflowIdFromURL(),
      id: this.form.id,
    }

    this.getCurrentStatus(this.form.id);
    this.getAvailableInStatus(data);
    this.getAvailableOutStatus(data);

    this.getAllFormType();

    this.getNonStatusParams(this.form.id);
  },

}
</script>
<style>
</style>