<template>
  <div id="workflowspace-tools-menu">
    <h2 style="align-items: center"> {{ this.menuTitle }} </h2>
    <li v-for="(item,index) in items" style="line-height: 2.8">
      <i :class="item.icon"/>
      <span style="margin: 0 35px"> {{ item.item_name }} </span>
      <button class="add-button" @click="addNode(index+1)">(+)</button>
    </li>
  </div>
</template>

<script>
import axios from "axios";
const qs = require('qs');
import { commonMixin } from "../common-mixin";

export default {
  name: "WorkflowSpaceToolsMenu",
  mixins: [commonMixin],

  props: {
    stepID: Object,
  },

  data: function() {
    return {
      items: [],
      menuTitle: 'Menu',
      nodeCategory: [],
    }
  },

  created() {
    this.getMenu();     /// get tools menu
  },

  methods: {
    getMenu: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=item&task=getitemmenu").
      then(response => {
        let itemCategory = [];
        (response.data.data).forEach(element => itemCategory.push(element.item_name));

        this.nodeCategory = itemCategory;     // return the nodeCategory

        this.items = response.data.data;
        this.items.splice(0, 1);

      }).catch(error => {
        console.log(error);
      })
    },

    addNode: function(node) {
      let category = this.nodeCategory;

      let item = {
        item_name: category[node],
        item_id: node + 1,
        workflow_id: this.$data.id,
        params: '',
        axisX: -400 + Math.floor((Math.random() * 100) + 1),
        axisY: 50 + Math.floor((Math.random() * 100) + 1),
        step_id: this.stepID.id,
      }

      axios({
        method: 'post',
        url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + item.workflow_id + "&itemid=" + item.item_id,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: item
        })
      }).then(response => {
        let newItem = {
          id: response.data.data.id,
          x: item.axisX,
          y: item.axisY,
          type: category[node],
          label: '',
          background: response.data.data.style.CSS_style,
        }
        this.$emit('createItem', newItem);    // pass event to workflow space
      })
    }
  }
}
</script>

<style scoped>

</style>