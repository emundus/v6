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
        </template>
      </v-popover>
    </div>

    <div v-for="node in nodes">
      <Tree :node="node" @addNode="addNode" @deleteNode="deleteNode" @saveConfig="saveConfig" :level_max="level_max" :emundus_tags="emundus_tags" />
    </div>

    <hr/>

    <FilesName @updateName="updateName" />
  </div>
</template>

<script>
import Tree from "../Tree";
import FilesName from "../FilesName";

import storageService from "com_emundus/src/services/storage";
import mixin from "../../../../mixins/mixin";

export default {
  name: "IntegrationGED",
  components: {FilesName, Tree},
  mixins: [mixin],
  props:{
    site: String,
    level_max: Number
  },
  data() {
    return {
      loading: false,

      emundus_tags: [],
      nodes: [],
      name: '',
    }
  },
  created() {
    storageService.getConfig('ged').then((response) => {
      if(response.data.data !== null) {
        this.nodes = response.data.data.tree;
      }
    });
    /*storageService.getEmundusTags().then((response) => {
      this.emundus_tags = response.data.data;
    })*/
  },

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

      this.saveConfig();
    },

    deleteNode(id){
      let node_found = this.nodes.findIndex(function(node, index) {
        if(node.id === id)
          return true;
      });

      this.nodes.splice(node_found,1);
    },

    updateName(name){
      this.name = name;

      if(this.nodes.length > 0) {
        this.saveConfig();
      }
    },

    saveConfig(){
      this.$emit('updateSaving',true)
      let config = {
        tree: this.nodes,
        name: this.name
      }
      storageService.saveConfig(config,'ged').then(() => {
        this.$emit('updateLastSaving',this.formattedDate('','LT'));
        this.$emit('updateSaving',false);
      })
    }
  }
}
</script>

<style scoped>
</style>
