<template>
  <div :id="ModalConfigElement">
    <modal :name="'elementModal' + ID" :width="500" :height="500" :adaptive="true" :draggable="true" @before-open="beforeOpen">
      <form-modal v-if="this.type == 'Formulaire'"/>
      <message-modal v-if="this.type == 'Message'"/>
    </modal>

  </div>
</template>

<script>
import formModal from './elements/formModal';
import messageModal from './elements/messageModal';

import axios from 'axios';
const qs = require('qs');

export default {
  name: "ModalConfigElement",

  components: {
    formModal,
    messageModal,
  },

  data: function() {
    return {
      type: '',
    }
  },

  props: {
    ID: Number,
    item: Object,
  },

  methods: {
    getElementByItem: function () {
      //call to the table [ jos_emundus_workflow_items :: id ] --> detect the item_id in the table [ jos_emundus_workflow_item_type ]
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=getitem',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          id: this.ID,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        var rawData = (response.data.data)[0];
        this.$data.type = rawData.item_name;
      }).catch(error => {
        console.log(error);
      })
    },

    beforeOpen(event) {
      this.getElementByItem();
    }
  }
}
</script>

<style scoped>

</style>