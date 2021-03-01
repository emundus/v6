<template>
  <div id="app">
    <h1> simple flowchart</h1>
    <div class="tool-wrapper">
<!--      {{ this.$data.nodeCategory }}-->
      <select v-model="newNodeType">
        <option v-for="(item, index) in this.$props.items" :key="index" :value="index">{{ item.item_name }}</option>
      </select>

      <input type="text" v-model="newNodeLabel" placeholder="Input node label">
      <button @click="addNode">ADD</button>
    </div>

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
    SimpleFlowchart
  },

  props: {
    items: Array,
  },

  data() {
    return {
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
            type: 'Script',
            label: 'test2',
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
      newNodeType: 0,
      newNodeLabel: '',
      nodeCategory: this.getItemSimpleName(),
    }
  },

  created() {
    this.getAllItems();
    this.getItemSimpleName();
  },

  methods: {

    getWorkflowIdFromURL: function() {
      return window.location.href.split('id=')[1];
    },

    getAllItems: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=item&task=getallitems").
        then(response => {
          this.items = response.data.data;
        })
    },

    getItemSimpleName: async function() {
      var json = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitems');
      var rawData = (JSON.parse(JSON.stringify(json))).data.data;

      var itemCategory = [];
      rawData.forEach(element => itemCategory.push(element.item_name));
      return itemCategory;
    },

    addNode: async function() {
      let nodeCategory = await this.getItemSimpleName();

      let maxID = Math.max(0, ...this.scene.nodes.map((link) => {return link.id}))

      this.scene.nodes.push({
        id: maxID + 1,
        x: -400,
        y: 50,
        type: nodeCategory[this.newNodeType],
        label: this.newNodeLabel ? this.newNodeLabel: `test${maxID + 1}`,
      })



      console.log(nodeCategory[this.newNodeType]);

      // //bugs here
      // var items = {
      //   item_name: nodeCategory[this.newNodeType])
      //   item_id: items.id,
      //   workflow_id: this.getWorkflowIdFromURL(),
      // }
      //
      // axios({
      //   method: 'post',
      //   url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + items.workflow_id + "&itemid=" + items.item_id,
      //   headers: {
      //     "Content-Type": "application/x-www-form-urlencoded"
      //   },
      //   data: qs.stringify({
      //     data: items
      //   })
      // }).then(response =>{
      //   this.item = response.data.data;
      // }).catch(error => {
      //   console.log(error);
      // })
    },

  }
}
</script>

<style scoped>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin: 0;
  overflow: hidden;
  height: 800px;
}
#app .tool-wrapper {
  position: relative;
}
</style>