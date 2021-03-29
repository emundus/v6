<template>
  <div id="app">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div contenteditable="true" class="editable-workflow-name" id="editable-workflow-name-div" v-on:keyup.enter="updateWorkflowname()" v-b-tooltip.top.hover title="Cliquer sur le nom du workflow pour le changer">
      {{ this.$data.workflowname }}
    </div>

    <p class="tooltip"> Dernier mis a jour: {{ this.$data.lastSave }}</p>

    <button class="vertical-menu" @click="seen=!seen">
      NEW BLOCK
    </button>

    <button class='save-button' @click="alertSaveDisplay()">
      SAUVEGARDER
    </button>

    <button class='exit-button' @click="alertExitDisplay()">
      QUITTER
    </button>

    <transition name="bounce">
      <div class="element-menu" v-if="seen">
        <h2 style="align-items: center"> {{ this.$data.menu_message }} </h2>
        <li v-for="(item,index) in items" style="line-height: 2.8">
          <i :class="item.icon"/>
          <span style="margin: 0 35px"> {{ item.item_name }} </span>
          <button @click="addNode(index+1)" class="add-button">ADD</button>
        </li>
      </div>
    </transition>

    <simple-flowchart :scene.sync="scene"
                      @nodeClick="nodeClick"
                      @nodeDelete="nodeDelete"
                      @linkBreak="linkBreak"
                      @linkAdded="linkAdded"
                      @canvasClick="canvasClick"
                      :height="800"/>
  </div>
</template>

<script>
import SimpleFlowchart from './components/SimpleFlowchart.vue';
import addWorkflow from "./addWorkflow";
import axios from 'axios';
import Swal from "sweetalert2";
import ModalConfigElement from "./ModalConfigElement";
let now = new Date();
const qs = require('qs');

const _lst = [];

