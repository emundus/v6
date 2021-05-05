<template>
  <div id="ModalConfigStep" class="ModalConfigStep">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div id="bounding" class="bounding">
    <modal :name="'stepModal' + ID" :width="580" :height="1000" :adaptive="true" :draggable="true" :scrollable="true" :clickToClose="true" @before-open="beforeOpen" @before-close="beforeClose">
      <!--      please keep this code part, do not remove ||| option 1 : only one step in -->


      <!--      &lt;!&ndash;  Set step in   &ndash;&gt;-->
      <!--      <div class="row mb-3">-->
      <!--        <label class="col-sm-6 col-form-label">{{ this.title.inputStatusTitle }}</label>-->
      <!--        <div class="col-xs-8">-->
      <!--          <select v-model="form.inputStatus" class="form-control-select" id="instatus-selected">-->
      <!--            <b-form-select-option selected disabled>&#45;&#45; Statut d'entre de l'etape &#45;&#45;</b-form-select-option>-->
      <!--            <option v-for="instatus in this.inStatus" :value="instatus.step"> {{ instatus.value }}</option>-->
      <!--          </select>-->
      <!--        </div>-->
      <!--      </div>-->


      <!--&lt;!&ndash;    option 2 : multiple step in &ndash;&gt;-->

      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.label }}</label>
        <div class="col-xs-8">
          <textarea class='notes' id="step_label" rows="3" v-model="stepLabel" placeholder="Nom de l'etape" style="width: 95%; height: 35px !important"></textarea>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.inputStatusTitle }}</label>
        <tr v-for="item in this.$data.inStatus" v-if="!item.disabled">
          <th><input type="checkbox" :id="item.step" :value="item.step" v-model="checked[item.step]"/></th>
          <th><label class="form-check-label" :id="'status'+ item.step" name=""> {{item.value}}</label></th>
        </tr>
      </div>

      <!-- Step step out -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.outputStatusTitle }}</label>
        <div class="col-xs-8">
          <select v-model="form.outputStatus" class="form-control-select" id="outstatus-selected">
            <b-form-select-option selected disabled>-- Statut sortie de l'etape --</b-form-select-option>
            <option v-for="outstatus in this.outStatus" :value="outstatus.step"> {{ outstatus.value }}</option>
          </select>
        </div>
      </div>

      <!--       Step start date >>> add datetime picker -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.startDateTitle }}</label>
        <div class="col-xs-8">
          <date-picker v-model="startDate" mode="dateTime" is24hr>
            <template v-slot="{ inputValue, inputEvents }">
              <input
                  class="px-2 py-1 border rounded focus:outline-none focus:border-blue-300"
                  :value="inputValue"
                  v-on="inputEvents"
                  :id="'start_date_' + ID"
              />
            </template>
          </date-picker>

        </div>
      </div>

      <!-- Step end date >>> add datetime picker -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.endDateTitle }}</label>
        <div class="col-xs-8">
          <date-picker v-model="endDate" mode="dateTime" is24hr>
            <template v-slot="{ inputValue, inputEvents }">
              <input
                  class="px-2 py-1 border rounded focus:outline-none focus:border-blue-300"
                  :value="inputValue"
                  v-on="inputEvents"
                  :id="'end_date_' + ID"
              />
            </template>
          </date-picker>

        </div>
      </div>

      <!-- Supplementary information -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.notes }}</label>
        <div class="col-xs-8">
          <textarea id="notes_form" rows="3" v-model="form.stepNotes" placeholder="Informations supplementaires" style="margin: -5px; width: 102%"></textarea>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.messageTitle }}</label>
        <tr>
          <th><input type="checkbox" @click="showMessage=!showMessage" :checked="showMessage==true">Oui</th>
        </tr>
      </div>

      <message-modal :element="form" :stepParams="stepParams" :activateParams="showMessage" v-if="showMessage==true"/>

      <div class="row mb-3" v-if="showMessage==false"/>

      <div class="row mb-3">
        <b-button variant="success" @click="updateParams()">Sauvegarder</b-button>
        <b-button variant="danger" @click="exitModal()">Quitter</b-button>
      </div>

    </modal>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from "sweetalert2";
import $ from 'jquery';
import Calendar from 'v-calendar/lib/components/calendar.umd'
import DatePicker from 'v-calendar/lib/components/date-picker.umd'
import { commonMixin } from "../../../mixins/common-mixin";
import messageModal from "../WorkflowModal/element/messageModal";

require('moment')().format('YYYY-MM-DD HH:mm:ss');

const qs = require('qs');

