<template>
  <div class="section-principale">
    <div class="w-container">
      <form id="campaign-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required">{{RequiredFieldsIndicate}}</p>
          <div class="heading-form">
            <div class="icon-title"></div>
            <h2 class="heading">{{ CreateForm }}</h2>
          </div>
          <p class="paragraphe-sous-titre">
          </p>
          <div class="form-group form-label">
            <label for="formLabel">{{FormName}} *</label>
            <input
                    id="formLabel"
                    type="text"
                    class="form__input field-general w-input"
                    placeholder=" "
                    v-model.lazy="$v.form.label.$model"
                    v-focus
                    maxlength="100"
                    :class="{ 'is-invalid': errors || formTouched }"
            />
          </div>
          <p v-if="errors || formTouched" class="error">
            <span class="error" v-if="!$v.form.label.required">{{LabelRequired}}</span>
          </p>
          <div class="form-group">
            <label>{{Description}}</label>
            <textarea
                    type="textarea"
                    rows="4"
                    maxlength="400"
                    class="form__input field-general w-input"
                    v-model="form.description"
            />
          </div>
          <!--<div class="form-group d-flex">
            <div class="toggle">
              <input
                      type="checkbox"
                      true-value="1"
                      false-value="0"
                      class="check"
                      id="published"
                      name="published"
                      v-model="form.published"
              />
              <strong class="b switch"></strong>
              <strong class="b track"></strong>
            </div>
            <label for="published" class="ml-10px">{{ Publish }}</label>
          </div>-->
        </div>
        <div class="divider"></div>
        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer"
                      @click="quit = 1; submit()"
              >
                {{ Continuer }}
              </button>
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-quitter"
                      @click="quit = 0; submit()"
              >
                {{ Quitter }}
              </button>
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-retour"
                      onclick="history.go(-1)"
              >
                {{ Retour }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="loading-form" v-if="submitted">
      <Ring-Loader :color="'#de6339'" />
    </div>
  </div>
</template>

<script>
  import { required } from "vuelidate/lib/validators";
  import axios from "axios";

  const qs = require("qs");

  export default {
    name: "addForm",

    quit: 1,

    props: {
      profileId: Number,
      campaignId: Number
    },

    components: {},

    directives: { focus: {
        inserted: function (el) {
          el.focus()
        }
      }
    },

    data: () => ({
      form: {
        label: "",
        description: "",
        published: 1
      },
      //forms: [],

      CreateForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_FORM"),
      FormName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDFORM_FORMNAME"),
      Description: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Quitter: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_QUITTER"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Publish: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
      RequiredFieldsIndicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE"),

      submitted: false,
      errors: '',
      formTouched: ''
    }),

    validations: {
      form: {
        label: { required },
      }
    },

    created() {
      if (this.profileId !== "") {
        axios.get(
                `index.php?option=com_emundus_onboard&controller=form&task=getformbyid&id=${this.profileId}`
        ).then(response => {
          this.form.label = response.data.data.label;
          this.form.description = response.data.data.description;
        }).catch(e => {
          console.log(e);
        });
      }
    },

    methods: {
      submit() {
        this.formTouched = !this.$v.form.$anyDirty;
        this.errors = this.$v.form.$anyError;
        if (this.errors === false) {
          if(this.form.label === ''){
            this.formTouched = true;
          } else {
            //this is where you send the responses
            this.submitted = true;

            if (this.profileId !== "") {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=form&task=updateform",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({body: this.form, pid: this.profileId})
              }).then(response => {
                this.quitFunnelOrContinue(this.quit);
              }).catch(error => {
                console.log(error);
              });
            } else {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=form&task=createform",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({body: this.form})
              }).then(response => {
                this.profileId = response.data.data;
                this.quitFunnelOrContinue(this.quit);
              }).catch(error => {
                console.log(error);
              });
            }
          }
        }
      },

      quitFunnelOrContinue(quit) {
        if(this.campaignId != null){
          let campaigns = [];
          campaigns.push(this.campaignId);
          axios({
            method: "post",
            url: 'index.php?option=com_emundus_onboard&controller=form&task=affectcampaignstoform',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              prid: this.profileId,
              campaigns: campaigns
            })
          }).then(() => {
            if (quit == 0) {
              window.location.href = '/configuration-forms'
            } else if (quit == 1) {
              this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=' + this.campaignId);
            }
          });
        } else {
          if (quit == 0) {
            window.location.href = '/configuration-forms'
          } else if (quit == 1) {
            this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=');
          }
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
      }
    },
  };
</script>

<style scoped>
</style>
