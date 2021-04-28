<template>
  <div id="workflowspace">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div contenteditable="true" class="editable-label" id="editable-label" v-on:keyup.enter="updateStepLabel()">
      {{ this.step.title }}
    </div>

    <div class="button-group">
      <b-button variant="warning" @click="seen=!seen" style="margin: 10px">Ajouter bloc &nbsp<b-icon icon="clipboard-plus"></b-icon></b-button>
      <b-button variant="success" @click="alertSaveDisplay()" style="margin: 10px">Sauvegarder &nbsp<b-icon icon="bookmark-check"></b-icon></b-button>
      <b-button variant="danger" @click="alertExitDisplay()" style="margin: 10px">Quitter &nbsp<b-icon icon="x-circle"></b-icon></b-button>
    </div>

    <!--    <button @click="autoMatchLink()">Creer des liens</button>-->

    <transition name="bounce">
      <div class="element-menu" v-if="seen">
        <workflow-space-tools-menu :stepID="step" @createItem="createNode"/>
      </div>
    </transition>

    <simple-flowchart :scene.sync="scene"
                      @nodeClick="nodeClick"
                      @nodeDelete="nodeDelete"
                      @linkBreak="linkBreak"
                      @linkAdded="linkAdded"
                      @canvasClick="canvasClick"
                      :height="800"
                      :step="step"
    />
  </div>
</template>

<script>
import SimpleFlowchart from './elements/SimpleFlowchart.vue';
import { commonMixin } from "../../../mixins/common-mixin";

import axios from 'axios';
import Swal from "sweetalert2";
import ModalConfigElement from "../../Modal/WorkflowModal/ModalConfigElement";
import WorkflowSpaceToolsMenu from "./elements/WorkflowSpaceToolsMenu";

import $ from 'jquery';

const qs = require('qs');

export default {
  name: 'WorkflowSpace',
  mixins: [commonMixin],      //// using mixin
  components: {ModalConfigElement, SimpleFlowchart, WorkflowSpaceToolsMenu},

  props: {
    items: Array,
    step: Object,
  },

  data: function () {
    return {
      seen: false,
      scene: {
        centerX: 1024,
        centerY: 140,
        scale: 1,
        nodes: [],
        links: [],
      },
    }
  },

  created() {
    this.loadStep();              // load step --> retrieve items and links
    //this.insertInitBloc();        // check if the init item exists or not --> if not, create it, if yes, do nothing     // --> temporary unuse
    this.cronUpdate();
  },

  methods: {
    createNode: function (newItem) {
      this.scene.nodes.push(newItem);
      setTimeout(() => {
        this.$modal.show('elementModal' + newItem.id)
      }, 500);
    },

    alertWelcomeDisplay: function () {
      Swal.fire({
        icon: 'success',
        title: 'Bienvenue',
        text: 'C\'est vos espace de travail!',
        footer: '<a href>Tutorials</a>',
        timer: 3000,
        showConfirmButton: false,
      })
    },

    insertInitBloc: function () {
      var init = {
        item_id: 1,
        step_id: this.step.id,                      // get step_id of item
        item_name: "Initialisation",
        workflow_id: this.$data.id,
        axisX: -700,
        axisY: -50,
        style: '#9bde74',
        params: '',     //empty string
      }

      axios({
        // method: 'post',
        method: 'get',
        url: "index.php?option=com_emundus_workflow&controller=item&task=getcounditembyid",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          data: init,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        if (response.data.status == 0) {
          axios({
            method: 'post',
            url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + init.workflow_id + "&itemid=1",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              data: init,
            })
          }).then(response => {
            this.scene.nodes.push({
              id: response.data.data,
              x: -700,
              y: -69,
              type: 'Initialisation',
              label: '',
              background: '#9bde74',
            })
          }).catch(error => {
            console.log(error);
          })
        } else {
        }
      })
    },

    saveWorkflow: function () {
      this.$data.scene.nodes.forEach(element => {
        var current_nodes = {
          id: element.id,
          type: element.type,
          axisX: element.x,
          axisY: element.y,
          item_label: document.getElementById('label_' + element.id).innerText,
          style: element.background,
        };

        axios({
          method: 'post',
          url: 'index.php?option=com_emundus_workflow&controller=item&task=saveworkflow',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            data: current_nodes
          })
        }).then(response => {
        }).catch(error => {
          console.log(error);
        })
      });
    },

    quitWorkflow: function () {
      this.saveWorkflow();
      setTimeout(this.changeToDashboard(), 4500);    //set timeout = 4.5 seconds
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_workflow&controller=settings&task=redirectjroute",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = response.data.data;
      });
    },

    changeToDashboard() {
      this.redirectJRoute('index.php?option=com_emundus_workflow&view=workflow');
    },

    //load step --> get all items and links of this step
    loadStep: async function () {
      let rawItems = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitemsbystep', {params: {data: this.step.id}}); //get all items
      let rawLinks = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getalllinks', {params: {data: this.step.id}}); //get all links

      var items = rawItems.data.data;    //items : Array
      var links = rawLinks.data.data;    //links: Array

      items.forEach(element => {
        this.$data.scene.nodes.push({
          id: element.id,
          label: element.item_label,
          type: element.item_name,
          x: Number(element.axisX),
          y: Number(element.axisY),
          background: element.style,
        })
      });

      links.forEach(element => {
        this.$data.scene.links.push({
          id: element.id,
          from: element.from,
          to: element.to,
        })
      });
    },
    //next step --> clone workflow = clone all items

    alertExitDisplay: function () {
      Swal.fire({
        title: 'Quitter l\'espace de travail',
        text: "Le workflow sera sauvegardé automatiquement",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '<i class="far fa-thumbs-up"></i> Oui, c\'est sûr',
        cancelButtonText: 'Non, garder ce workflow',
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire('Merci', 'Le workflow est sauvegardé', 'success');
          this.quitWorkflow();
        } else if (result.isDismissed) {
          Swal.fire('Merci', 'Rester ici', 'success');
        }
      })
    },

    alertSaveDisplay: function () {
      Swal.fire({
        icon: 'success',
        title: 'Congrat',
        text: 'Le workflow est sauvegardé!',
        footer: '<a href>EMundus SAS</a>',
        timer: 2000,
        showConfirmButton: false,
      })
      this.saveWorkflow();
    },

    setStepLabel: function () {

    },

    cronUpdate: function () {
      setInterval(this.getWorkflowInfo, 45000);
    },

    //match all links by workflow
    autoMatchLink: function () {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=matchalllinksbyworkflow',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({data: this.$data.id,})
      }).then(response => {
        // console.log(response);
      }).catch(error => {
        console.log(error);
      })
    },

    updateStepLabel: function () {
      let updatedData = {
        step_label: document.getElementById('editable-label').innerText,
        id: this.step.id,
      }
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=updateparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ data: updatedData, })
      }).then(response => {

      }).catch(error => {
        console.log(error);
      })
    }
  }
}

