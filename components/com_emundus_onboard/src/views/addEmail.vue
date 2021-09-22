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
      <div class="section-sub-menu sub-form" v-if="email == ''">
        <div class="container-2 w-container" style="max-width: unset">
          <div class="d-flex">
            <img src="/images/emundus/menus/email.png" class="tchooz-icon-title" alt="email">
            <h1 class="tchooz-section-titles">{{AddEmail}}</h1>
          </div>
        </div>
      </div>
      <form id="program-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required mb-1">{{RequiredFieldsIndicate}}</p>
          <div class="heading-form">
            <h2 class="heading">{{ Informations }}</h2>
          </div>
          <div class="w-form">
            <div class="form-group">
              <label>{{emailName}} <span style="color: #E5283B">*</span></label>
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
              <label>{{emailBody}} <span style="color: #E5283B">*</span></label>
              <editor :height="'30em'" :text="form.message" v-if="dynamicComponent" :lang="actualLanguage" :enable_variables="true" v-model="form.message" :id="'email'" :placeholder="EmailResume" :class="{ 'is-invalid': errors.message}"></editor>
            </div>
            <p v-if="errors.message" class="error col-md-12 mb-2">
              <span class="error">{{BodyRequired}}</span>
            </p>
          </div>
        </div>

        <div class="divider"></div>
        <div class="sous-container last-container">
          <div class="heading-form">
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

          <div class="form-group" id="receivers_cc">
            <label>{{ ReceiversCC }}</label>
            <multiselect v-model="selectedReceiversCC" label="email" track-by="email" :options="receivers_cc" :multiple="true" :searchable="true"
                         :taggable="true" :placeholder="ReceiversCCPlaceHolder" @tag="addNewCC" :close-on-select="false" :clear-on-select="false"></multiselect>
            <i id="cc-tooltips" style="font-size: .8rem; color: #8c8c8c"> {{ CopiesTooltips }} ${1234}}</i>
          </div>

          <!-- Email -- BCC (in form of email adress or fabrik element -->
          <div class="form-group" id="receivers_bcc">
            <label>{{ ReceiversBCC }}</label>
            <multiselect v-model="selectedReceiversBCC" label="email" track-by="email" :options="receivers_bcc" :multiple="true" :searchable="true"
                         :taggable="true" :placeholder="ReceiversBCCPlaceHolder" @tag="addNewBCC" :close-on-select="false" :clear-on-select="false"></multiselect>
            <i id="bcc-tooltips" style="font-size: .8rem; color: #8c8c8c"> {{ CopiesTooltips }} ${1234}}</i>
          </div>

          <!-- Email -- Associated letters (in form of email adress or fabrik element -->
          <div class="form-group" id="attached_letters">
            <label>{{ Letters }}</label>
            <multiselect v-model="selectedLetter" label="value" track-by="id" :options="attached_letters" :multiple="true"
                         :taggable="true" :placeholder="LettersPlaceHolder" :close-on-select="false" :clear-on-select="false"></multiselect>
          </div>

        </div>
        <div class="divider"></div>
        <div class="sous-container last-container" v-if="email == ''">
          <div class="heading-form">
            <h2 class="heading">{{ Trigger }}</h2>
          </div>

          <div class="form-group">
            <label>{{Program}}</label>
            <select v-model="trigger.program" class="dropdown-toggle w-select" @change="addTrigger">
              <option :value="null"></option>
              <option v-for="(program,index) in programs" :value="program.id">{{program.label}}</option>
            </select>
          </div>

          <div v-if="triggered">
            <div class="form-group">
              <label>{{Actions}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.action_status" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.action_status}">
                <option value="to_current_user">{{TheCandidate}}</option>
                <option value="to_applicant">{{Manual}}</option>
              </select>
              <p v-if="errors.trigger.action_status" class="error">
                <span class="error">{{StatusRequired}}</span>
              </p>
            </div>

            <div class="form-group">
              <label>{{Status}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.status" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.status}">
                <option v-for="(statu,index) in status" :key="index" :value="statu.step">{{statu.value}}</option>
              </select>
              <p v-if="errors.trigger.status" class="error">
                <span class="error">{{StatusRequired}}</span>
              </p>
            </div>

            <div class="form-group">
              <label>{{Target}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.target" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.target}">
                <option value="5">{{Administrators}}</option>
                <option value="6">{{Evaluators}}</option>
                <option value="1000">{{Candidates}}</option>
                <option value="0">{{DefinedUsers}}</option>
              </select>
              <p v-if="errors.trigger.target" class="error">
                <span class="error">{{TargetRequired}}</span>
              </p>
            </div>
            <div class="form-group" v-if="trigger.target == 0" style="align-items: baseline">
              <label>{{ChooseUsers}}<span style="color: #E5283B">*</span> :</label>
              <div class="wrap">
                <div class="search">
                  <input type="text" class="searchTerm" :placeholder="Search" v-model="searchTerm" @keyup="searchUserByTerm">
                  <button type="button" class="searchButton" @click="searchUserByTerm">
                    <em class="fas fa-search"></em>
                  </button>
                </div>
              </div>
              <div class="select-all">
                <input type="checkbox" class="form-check-input bigbox" @click="selectAllUsers" v-model="selectall">
                <label>
                  {{SelectAll}}
                </label>
              </div>
              <div class="users-block" :class="{ 'is-invalid': errors.trigger.selectedUsers}">
                <div v-for="(user, index) in users" :key="index" class="user-item">
                  <input type="checkbox" class="form-check-input bigbox" v-model="selectedUsers[user.id]">
                  <div class="ml-10px">
                    <p>{{user.name}}</p>
                    <p>{{user.email}}</p>
                  </div>
                </div>
              </div>
              <p v-if="errors.trigger.selectedUsers" class="error">
                <span class="error">{{UsersRequired}}</span>
              </p>
            </div>
          </div>
        </div>

        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation d-flex justify-content-between">
              <button
                  type="button"
                  class="bouton-sauvergarder-et-continuer w-retour"
                  onclick="history.go(-1)">
                {{ retour }}
              </button>
              <button type="submit" class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green">
                {{ continuer }}
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
import Tasks from "@/views/tasks";
import Multiselect from 'vue-multiselect';

  const qs = require("qs");

  export default {
    name: "addEmail",

  components: {
    Tasks,
    Editor,
    Autocomplete,
    Multiselect
  },

    props: {
      email: Number,
      actualLanguage: String
    },

    data: () => ({
      langue: 0,

    dynamicComponent: false,

      AddEmail: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_EMAIL"),
      Advanced: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADVANCED_CUSTOMING"),
      Informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION"),
      Trigger: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_TRIGGER"),
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
      Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      Model: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERMODEL"),
      ModelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERMODEL_REQUIRED"),
      Status: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS"),
      StatusRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED"),
      Target: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERTARGET"),
      TargetRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERTARGET_REQUIRED"),
      Administrators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
      Evaluators: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
      Candidates: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_CANDIDATES"),
      DefinedUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_DEFINED_USERS"),
      ChooseUsers: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGER_CHOOSE_USERS"),
      UsersRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGER_USERS_REQUIRED"),
      Search: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEARCH_USERS"),
      TheCandidate: Joomla.JText._("COM_EMUNDUS_ONBOARD_THE_CANDIDATE"),
      Manual: Joomla.JText._("COM_EMUNDUS_ONBOARD_MANUAL"),
      Actions: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGER_ACTIONS"),
      Tags: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_TAGS"),
      DocumentType: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT"),

    /// Letters field
    Letters: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT"),
    LettersPlaceHolder: Joomla.JText._("COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_DOCUMENT"),

    /// Receiver CC field
    ReceiversCC: Joomla.JText._("COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS"),
    ReceiversCCPlaceHolder: Joomla.JText._("COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS_PLACEHOLDER"),

    /// Receiver BCC field
    ReceiversBCC: Joomla.JText._("COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS"),
    ReceiversBCCPlaceHolder: Joomla.JText._("COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS_PLACEHOLDER"),

    /// Receiver Tooltips
    CopiesTooltips: Joomla.JText._("COM_EMUNDUS_ONBOARD_CC_BCC_TOOLTIPS"),

    categories: [],
    programs: [],
    status: [],
    users: [],
    selectedUsers: [],
    enableTip: false,
    searchTerm: '',
    selectall: false,

      tags: [],         /// email --- tags
      documents: [],    /// email -- document types

      selectedTags: [],
      selectedDocuments: [],

    form: {
      lbl: "",
      subject: "",
      name: "",
      emailfrom: "",
      message: "",
      type: 2,
      category: "",
      published: 1
    },
    trigger: {
      model: null,
      status: null,
      action_status: null,
      target: null,
      program: null
    },
    triggered: false,
    errors: {
      subject: false,
      message: false,
      trigger: {
        model: false,
        status: false,
        target: false,
        selectedUsers: false,
        action_status: false
      }
    },
    submitted: false,

    selectedReceiversCC: [],
    selectedReceiversBCC: [],
    selectedLetter: [],

    receivers_cc: [],
    receivers_bcc: [],
    attached_letters: [],
  }),

  methods: {
    addNewCC (newCC) {
      const tag = {
        email: newCC,
        id: newCC.substring(0, 2) + Math.floor((Math.random() * 10000000))
      }
      this.receivers_cc.push(tag);
      this.selectedReceiversCC.push(tag);
    },

    /// add new BCC
    addNewBCC (newBCC) {
      const tag = {
        email: newBCC,
        id: newBCC.substring(0, 2) + Math.floor((Math.random() * 10000000))
      }
      this.receivers_bcc.push(tag);
      this.selectedReceiversBCC.push(tag);
    },

    /// get all users
    getAllUsers: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=getallusers',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        this.receivers_cc = response.data.users;
        this.receivers_bcc = response.data.users;
      }).catch(error => {
        console.log(error);
      })
    },

    getProgramsList() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=program&task=getallprogram",
        params: {
          filter: '',
          sort: '',
          recherche: '',
          lim: 100,
          page: 1,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.programs = response.data.data;
      });
    },
    getStatus() {
      axios.get("index.php?option=com_emundus_onboard&controller=email&task=getstatus")
          .then(response => {
            this.status = response.data.data;
          });
    },
    getUsers() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getuserswithoutapplicants")
          .then(response => {
            this.users = response.data.data;
          });
    },
    searchUserByTerm() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=searchuserbytermwithoutapplicants&term=" + this.searchTerm)
          .then(response => {
            this.users = response.data.data;
          });
    },

    addTrigger() {
      if(this.trigger.program != null) {
        this.triggered = true;
      } else {
        this.triggered = false;
      }
    },
    selectAllUsers() {
      this.users.forEach(element => {
        if(!this.selectall) {
          this.selectedUsers[element.id] = true;
        } else {
          this.selectedUsers[element.id] = false;
        }
      });
      this.$forceUpdate();
    },

    submit() {
      this.errors = {
        subject: false,
        message: false,
        trigger: {
          model: false,
          status: false,
          target: false,
          selectedUsers: false,
        }
      };

      if(this.form.subject == ""){
        this.errors.subject = true;
        return 0;
      }
      if(this.form.message == ""){
        this.errors.message = true;
        return 0;
      }
      if(this.trigger.program != null){
        if(this.trigger.action_status == null){
          this.errors.trigger.action_status = true;
          return 0;
        }
        if(this.trigger.status == null){
          this.errors.trigger.status = true;
          return 0;
        }
        if(this.trigger.target == null){
          this.errors.trigger.target = true;
          return 0;
        } else if (this.trigger.target == 0) {
          if(this.selectedUsers.length === 0) {
            this.errors.trigger.selectedUsers = true;
            return 0;
          }
        }
      }
      this.submitted = true;

      if (this.email !== "") {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=email&task=updateemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form, code: this.email, selectedReceiversCC: this.selectedReceiversCC, selectedReceiversBCC: this.selectedReceiversBCC, selectedLetters: this.selectedLetter})
        }).then(response => {
          // this.redirectJRoute('index.php?option=com_emundus_onboard&view=email&layout=add&eid=' + this.email);
          this.redirectJRoute('index.php?option=com_emundus_onboard&view=email');
        }).catch(error => {
          console.log(error);
        });
      } else {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=email&task=createemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form, selectedReceiversCC: this.selectedReceiversCC, selectedReceiversBCC: this.selectedReceiversBCC, selectedLetters:this.selectedLetter })
        }).then(response => {
          this.trigger.model = response.data.data;
          axios({
            method: "post",
            url: 'index.php?option=com_emundus_onboard&controller=email&task=createtrigger',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              trigger: this.trigger,
              users: this.selectedUsers
            })
          }).then((rep) => {
            // this.redirectJRoute('index.php?option=com_emundus_onboard&view=email&layout=add&eid=' + this.email);
            this.redirectJRoute('index.php?option=com_emundus_onboard&view=email');
          });
        }).catch(error => {
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

    /// get all tags
    getAllTags: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=gettags',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        let _tags = response.data.data;
        this.tags = _tags;
      }).catch(error => {
        console.log(error);
      })
    },

    getAllDocumentLetter: function() {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus&controller=messages&task=getalldocumentsletters',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        console.log(response);
        let _documents = response.data.documents;
        this.attached_letters = _documents;
      }).catch(error => {
        console.log(error);
      })
    }
  },

  created() {
    this.getAllUsers();
    // this.getAllTags();
    this.getAllDocumentLetter();
    axios.get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
        .then(rep => {
          this.categories = rep.data.data;
          if (this.email !== "") {
            axios.get(`index.php?option=com_emundus_onboard&controller=email&task=getemailbyid&id=${this.email}`)
                .then(resp => {
                  this.form.lbl = resp.data.data.email.lbl;
                  this.form.subject = resp.data.data.email.subject;
                  this.form.name = resp.data.data.email.name;
                  this.form.emailfrom = resp.data.data.email.emailfrom;
                  this.form.message = resp.data.data.email.message;
                  this.form.type = resp.data.data.email.type;
                  this.form.category = resp.data.data.email.category;
                  this.form.published = resp.data.data.email.published;
                  this.dynamicComponent = true;

                  // get attached letters
                  if(resp.data.data.letter_attachment) {
                    let _documents = resp.data.data.letter_attachment;
                    this.selectedLetter = _documents;
                  }

                  /// get receivers (cc and bcc)
                  if(resp.data.data.receivers !== null && resp.data.data.receivers !== undefined && resp.data.data.receivers !== "") {
                    let receiver_cc = [];
                    let receiver_bcc = [];

                    let receivers = resp.data.data.receivers;

                    for (let index = 0; index < receivers.length; index++) {
                      receiver_cc[index] = {};
                      receiver_bcc[index] = {};

                      if (receivers[index].type == 'receiver_cc_email' || receivers[index].type == 'receiver_cc_fabrik') {
                        receiver_cc[index]['id'] = receivers[index].id;
                        receiver_cc[index]['email'] = receivers[index].receivers;
                      } else if (receivers[index].type == 'receiver_bcc_email' || receivers[index].type == 'receiver_bcc_fabrik') {
                        receiver_bcc[index]['id'] = receivers[index].id;
                        receiver_bcc[index]['email'] = receivers[index].receivers;
                      }
                    }

                    const cc_filtered = receiver_cc.filter(el => { return el['id'] !== null && el['id'] !== undefined; })

                    const bcc_filtered = receiver_bcc.filter(el => { return el['id'] !== null && el['id'] !== undefined; })

                    this.selectedReceiversCC = cc_filtered;
                    this.selectedReceiversBCC = bcc_filtered;
                  }
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
    this.getProgramsList();
    this.getStatus();
    this.getUsers();
  },

  mounted() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  }
};
</script>

<!--<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>-->

<style>
fieldset[disabled] .multiselect {
  pointer-events: none;
}

.multiselect__spinner {
  position: absolute;
  right: 1px;
  top: 1px;
  width: 48px;
  height: 35px;
  background: #fff;
  display: block;
}

.multiselect__spinner:before,
.multiselect__spinner:after {
  position: absolute;
  content: "";
  top: 50%;
  left: 50%;
  margin: -8px 0 0 -8px;
  width: 16px;
  height: 16px;
  border-radius: 100%;
  border-color: #c8c8c8 transparent transparent;
  border-style: solid;
  border-width: 2px;
  box-shadow: 0 0 0 1px transparent;
}

.multiselect__spinner:before {
  animation: spinning 2.4s cubic-bezier(0.41, 0.26, 0.2, 0.62);
  animation-iteration-count: infinite;
}

.multiselect__spinner:after {
  animation: spinning 2.4s cubic-bezier(0.51, 0.09, 0.21, 0.8);
  animation-iteration-count: infinite;
}

.multiselect__loading-enter-active,
.multiselect__loading-leave-active {
  transition: opacity 0.4s ease-in-out;
  opacity: 1;
}

.multiselect__loading-enter,
.multiselect__loading-leave-active {
  opacity: 0;
}

.multiselect,
.multiselect__input,
.multiselect__single {
  font-family: inherit;
  font-size: 16px !important;
  touch-action: manipulation;
}

.multiselect {
  box-sizing: content-box;
  display: block;
  position: relative;
  width: 100%;
  min-height: 40px;
  text-align: left;
  color: #35495e;
}

.multiselect * {
  box-sizing: border-box;
}

.multiselect:focus {
  outline: none;
}

.multiselect--disabled {
  background: #ededed;
  pointer-events: none;
  opacity: 0.6;
}

.multiselect--active {
  z-index: 50;
}

.multiselect--active:not(.multiselect--above) .multiselect__current,
.multiselect--active:not(.multiselect--above) .multiselect__input,
.multiselect--active:not(.multiselect--above) .multiselect__tags {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.multiselect--active .multiselect__select {
  transform: rotateZ(180deg);
}

.multiselect--above.multiselect--active .multiselect__current,
.multiselect--above.multiselect--active .multiselect__input,
.multiselect--above.multiselect--active .multiselect__tags {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.multiselect__input,
.multiselect__single {
  position: relative;
  display: inline-block !important;
  min-height: 20px;
  line-height: 20px !important;
  border: none !important;
  border-radius: 5px !important;
  background: #fff;
  background-color: rgb(255, 255, 255);
  padding: 0 0 0 5px;
  width: 0px;
  -webkit-transition: border .1s ease;
  transition: border .1s ease !important;
  -webkit-box-sizing: border-box;
  box-sizing: border-box !important;
  margin-bottom: 8px !important;
  vertical-align: top !important;
  padding: 0px !important;
  left: 0px !important;
  height: auto !important;
}

.multiselect__input::placeholder {
  color: #35495e;
}

.multiselect__tag ~ .multiselect__input,
.multiselect__tag ~ .multiselect__single {
  width: auto;
}

.multiselect__input:hover,
.multiselect__single:hover {
  border-color: #cfcfcf;
}

.multiselect__input:focus,
.multiselect__single:focus {
  border-color: #a8a8a8;
  outline: none;
}

.multiselect__single {
  padding-left: 5px;
  margin-bottom: 8px;
}

.multiselect__tags-wrap {
  display: inline;
}

.multiselect__tags {
  min-height: 50px;
  display: block;
  padding: 10px 40px 0 8px;
  border-radius: 5px;
  border: 2px solid #ccc;
  background: #fff;
  font-size: 14px;
}

.multiselect__tag {
  position: relative;
  display: inline-block;
  padding: 4px 26px 4px 10px;
  border-radius: 5px;
  margin-right: 10px;
  color: #fff;
  line-height: 1;
  background: #b5b4b4;
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
  max-width: 100%;
  text-overflow: ellipsis;
  /*margin-top: 1px;*/
}

.multiselect__tag span {
  font-weight: bold;
}

.multiselect__tag-icon {
  cursor: pointer;
  margin-left: 7px;
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  font-weight: 700;
  font-style: initial;
  width: 22px;
  text-align: center;
  line-height: 22px;
  transition: all 0.2s ease;
  border-radius: 5px;
}

.multiselect__tag-icon:after {
  content: "×";
  color: black;
  font-size: 14px;
}

.multiselect__tag-icon:focus,
.multiselect__tag-icon:hover {
  background: #ccc;
}

.multiselect__tag-icon:focus:after,
.multiselect__tag-icon:hover:after {
  color: red;
}

.multiselect__current {
  line-height: 16px;
  min-height: 40px;
  box-sizing: border-box;
  display: block;
  overflow: hidden;
  padding: 8px 12px 0;
  padding-right: 30px;
  white-space: nowrap;
  margin: 0;
  text-decoration: none;
  border-radius: 5px;
  border: 1px solid #e8e8e8;
  cursor: pointer;
}

.multiselect__select {
  line-height: 16px;
  display: block;
  position: absolute;
  box-sizing: border-box;
  width: 40px;
  height: 38px;
  right: 1px;
  top: 1px;
  padding: 4px 8px;
  margin: 0;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.multiselect__select:before {
  position: relative;
  right: 0;
  top: 65%;
  color: #999;
  margin-top: 4px;
  border-style: solid;
  border-width: 5px 5px 0 5px;
  border-color: #999999 transparent transparent transparent;
  content: "";
}

.multiselect__placeholder {
  color: #adadad;
  display: inline-block;
  margin-bottom: 10px;
  padding-top: 5px;
}

.multiselect--active .multiselect__placeholder {
  display: none;
}

.multiselect__content-wrapper {
  position: absolute;
  display: block;
  background: #fff;
  width: 100%;
  max-height: 240px;
  overflow: auto;
  border: 1px solid #e8e8e8;
  border-top: none;
  border-bottom-left-radius: 5px;
  border-bottom-right-radius: 5px;
  z-index: 50;
  -webkit-overflow-scrolling: touch;
}

.multiselect__content {
  list-style: none;
  display: inline-block;
  padding: 0;
  margin: 0;
  min-width: 100%;
  vertical-align: top;
}

.multiselect--above .multiselect__content-wrapper {
  bottom: 100%;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  border-bottom: none;
  border-top: 1px solid #e8e8e8;
}

.multiselect__content::webkit-scrollbar {
  display: none;
}

.multiselect__element {
  display: block;
}

.multiselect__option {
  display: block;
  padding: 12px;
  min-height: 40px;
  line-height: 16px;
  text-decoration: none;
  text-transform: none;
  vertical-align: middle;
  position: relative;
  cursor: pointer;
  white-space: nowrap;
}

.multiselect__option:after {
  top: 0;
  right: 0;
  position: absolute;
  line-height: 40px;
  padding-right: 12px;
  padding-left: 20px;
  font-size: 13px;
}

.multiselect__option--highlight {
  background: #41b883;
  outline: none;
  color: white;
}

.multiselect__option--highlight:after {
  content: attr(data-select);
  background: #41b883;
  color: white;
}

.multiselect__option--selected {
  background: #f3f3f3;
  color: #35495e;
  font-weight: bold;
}

.multiselect__option--selected:after {
  content: attr(data-selected);
  color: silver;
}

.multiselect__input:focus {
  box-shadow: unset !important;
}

.multiselect__input:active {
  box-shadow: unset !important;
}

.multiselect__input:hover {
  box-shadow: unset !important;
}

.multiselect__option--selected.multiselect__option--highlight {
  background: #ff6a6a;
  color: #fff;
}

.multiselect__option--selected.multiselect__option--highlight:after {
  background: #ff6a6a;
  content: attr(data-deselect);
  color: #fff;
}

.multiselect--disabled .multiselect__current,
.multiselect--disabled .multiselect__select {
  background: #ededed;
  color: #a6a6a6;
}

.multiselect__option--disabled {
  background: #ededed !important;
  color: #a6a6a6 !important;
  cursor: text;
  pointer-events: none;
}

.multiselect__option--group {
  background: #ededed;
  color: #35495e;
}

.multiselect__option--group.multiselect__option--highlight {
  background: #35495e;
  color: #fff;
}

.multiselect__option--group.multiselect__option--highlight:after {
  background: #35495e;
}

.multiselect__option--disabled.multiselect__option--highlight {
  background: #dedede;
}

.multiselect__option--group-selected.multiselect__option--highlight {
  background: #ff6a6a;
  color: #fff;
}

.multiselect__option--group-selected.multiselect__option--highlight:after {
  background: #ff6a6a;
  content: attr(data-deselect);
  color: #fff;
}

.multiselect-enter-active,
.multiselect-leave-active {
  transition: all 0.15s ease;
}

.multiselect-enter,
.multiselect-leave-active {
  opacity: 0;
}

.multiselect__strong {
  margin-bottom: 8px;
  line-height: 20px;
  display: inline-block;
  vertical-align: top;
}

*[dir="rtl"] .multiselect {
  text-align: right;
}

*[dir="rtl"] .multiselect__select {
  right: auto;
  left: 1px;
}

*[dir="rtl"] .multiselect__tags {
  padding: 8px 8px 0px 40px;
}

*[dir="rtl"] .multiselect__content {
  text-align: right;
}

*[dir="rtl"] .multiselect__option:after {
  right: auto;
  left: 0;
}

*[dir="rtl"] .multiselect__clear {
  right: auto;
  left: 12px;
}

*[dir="rtl"] .multiselect__spinner {
  right: auto;
  left: 1px;
}

@keyframes spinning {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(2turn);
  }
}
</style>
