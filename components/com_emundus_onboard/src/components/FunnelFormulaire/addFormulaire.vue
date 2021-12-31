<template>
  <div class="container-evaluation formulairedepresentation">
    <p class="heading">{{ChooseForm}}</p>
    <div class="heading-block" style="margin-left: unset">
      <select class="dropdown-toggle" id="select_profile" v-model="$props.profileId" @change="updateProfileCampaign">
        <option v-for="(profile, index) in profiles" :key="index" :value="profile.id">
          {{profile.form_label}}
        </option>
      </select>
      <a @click="addNewForm" class="bouton-ajouter bouton-ajouter-green pointer">
        <div class="add-button-div">
          <em class="fas fa-plus mr-1"></em>
          {{ AddForm }}
        </div>
      </a>
    </div>
    <FormCarrousel 
      v-if="formList" 
      :formList="formList" 
      :documentsList="documentsList" 
      :visibility="visibility" 
      :key="formListReload" 
      @getEmitIndex="getEmitIndex" 
      @formbuilder="formbuilder" 
    />
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
    campaignId: Number,
    profiles: Array,
    formulaireEmundus: Number,
    visibility: Number
  },
  components: {
    FormCarrousel
  },

  data() {
    return {
      ChooseForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_FORM"),
      AddForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
      EmitIndex: "0",
      formList: [],
      documentsList: [],
      formListReload: 0,

      form: {
        label: "Nouveau formulaire",
        description: "",
        published: 1
      },

      formdescription: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMDESCRIPTION")
    };
  },
  methods: {
    getEmitIndex(value) {
      this.EmitIndex = value;
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
          this.formListReload += 1;
        })
        .catch(e => {
          console.log(e);
        });
    },

    getDocuments(profile_id){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=form&task=getDocuments",
        params: {
          pid: profile_id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.documentsList = response.data.data;
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
    },

    addNewForm() {
      this.loading = true;
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=form&task=createform",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: this.form})
      }).then(response => {
        this.loading = false;
        this.$props.profileId = response.data.data;
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=' + this.campaignId);
      }).catch(error => {
        console.log(error);
      });
    },

    updateProfileCampaign(){
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=updateprofile",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          profile: this.profileId,
          campaign: this.campaignId
        })
      }).then(() => {
        this.getForms(this.profileId);
        this.getDocuments(this.profileId);
        this.$emit("profileId", this.profileId);
      })
    },

    formbuilder(index) {
      axios.get("index.php?option=com_emundus_onboard&controller=form&task=getfilesbyform&pid=" + this.profileId)
          .then(response => {
            if(response.data.data != 0){
              this.$modal.show('modalWarningFormBuilder');
            } else {
              this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' +
                  this.profileId +
                  '&index=' +
                  index +
                  '&cid=' +
                  this.campaignId)
            }
          });
    },
  },
  created() {
    this.getForms(this.profileId);
    this.getDocuments(this.profileId);
  },
};
</script>

<style scoped>
#select_profile{
  width: 23%;
  margin-right: 10px;
}
</style>

