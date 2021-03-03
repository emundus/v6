<template>
  <div id="app">
    <h1> {{ this.$data.workflowname.workflow_name }} </h1>
    <button class="vertical-menu" @click="seen=!seen">
      NEW BLOCK
    </button>

    <transition name="bounce">
    <div class="element-menu" v-if="seen">
      <h2 style="align-items: center"> {{ this.$data.menu_message }} </h2>
        <li v-for="(item,index) in items">
          <i :class="item.icon" style="margin-right: 0px"/>
          <span style="margin-left: 20px !important"> {{ item.item_name }} </span>
          <button @click="addNode(index)" class="add-button">ADD</button>
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
import axios from 'axios';

const qs = require('qs');

export default {
  name: 'app',
  components: {
    SimpleFlowchart,
  },

  props: {
    items: Array,
  },

  data() {
    return {
      workflowname: [],
      seen: false,
      menu_message: "Menu",
      scene: {
        centerX: 1024,
        centerY: 140,
        scale: 1,
        nodes: [
          {
            id: 2,
            x: -700,
            y: -69,
            type: 'Initialisation',
            label: 'init',
          },
          {
            id: 3,
            x: -357,
            y: 80,
            type: 'Item',
            label: 'item',
          },
        ],
        links: [
          {
            id: 2,
            from: 2, // node id the link start
            to: 3,  // node id the link end
          }
        ]
      },
    }

  },

  created() {
    this.getAllItems();
    this.getItemSimpleName();
    this.insertInitBloc();
    this.getworkflowname();
  },

  methods: {

    getworkflowname: function() {
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
        this.$data.workflowname = (response.data.data)[0];
      }).catch(error => {
        console.log(error);
      })
    },

    insertInitBloc: async function() {
      var init = {
        item_id: 1,
        item_name: "Initialisation",
        workflow_id: this.getWorkflowIdFromURL(),
        item_label: 'init',
      }

      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + init.workflow_id + "&itemid=1",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: init,
        })
      }).then(response =>{
        this.init = response.data.data;
      }).catch(error => {
        console.log(error);
      })
    },

    getWorkflowIdFromURL: function() {
      return window.location.href.split('id=')[1];
    },

    getAllItems: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=item&task=getallitems").
        then(response => {
          this.items = response.data.data;
          this.items.splice(0,1);
        }).catch(error => {
        console.log(error);
      })
    },

    getItemSimpleName: async function() {
      var json = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitems');
      var rawData = (JSON.parse(JSON.stringify(json))).data.data;

      var itemCategory = [];
      rawData.forEach(element => itemCategory.push(element.item_name));
      return itemCategory;
    },

    addNode: async function(index) {
      let nodeCategory = await this.getItemSimpleName();

      let maxID = Math.max(0, ...this.scene.nodes.map((link) => {return link.id}))

      this.scene.nodes.push({
        id: maxID + 1,
        x: -400,
        y: 50,
        type: nodeCategory[index],
        label: this.newNodeLabel ? this.newNodeLabel: `test${maxID + 1}`,
      })

      var items = {
        item_name: nodeCategory[index],
        item_id: index+1,
        workflow_id: this.getWorkflowIdFromURL(),
        item_label: this.newNodeLabel,
        //add element_label column in jos_emundus_workflow_item
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
      }).then(response =>{
        console.log(response.data.data);
      }).catch(error => {
        console.log(error);
      })
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
  width: 250px;
  position: fixed;
  display: block;
  top: 0;
  background: #fff;
  left: 75px;
  padding-top: 5%;
  height: 100%;
  border-left: 1px solid #F4F4F6;
}

.vertical-menu {
  transform: rotate(270deg);
  position: absolute !important;
  top: 560px !important;
  left: 400px !important;
  background: #28a745;
  color: #fff;
  border-radius: .25rem !important;
  padding: 15px 32px;
}

.vertical-menu:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.add-button {
  position: absolute;
  right: 25px;
  top: auto;
  background-color: #28a745;
  border-color: #28a745;
  color: #fff;
  display: inline-block;
  text-align: center;
  vertical-align: center;
  user-select: none;
  border-radius: .25rem;
  margin: 6px;
}

.add-button:hover {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
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

</style>