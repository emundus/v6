<template>
  <div class="emails__add-email em-mt-24">
    <notifications
        group="foo-velocity"
        position="bottom left"
        animation-type="velocity"
        :speed="500"
        :classes="'vue-notification-custom'"
    />
    <div>
      <form @submit.prevent="submit" class="fabrikForm emundus-form">
        <div>
          <div class="mb-4">
            <h1>{{ translate('COM_EMUNDUS_ONBOARD_ADD_EMAIL') }}</h1>
            <span class="em-red-500-color em-mb-8">{{ translations.RequiredFieldsIndicate }}</span>
          </div>

          <div>
            <div class="em-mb-16">
              <label>{{ translations.emailName }} <span style="color: #E5283B">*</span></label>
              <input
                  type="text"
                  class="w-full"
                  v-model="form.subject"
                  :class="{ 'is-invalid': errors.subject}"
              />
            </div>
            <span v-if="errors.subject" class="em-red-500-color mb-2">
              <span class="em-red-500-color">{{ translations.SubjectRequired }}</span>
            </span>

            <div class="mb-4">
              <label>{{ translations.emailBody }} <span style="color: #E5283B">*</span></label>
              <editor-quill
                  style="height: 30em"
                  :text="form.message"
                  v-model="form.message"
                  :enable_variables="true"
                  :placeholder="translations.EmailResume"
                  :id="'email'"
                  :key="dynamicComponent"
                  :class="{ 'is-invalid': errors.message}"
              >
              </editor-quill>
              <div class="mt-12">
                <a href="component/emundus/?view=export_select_columns&format=html&layout=all_programs&Itemid=1173"
                   class="em-main-500-color em-hover-main-600"
                   target="_blank">{{ translate('COM_EMUNDUS_EMAIL_SHOW_TAGS') }}</a>
              </div>
            </div>
            <p v-if="errors.message" class="em-red-500-color mb-2">
              <span class="em-red-500-color">{{ translations.BodyRequired }}</span>
            </p>

            <div class="form-group">
              <label>{{ translations.emailCategory }}</label>
              <incremental-select
                  v-if="categories.length > 0"
                  :options="this.categoriesList"
                  :defaultValue="incSelectDefaultValue"
                  :locked="mode != 'create'"
                  @update-value="updateCategorySelectedValue"
              >
              </incremental-select>
            </div>
          </div>
        </div>

        <hr/>

        <div>
          <div class="flex items-center mb-4 gap-1">
            <h3 class="cursor-pointer em-mb-0-important" @click="displayAdvanced">{{ translations.Advanced }}</h3>
            <button :title="translations.Advanced" type="button" class="em-transparent-button flex flex-col"
                    @click="displayAdvanced" v-show="!displayAdvancedParameters">
              <span class="material-icons-outlined em-main-500-color">add_circle_outline</span>
            </button>
            <button :title="translations.Advanced" type="button" @click="displayAdvanced"
                    class="em-transparent-button flex flex-col" v-show="displayAdvancedParameters">
              <span class="material-icons-outlined em-main-500-color">remove_circle_outline</span>
            </button>
          </div>
          <div id="email-advanced-parameters" v-if="displayAdvancedParameters">
            <div class="form-group mb-4">
              <label>{{ translate('COM_EMUNDUS_ONBOARD_ADDEMAIL_SENDER_EMAIL') }}</label>
              <span>{{ email_sender }}</span>
            </div>

            <div class="form-group mb-4">
              <label>{{ translations.receiverName }}</label>
              <input
                  type="text"
                  class="w-full fabrikinput"
                  v-model="form.name"
              />
            </div>

            <div class="form-group mb-4">
              <label>{{ translations.emailAddress }}</label>
              <input
                  type="text"
                  class="w-full fabrikinput"
                  v-model="form.emailfrom"
                  placeholder="reply-to@tchooz.io"
              />
              <p class="em-font-size-12 em-neutral-700-color">
                {{ translate('COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESTIP') }}</p>
            </div>

            <div class="form-group mb-4" id="receivers_cc">
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
                  @tag="addNewCC"
                  :close-on-select="false"
                  :clear-on-select="false"
              ></multiselect>
            </div>

            <!-- Email -- BCC (in form of email adress or fabrik element -->
            <div class="form-group mb-4" id="receivers_bcc">
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
                  @tag="addNewBCC"
                  :close-on-select="false"
                  :clear-on-select="false">
              </multiselect>
            </div>

            <!-- Email -- Associated letters (in form of email adress or fabrik element -->
            <div class="form-group mb-4" id="attached_letters" v-if="attached_letters">
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
            <div class="form-group mb-4" v-if="tags">
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
            <div class="form-group mb-4">
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

        <div class="flex justify-between mt-4">
          <button
              type="button"
              class="em-secondary-button em-w-auto"
              onclick="history.back()">
            {{ translations.retour }}
          </button>
          <button type="submit" class="em-primary-button em-w-auto">
            {{ translations.continuer }}
          </button>
        </div>
      </form>
    </div>

    <div class="em-page-loader" v-if="loading || submitted"></div>
  </div>
</template>

<script>
import Autocomplete from "../components/autocomplete";
import axios from "axios";
import EditorQuill from "../components/editorQuill";
import Multiselect from 'vue-multiselect';
import IncrementalSelect from "@/components/IncrementalSelect.vue";

const qs = require("qs");

export default {
  name: "addEmail",

  components: {
    IncrementalSelect,
    EditorQuill,
    Autocomplete,
    Multiselect
  },
  props: {
    mode: {
      type: String,
      default: "create"
    }
  },

  data: () => ({
    email: 0,
    actualLanguage: '',

    langue: 0,

    dynamicComponent: false,
    displayAdvancedParameters: false,

    translations: {
      AddEmail: "COM_EMUNDUS_ONBOARD_ADD_EMAIL",
      Advanced: "COM_EMUNDUS_ONBOARD_ADVANCED_CUSTOMING",
      Informations: "COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION",
      Trigger: "COM_EMUNDUS_ONBOARD_EMAIL_TRIGGER",
      emailType: "COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE",
      emailCategory: "COM_EMUNDUS_ONBOARD_CHOOSECATEGORY",
      retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
      continuer: "COM_EMUNDUS_ONBOARD_ADD_CONTINUER",
      emailName: "COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME",
      emailBody: "COM_EMUNDUS_ONBOARD_ADDEMAIL_BODY",
      receiverName: "COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER",
      emailAddress: "COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS",
      EmailResume: "COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME",
      RequiredFieldsIndicate: "COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE",
      EmailType: "COM_EMUNDUS_ONBOARD_EMAILTYPE",
      SubjectRequired: "COM_EMUNDUS_ONBOARD_SUBJECT_REQUIRED",
      BodyRequired: "COM_EMUNDUS_ONBOARD_BODY_REQUIRED",
      Program: "COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM",
      Model: "COM_EMUNDUS_ONBOARD_TRIGGERMODEL",
      ModelRequired: "COM_EMUNDUS_ONBOARD_TRIGGERMODEL_REQUIRED",
      Status: "COM_EMUNDUS_ONBOARD_TRIGGERSTATUS",
      StatusRequired: "COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED",
      Target: "COM_EMUNDUS_ONBOARD_TRIGGERTARGET",
      TargetRequired: "COM_EMUNDUS_ONBOARD_TRIGGERTARGET_REQUIRED",
      Administrators: "COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS",
      Evaluators: "COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS",
      Candidates: "COM_EMUNDUS_ONBOARD_PROGRAM_CANDIDATES",
      DefinedUsers: "COM_EMUNDUS_ONBOARD_PROGRAM_DEFINED_USERS",
      ChooseUsers: "COM_EMUNDUS_ONBOARD_TRIGGER_CHOOSE_USERS",
      UsersRequired: "COM_EMUNDUS_ONBOARD_TRIGGER_USERS_REQUIRED",
      Search: "COM_EMUNDUS_ONBOARD_SEARCH_USERS",
      TheCandidate: "COM_EMUNDUS_ONBOARD_THE_CANDIDATE",
      Manual: "COM_EMUNDUS_ONBOARD_MANUAL",
      Actions: "COM_EMUNDUS_ONBOARD_TRIGGER_ACTIONS",
      Tags: "COM_EMUNDUS_ONBOARD_EMAIL_TAGS",
      DocumentType: "COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT",

      /// Letters field
      Letters: "COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT",
      LettersPlaceHolder: "COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_DOCUMENT",

      /// Receiver CC field
      ReceiversCC: "COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS",
      ReceiversCCPlaceHolder: "COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS_PLACEHOLDER",

      /// Receiver BCC field
      ReceiversBCC: "COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS",
      ReceiversBCCPlaceHolder: "COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS_PLACEHOLDER",

      /// Receiver Tooltips
      CopiesTooltips: "COM_EMUNDUS_ONBOARD_CC_BCC_TOOLTIPS",

      /// Selected Action Tags
      TagsPlaceHolder: "COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_TAGS",

      /// Candidat Attachments (title, placeholder)
      CandidateAttachments: "COM_EMUNDUS_ONBOARD_CANDIDAT_ATTACHMENTS",
      CandidateAttachmentsPlaceholder: "COM_EMUNDUS_ONBOARD_PLACEHOLDER_CANDIDAT_ATTACHMENTS",
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
    selectedCategory: 0,

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
    loading: false,

    selectedReceiversCC: [],
    selectedReceiversBCC: [],
    selectedLetterAttachments: [],

    receivers_cc: [],
    receivers_bcc: [],
    attached_letters: [],

    action_tags: [],
    candidate_attachments: [],
    email_sender: '',
  }),
  created() {
    this.loading = true;

    this.getEmailSender();
    this.getAllAttachments();
    this.getAllTags();
    this.getAllDocumentLetter();

    this.actualLanguage = this.$store.getters['global/shortLang'];

    axios.get("index.php?option=com_emundus&controller=email&task=getemailcategories")
        .then(rep => {
          this.categories = rep.data.data;
          this.email = this.$store.getters['global/datas'].email.value;
          if (typeof this.email !== 'undefined' && this.email !== 0 && this.email !== '') {
            this.getEmailById(this.email);
          } else {
            this.dynamicComponent = true;
            this.loading = false;
          }
        }).catch(e => {
      console.log(e);
    });
    setTimeout(() => {
      this.enableVariablesTip();
    }, 2000);
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
            this.loading = false;
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

      const cc_filtered = receiver_cc.filter(el => {
        return el['id'] !== null && el['id'] !== undefined;
      })
      const bcc_filtered = receiver_bcc.filter(el => {
        return el['id'] !== null && el['id'] !== undefined;
      })

      this.selectedReceiversCC = cc_filtered;
      this.selectedReceiversBCC = bcc_filtered;
    },
    displayAdvanced() {
      this.displayAdvancedParameters = !this.displayAdvancedParameters;
    },
    addNewCC(newCC) {
      const tag = {
        email: newCC,
        id: newCC.substring(0, 2) + Math.floor((Math.random() * 10000000))
      }
      this.receivers_cc.push(tag);
      this.selectedReceiversCC.push(tag);
    },

    /// add new BCC
    addNewBCC(newBCC) {
      const tag = {
        email: newBCC,
        id: newBCC.substring(0, 2) + Math.floor((Math.random() * 10000000))
      }
      this.receivers_bcc.push(tag);
      this.selectedReceiversBCC.push(tag);
    },

    /// get all users
    getAllUsers: function () {
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
        url: "index.php?option=com_emundus&controller=programme&task=getallprogram",
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
      axios.get("index.php?option=com_emundus&controller=programme&task=getuserswithoutapplicants")
          .then(response => {
            this.users = response.data.data;
          });
    },
    searchUserByTerm() {
      axios.get("index.php?option=com_emundus&controller=programme&task=searchuserbytermwithoutapplicants&term=" + this.searchTerm)
          .then(response => {
            this.users = response.data.data;
          });
    },
    getEmailSender() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getemailsender")
          .then(response => {
            this.email_sender = response.data.data;
          });
    },

    addTrigger() {
      if (this.trigger.program != null) {
        this.triggered = true;
      } else {
        this.triggered = false;
      }
    },
    selectAllUsers() {
      this.users.forEach(element => {
        if (!this.selectall) {
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

      if (this.form.subject == "") {
        this.errors.subject = true;
        return 0;
      }

      if (this.form.message == "") {
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
          data: qs.stringify({
            body: this.form,
            code: this.email,
            selectedReceiversCC: this.selectedReceiversCC,
            selectedReceiversBCC: this.selectedReceiversBCC,
            selectedLetterAttachments: this.selectedLetterAttachments,
            selectedCandidateAttachments: this.selectedCandidateAttachments,
            selectedTags: this.selectedTags
          })
        }).then(() => {
          this.redirectJRoute('index.php?option=com_emundus&view=emails');
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
          data: qs.stringify({
            body: this.form,
            selectedReceiversCC: this.selectedReceiversCC,
            selectedReceiversBCC: this.selectedReceiversBCC,
            selectedLetterAttachments: this.selectedLetterAttachments,
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
            this.redirectJRoute('index.php?option=com_emundus&view=emails');
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
      if (!this.enableTip) {
        this.enableTip = true;
        this.tip();
      }
    },

    redirectJRoute(link) {
      window.location.href = link;
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
      this.$notify({group, clean: true});
    },

    /// get all tags
    getAllTags: function () {
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

    getAllDocumentLetter: function () {
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

    getAllAttachments: function () {
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

    updateCategorySelectedValue(category) {
      if (category.label) {
        this.form.category = category.label;
      } else {
        this.selectedCategory = null;
        this.form.category = '';
      }
    },
  },

  computed: {
    categoriesList() {
      return this.categories.map((category, index) => {
        return {
          id: index + 1,
          label: category
        };
      });
    },

    incSelectDefaultValue() {
      let defaultValue = null;
      if (this.form && (this.form.category)) {
        this.categories.forEach((category, index) => {
          if (category === this.form.category) {
            defaultValue = index + 1;
          }
        });
      }
      return defaultValue;
    },
  }
};
</script>

<style scoped>
.emails__add-email {
  width: 100%;
  margin-left: auto;
}
</style>
