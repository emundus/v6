<template>
  <div>
    <ModalAddEvaluation
      :prog="prog"
      :grid="grid"
      @updateGrid="getEvaluationGridByProgram(1)"
    />
    <div class="container-evaluation">
      <div class="text-center" v-if="grid == null">
        <button class="bouton-sauvergarder-et-continuer" style="float: none" type="button" @click="$modal.show('modalAddEvaluation')">{{translations.addGrid}}</button>
      </div>
      <div class="em-flex-row" v-if="grid != null">
        <button class="bouton-sauvergarder-et-continuer em-mr-4" style="float: none" type="button" @click="evaluationBuilder">{{translations.editGrid}}</button>
        <button class="bouton-sauvergarder-et-continuer w-delete" style="float: none" type="button" @click="deleteGrid">
          {{ translations.deleteGrid }}</button>
      </div>
      <FormViewerEvaluation :link="link" :prog="prog" :key="viewer" v-if="grid != null"/>
    </div>
  </div>
</template>

<script>
import { Datetime } from "vue-datetime";
import axios from "axios";
import FormViewerEvaluation from "../Form/FormViewerEvaluation";
import ModalAddEvaluation from "../AdvancedModals/ModalAddEvaluation";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "addEvaluationGrid",

  components: {
    ModalAddEvaluation,
    Datetime,
    FormViewerEvaluation
  },

  props: {
    prog: {
      type: Number,
      default: "",
    }
  },

  data() {
    return {
      link: {
        link: ''
      },
      visibility: null,
      viewer: 0,
      grid: null,
      translations:{
        addGrid: "COM_EMUNDUS_ONBOARD_BUILDER_ADDGRID",
        editGrid: "COM_EMUNDUS_ONBOARD_BUILDER_EDITGRID",
        deleteGrid: "COM_EMUNDUS_ONBOARD_BUILDER_DELETEGRID",
      },
    };
  },
  methods: {
    getEvaluationGridByProgram(redirect){
      axios.get("index.php?option=com_emundus&controller=programme&task=getevaluationgrid&pid=" + this.prog)
              .then(response => {
                this.grid = response.data.data;
                if (this.grid != null) {
                  this.link.link = 'index.php?option=com_fabrik&view=form&formid=' + response.data.data;
                  this.viewer++;
                  if(redirect){
                    this.evaluationBuilder();
                  }
                }
              });
    },

    evaluationBuilder() {

      this.redirectJRoute('index.php?option=com_emundus&view=form&layout=formbuilder&prid=&index=0&cid=' +
              this.prog +
              '&evaluation=' +
              this.grid);
    },

    deleteGrid() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGRID"),
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGRID_QUESTION"),
        type: "warning",
        showCancelButton: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=programme&task=deletegrid",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              grid: this.grid,
              pid: this.prog,
            })
          }).then(() => {
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_GRIDDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.getEvaluationGridByProgram(0);
            });
          }).catch(e => {
            console.log(e);
          });
        }
      });
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=redirectjroute",
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
    this.getEvaluationGridByProgram(0);
  }
};
</script>
