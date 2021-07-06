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

          <!-- Email -- tags         -->
          <div class="form-group">
            <label>{{ Tags }}</label>
            <select v-model="selectedTags" class="dropdown-toggle w-select" multiple>
              <option v-for="tag in tags" :value="tag.id" :id="'tag_'+tag.id" @dblclick="unselectTag(tag.id)">{{tag.label}}</option>
            </select>
          </div>

          <!-- Email -- document type         -->
          <div class="form-group">
            <label>{{ DocumentType }}</label>
            <select v-model="selectedDocuments" class="dropdown-toggle w-select" multiple>
              <option v-for="document in documents" :value="document.id" :id="'document_'+document.id" @dblclick="unselectDocument(document.id)">{{document.value}}</option>
            </select>
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
  import JQuery from 'jquery';

  window.$ = JQuery;

  const qs = require("qs");

  export default {
    name: "addEmail",

    components: {
      Tasks,
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
      submitted: false
    }),

    methods: {
      unselectTag: function(element) {
        $('#tag_' + element).prop('selected', false); /// set unselected tag
      },

      unselectDocument: function(element) {
        $('#document_' + element).prop('selected', false); /// set unselected document
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
            data: qs.stringify({ body: this.form, code: this.email, tags: this.selectedTags, documents: this.selectedDocuments })
          }).then(response => {
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
            data: qs.stringify({ body: this.form, tags: this.selectedTags, documents: this.selectedDocuments }),
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
          let _documents = response.data.documents;
          this.documents = _documents;
        }).catch(error => {
          console.log(error);
        })
      }
    },

    created() {
      this.getAllTags();
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

                            // bind selected tags
                            this.dynamicComponent = true;

                            if(resp.data.data.tags !== null && resp.data.data.tags !== undefined && resp.data.data.tags !== "") {
                              let _tags = resp.data.data.tags;
                              _tags.forEach((tag, index) => {
                                this.selectedTags[index] = tag.id;
                              })
                            }

                            if(resp.data.data.attachments !== null && resp.data.data.attachments !== undefined && resp.data.data.attachments !== "") {
                              let _documents = resp.data.data.attachments;
                              _documents.forEach((document, index) => {
                                this.selectedDocuments[index] = document.id;
                              })
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

<style scoped>
</style>
