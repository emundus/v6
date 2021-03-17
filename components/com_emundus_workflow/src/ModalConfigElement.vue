<template>
  <div id="ModalConfigElement">
    <modal :name="'elementModal' + ID" :width="500" :height="500" :adaptive="true" :draggable="true" @before-open="beforeOpen">
      <b-nav tabs>
        <b-nav-item active>Configurations</b-nav-item>
        <b-nav-item>Lorem Ipsum</b-nav-item>
      </b-nav>
      <br/>
      <br/>
      <form-modal v-if="this.type == 'Formulaire'" ref="forms" :element="element"/>
      <message-modal v-if="this.type == 'Message'" ref="emails" :element="element"/>
      <button class="update-button" @click="updateParams()">Sauvegarder</button>
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
        console.log(response);
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

<style>

  .button-area {
    align-items: center;
    justify-content: center;
  }

  .update-button {
    top: auto;
    background-color: #06ba00;
    border-color: #06ba00;
    color: #fff;
    display: inline-block;
    text-align: center;
    vertical-align: center;
    user-select: none;
    border-radius: .25rem;
    margin: 5px;
    padding: 4px 20px;
  }
</style>