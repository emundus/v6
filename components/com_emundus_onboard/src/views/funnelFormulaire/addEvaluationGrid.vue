<template>
  <div>
    <ModalAddEvaluation
            :prog="this.prog"
            :grid="this.grid"
            @updateGrid="getEvaluationGridByProgram"
    />
    <div class="container-evaluation">
      <div class="text-center" v-if="grid == null">
        <button class="bouton-sauvergarder-et-continuer" style="float: none" type="button" @click="$modal.show('modalAddEvaluation')">{{addGrid}}</button>
      </div>
      <div class="text-center" v-if="grid != null">
        <button class="bouton-sauvergarder-et-continuer" style="float: none" type="button" @click="evaluationBuilder">Modifier la grille</button>
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

const qs = require("qs");

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
    },

    evaluationBuilder() {
      this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=&index=0&cid=' +
              this.prog +
              '&evaluation=' +
              this.grid);
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    }
  },

  created() {
    this.getEvaluationGridByProgram();
  }
};
</script>