</script>

<style>

.workflowspace {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin: 0;
  overflow: hidden;
  height: 1020px;
}
.app > tool-wrapper {
  position: relative;
}

#g-container-main .element-menu {
  padding-bottom: 2px;
  width: 210px;
  position: absolute;
  display: block;
  top: 0;
  background: #fff;
  left: 75px;
  padding-top: 5%;
  height: 100%;
  border-left: 0px solid #F4F4F6;
}

.vertical-menu {
  transform: rotate(270deg);
  position: relative !important;
  top: 215px !important;
  left: -100px !important;
  background: #28a745;
  color: #fff;
  border-radius: .25rem !important;
  padding: 15px 32px;
}

.vertical-menu:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.save-button {
  transform: rotate(270deg);
  position: relative !important;
  top: 415px !important;
  left: -265px !important;
  background: #8a8a8a;
  color: #fff;
  border-radius: .25rem !important;
  padding: 15px 32px;
}

.save-button:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.exit-button {
  transform: rotate(270deg);
  position: relative !important;
  top: 610px !important;
  left: -415px !important;
  background: #de2f2f;
  color: #fff;
  border-radius: .25rem !important;
  padding: 15px 32px;
}

.exit-button:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.add-button {
  position: absolute !important;
  right: 25px !important;
  top: initial !important;
  background-color: #28a745 !important;
  border-color: #28a745 !important;
  color: #fff !important;
  display: inline-block !important;
  text-align: center !important;
  vertical-align: center !important;
  user-select: none !important;
  border-radius: .25rem !important;
  line-height: 35px;
  margin: 5px;
}

.add-button:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19) !important;
}

#g-container-main .g-container {
  width: 100rem !important;
}

.bounce-enter-active {
  animation: bounce-in .5s;
}
.bounce-leave-active {
  animation: bounce-in .5s reverse;
}
@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.5);
  }
  100% {
    transform: scale(1);
  }
}

.swal2-styled.swal2-confirm {
  border-radius: 5px !important;
}

.swal2-styled.swal2-confirm:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.swal2-styled.swal2-cancel {
  border-radius: 5px !important;
}

.swal2-styled.swal2-cancel:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.editable-label {
  color: #118a3b !important;
  font-size: xx-large !important;
  /*font-weight: bold !important;*/
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
}

[contenteditable="true"].editable-label {
  white-space: nowrap;
  width:max-content;
  overflow: hidden;
  position: absolute;
  top: 7vh;
  /*font-weight: bold !important;*/
}

[contenteditable="true"].editable-label br {
  display:none;
}

[contenteditable="true"].editable-label * {
  display:inline;
  white-space:nowrap;
}

.tooltip {
  opacity: 1 !important;
  font-size: small !important;
  color: #8a8a8a;
  position: absolute;
  top: 12vh;
}

.button-group {
  position: sticky;
  right: 85vh;
  bottom: 85vh;
}

.step-label {
  position: absolute;
  top: 100px;
}
</style>
