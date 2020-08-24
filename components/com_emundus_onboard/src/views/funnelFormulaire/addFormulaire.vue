<template>
  <div class="container-evaluation formulairedepresentation">
    <FormCarrousel :formList="this.formList" :visibility="this.visibility" v-if="this.formList" @getEmitIndex="getEmitIndex" />
  </div>
</template>

<script>
import FormCarrousel from "../../components/Form/FormCarrousel";
import axios from "axios";
const qs = require("qs");

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

export default {
  name: "addFormulaire",

  props: {
    profileId: String,
    formulaireEmundus: Number,
    visibility: Number
  },
  components: {
    FormCarrousel
  },

  data() {
    return {
      ChooseForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_FORM"),
      EmitIndex: "0",
      formList: "",

      formdescription: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMDESCRIPTION")
    };
  },
  methods: {
    getEmitIndex(value) {
      this.EmitIndex = value;
    },
    formbuilder() {
      this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' +
              this.profileId +
              '&index=' +
              this.EmitIndex +
              '&fid=' +
              this.formulaireEmundus);
    },
    getForms(profile_id) {
      axios({
        method: "get",
        url:
          "index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
        params: {
          profile_id: profile_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      })
        .then(response => {
          this.formList = response.data.data;
        })
        .catch(e => {
          console.log(e);
        });
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
    this.getForms(this.profileId);
  },
  watch: {
    profileId: function() {
      this.getForms(this.profileId);
    }
  }
};
</script>

