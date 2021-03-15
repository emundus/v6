<template>
  <div>
    <div class="col-md-12 separator-top top-responsive">
      <form v-if="this.$data.type == 'form'"></form>
      <message v-if="this.$data.type == 'message'"></message>
    </div>
  </div>
</template>

<script>
import form from './elements/form';
import message from './elements/message';

export default {
  name: "ModalElement",

  components: {
    form,
    message,
  },

  data: function() {
    return {
      type: '',
      element: {
        form: {
          name: "form",
        },
        message: {
          name: 'message',
        }
      }
    }
  },

  props: {

  },

  created() {

  },

  methods: {
    getElementByItemID: function(id) {
      //call to the table [ jos_emundus_workflow_items :: id ] --> detect the item_id in the table [ jos_emundus_workflow_item_type ]
      return null;
    },
  }

}
</script>

<style scoped>

</style>