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
          <div class="form-group d-flex">
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
          </div>
        </div>
        <div class="divider"></div>
        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-button"
                      @click="quit = 1; submit()"
              >
                {{ Continuer }}
              </button>
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-quitter w-button"
                      @click="quit = 0; submit()"
              >
                {{ Quitter }}
              </button>
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-retour w-button"
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
        if (quit == 0) {
          window.location.replace(
                  "forms"
          );
        }
        else if (quit == 1) {
          if(this.campaignId != null){
            window.location.replace('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=' + this.campaignId);
          } else {
            window.location.replace('index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=' + this.profileId + '&index=0&cid=');
          }
        }
      },
    },

    mounted() {
      /*var cid = this.campaign;
      jQuery(document).ready(function($) {
        if (window.history && window.history.pushState) {
          window.history.pushState(
            "forward",
            null,
            "index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=" + cid + "#forward"
          );

          $(window).on("popstate", function() {
            window.location.replace("./campaigns");
          });
        }
      });*/
    }
  };
</script>

<style scoped>
  .container-evaluation {
    position: relative;
    width: 85%;
    margin-right: auto;
    margin-left: auto;
  }

  h2 {
    color: #1b1f3c !important;
  }

  .w-input {
    min-height: 55px;
    padding: 12px;
    font-weight: 300;
  }

  .bouton-sauvergarder-et-continuer {
    position: relative;
    padding: 10px 30px;
    float: right;
    border-radius: 4px;
    background-color: #1b1f3c;
    -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
  }

  .section-principale {
    padding-bottom: 0;
  }

  .toggle > b {
    display: block;
  }

  .toggle {
    position: relative;
    width: 40px;
    height: 20px;
    border-radius: 100px;
    background-color: #ddd;
    overflow: hidden;
    box-shadow: inset 0 0 2px 1px rgba(0, 0, 0, 0.05);
  }

  .check {
    position: absolute;
    display: block;
    cursor: pointer;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    z-index: 6;
  }

  .check:checked ~ .track {
    box-shadow: inset 0 0 0 20px #4bd863;
  }

  .check:checked ~ .switch {
    right: 2px;
    left: 22px;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    transition-property: left, right;
    transition-delay: 0.05s, 0s;
  }

  .switch {
    position: absolute;
    left: 2px;
    top: 2px;
    bottom: 2px;
    right: 22px;
    background-color: #fff;
    border-radius: 36px;
    z-index: 1;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    transition-property: left, right;
    transition-delay: 0s, 0.05s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  }

  .track {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.05);
    border-radius: 40px;
  }

  .w-quitter {
    margin-right: 5%;
    background: none !important;
    border: 1px solid #1b1f3c;
    color: #1b1f3c;
  }

  .d-flex{
    display: flex;
    align-items: center;
  }

  .d-flex label{
    margin-bottom: 0;
    margin-right: 10px;
  }

  textarea{
    max-width: unset !important;
  }
</style>