export default {
  name: "ModalConfigStep",
  mixins: [commonMixin],
  props: {
    ID: Number,
    element: Object,
  },

  components: {
    Calendar,
    DatePicker,
    messageModal,
  },

  data: function() {
    return {
      stepParams: Object,
      title: {
        label: "Nom de l'etape",
        inputStatusTitle: "Statut d'entre",
        outputStatusTitle: "Statut de sortie",
        startDateTitle: "Date debut",
        endDateTitle: "Date fin",
        notes: "Informations supplementaires",
        messageTitle: "Voulez-vous envoyer un message?"
      },
      // use for form v-model
      form: {
        id: '',
        inputStatus: [],
        outputStatus: '',
        stepNotes: '',
      },

      // use for date v-model
      startDate: '',
      endDate: '',
      //use to keep the axios api
      inStatus: [],
      outStatus: [],

      checked: [],
      inputList: [],

      inStatusSelected: [],
      stepLabel: '',

      showMessage: false,
    }
  },

  created() {
    this.form.inputStatus = this.checked;
  },

  methods: {
    showMesssageParams: function() {
      this.showMessage = true;
      console.log('show message params');
    },

    getCurrentParams: function(sid) {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=getcurrentparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: { sid },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.showMessage = false;
        this.form.id = this.ID;
        this.form.outputStatus = response.data.data.outputStatus;
        this.form.stepNotes = response.data.data.notes;

        this.startDate = response.data.data.startDate;
        this.endDate = response.data.data.endDate;
        var _temp = response.data.data.inputStatus.split(',');
        _temp.forEach(elt => {
          this.checked[elt] = true;
        });

        this.stepLabel = response.data.data.stepLabel;

        this.stepParams = response.data.data.message;

        if(response.data.data.message !== undefined) {
          this.showMessage = true;
        } else {
          this.showMessage = false;
        }
        console.log('');
      })
    },

    getAvailableStatus: function(data) {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=getavailablestatus',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: { data },
        paramsSerializer: params => {
          return qs.stringify(params)
        }
      }).then(response => {
        var _temp = response.data.dataIn;

        if(_temp !== null) {
          _temp.forEach(elt => elt['disabled'] = false);
          this.inStatus = _temp;
        }
        this.outStatus = response.data.dataOut;
      })
    },

    updateParams: function() {
      // params :: this.form

      if(this.form.inputStatus !== null && this.form.outputStatus !== null
          && this.startDate !== null && this.endDate !== null
          && new Date(this.startDate).toISOString().slice(0, 19).replace('T', ' ') <= new Date(this.endDate).toISOString().slice(0, 19).replace('T', ' ')) {
        axios({
          method: 'post',
          url: 'index.php?option=com_emundus_workflow&controller=step&task=updateparams',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            data: {
              id: this.element.id,
              step_label: this.stepLabel,
              workflow_id: this.$data.id,
              params: this.form,
              start_date: new Date(this.startDate).toISOString().slice(0, 19).replace('T', ' '),
              end_date: new Date(this.endDate).toISOString().slice(0, 19).replace('T', ' '),
            }
          })
        }).then(response => {
          Swal.fire({
            icon: 'success',
            title: 'Congrat',
            text: 'Les parametres sont sauvegardés',
            footer: '<a href>EMundus SAS</a>',
            confirmButtonColor: '#28a745',
          }).then(result => {
            if(result.isConfirmed) {
              this.$modal.hide("stepModal" + this.element.id);
            }
            // this.inStatusSelected = $( "#instatus-selected option:selected" ).text();
          })
        }).catch(error => {
          console.log(error);
        })
      }
      else {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          html: 'Le statut d\'entré et le statut de sortie doivent être configuré',
          timer: 1500,
          showConfirmButton:false,
        })
      }
    },

    exitModal: function() {
      var _in = this.form.inputStatus;
      var _out = this.form.outputStatus;

      if(_in.length == 0 || _out === null) {
        this.$emit('deleteStep', this.element.id);          // using emit to pass event
        this.$modal.hide("stepModal" + this.element.id);
      }
    },

    beforeOpen() {
      var data = {
        wid: this.$data.id,
        sid: this.element.id,
      }
      this.getCurrentParams(this.element.id);
      this.getAvailableStatus(data);  //for test only
    },

    beforeClose() {
      let _result = [];
      let _emit = [];
      for(i = 0; i <= this.form.inputStatus.length; i++) {
        if(this.form.inputStatus[i] == true) {
          _result.push(document.getElementById('status'+i).innerText);
        }
      }

      _emit['output'] = $( "#outstatus-selected option:selected" ).text();
      _emit['input'] = _result.toString();
      _emit['startDate'] = $( "#start_date_" + this.ID).val();
      _emit['endDate'] = $( "#end_date_" + this.ID).val();

      _emit['id'] = this.form.id;

      _emit['label'] = $("#step_label").val();      // pass label to parent component (stepflow)

      if( $( "#email-selected option:selected" ).text() !== "" || $( "#email-selected option:selected" ).text() !== undefined || $( "#email-selected option:selected" ).text() !== null) {
        _emit['email'] = $( "#email-selected option:selected" ).text();
      }

      if( $( "#destination-selected option:selected" ).text() !== "" || $( "#destination-selected option:selected" ).text() !== undefined || $( "#destination-selected option:selected" ).text() !== null) {
        _emit['destination'] = $( "#destination-selected option:selected" ).text();
      }

      this.$emit('updateStep', _emit);
    }
  },
}
</script>

<style>
.vm--modal {
  padding: 10px 30px !important;
}

.select {
  max-width: 300px !important;
}

.col-form-label {
  color: blue !important;
}

.theme-orange .vdatetime-popup__header,
.theme-orange .vdatetime-calendar__month__day--selected > span > span,
.theme-orange .vdatetime-calendar__month__day--selected:hover > span > span {
  background: #FF9800;
}

.theme-orange .vdatetime-year-picker__item--selected,
.theme-orange .vdatetime-time-picker__item--selected,
.theme-orange .vdatetime-popup__actions__button {
  color: #ff9800;
}
</style>
