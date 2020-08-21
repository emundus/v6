<template>
  <div class="section-principale">
    <notifications
        group="foo-velocity"
        position="bottom left"
        animation-type="velocity"
        :speed="500"
        :classes="'vue-notification-custom'"
    />
    <div class="w-container">
      <form id="program-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required">{{RequiredFieldsIndicate}}</p>
          <div class="heading-form">
            <div class="icon-title informations"></div>
            <h2 class="heading">{{ Informations }}</h2>
          </div>
          <div class="w-form">
            <div class="form-group">
              <label>{{emailName}} *</label>
              <input
                      type="text"
                      class="form__input field-general w-input"
                      v-model="form.subject"
                      :class="{ 'is-invalid': errors.subject}"
              />
            </div>
            <p v-if="errors.subject" class="error col-md-12 mb-2">
              <span class="error">{{SubjectRequired}}</span>
            </p>

            <div class="form-group controls forms-emails-editor">
              <label>{{emailBody}} *</label>
              <editor :text="form.message" v-if="dynamicComponent" :lang="actualLanguage" v-model="form.message" :id="'email'" :placeholder="EmailResume" :class="{ 'is-invalid': errors.message}"></editor>
            </div>
            <p v-if="errors.message" class="error col-md-12 mb-2">
              <span class="error">{{BodyRequired}}</span>
            </p>
          </div>
        </div>

        <div class="divider"></div>
        <div class="sous-container last-container">
          <div class="heading-form">
            <div class="icon-title"></div>
            <h2 class="heading">{{ Advanced }}</h2>
          </div>
          <div class="form-group">
            <label>{{receiverName}}</label>
            <input
                    type="text"
                    class="form__input field-general w-input"
                    v-model="form.name"
            />
          </div>

          <div class="form-group">
            <label>{{emailAddress}}</label>
            <input
                    type="text"
                    class="form__input field-general w-input"
                    v-model="form.emailfrom"
            />
          </div>

          <div class="form-group">
            <label>{{ emailCategory }}</label>
            <autocomplete
                    @searched="onSearchCategory"
                    :items="this.categories"
                    :year="this.form.category"
            />
          </div>
        </div>

        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button type="submit" class="bouton-sauvergarder-et-continuer w-button">
                {{ continuer }}
              </button>
              <button
                      type="button"
                      class="bouton-sauvergarder-et-continuer w-retour w-button"
                      onclick="history.go(-1)"
              >
                {{ retour }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
  import Autocomplete from "../components/autocomplete";
  import axios from "axios";
  import Editor from "../components/editor";

  const qs = require("qs");

  export default {
    name: "addEmail",

    components: {
      Editor,
      Autocomplete
    },

    props: {
      email: Number,
      actualLanguage: String
    },

    data: () => ({
      langue: 0,

      dynamicComponent: false,

      Advanced: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADVANCED_CUSTOMING"),
      Informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION"),
      emailType: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE"),
      emailCategory: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSECATEGORY"),
      retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      emailName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME"),
      emailBody: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_BODY"),
      receiverName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER"),
      emailAddress: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS"),
      EmailResume: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME"),
      RequiredFieldsIndicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE"),
      EmailType: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILTYPE"),
      SubjectRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_SUBJECT_REQUIRED"),
      BodyRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_BODY_REQUIRED"),

      categories: [],
      enableTip: false,

      form: {
        lbl: "",
        subject: "",
        name: "",
        emailfrom: "",
        message: "",
        type: 1,
        category: "",
        published: 1
      },
      errors: {
        subject: false,
        message: false
      },
      submitted: false
    }),

    methods: {
      submit() {
        this.errors = {
          subject: false,
          message: false
        };

        if(this.form.subject == ""){
          this.errors.subject = true;
          return 0;
        }
        if(this.form.message == ""){
          this.errors.message = true;
          return 0;
        }
        this.submitted = true;
        this.form.lbl = this.form.subject;

        if (this.email !== "") {
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=email&task=updateemail",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.form, code: this.email })
          }).then(response => {
            history.go(-1);
          })
                  .catch(error => {
                    console.log(error);
                  });
        } else {
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=email&task=createemail",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.form })
          })
                  .then(response => {
                    history.go(-1);
                  })
                  .catch(error => {
                    console.log(error);
                  });
        }
      },

      onSearchCategory(value) {
        this.form.category = value;
      },

      enableVariablesTip() {
        if(!this.enableTip){
          this.enableTip = true;
          this.tip();
        }
      },

      /**
       * ** Methods for notify
       */
      tip(){
        this.show(
            "foo-velocity",
            Joomla.JText._("COM_EMUNDUS_ONBOARD_VARIABLESTIP") + ' <strong style="font-size: 16px">/</strong>',
            Joomla.JText._("COM_EMUNDUS_ONBOARD_TIP"),
        );
      },

      show(group, text = "", title = "Information") {
        this.$notify({
          group,
          title: `${title}`,
          text,
          duration: 10000
        });
      },
      clean(group) {
        this.$notify({ group, clean: true });
      },
    },

    created() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
              .then(rep => {
                this.categories = rep.data.data;
                if (this.email !== "") {
                  axios.get(`index.php?option=com_emundus_onboard&controller=email&task=getemailbyid&id=${this.email}`)
                          .then(resp => {
                            this.form.subject = resp.data.data.subject;
                            this.form.name = resp.data.data.name;
                            this.form.emailfrom = resp.data.data.emailfrom;
                            this.form.message = resp.data.data.message;
                            this.form.type = resp.data.data.type;
                            this.form.category = resp.data.data.category;
                            this.form.published = resp.data.data.published;
                            this.dynamicComponent = true;
                          }).catch(e => {
                            console.log(e);
                          });
                } else {
                  this.dynamicComponent = true;
                }
              }).catch(e => {
                console.log(e);
              });
      setTimeout(() => {
        this.enableVariablesTip();
      },2000);
    },

    mounted() {
      if (this.actualLanguage == "en") {
        this.langue = 1;
      }
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

  .last-container {
    padding-bottom: 30px;
  }

  .section-principale {
    padding-bottom: 0;
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
</style>
