<template>
  <div>
    <ModalAddEvaluation
            :prog="this.prog"
            :grid="this.grid"
            @updateGrid="getEvaluationGridByProgram"
    />
    <div class="container-evaluation">
      <div class="text-center" v-if="grid == null">
        <button class="bouton-sauvergarder-et-continuer-3" style="float: none" type="button" @click="$modal.show('modalAddEvaluation')">{{addGrid}}</button>
      </div>
      <FormViewerEvaluation :link="link" :prog="prog" :key="viewer" v-if="grid != null"/>
    </div>
  </div>
</template>

<script>
import { Datetime } from "vue-datetime";
import axios from "axios";
import FormViewerEvaluation from "../../components/Form/FormViewerEvaluation";
import ModalAddEvaluation from "../advancedModals/ModalAddEvaluation";

export default {
  name: "addEvaluationGrid",

  components: {
    ModalAddEvaluation,
    Datetime,
    FormViewerEvaluation
  },

  props: {
    funnelCategorie: Object,
    prog: Number
  },

  data() {
    return {
      link: {
        link: ''
      },
      visibility: null,
      viewer: 0,
      grid: null,
      addGrid: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDGRID"),
    };
  },

  methods: {
    getEvaluationGridByProgram(){
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getevaluationgrid&pid=" + this.prog)
              .then(response => {
                this.grid = response.data.data;
                if(this.grid != null) {
                  this.link.link = 'index.php?option=com_fabrik&view=form&formid=' + response.data.data;
                  this.viewer++;
                }
              });
    }
  },

  created() {
    this.getEvaluationGridByProgram();
  }
};
</script>
