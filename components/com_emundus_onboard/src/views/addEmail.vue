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
            <img src="/images/emundus/menus/email.png" srcset="/images/emundus/menus/email.png" class="tchooz-icon-title" alt="email">
            <h1 class="tchooz-section-titles">{{translations.AddEmail}}</h1>
          </div>
        </div>
      </div>
      <form id="program-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required mb-1">{{translations.RequiredFieldsIndicate}}</p>
          <div class="heading-form">
            <h2 class="heading">{{ translations.Informations }}</h2>
          </div>
          <div class="w-form">
            <div class="form-group">
              <label>{{translations.emailName}} <span style="color: #E5283B">*</span></label>
              <input
                type="text"
                class="form__input field-general w-input"
                v-model="form.subject"
                :class="{ 'is-invalid': errors.subject}"
              />
            </div>
            <p v-if="errors.subject" class="error col-md-12 mb-2">
              <span class="error">{{translations.SubjectRequired}}</span>
            </p>

            <div class="form-group controls forms-emails-editor">
              <label>{{translations.emailBody}} <span style="color: #E5283B">*</span></label>
              <editor
                v-if="dynamicComponent"
                v-model="form.message"
                :id="'email'"
                :class="{ 'is-invalid': errors.message}"
                :height="'30em'"
                :text="form.message"
                :lang="actualLanguage"
                :enable_variables="true"
                :placeholder="translations.EmailResume"
              >
              </editor>
            </div>
            <p v-if="errors.message" class="error col-md-12 mb-2">
              <span class="error">{{translations.BodyRequired}}</span>
            </p>
          </div>
        </div>

        <div class="divider"></div>
        <div class="sous-container last-container">
          <div class="heading-form d-flex">
            <h2 class="heading mb-0">{{ translations.Advanced }}</h2>
            <button :title="translations.Advanced" type="button" @click="displayAdvanced" class="buttonAddDoc" v-show="!displayAdvancedParameters">
              <em class="fas fa-plus"></em>
            </button>
            <button :title="translations.Advanced" type="button" @click="displayAdvanced" class="buttonAddDoc" v-show="displayAdvancedParameters">
              <em class="fas fa-minus"></em>
            </button>
          </div>
          <div v-if="displayAdvancedParameters">
          <div class="form-group">
            <label>{{translations.receiverName}}</label>
            <input
              type="text"
              class="form__input field-general w-input"
              v-model="form.name"
            />
          </div>

          <div class="form-group">
            <label>{{translations.emailAddress}}</label>
            <input
              type="text"
              class="form__input field-general w-input"
              v-model="form.emailfrom"
            />
          </div>

          <div class="form-group">
            <label>{{ translations.emailCategory }}</label>
            <autocomplete
              @searched="onSearchCategory"
              :items="this.categories"
              :year="this.form.category"
            />
          </div>

          <div class="form-group" id="receivers_cc">
            <label>{{ translations.ReceiversCC }}</label>
            <multiselect
              v-model="selectedReceiversCC"
              label="email"
              track-by="email"
              :options="receivers_cc"
              :multiple="true"
              :searchable="true"
              :taggable="true"
              select-label=""
              selected-label=""
              deselect-label=""
              :placeholder="translations.ReceiversCCPlaceHolder"
              @tag="addNewCC"
              :close-on-select="false"
              :clear-on-select="false"
            ></multiselect>
          </div>

          <!-- Email -- BCC (in form of email adress or fabrik element -->
          <div class="form-group" id="receivers_bcc">
            <label>{{ translations.ReceiversBCC }}</label>
            <multiselect
              v-model="selectedReceiversBCC"
              label="email"
              track-by="email"
              :options="receivers_bcc"
              :multiple="true"
              :searchable="true"
              :taggable="true"
              select-label=""
              selected-label=""
              deselect-label=""
              :placeholder="translations.ReceiversBCCPlaceHolder"
              @tag="addNewBCC"
              :close-on-select="false"
              :clear-on-select="false">
            </multiselect>
          </div>

          <!-- Email -- Associated letters (in form of email adress or fabrik element -->
          <div class="form-group" id="attached_letters" v-if="attached_letters">
            <label>{{ translations.Letters }}</label>
            <multiselect
              v-model="selectedLetterAttachments"
              label="value"
              track-by="id"
              :options="attached_letters"
              :multiple="true"
              :taggable="true"
              select-label=""
              selected-label=""
              deselect-label=""
              :placeholder="translations.LettersPlaceHolder"
              :close-on-select="false"
              :clear-on-select="false"
            ></multiselect>
          </div>

          <!-- Email -- Action tags -->
          <div class="form-group" v-if="tags">
            <label>{{ translations.Tags }}</label>
            <multiselect
                v-model="selectedTags"
                label="label"
                track-by="id"
                :options="action_tags"
                :multiple="true"
                :taggable="true"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="translations.TagsPlaceHolder"
                :close-on-select="false"
                :clear-on-select="false"
            ></multiselect>
          </div>

          <!-- Email -- Candidat attachments -->
          <div class="form-group" id="">
            <label>{{ translations.CandidateAttachments }}</label>
            <multiselect
                v-model="selectedCandidateAttachments"
                label="value"
                track-by="id"
                :options="candidate_attachments"
                :multiple="true"
                :taggable="true"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="translations.CandidateAttachmentsPlaceholder"
                :close-on-select="false"
                :clear-on-select="false"
            ></multiselect>
          </div>
          </div>
        </div>
        <div class="divider"></div>
        <div class="sous-container last-container" v-if="email == ''">
          <div class="heading-form">
            <h2 class="heading">{{ translations.Trigger }}</h2>
          </div>

          <div class="form-group">
            <label>{{translations.Program}}</label>
            <select v-model="trigger.program" class="dropdown-toggle w-select" @change="addTrigger">
              <option :value="null"></option>
              <option v-for="program in programs" :key="'program-' + program.id" :value="program.id">{{program.label}}</option>
            </select>
          </div>

          <div v-if="triggered">
            <div class="form-group">
              <label>{{translations.Actions}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.action_status" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.action_status}">
                <option value="to_current_user">{{translations.TheCandidate}}</option>
                <option value="to_applicant">{{translations.Manual}}</option>
              </select>
              <p v-if="errors.trigger.action_status" class="error">
                <span class="error">{{translations.StatusRequired}}</span>
              </p>
            </div>

            <div class="form-group">
              <label>{{translations.Status}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.status" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.status}">
                <option v-for="statu in status" :key="'status-' +statu.step" :value="statu.step">{{statu.value}}</option>
              </select>
              <p v-if="errors.trigger.status" class="error">
                <span class="error">{{translations.StatusRequired}}</span>
              </p>
            </div>

            <div class="form-group">
              <label>{{translations.Target}}<span style="color: #E5283B">*</span></label>
              <select v-model="trigger.target" class="dropdown-toggle w-select" :class="{ 'is-invalid': errors.trigger.target}">
                <option value="5">{{translations.Administrators}}</option>
                <option value="6">{{translations.Evaluators}}</option>
                <option value="1000">{{translations.Candidates}}</option>
              </select>
              <p v-if="errors.trigger.target" class="error">
                <span class="error">{{translations.TargetRequired}}</span>
              </p>
            </div>
            <div class="form-group" v-if="trigger.target == 0" style="align-items: baseline">
              <label>{{translations.ChooseUsers}}<span style="color: #E5283B">*</span> :</label>
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
                  {{translations.SelectAll}}
                </label>
              </div>
              <div class="users-block" :class="{ 'is-invalid': errors.trigger.selectedUsers}">
                <div v-for="user in users" :key="'user-' + user.id" class="user-item">
                  <input type="checkbox" class="form-check-input bigbox" v-model="selectedUsers[user.id]">
                  <div class="ml-10px">
                    <p>{{user.name}}</p>
                    <p>{{user.email}}</p>
                  </div>
                </div>
              </div>
              <p v-if="errors.trigger.selectedUsers" class="error">
                <span class="error">{{translations.UsersRequired}}</span>
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
                  onclick="history.back()">
                {{ translations.retour }}
              </button>
              <button type="submit" class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green">
                {{ translations.continuer }}
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
import Multiselect from 'vue-multiselect';
import {global} from "../store/global";

const qs = require("qs");

export default {
  name: "addEmail",

  components: {
    Editor,
    Autocomplete,
    Multiselect
  },

  data: () => ({
    email: 0,
    actualLanguage: '',

    langue: 0,

    dynamicComponent: false,
    displayAdvancedParameters: false,

    translations:{
      AddEmail: this.translate("COM_EMUNDUS_ONBOARD_ADD_EMAIL"),
      Advanced: this.translate("COM_EMUNDUS_ONBOARD_ADVANCED_CUSTOMING"),
      Informations: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION"),
      Trigger: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_TRIGGER"),
      emailType: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE"),
      emailCategory: this.translate("COM_EMUNDUS_ONBOARD_CHOOSECATEGORY"),
      retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      emailName: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME"),
      emailBody: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_BODY"),
      receiverName: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER"),
      emailAddress: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS"),
      EmailResume: this.translate("COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME"),
      RequiredFieldsIndicate: this.translate("COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE"),
      EmailType: this.translate("COM_EMUNDUS_ONBOARD_EMAILTYPE"),
      SubjectRequired: this.translate("COM_EMUNDUS_ONBOARD_SUBJECT_REQUIRED"),
      BodyRequired: this.translate("COM_EMUNDUS_ONBOARD_BODY_REQUIRED"),
      Program: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      Model: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERMODEL"),
      ModelRequired: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERMODEL_REQUIRED"),
      Status: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS"),
      StatusRequired: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED"),
      Target: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERTARGET"),
      TargetRequired: this.translate("COM_EMUNDUS_ONBOARD_TRIGGERTARGET_REQUIRED"),
      Administrators: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS"),
      Evaluators: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS"),
      Candidates: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_CANDIDATES"),
      DefinedUsers: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_DEFINED_USERS"),
      ChooseUsers: this.translate("COM_EMUNDUS_ONBOARD_TRIGGER_CHOOSE_USERS"),
      UsersRequired: this.translate("COM_EMUNDUS_ONBOARD_TRIGGER_USERS_REQUIRED"),
      Search: this.translate("COM_EMUNDUS_ONBOARD_SEARCH_USERS"),
      TheCandidate: this.translate("COM_EMUNDUS_ONBOARD_THE_CANDIDATE"),
      Manual: this.translate("COM_EMUNDUS_ONBOARD_MANUAL"),
      Actions: this.translate("COM_EMUNDUS_ONBOARD_TRIGGER_ACTIONS"),
      Tags: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_TAGS"),
      DocumentType: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT"),

      /// Letters field
      Letters: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT"),
      LettersPlaceHolder: this.translate("COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_DOCUMENT"),

      /// Receiver CC field
      ReceiversCC: this.translate("COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS"),
      ReceiversCCPlaceHolder: this.translate("COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS_PLACEHOLDER"),

      /// Receiver BCC field
      ReceiversBCC: this.translate("COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS"),
      ReceiversBCCPlaceHolder: this.translate("COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS_PLACEHOLDER"),

      /// Receiver Tooltips
      CopiesTooltips: this.translate("COM_EMUNDUS_ONBOARD_CC_BCC_TOOLTIPS"),

      /// Selected Action Tags
      TagsPlaceHolder: this.translate("COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_TAGS"),

      /// Candidat Attachments (title, placeholder)
      CandidateAttachments: this.translate("COM_EMUNDUS_ONBOARD_CANDIDAT_ATTACHMENTS"),
      CandidateAttachmentsPlaceholder: this.translate("COM_EMUNDUS_ONBOARD_PLACEHOLDER_CANDIDAT_ATTACHMENTS"),
    },

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
    selectedCandidateAttachments: [],

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
    selectedLetterAttachments: [],

    receivers_cc: [],
    receivers_bcc: [],
    attached_letters: [],

    action_tags: [],
    candidate_attachments: [],
  }),
  created() {
    this.$parent.loading = true;

    this.getAllAttachments();
    this.getAllTags();
    this.getAllDocumentLetter();

    this.actualLanguage = global.getters.actualLanguage;

    axios.get("index.php?option=com_emundus&controller=email&task=getemailcategories")
      .then(rep => {
        this.categories = rep.data.data;
        this.email = global.getters.datas.email.value;
        if (typeof this.email !== 'undefined' && this.email !== 0 && this.email !== '') {
          this.getEmailById(this.email);
        } else {
          this.dynamicComponent = true;
          this.$parent.loading = false;
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
    if (this.actualLanguage === "en") {
      this.langue = 1;
    }
  },
  methods: {
    getEmailById() {
      axios.get(`index.php?option=com_emundus&controller=email&task=getemailbyid&id=${this.email}`)
      .then((resp) => {
        if (resp.data.data === false || resp.data.status == 0) {
          this.runError(undefined, resp.data.msg);
          return;
        }

        this.form = resp.data.data.email;
        this.dynamicComponent = true;

        this.selectedLetterAttachments = resp.data.data.letter_attachment ? resp.data.data.letter_attachment : [];
        this.selectedCandidateAttachments = resp.data.data.candidate_attachment ? resp.data.data.candidate_attachment : [];
        this.selectedTags = resp.data.data.tags ? resp.data.data.tags : [];

        if (resp.data.data.receivers !== null && resp.data.data.receivers !== undefined && resp.data.data.receivers !== "") {
          this.setEmailReceivers(resp.data.data.receivers);
        }
        this.$parent.loading = false;
      }).catch(e => {
        console.log(e);
        this.runError(undefined, e.data.msg);
      });
    },
    setEmailReceivers(receivers) {
      let receiver_cc = [];
      let receiver_bcc = [];
      for (let index = 0; index < receivers.length; index++) {
        receiver_cc[index] = {};
        receiver_bcc[index] = {};
        if (receivers[index].type === 'receiver_cc_email' || receivers[index].type === 'receiver_cc_fabrik') {
          receiver_cc[index]['id'] = receivers[index].id;
          receiver_cc[index]['email'] = receivers[index].receivers;
        } else if (receivers[index].type === 'receiver_bcc_email' || receivers[index].type === 'receiver_bcc_fabrik') {
          receiver_bcc[index]['id'] = receivers[index].id;
          receiver_bcc[index]['email'] = receivers[index].receivers;
        }
      }

      const cc_filtered = receiver_cc.filter(el => { return el['id'] !== null && el['id'] !== undefined; })
      const bcc_filtered = receiver_bcc.filter(el => { return el['id'] !== null && el['id'] !== undefined; })

      this.selectedReceiversCC = cc_filtered;
      this.selectedReceiversBCC = bcc_filtered;
    },
    displayAdvanced() {
      this.displayAdvancedParameters = !this.displayAdvancedParameters;
    },
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
        url: 'index.php?option=com_emundus&controller=settings&task=getallusers',
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
        url: "index.php?option=com_emundus&controller=program&task=getallprogram",
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
      axios.get("index.php?option=com_emundus&controller=email&task=getstatus")
        .then(response => {
          this.status = response.data.data;
        });
    },
    getUsers() {
      axios.get("index.php?option=com_emundus&controller=program&task=getuserswithoutapplicants")
        .then(response => {
          this.users = response.data.data;
        });
    },
    searchUserByTerm() {
      axios.get("index.php?option=com_emundus&controller=program&task=searchuserbytermwithoutapplicants&term=" + this.searchTerm)
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

      if (this.form.subject == ""){
        this.errors.subject = true;
        return 0;
      }

      if (this.form.message == ""){
        this.errors.message = true;
        return 0;
      }

      if (this.trigger.program != null) {
        if (this.trigger.action_status == null) {
          this.errors.trigger.action_status = true;
          return 0;
        }

        if (this.trigger.status == null) {
          this.errors.trigger.status = true;
          return 0;
        }

        if (this.trigger.target == null) {
          this.errors.trigger.target = true;
          return 0;
        } else if (this.trigger.target == 0) {
          if (this.selectedUsers.length === 0) {
            this.errors.trigger.selectedUsers = true;
            return 0;
          }
        }
      }
      this.submitted = true;

      if (this.email !== "") {
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=email&task=updateemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form,
            code: this.email,
            selectedReceiversCC: this.selectedReceiversCC,
            selectedReceiversBCC: this.selectedReceiversBCC,
            selectedLetterAttachments:this.selectedLetterAttachments,
            selectedCandidateAttachments: this.selectedCandidateAttachments,
            selectedTags: this.selectedTags
          })
        }).then(() => {
          this.redirectJRoute('index.php?option=com_emundus_onboard&view=email');
        }).catch(error => {
          console.log(error);
        });
      } else {
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=email&task=createemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form,
            selectedReceiversCC: this.selectedReceiversCC,
            selectedReceiversBCC: this.selectedReceiversBCC,
            selectedLetterAttachments:this.selectedLetterAttachments,
            selectedCandidateAttachments: this.selectedCandidateAttachments,
            selectedTags: this.selectedTags
          })
        }).then(response => {
          this.trigger.model = response.data.data;
          axios({
            method: "post",
            url: 'index.php?option=com_emundus&controller=email&task=createtrigger',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              trigger: this.trigger,
              users: this.selectedUsers
            })
          }).then(() => {
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
        url: "index.php?option=com_emundus&controller=settings&task=redirectjroute",
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
    tip: function () {
      this.show(
        "foo-velocity",
        this.translate("COM_EMUNDUS_ONBOARD_VARIABLESTIP") + ' <strong style="font-size: 16px">/</strong>',
        this.translate("COM_EMUNDUS_ONBOARD_TIP"),
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
        url: 'index.php?option=com_emundus&controller=settings&task=gettags',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        this.action_tags = response.data.data;
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
        this.attached_letters = response.data.documents;
      }).catch(error => {
        console.log(error);
      })
    },

    getAllAttachments: function() {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus&controller=messages&task=getallattachments',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        this.candidate_attachments = response.data.attachments;
      }).catch(error => {
        console.log(error);
      })
    },
  },
};
</script>

<style>

</style>
