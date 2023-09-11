<template>
  <div>
    <a class="em-pointer" @click="addNewForm">{{ translate('COM_EMUNDUS_ONBOARD_NO_FORM_FOUND_ADD_FORM') }}</a>

    <div class="em-mb-4 em-mt-16 em-text-color">{{ChooseForm}} : </div>
    <div class="em-mb-4">
      <select id="select_profile" v-model="$props.profileId" @change="updateProfileCampaign">
        <option v-for="(profile, index) in profiles" :key="index" :value="profile.id">
          {{profile.form_label}}
        </option>
      </select>
    </div>
    <a class="em-pointer" @click="formbuilder">{{ translate('COM_EMUNDUS_ONBOARD_EDIT_FORM') }}</a>

    <hr/>
    <h5>{{ translate('COM_EMUNDUS_FORM_PAGES_PREVIEW')}}</h5>
    <div class="em-flex-row em-flex-wrap">
      <div v-for="form in fabrikFormList" :key="form.id"
           class="card-wrapper em-mr-32"
           :title="form.label"
      >
        <form-builder-preview-form
            :form_id="Number(form.id)"
            :form_label="form.label"
            class="card em-shadow-cards model-preview"
        ></form-builder-preview-form>
      </div>
    </div>

    <div v-if="documentsList.length > 0">
      <h5 class="em-mt-12">{{ translate('COM_EMUNDUS_FORM_ATTACHMENTS_PREVIEW')}}</h5>
      <div class="em-flex-row">
        <div v-for="document in documentsList" :key="document.id"
             class="card-wrapper em-mr-32"
             :title="document.label"
        >
          <form-builder-preview-attachments
              :document_id="Number(document.id)"
              :document_label="document.label"
              class="card em-shadow-cards model-preview"
          ></form-builder-preview-attachments>
        </div>
      </div>
    </div>


    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import FormCarrousel from "../../components/Form/FormCarrousel";
import axios from "axios";

const qs = require("qs");
import FormBuilderPreviewForm from "@/components/FormBuilder/FormBuilderPreviewForm.vue";
import FormBuilderPreviewAttachments from "@/components/FormBuilder/FormBuilderPreviewAttachments";

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
    FormBuilderPreviewAttachments,
    FormBuilderPreviewForm,
    FormCarrousel
  },

  data() {
    return {
      ChooseForm: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_FORM"),
      AddForm: this.translate("COM_EMUNDUS_ONBOARD_ADD_FORM"),
      EmitIndex: "0",
      formList: [],
      documentsList: [],
      loading: false,

      form: {
        label: "Nouveau formulaire",
        description: "",
        published: 1
      },

      formdescription: this.translate("COM_EMUNDUS_ONBOARD_FORMDESCRIPTION")
    };
  },
  created() {
    this.getForms(this.profileId);
    this.getDocuments(this.profileId);
  },
  methods: {
    getEmitIndex(value) {
      this.EmitIndex = value;
    },
    getForms(profile_id) {
      this.loading = true;
      axios({
        method: "get",
        url:
          "index.php?option=com_emundus&controller=form&task=getFormsByProfileId",
        params: {
          profile_id: profile_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      })
        .then(response => {
          this.formList = response.data.data;
          this.loading = false;
        })
        .catch(e => {
          console.log(e);
        });
    },

    getDocuments(profile_id){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getDocuments",
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
      window.location.href = link;
    },

    addNewForm() {
      this.loading = true;
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=form&task=createform",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: this.form})
      }).then(response => {
        this.loading = false;
        this.$props.profileId = response.data.data;
        this.redirectJRoute('index.php?option=com_emundus&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=' + this.campaignId);
      }).catch(error => {
        console.log(error);
      });
    },

    updateProfileCampaign(){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=campaign&task=updateprofile",
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
      index = 0;
      this.redirectJRoute('index.php?option=com_emundus&view=form&layout=formbuilder&prid=' +
          this.profileId +
          '&index=' +
          index +
          '&cid=' +
          this.campaignId)
    },
  },
  computed: {
    fabrikFormList() {
      return this.formList.filter(form => form.link.includes('fabrik'));
    },
  }
};
</script>

<style scoped lang="scss">
.card-wrapper {
  width: 150px;

  .em-shadow-cards {
    background-color: white;
    width: 150px;
    border: 2px solid transparent;
  }

  .card {
    margin: 24px 0 12px 0;
  }

  p {
    text-align: center;
    border-radius: 4px;
    padding: 4px;
    transition: all .3s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
  }

  input {
    width: 200px;
    height: 20px;
    font-size: 12px;
    border: 0;
    text-align: center;
  }

  &.selected {
    .em-shadow-cards {
      border: 2px solid #20835F;
    }

    p, input {
      color: white !important;
      background-color: #20835F !important;
    }
  }
}
#select_profile{
  min-width: 250px;
  width: max-content;
  max-width: 350px;
}
</style>