export default {
  name: 'app',
  components: {
    ModalConfigElement,
    SimpleFlowchart,
    addWorkflow,
  },

  props: {
    items: Array,
    nodeCategory: Array,
  },

  data: function() {
    return {
      lastSave: '',
      workflowname: '',
      seen: false,
      menu_message: "Menu",
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
    this.cronSave();
    this.alertWelcomeDisplay();

    this.loadWorkflow();
    this.insertInitBloc();

    this.cronUpdate();

    this.getWorkflowInfo();
    this.getMenu();
    this.getItemSimpleName();

    // setInterval(this.getWorkflowInfo(), 5000);
  },

  methods: {
    alertWelcomeDisplay: function() {
      Swal.fire({
        icon: 'success',
        title: 'Bienvenue',
        text: 'C\'est vos espace de travail!',
        footer: '<a href>Tutorials</a>',
        timer: 3000,
        showConfirmButton:false,
      })
    },

    getWorkflowInfo: function () {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=getworkflowbyid',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          wid: this.getWorkflowIdFromURL()
        })
      }).then(response => {
        var _data = (response.data.data)[0];
        this.$data.workflowname = _data.workflow_name;
        this.$data.lastSave = _data.updated_at;
      }).catch(error => {
        console.log(error);
      })
    },

    insertInitBloc: function () {
      var init = {
        item_id: 1,
        item_name: "Initialisation",
        workflow_id: this.getWorkflowIdFromURL(),
        axisX: -700,
        axisY: -50,
        style: '#9bde74',
      }

      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=getcounditembyid",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: init,
        })
      }).then(response => {
        //insert new init bloc
        if (response.data.status == 0) {
          //create new init bloc
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
        }
        //restore current init bloc
        else {
          // axios({
          //   method: 'post',
          //   url: 'index.php?option=com_emundus_workflow&controller=item&task=getinitid',
          //   headers: {
          //     "Content-Type": "application/x-www-form-urlencoded"
          //   },
          //   data: qs.stringify({
          //     data: init.workflow_id
          //   })
          // }).then(response => {
          //   this.scene.nodes.push({
          //     id: (response.data.data)[0].id,
          //     x: (response.data.data)[0].axisX,
          //     y: (response.data.data)[0].axisY,
          //     type: (response.data.data)[0].item_name,
          //     label: (response.data.data)[0].item_label,
          //   })
          // }).catch(error => {
          //   console.log(error)
          // })
        }
      })
    },

    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    getMenu: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=item&task=getallitems").
      then(response => {
        this.items = response.data.data;
        this.items.splice(0, 1);
      }).catch(error => {
        console.log(error);
      })
    },

    getItemSimpleName: async function () {
      var json = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitems');
      var rawData = (JSON.parse(JSON.stringify(json))).data.data;

      var itemCategory = [];
      rawData.forEach(element => itemCategory.push(element.item_name));
      this.$props.nodeCategory = itemCategory;
      return this.$props.nodeCategory;
    },

    addNode: function (index) {
      let nodeCategory = this.$props.nodeCategory;
      var items = {
        item_name: nodeCategory[index],
        item_id: index+1,
        workflow_id: this.getWorkflowIdFromURL(),
        params: '',
      }

      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + items.workflow_id + "&itemid=" + items.item_id,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: items
        })
      }).then(response => {
        axios({
          method: 'post',
          url: "index.php?option=com_emundus_workflow&controller=item&task=getitem",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            id: response.data.data,
          })
        }).then(answer => {
          // console.log(answer);

          var _style = (answer.data.data)[0];
          var _id = (response.data.data).toString();

          this.$data.scene.nodes.push({
            id: _id,
            x: -400 + Math.floor((Math.random() * 100) + 1),
            y: 50 + Math.floor((Math.random() * 100) + 1),
            type: nodeCategory[index],
            label: '',
            background: _style.CSS_style,
          });

          setTimeout(() => {
            this.$modal.show('elementModal' + _id);
          },500);
        })

      }).catch(error => {
        console.log(error);
      })
    },


    cronSave: function() {
      setInterval(this.saveWorkflow,60000);
    },

    saveWorkflow: function() {
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
          url: 'index.php?option=com_emundus_workflow&controller=item&task=saveitems',
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

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=updatelastsavedworkflow',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: this.getWorkflowIdFromURL(),
        })
      }).then(response => {
      })
    },

    quitWorkflow: function() {
      this.saveWorkflow();
      setTimeout(this.changeToDashboard(),4500);    //set timeout = 4.5 seconds
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_workflow&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href =  response.data.data;
      });
    },

    changeToDashboard() {
      this.redirectJRoute('index.php?option=com_emundus_workflow&view=workflow');
    },

    //load workflow = load items + load links
    loadWorkflow: async function() {
      let rawItems = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitemsbyworkflow', {params: {data: this.getWorkflowIdFromURL()}}); //get all items
      let rawLinks = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getalllinks', {params: {data: this.getWorkflowIdFromURL()}}); //get all links

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

    alertExitDisplay: function() {
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

    alertSaveDisplay: function() {
      Swal.fire({
        icon: 'success',
        title: 'Congrat',
        text: 'Le workflow est sauvegardé!',
        footer: '<a href>EMundus SAS</a>',
        timer: 2000,
        showConfirmButton:false,
      })
      this.saveWorkflow();
    },

    updateWorkflowname: function() {
      var info = {
        workflow_name: document.getElementById('editable-workflow-name-div').innerText,
        id: this.getWorkflowIdFromURL(),
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=updateworkflow',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: info,
        })
      }).then(response => {
      }).catch(error => {
        console.log(error);
      })
    },

    cronUpdate: function() {
      setInterval(this.getWorkflowInfo, 45000);
    },
  }
}

</script>

<style>

.app {
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
  width: 300px;
  position: fixed;
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

.editable-workflow-name {
  color: #8a1f11 !important;
  font-size: xx-large !important;
  font-weight: bold !important;
  width: max-content;
  border-bottom: 1px dotted black;
}

[contenteditable="true"].editable-workflow-name {
  white-space: nowrap;
  width:max-content;
  overflow: hidden;
}
[contenteditable="true"].editable-workflow-name br {
  display:none;

}
[contenteditable="true"].editable-workflow-name * {
  display:inline;
  white-space:nowrap;
}

.tooltip {
  opacity: 1 !important;
  font-size: small !important;
  color: #8a8a8a;
}
</style>