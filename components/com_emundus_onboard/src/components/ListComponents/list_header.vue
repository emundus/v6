<template>
  <div class="section-sub-menu">
    <div class="container-2 w-container" style="max-width: unset">
      <transition :name="'slide-down'" type="transition">
        <div>
          <div class="d-flex" v-if="data.type === 'campaign'">
            <img src="images/emundus/menus/megaphone.svg" srcset="/images/emundus/menus/megaphone.svg" class="tchooz-icon-title" alt="megaphone">
            <h1 class="tchooz-section-titles">{{ translations.Campaigns }}</h1>
          </div>
          <div class="d-flex" v-if="data.type === 'email'">
            <img src="images/emundus/menus/email.png" srcset="/images/emundus/menus/email.png" class="tchooz-icon-title" alt="email">
            <h1 class="tchooz-section-titles">{{ translations.Emails }}</h1>
          </div>
          <div class="d-flex" v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'">
            <img src="images/emundus/menus/form.png" srcset="/images/emundus/menus/form.png" class="tchooz-icon-title" alt="form">
            <h1 class="tchooz-section-titles">{{ translations.Forms }}</h1>
          </div>

          <div class="actions-add-block">
              <p v-if="data.type === 'campaign'" class="tchooz-section-description">{{ translations.CampaignsDesc }}</p>
              <p v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'" class="tchooz-section-description">{{ translations.FormsDesc }}</p>
              <p v-if="data.type === 'email'" class="tchooz-section-description">{{ translations.EmailsDesc }}</p>
              <a @click="redirectToAdd" class="bouton-ajouter pointer" v-if="!addHidden">
                <div v-if="data.type === 'campaign'" class="add-button-div">
                  <em class="fas fa-plus mr-1"></em>
                  {{ translations.AddCampaign }}
                </div>
                <div v-if="data.type === 'email'" class="add-button-div">
                  <em class="fas fa-plus mr-1"></em>
                  {{ translations.AddEmail }}
                </div>
                <div v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'" class="add-button-div">
                  <em class="fas fa-plus mr-1"></em>
                  {{ translations.AddForm }}
                </div>
              </a>
            </div>
        </div>
      </transition>
    </div>
    <div class="loading-form" style="top: 10vh" v-if="loading">
      <Ring-Loader :color="'#12db42'" />
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  import "sweetalert2/src/sweetalert2.scss";
  import { list } from "../../store/store";
  import "@fortawesome/fontawesome-free/css/all.css";
  import "@fortawesome/fontawesome-free/js/all.js";

  const qs = require("qs");

  export default {
    name: "action_menu",

    props: {
      data: Object,
    },

    computed: {
      checkItem() {
        return list.getters.selectedItems;
      }
    },

    data() {
      return {
        form: {
          label: "Nouveau formulaire",
          description: "",
          published: 1
        },
        loading: false,
        translations:{
          AddCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
          AddEmail: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_EMAIL"),
          AddForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
          Campaigns: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMPAIGNS"),
          CampaignsDesc: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMPAIGNS_DESC"),
          Emails: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILS"),
          Forms: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMS"),
          FormsDesc: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMS_DESC"),
          EmailsDesc: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILS_DESC"),
        },
        addHidden: false
      };
    },

    methods: {
      redirectToAdd() {
        if(this.data.add_url == 'index.php?option=com_emundus_onboard&view=form&layout=add'){
          this.createForm();
        } else {
          this.redirectJRoute(this.data.add_url);
        }
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

      createForm(){
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
          this.profileId = response.data.data;
          this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=');
        }).catch(error => {
          console.log(error);
        });
      }
    },
  };
</script>

<style scoped>
  div nav a:hover {
    cursor: pointer;
  }
</style>
