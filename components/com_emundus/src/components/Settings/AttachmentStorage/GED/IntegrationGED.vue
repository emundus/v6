<template>
  <div class="em-mt-32">

    <div class="em-h4 em-mb-16">{{
        translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_CONF_WRITING')
      }}
    </div>

    <div class="em-flex-row em-mb-16">
      <span class="material-icons">folder</span>
      <span class="em-ml-8 em-mr-8">/{{ site }}</span>

      <v-popover :popoverArrowClass="'custom-popover-arrow'">
        <span class="tooltip-target b3 material-icons">more_horiz</span>
        <template slot="popover">
          <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300" @click="addNode(null)">
            {{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_ADD_MENU') }}
          </div>
        </template>
      </v-popover>
    </div>

    <div v-for="node in nodes">
      <Tree :node="node" @addNode="addNode" @deleteNode="deleteNode" @saveConfig="saveConfig" :level_max="level_max"
            :emundus_tags="emundus_tags"/>
    </div>

    <hr/>

    <FilesName v-if="buildedComponent" @updateName="updateName" :name="name"/>

    <hr/>

    <Aspects :aspects="aspects" @update-aspects="updateAspects"></Aspects>
  </div>
</template>

<script>
import Tree from "../Tree";
import FilesName from "../FilesName";
import Aspects from "./Aspects";

import syncService from "com_emundus/src/services/sync";
import mixin from "../../../../mixins/mixin";

export default {
  name: "IntegrationGED",
  components: {FilesName, Tree, Aspects},
  mixins: [mixin],
  props: {
    site: String,
    level_max: {
      type: Number,
      default: 3
    }
  },
  data() {
    return {
      loading: false,
      buildedComponent: false,

      emundus_tags: [],
      nodes: [],
      aspects: [],
      name: '',
    }
  },
  created() {
    syncService.getConfig('ged').then((response) => {
      if (response.data.data !== null) {
        this.nodes = response.data.data.tree;
        this.name = response.data.data.name;
        this.aspects = response.data.data.aspects;
      }
      this.buildedComponent = true;
    });
  },

  methods: {
    addNode(node_parent) {
      if (node_parent === null) {
        let id = 1;
        if (typeof this.nodes[this.nodes.length - 1] !== 'undefined') {
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
        if (typeof node_parent.childrens[node_parent.childrens.length - 1] !== 'undefined') {
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

    deleteNode(node) {
      this.deleteNodeById(this.nodes, node.id);
      this.saveConfig();
    },
    deleteNodeById(nodes, id) {
      for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === id) {
          nodes.splice(i, 1);
          return;
        }

        if (nodes[i].childrens.length > 0) {
          this.deleteNodeById(nodes[i].childrens, id);
        }
      }
    },
    updateName(name) {
      this.name = name;

      if (this.nodes.length > 0) {
        this.saveConfig();
      }
    },
    updateAspects(aspects) {
      this.aspects = aspects;
      this.saveConfig();
    },

    saveConfig() {
      this.$emit('updateSaving', true)
      let config = {
        tree: this.nodes,
        name: this.name,
        aspects: this.aspects
      }
      syncService.saveConfig(config, 'ged').then(() => {
        this.$emit('updateLastSaving', this.formattedDate('', 'LT'));
        this.$emit('updateSaving', false);
      });
    }
  }
}
</script>

<style scoped>
</style>
