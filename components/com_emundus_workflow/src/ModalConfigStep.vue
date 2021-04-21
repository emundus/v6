<template>
  <div id="ModalConfigStep">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <modal :name="'stepModal' + ID" :width="580" :height="600" :adaptive="true" :draggable="true" :scrollable="true" :clickToClose="true" @before-open="beforeOpen" @before-close="beforeClose">
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
        <label class="col-sm-6 col-form-label">{{ this.title.inputStatusTitle }}</label>
        <div v-for="item in this.$data.inStatus" v-if="!item.disabled">
          <input type="checkbox" :id="item.step" :value="item.step" v-model="checked[item.step]"/>
          <label class="form-check-label" :id="'status'+ item.step" name=""> {{item.value}}</label>
        </div>
      </div>

      <!-- Step step out -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.outputStatusTitle }}</label>
        <div class="col-xs-8">
          <select v-model="outputStatus" class="form-control-select" id="outstatus-selected">
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
              />
            </template>
          </date-picker>

        </div>
      </div>

      <!-- Supplementary information -->
      <div class="row mb-3">
        <label class="col-sm-6 col-form-label">{{ this.title.notes }}</label>
        <div class="col-xs-8">
          <textarea id="notes_form" rows="3" v-model="form.notes" placeholder="Informations supplementaires" style="margin: -5px; width: 102%"></textarea>
        </div>
      </div>

      <div class="row mb-3">
        <b-button variant="success" @click="updateParams()">Sauvegarder</b-button>
        <b-button variant="danger" @click="exitModal()">Quitter</b-button>
      </div>
    </modal>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from "sweetalert2";
import $ from 'jquery';
import Calendar from 'v-calendar/lib/components/calendar.umd'
import DatePicker from 'v-calendar/lib/components/date-picker.umd'
require('moment')().format('YYYY-MM-DD HH:mm:ss');

const qs = require('qs');

export default {
  name: "ModalConfigStep",

  props: {
    ID: Number,
    element: Object,
  },

  components: {
    Calendar,
    DatePicker,
  },

  data: function() {
    return {
      title: {
        inputStatusTitle: "Statut d'entre",
        outputStatusTitle: "Statut de sortie",
        startDateTitle: "Date debut",
        endDateTitle: "Date fin",
        notes: "Informations supplementaires",
      },
      // use for form v-model
      form: {
        id: '',
        notes: '',
      },

      // use for date v-model
      startDate: '',
      endDate: '',

      // use for status v-model
      inputStatus: [],
      outputStatus: '',

      //use to keep the axios api
      inStatus: [],
      outStatus: [],

      checked: [],
      inputList: [],

      inStatusSelected: [],
    }
  },

  created() {
    this.inputStatus = this.checked;
  },

  methods: {
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
        // console.log(response);
        this.form.id = this.ID;
        this.outputStatus = response.data.data.outputStatus;
        this.form.notes = response.data.data.notes;
        this.startDate = response.data.data.startDate;
        this.endDate = response.data.data.endDate;

        var _temp = response.data.data.inputStatus.split(',');
        _temp.forEach(elt => {
          this.checked[elt] = true;
        });
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

      if(this.inputStatus !== null && this.outputStatus !== null
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
              wid: this.getWorkflowIdFromURL(),
              params: this.form,
              input_status: this.inputStatus,
              output_status: this.outputStatus,
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
      var _in = this.inputStatus;
      var _out = this.outputStatus;

      if(_in.length == 0 || _out === null) {
        // console.log('--- delete ---');
        this.$emit('deleteStep', this.element.id);          // using emit to pass event
        this.$modal.hide("stepModal" + this.element.id);
      }
    },

    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    beforeOpen() {
      var data = {
        wid: this.getWorkflowIdFromURL(),
        sid: this.element.id,
      }
      this.getCurrentParams(this.element.id);
      this.getAvailableStatus(data);  //for test only
    },

    beforeClose() {
      let _result = [];
      let _emit = [];
      for(i = 0; i <= this.inputStatus.length; i++) {
        if(this.inputStatus[i] == true) {
          _result.push(document.getElementById('status'+i).innerText);
        }
      }

      _emit['output'] = $( "#outstatus-selected option:selected" ).text();
      _emit['input'] = _result.toString();
      _emit['id'] = this.form.id;
      //console.log(_emit);
      this.$emit('updateState', _emit);
    }
  },
}
</script>

<style>
.vm--modal {
  padding: 10px 25px !important;
}

.row {
  margin-right:100px !important;
  margin-left: 30px !important;
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
