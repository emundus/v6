<template>
  <div class="section-sub-menu">
    <div class="container-2 w-container" style="max-width: unset">
      <transition :name="'slide-down'" type="transition">
        <div>
          <div class="em-flex-row" v-if="data.type === 'campaign'">
            <img src="images/emundus/menus/megaphone.svg" srcset="/images/emundus/menus/megaphone.svg" class="tchooz-icon-title" alt="megaphone">
            <h1 class="tchooz-section-titles">{{ translations.Campaigns }}</h1>
          </div>
          <div class="em-flex-row" v-if="data.type === 'email'">
            <img src="images/emundus/menus/email.png" srcset="/images/emundus/menus/email.png" class="tchooz-icon-title" alt="email">
            <h1 class="tchooz-section-titles">{{ translations.Emails }}</h1>
          </div>
          <div class="em-flex-row" v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'">
            <img src="images/emundus/menus/form.png" srcset="/images/emundus/menus/form.png" class="tchooz-icon-title" alt="form">
            <h1 class="tchooz-section-titles">{{ translations.Forms }}</h1>
          </div>

          <div class="actions-add-block">
              <p v-if="data.type === 'campaign'" class="tchooz-section-description">{{ translations.CampaignsDesc }}</p>
              <p v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'" class="tchooz-section-description">{{ translations.FormsDesc }}</p>
              <p v-if="data.type === 'email'" class="tchooz-section-description">{{ translations.EmailsDesc }}</p>
              <a @click="redirectToAdd" class="bouton-ajouter pointer" v-if="!addHidden">
                <div v-if="data.type === 'campaign'" class="add-button-div">
                  <em class="fas fa-plus em-mr-4"></em>
                  {{ translations.AddCampaign }}
                </div>
                <div v-if="data.type === 'email'" class="add-button-div">
                  <em class="fas fa-plus em-mr-4"></em>
                  {{ translations.AddEmail }}
                </div>
                <div v-if="data.type === 'form' ||data.type === 'grilleEval' ||data.type === 'formulaire'" class="add-button-div">
                  <em class="fas fa-plus em-mr-4"></em>
                  {{ translations.AddForm }}
                </div>
              </a>
            </div>
        </div>
      </transition>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
  import axios from "axios";
  import "sweetalert2/src/sweetalert2.scss";
  ;
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
        return this.$store.getters['lists/selectedItems'];
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
          AddCampaign: this.translate("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
          AddEmail: this.translate("COM_EMUNDUS_ONBOARD_ADD_EMAIL"),
          AddForm: this.translate("COM_EMUNDUS_ONBOARD_ADD_FORM"),
          Campaigns: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNS"),
          CampaignsDesc: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNS_DESC"),
          Emails: this.translate("COM_EMUNDUS_ONBOARD_EMAILS"),
          Forms: this.translate("COM_EMUNDUS_ONBOARD_FORMS"),
          FormsDesc: this.translate("COM_EMUNDUS_ONBOARD_FORMS_DESC"),
          EmailsDesc: this.translate("COM_EMUNDUS_ONBOARD_EMAILS_DESC"),
        },
        addHidden: false
      };
    },

    methods: {
      redirectToAdd() {
        console.log(this.data.add_url)
        if(this.data.add_url == 'index.php?option=com_emundus&view=form&layout=add'){
          this.createForm();
        } else {
          this.redirectJRoute(this.data.add_url);
        }
      },

      redirectJRoute(link) {
        window.location.href = link;
      },

      createForm(){
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
          this.profileId = response.data.data;
          this.redirectJRoute('index.php?option=com_emundus&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=');
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
