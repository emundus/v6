<template>
  <div class="menu-alignment">
    <ul v-for = "item in items" @click="createitem(item)">{{ item.item_name }}<em :class = '["fas fa-plus-circle"]'></em></ul>
  </div>
</template>

<script>
import axios from "axios";
const qs = require('qs');
export default {
  name: "addItem",
  props: {
    items: Array,
  },

  created() {
    this.getAllItems();
  },

  methods: {
    getAllItems: function() {
      //get all items
      axios.get("index.php?option=com_emundus_workflow&controller=item&task=getallitems")
          .then(response=>{
            this.items = response.data.data;
          })
    },

    createitem: function(items) {
      //create new item from id
      var items = {
        item_name: items.item_name,
        item_id: items.id,
        workflow_id: this.getWorkflowIdFromURL(),
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
        this.item = response.data.data;
      }).catch(error => {
        console.log(error);
      })
    },

    getWorkflowIdFromURL: function() {
      return window.location.href.split('id=')[1];
    }
  },
}
</script>

<style scoped>
  .menu-alignment {
    float: left;
    width: auto;
    height: auto;
    text-align: left;
    border: 4px solid yellowgreen;
    padding: initial;
  }
  #g-container-main .g-container {
    width: 90%;
  }
</style>