<template>
  <div id="steps">
    <div class="min-h-screen flex overflow-x-scroll py-12">
      <div v-for="step in steps" :key="step.id" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4">
        <h1>{{step.label}}</h1>

        <hr/>

        <div class="form-group">
          <multiselect
              v-model="form.istatus"
              label="label"
              track-by="id"
              :options="action_istatus"
              :multiple="true"
              :taggable="true"
              select-label=""
              selected-label=""
              deselect-label=""
              :close-on-select="false"
              :clear-on-select="false"
          />

        </div>


        <div v-for="(status,index) in step.input" :id="index">
          <span :class = "'label label-' + status.class"> {{ status.lbl }} </span>
        </div>

        <hr/>

        <div class="form-group">
          <multiselect
              v-model="form.ostatus"
              label="label"
              track-by="id"
              :options="action_ostatus"
              :multiple="true"
              :taggable="true"
              select-label=""
              selected-label=""
              deselect-label=""
              :close-on-select="false"
              :clear-on-select="false"
          />

        </div>

          <div v-for="(status,index) in step.output" :id="index">
            <span :class = "'label label-' + status.class"> {{ status.lbl }} </span>
          </div>

        <hr/>

        <div>
          {{ step.start_date }}
        </div>

        <hr/>

        <div>
          {{ step.end_date }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');
import draggable from "vuedraggable";
import Multiselect from 'vue-multiselect';

export default {
  name: "stepsBuilder",

  components: {
    draggable,
    Multiselect
  },

  data: function() {
    return {
      steps: [],

      form : {
        istatus: [],
        ostatus: [],
      },


      action_istatus: [],
      action_ostatus: [],
    };
  },

  methods: {
    getAllSteps: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=steps&task=getallsteps',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: this.parseUrl('wid')
        })
      }).then(response => {
        this.steps = response.data.data;

      }).catch(error => {
        console.log(error);
      })
    },

    parseUrl: function(param) {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      return urlParams.get(param);
    }
  },

  created() {
    this.getAllSteps();
  }
}
</script>

<style>
.column-width {
  min-width: 240px;
  width: 450px;
}
/*.column-width:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.px-3 {
  padding-left: .75rem;
  padding-right: .75rem;
}
/*.px-3:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.py-3 {
  padding-top: .75rem;
  padding-bottom: .75rem;
}
/*.py-3:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.mr-4 {
  margin-right: 1rem;
}
/*.mr-4:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.rounded-lg {
  border-radius: .5rem;
}
/*.rounded-lg:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.bg-gray-100 {
  background-color: #fff;;
  /*background-image: radial-gradient(circle, black 1px, rgba(0, 0, 0, 0) 1px);*/
  background-size: 2em 2em;

}
/*.bg-gray-100:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.overflow-x-scroll {

}

.min-h-screen {
  min-height: 25vh;
  display: grid !important;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 60px;
}

.flex {
  display: flex;
}

*, ::after, ::before {
  box-sizing: border-box;
  border-width: 0;
  border-style: solid;
  border-color: #e2e8f0;
}

.editable-workflow-label {
  color: #118a3b !important;
  font-size: xx-large !important;
  /*font-weight: bold !important;*/
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
}

[contenteditable="true"].editable-workflow-label {
  white-space: nowrap;
  overflow: hidden;
}
[contenteditable="true"].editable-workflow-label br {
  display:none;
}
[contenteditable="true"].editable-workflow-label * {
  display:inline;
  white-space:nowrap;
}

/* editable step label */
.editable-step-label {
  color: #118a3b !important;
  font-size: xx-large !important;
  /*font-weight: bold !important;*/
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
}

[contenteditable="true"].editable-step-label {
  white-space: nowrap;
  overflow: hidden;
}
[contenteditable="true"].editable-step-label br {
  display:none;
}
[contenteditable="true"].editable-step-label * {
  display:inline;
  white-space:nowrap;
}

.flex {
  display: flex;
  flex-direction: row;
  justify-content: center;
  flex-wrap: wrap;
  gap: 60px;
  min-height: 60vh;
  margin-top: 5vh;
}
@keyframes shake {
  from {
    transform: rotate(-4deg);
  }
  to {
    transform: rotate(4deg);
  }
}

.message-block {
  box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
  padding-bottom: 1.25rem;
  padding-top: .75rem;
  padding-left: .75rem;
  padding-right: .75rem;
  margin-top: .75rem;
  border-width: 1px;
  border-radius: .25rem;
  --border-opacity: 1;
  border-color: rgba(255,255,255,var(--border-opacity));
  overflow-y: scroll;
  border-color: red;
}

.remove-message {
  font-size: xx-small;
  right: 85vh !important;
  top: 45vh !important;
  position: fixed !important;
}

</style>

