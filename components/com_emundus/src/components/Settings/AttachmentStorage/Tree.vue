<template>
  <div>
    <div class="em-flex-row em-mb-16" :class="'em-level-' + node.level">
      <span class="material-icons" v-if="node.type !== 0">folder</span>
      <span class="material-icons-outlined" v-else>folder</span>

      <select class="em-ml-8 em-mr-8 em-clear-dropdown" v-model="node.type" v-if="node.level === 1">
        <option value="0" selected>/{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SELECT_TYPE') }}</option>
        <option value="campaign">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_CAMPAIGN_TYPE') }}</option>
      </select>

      <select class="em-ml-8 em-mr-8 em-clear-dropdown" v-model="node.type" v-else>
        <option value="0" selected>/{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_SELECT_TYPE') }}</option>
        <option value="form">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_FORM_TYPE') }}</option>
      </select>

      <v-popover :popoverArrowClass="'custom-popover-arrow'">
        <span class="tooltip-target b3 material-icons">more_horiz</span>
        <template slot="popover">
          <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300" @click="$emit('addNode',node)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_ADD_MENU') }}</div>
          <div class="em-font-size-14 em-pointer em-p-8-12 em-hover-background-neutral-300 em-red-500-color" @click="$emit('deleteNode',node.id)">{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO_DELETE') }}</div>
        </template>
      </v-popover>
    </div>

    <div v-for="children in node.childrens" class="em-flex-row" :class="'em-level-' + children.level">
      <Tree :node="children" @addNode="$emit('addNode',children)" @deleteNode="$emit('deleteNode',children.id)" />
    </div>
  </div>
</template>

<script>
export default {
  name: "Tree",
  props:{
    node: Object
  },
}
</script>

<style scoped>
.em-level-1{
  margin-left: 16px;
}
.em-level-2{
  margin-left: 24px;
}
.em-level-3{
  margin-left: 32px;
}
.em-level-4{
  margin-left: 40px;
}
.em-level-5{
  margin-left: 48px;
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
