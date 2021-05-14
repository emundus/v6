<template>
  <div id="ModalConfigElement">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <modal :name="'elementModal' + ID" :width="580" :height="600" :adaptive="true" :draggable="true" :scrollable="true" :clickToClose="true" @before-open="beforeOpen" @before-close="beforeClose">
      <b-badge variant="warning"><h3 style="color:white !important">{{ this.$data.name }} {{ configTitle }}</h3></b-badge>
      <br/>
      <br/>
      <b-nav tabs>
        <b-nav-item active>{{configBreadcrumb }}</b-nav-item>
      </b-nav>
      <br/>
      <br/>
      <espace-modal v-if="this.type == 2" ref="forms" :element="element"/>
      <message-modal v-if="this.type == 4" ref="emails" :element="element"/>
      <b-button variant="success" @click="updateParams()">{{ saveButtonLabel }}</b-button>
      <b-button variant="danger" @click="exitModal()">{{ exitButtonLabel }}</b-button>
    </modal>
  </div>
</template>

<script>
import espaceModal from './element/espaceModal.vue';
import messageModal from './element/messageModal.vue';

import axios from 'axios';
import Swal from "sweetalert2";
const qs = require('qs');

import { commonMixin } from "../../../mixins/common-mixin";

export default {
  name: "ModalConfigElement",
  mixins: [commonMixin],

  components: {
    espaceModal,
    messageModal,
  },

  data: function() {
    return {
      type: '',
      name: '',
      configTitle: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_CONFIGURATION_TITLE"),
      configBreadcrumb: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_CONFIGURATION_BREADCRUMB"),
      saveButtonLabel: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_BUTTON_SAVE_PARAMS"),
      exitButtonLabel: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_BUTTON_EXIT_PARAMS"),
      swalCongratTitle: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_SWEET_ALERT_CONGRATULATION_TITLE"),
      swalSuccessMessage: Joomla.JText._("COM_EMUNDUS_WORKFLOW_ELEMENT_SWEET_ALERT_SUCCESS_MESSAGE"),
    }
  },

  props: {
    ID: Number,
    element: Object,
  },

  methods: {
    beforeClose: function() {
      /// emit all params
      let _emit = [];
      _emit['id'] = this.$refs.forms.form.id;
      _emit['label'] = this.$refs.forms.form.itemLabel;
      this.$emit('updateLabel', _emit);
    },

    getElementByItem: function () {
      axios({
        method: 'get',
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
        this.$data.type = (response.data.data)[0].item_id;
        this.$data.name = (response.data.data)[0].item_name;
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
          data : {
            params: this.element,
            workflow_id: this.$data.id,     // get workflow id from url
          }
        })
      }).then(response => {
        Swal.fire({
          icon: 'success',
          title: this.swalCongratTitle,
          text: this.swalSuccessMessage,
          footer: '<a href>EMundus SAS</a>',
          confirmButtonColor: '#28a745',
        }).then((result) => {
          if (result.isConfirmed) {
            this.$modal.hide('elementModal' + this.ID);

            //// insert code here --> auto create link
            var _data = {
              wid: this.getWorkflowIdFromURL(),
              id: this.ID,
            }
            this.autoCreateLink(_data);
          }})
      }).catch(error => {
        console.log(error);
      })
    },

    exitModal: function() {
      this.$modal.hide('elementModal' + this.ID);
    },

    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    autoCreateLink: async function(data) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=item&task=matchalllinksbyitem',
        params: { data },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        //based on the returned status [0] or [1] --> we will create a link between blocs
        if((response.data.permission) == 'available') {
          var _data = {
            _from : response.data.data,
            _to : this.ID,
          }

          this.$emit('linkingStart', _data._from);
          this.$emit('linkingStop', _data._to);
        }
        else {
          // check if there are the links from this node --> if yes --> remove it [this.$emit.nodeDelete]// if no --> anything do
          axios({
            method: 'post',
            url: 'index.php?option=com_emundus_workflow&controller=item&task=checkexistlink',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({data: this.ID})
          }).then(answer => {
            console.log(answer);
            if (answer.data.data == true) {
              ///// link exists --> get link_id --> delete it
              var _data = {
                _from: undefined,
                _to: this.ID,
              }
              axios({
                method: 'post',
                url: 'index.php?option=com_emundus_workflow&controller=item&task=getlinkbytoitem',
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({ data: _data })
              }).then(reply => {

                //// work well if input status !== output status --> why???

                var _linkArray = reply.data.data;

                console.log(_linkArray[0].id);
                this.$emit('linkDelete', _linkArray[0].id);     /// <- bug here
              })
            }
          })
        }
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

.vm--modal {
  padding: 10px 30px !important;
}

/*.row {*/
/*  margin-right:100px !important;*/
/*}*/

.select {
  max-width: 300px !important;
}
</style>
