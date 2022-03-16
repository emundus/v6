<template>
  <div class="em-mt-32">

    <div class="em-h4 em-mb-16">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_CONF_WRITING') }}</div>

    <div class="em-flex-row em-mb-16">
      <span class="material-icons">folder</span>
      <span class="em-ml-8 em-mr-8">/{{site}}</span>

      <v-popover :popoverArrowClass="'custom-popover-arrow'">
        <span class="tooltip-target b3 material-icons">more_horiz</span>
        <template slot="popover">
          <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300" @click="addNode(null)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_ADD_MENU') }}</div>
<!--          <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_DELETE') }}</div>-->
        </template>
      </v-popover>
    </div>

    <div v-for="node in nodes">
      <Tree :node="node" @addNode="addNode" @deleteNode="deleteNode" :level_max="level_max" />
    </div>

    <hr/>

    <FilesName />
  </div>
</template>

<script>
import Tree from "../Tree";
import FilesName from "../FilesName";

export default {
  name: "IntegrationGED",
  components: {FilesName, Tree},
  props:{
    site: String,
    level_max: Number
  },
  data() {
    return {
      loading: false,

      nodes: [],
    }
  },
  created() {},

  methods: {
    addNode(node_parent){
      if(node_parent === null) {
        let id = 1;
        if(typeof this.nodes[this.nodes.length - 1] !== 'undefined') {
          id = this.nodes[this.nodes.length - 1].id++
        }

        let node = {
          id: id,
          level: 1,
          type: 0,
          parent: 0,
          childrens: []
        };

        this.nodes.push(node);
      } else {
        let level = node_parent.level + 1;
        let id = node_parent.id + '_1';
        if(typeof node_parent.childrens[node_parent.childrens.length - 1] !== 'undefined') {
          let increment = node_parent.childrens.length + 1;
          id = node_parent.id + '_' + increment;
        }

        let node = {
          id: id,
          level: level,
          type: 0,
          parent: node_parent.id,
          childrens: []
        };

        node_parent.childrens.push(node);
      }
    },

    deleteNode(id){
      let node_found = this.nodes.findIndex(function(node, index) {
        if(node.id === id)
          return true;
      });

      this.nodes.splice(node_found,1);
    }
  }
}
</script>

<style scoped>
</style>
