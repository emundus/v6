<template>
  <div>
    <EvaluationModal v-if="currentFile" :file="currentFile" :evaluation_form="Number(files.evaluation_form)" :readonly="this.$parent.$props.readonly == '1'" @reload-list="getMyEvaluations" />

    <div v-if="campaigns.length > 0 && !loading">
      <select v-model="currentCampaign" style="width: max-content">
        <option value="0">{{ translate('MOD_EMUNDUS_EVALUATIONS_PLEASE_SELECT') }}</option>
        <option v-for="campaign in campaigns" :value="campaign.id">{{ campaign.label }} - {{ campaign.files }} {{ translate('MOD_EMUNDUS_EVALUATIONS_FILES_TO_EVALUATE') }}</option>
      </select>
    </div>

    <div v-if="campaigns.length === 0 && !loading">
      <h5 style="text-align: center;margin-top: 32px">{{ translate('MOD_EMUNDUS_EVALUATIONS_NO_FILE') }}</h5>
    </div>

    <div v-if="!loading" class="em-mt-16">
      <div v-if="files.elements && files.evaluation_form !== 0" class="table-wrapper">
        <table :class="{ loading: loading }" class="em-mt-16" aria-describedby="Table of files to evaluate">
          <thead>
          <tr>
            <th id="fnum" @click="orderBy('fnum')">
              {{ translate("MOD_EMUNDUS_EVALUATIONS_FNUM") }}
              <span v-if="sort.orderBy == 'fnum' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
              <span v-if="sort.orderBy == 'fnum' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
            </th>
            <th id="applicant_name" @click="orderBy('applicant_name')">
              {{ translate("MOD_EMUNDUS_EVALUATIONS_APPLICANT_NAME") }}
              <span v-if="sort.orderBy == 'applicant_name' && sort.order == 'asc'" class="material-icons">
                arrow_upward
              </span>
              <span v-if="sort.orderBy == 'applicant_name' && sort.order == 'desc'" class="material-icons">
                arrow_downward
              </span>
            </th>
            <th v-for="element in shownElements" :key="element.id" :id="element.name" @click="orderBy(element.name)">
                {{ element.label }}
                <span v-if="sort.orderBy == element.name && sort.order == 'asc'" class="material-icons">
                  arrow_upward
                </span>
                <span v-if="sort.orderBy == element.name && sort.order == 'desc'" class="material-icons">
                  arrow_downward
                </span>
            </th>
            <th id="completed" class="em-text-align-center">{{ translate('MOD_EMUNDUS_EVALUATIONS_COMPLETED') }}</th>
          </tr>
          </thead>
          <tbody>
            <EvaluationRow
                v-for="file in files.evaluations"
                :key="file.fnum + '-' + file.id"
                :file="file"
                :elements="files.elements"
                @open-modal="openModal"
            >
            </EvaluationRow>
          </tbody>
        </table>
      </div>
      <div v-else-if="currentCampaign.id && files.elements && files.evaluation_form == 0">
        <p class="em-text-align-center">{{ translate('MOD_EMUNDUS_EVALUATIONS_MISSING_EVALUATION_GRID') }}</p>
      </div>
    </div>
    <div v-if="loading"><div class="em-page-loader"></div></div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import axios from "axios";
import EvaluationRow from "./EvaluationRow";
import EvaluationModal from "./EvaluationModal";

const qs = require("qs");

/* IMPORT YOUR SERVICES */

export default {
  name: "List",
  components: {EvaluationModal, EvaluationRow},
  props: {},
  data: () => ({
    loading: true,

    files: [],
    currentFile: null,
    sort: {
      last: "",
      order: "",
      orderBy: "",
    },

    campaigns: [],
    currentCampaign: 0,
  }),
  created() {
    console.log(this.$parent.$props.readonly);
    this.getCampaigns();
  },
  methods: {
    getCampaigns(){
      this.loading = true;
      axios({
        method: "GET",
        url: "index.php?option=com_emundus&controller=evaluation&task=getcampaignstoevaluate",
        params: {
          module: this.$parent.$props.module,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.campaigns = response.data.campaigns;
        if(this.campaigns.length === 1){
          this.currentCampaign = this.campaigns[0].id;
        }
        this.loading = false;
      });
    },

    getMyEvaluations(){
      this.loading = true;
      axios({
        method: "GET",
        url: "index.php?option=com_emundus&controller=evaluation&task=getmyevaluations",
        params: {
          campaign: this.currentCampaign,
          module: this.$parent.$props.module,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.currentFile = null;
        this.files = response.data.files;
        this.loading = false;
      });
    },

    openModal(file){
      this.currentFile = file;

      setTimeout(() => {
        this.$modal.show("evaluation-modal");
      },500)
    },

    orderBy(key) {
      // if last sort is the same as the current sort, reverse the order
      if (this.sort.last == key) {
        this.sort.order = this.sort.order == "asc" ? "desc" : "asc";
        this.files.evaluations.reverse();
      } else {
        // sort in ascending order by key
        this.files.evaluations.sort((a, b) => {
          if (a[key] < b[key]) {
            return -1;
          }
          if (a[key] > b[key]) {
            return 1;
          }
          return 0;
        });

        this.sort.order = "asc";
      }

      this.sort.orderBy = key;
      this.sort.last = key;
    },
  },
  computed: {
    shownElements() {
      let elementsShown = {};

      Object.entries(this.files.elements).forEach((element, index) => {
        if (element[1].show_in_list_summary == 1) {
          elementsShown[index] = element[1];
        }
      })

      return elementsShown;
    }
  },
  watch: {
    currentCampaign: function(value){
      if (value != '') {
        this.getMyEvaluations();
      } else {
        this.files = [];
      }
    }
  }
}
</script>

<style scoped lang="scss">
table {

  &.loading {
    visibility: hidden;
  }

  border: 0;

  tr {
    th:first-of-type {
      width: 39px;
      input {
        margin-right: 0px;
      }
    }
  }

  tr,
  th {
    height: 49px;
    background: transparent;
    background-color: transparent;
  }

  td,
  th {
    width: fit-content;
  }

  th.desc,
  td.desc {
    max-width: 300px;
    width: initial;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  th.status,
  td.status {
    min-width: 100px;
    white-space: nowrap;
  }

  thead {
    tr {
      th {
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;

        .material-icons {
          transform: translateY(3px);
        }
      }
    }
  }

  .attachment-check {
    width: 15px;
    height: 15px;
    border-radius: 0px;
  }
}
</style>
