<template>
  <div id="ModalConfigElement">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <modal :name="'elementModal' + ID" :width="580" :height="600" :adaptive="true" :draggable="true" :scrollable="true" :clickToClose="true" @before-open="beforeOpen">
      <b-badge variant="warning"><h3>{{ this.$data.type }} Configuration</h3></b-badge>
      <br/>
      <br/>
      <b-nav tabs>
        <b-nav-item active>Configurations</b-nav-item>
        <b-nav-item>Lorem Ipsum</b-nav-item>
      </b-nav>
      <br/>
      <br/>
      <espace-modal v-if="this.type == 'Espace'" ref="forms" :element="element"/>
      <message-modal v-if="this.type == 'Message'" ref="emails" :element="element"/>
      <b-button variant="success" @click="updateParams()">Sauvegarder</b-button>
      <b-button variant="danger" @click="exitModal()">Quitter</b-button>
    </modal>
  </div>
</template>

<script>
import espaceModal from './elements/espaceModal.vue';
import messageModal from './elements/messageModal.vue';

import axios from 'axios';
import Swal from "sweetalert2";
const qs = require('qs');

export default {
  name: "ModalConfigElement",

  components: {
    espaceModal,
    messageModal,
  },

  data: function() {
    return {
      type: '',
    }
  },

  props: {
    ID: Number,
    element: Object,
  },

  methods: {
    getElementByItem: function () {
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
        this.$data.type = (response.data.data)[0].item_name;
      }).catch(error => {
        console.log(error);
      })
    },

    updateParams: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=updateparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          params: this.element,
        })
      }).then(response => {
        Swal.fire({
          icon: 'success',
          title: 'Congrat',
          text: 'Les parametres sont sauvegardés',
          footer: '<a href>EMundus SAS</a>',
          confirmButtonColor: '#28a745',
        }).then((result) => {
          if (result.isConfirmed) {
            // Swal.fire('Merci', 'Le workflow est sauvegardé', 'success');
            this.$modal.hide('elementModal' + this.ID);
          }})
      }).catch(error => {
        console.log(error);
      })
    },

    exitModal: function() {
      this.$modal.hide('elementModal' + this.ID);
    },

    beforeOpen(event) {
      this.getElementByItem();
    }
  }
}
</script>

<style>

  .vm--modal {
    padding: 10px 25px !important;
  }

  .row {
    margin-right:100px !important;
  }

  .select {
    max-width: 300px !important;
  }
</style>