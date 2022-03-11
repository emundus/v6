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
      <div class="em-flex-row em-mb-16" :class="'em-level-' + node.level">
        <span class="material-icons" v-if="node.type !== 0">folder</span>
        <span class="material-icons-outlined" v-else>folder</span>
        <span class="em-ml-8 em-mr-8" v-if="node.type !== 0">/{{node.type}}</span>
        <select class="em-ml-8 em-mr-8 em-clear-dropdown" v-model="node.type" v-else-if="node.level === 1">
          <option value="0" selected>/{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SELECT_TYPE') }}</option>
          <option value="campaign">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_CAMPAIGN_TYPE') }}</option>
        </select>

        <v-popover :popoverArrowClass="'custom-popover-arrow'">
          <span class="tooltip-target b3 material-icons">more_horiz</span>
          <template slot="popover">
            <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300" @click="addNode(node)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_ADD_MENU') }}</div>
            <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300 em-red-500-color" @click="deleteNode(node.id)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_DELETE') }}</div>
          </template>
        </v-popover>
      </div>

      <div v-for="children in node.childrens" class="em-flex-row" :class="'em-level-' + children.level">
        <span class="material-icons" v-if="children.type !== 0">folder</span>
        <span class="material-icons-outlined" v-else>folder</span>
        <span class="em-ml-8 em-mr-8" v-if="children.type !== 0">/{{children.type}}</span>
        <select class="em-ml-8 em-mr-8 em-clear-dropdown" v-else-if="children.level === 2">
          <option value="0" selected>/{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SELECT_TYPE') }}</option>
          <option value="form">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_FORM_TYPE') }}</option>
        </select>

        <v-popover :popoverArrowClass="'custom-popover-arrow'">
          <span class="tooltip-target b3 material-icons">more_horiz</span>
          <template slot="popover">
            <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300" @click="addNode(children)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_ADD_MENU') }}</div>
            <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300 em-red-500-color" @click="deleteNode(children.id)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_DELETE') }}</div>
          </template>
        </v-popover>
      </div>
    </div>

    <hr/>
  </div>
</template>

<script>

export default {
  name: "IntegrationGED",
  props:{
    site: String
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
        let node = {
          id: 1,
          level: 1,
          type: 0,
          childrens: []
        };

        this.nodes.push(node);
      } else {
        let node = {
          id: 2,
          level: 2,
          type: 0,
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

      this.nodes.slice(node_found,1);
    }
  }
}
</script>

<style scoped>
.em-level-1{
  margin-left: 16px;
}
.em-level-2{
  margin-left: 32px;
}
.em-clear-dropdown{
  border: unset;
  height: auto;
}
.em-clear-dropdown:focus{
  outline: unset;
  background: #E3E5E8;
}
</style>
