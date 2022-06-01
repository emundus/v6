<template>
  <div :id="'formBuilder'" class="em-w-100 em-h-100">
    <modal
        :name="'formBuilder'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
    >
      <header class="em-flex-row em-flex-space-between">
        <div class="right-actions">
          <span
              id="go-back"
              class="material-icons em-p-12-16 em-pointer"
              @click="goTo('/formulaires')"
          >
            navigate_before
          </span>
        </div>
          <span
            class="em-font-size-14  em-font-weight-600 editable-data"
            contenteditable="true"
            ref="formTitle"
            @focusout="updateFormTitle"
          >
            {{ title }}
          </span>
        <div class="left-actions em-flex-row em-flex-space-between em-p-12-16">
          <p
            v-if="lastSave"
            id="saved-at"
            class="em-font-size-14 em-main-500-color"
          >
            {{ translate("COM_EMUNDUS_FORM_BUILDER_SAVED_AT") }} {{ lastSave }}
          </p>
        </div>
      </header>
      <div class="body em-flex-row em-flex-space-between">
        <aside class="left-panel em-flex-row em-flex-start em-h-100">
          <div class="tabs em-flex-column em-flex-start em-h-100">
            <div class="tab" v-for="(tab, index) in leftPanel.tabs" :key="index" :class="{ active: tab.active }">
              <span
                  class="material-icons em-p-16"
                  @click="tab.url ? goTo(tab.url, 'blank') : selectTab(index)"
              >
                {{ tab.icon }}
              </span>
            </div>
          </div>
          <div class="tab-content em-flex-start">
            <form-builder-elements
                v-if="leftPanelActiveTab === 'Elements'"
                @element-created="onElementCreated"
            ></form-builder-elements>
          </div>
        </aside>
        <section class="em-flex-column em-w-100 em-h-100">
          <transition name="fade" mode="out-in">
            <form-builder-page
              ref="formBuilderPage"
              v-if="currentPage && showInSection == 'page'"
              :key="currentPage.id"
              :profile_id="profile_id"
              :page="currentPage"
              @open-element-properties="onOpenElementProperties"
              @update-page-title="getPages(currentPage.id)"
            ></form-builder-page>
            <form-builder-document-list
              v-if="showInSection == 'documents'"
              :profile_id="profile_id"
              :campaign_id="campaign_id"
              @add-document="onOpenCreateDocument"
              @edit-document="onEditDocument"
            >
            </form-builder-document-list>
          </transition>
        </section>
        <aside class="right-panel em-flex-column em-h-100">
          <transition name="fade" mode="out-in">
            <div id="form-hierarchy" v-if="showInRightPanel == 'hierarchy'" class="em-w-100">
              <form-builder-pages
                  :pages="pages"
                  :selected="selectedPage"
                  :profile_id="profile_id"
                  @select-page="selectPage($event)"
                  @add-page="getPages(currentPage.id)"
                  @delete-page="selectedPage = pages[0].id;"
                  @open-page-properties="onOpenPageProperties"
              ></form-builder-pages>
              <hr>
              <form-builder-documents
                  :profile_id="profile_id"
                  :campaign_id="campaign_id"
                  @show-documents="setSectionShown('documents')"
                  @open-create-document="onOpenCreateDocument"
              ></form-builder-documents>
            </div>
            <form-builder-page-properties
                v-if="showInRightPanel == 'page-properties'"
                @close="onClosePageProperties"
                :profile_id="profile_id"
                :pages="pages"
            >
            </form-builder-page-properties>
            <form-builder-element-properties
                v-if="showInRightPanel == 'element-properties'"
                @close="onCloseElementProperties"
                :element="selectedElement"
                :profile_id="profile_id"
            ></form-builder-element-properties>
            <form-builder-create-document
                v-if="showInRightPanel == 'create-document'"
                :profile_id="profile_id"
                :current_document="selectedDocument ? selectedDocument : null"
                @close="onCloseCreateDocument"
                @documents-updated="onCloseCreateDocument"
            >
            </form-builder-create-document>
          </transition>
        </aside>
      </div>
    </modal>
  </div>
</template>

<script>
// components
import FormBuilderElements  from "../components/FormBuilder/FormBuilderElements";
import FormBuilderElementProperties  from "../components/FormBuilder/FormBuilderElementProperties";
import FormBuilderPage      from "../components/FormBuilder/FormBuilderPage";
import FormBuilderPageProperties from "../components/FormBuilder/FormBuilderPageProperties";
import FormBuilderPages     from "../components/FormBuilder/FormBuilderPages";
import FormBuilderDocuments from "../components/FormBuilder/FormBuilderDocuments";
import FormBuilderDocumentList from "../components/FormBuilder/FormBuilderDocumentList";
import FormBuilderCreateDocument from "../components/FormBuilder/FormBuilderCreateDocument";

// services
import formService from '../services/form.js';

export default {
  name: 'FormBuilder',
  components: {
    FormBuilderPageProperties,
    FormBuilderElements,
    FormBuilderElementProperties,
    FormBuilderPage,
    FormBuilderPages,
    FormBuilderDocuments,
    FormBuilderDocumentList,
    FormBuilderCreateDocument,
  },
  data() {
    return {
      profile_id: 0,
      campaign_id: 0,
      title: '',
      pages: [],
      showInSection: 'page',
      selectedPage: 0,
      selectedElement: null,
      selectedDocument: null,
      showInRightPanel: 'hierarchy',
      lastSave: null,
      leftPanel: {
        tabs: [
          {
            title: 'Elements',
            icon: 'edit_note',
            active: true
          },
          {
            title: 'Translations',
            icon: 'translate',
            active: false,
            url: '/parametres-globaux'
          },
        ],
      },
    }
  },
  created() {
    this.profile_id = this.$store.state.global.datas.prid.value;
    this.campaign_id = this.$store.state.global.datas.cid.value;
    this.getFormTitle();
    this.getPages();
  },
  mounted() {
    this.$modal.show('formBuilder');
  },
  methods: {
    getFormTitle() {
      formService.getProfileLabelByProfileId(this.profile_id).then(response => {
        if (response.status !== false) {
          this.title = response.data.data.label;
        }
      });
    },
    updateFormTitle()
    {
      this.title = this.$refs.formTitle.innerText;
      formService.updateFormLabel({
        label: this.title,
        prid: this.profile_id,
      });
    },
    getPages(page_id = 0) {
      formService.getFormsByProfileId(this.profile_id).then(response => {
        this.pages = response.data.data;

        if (page_id === 0) {
          this.selectedPage = this.pages[0].id;
        } else {
          this.selectedPage = page_id;
        }

        formService.getSubmissionPage(this.profile_id).then(response => {
          const formId = response.data.link.match(/formid=(\d+)/)[1];
          if (formId) {
            // check if the form is already in the pages
            const page = this.pages.find(page => page.id === formId);
            if (!page) {
              this.pages.push({
                id: formId,
                label: this.translate('COM_EMUNDUS_FORM_BUILDER_SUBMISSION_PAGE'),
                type: 'submission',
                elements: [],
              });
            }
          }
        });
      });
    },
    onElementCreated(elementIndex) {
      this.$refs.formBuilderPage.getSections(elementIndex);
    },
    onOpenElementProperties(event)
    {
      this.selectedElement = event;
      this.showInRightPanel = 'element-properties';
    },
    onOpenPageProperties()
    {
      this.showInRightPanel = 'page-properties';
    },
    onCloseCreateDocument()
    {
      this.showInRightPanel = 'hierarchy';
    },
    onCloseElementProperties()
    {
      this.selectedElement = null;
      this.showInRightPanel = 'hierarchy';
      this.$refs.formBuilderPage.getSections();
    },
    onClosePageProperties(page = null)
    {
      if(page) {
        this.pages.splice(this.pages.length-1,0, page)
        this.selectedPage = page.id;
      }
      this.showInRightPanel = 'hierarchy';
    },
    onOpenCreateDocument()
    {
      this.selectedDocument = null;
      this.showInRightPanel = 'create-document';
      this.setSectionShown('documents');
    },
    onEditDocument(document)
    {
      this.selectedDocument = document;
      this.showInRightPanel = 'create-document';
      this.setSectionShown('documents');
    },
    selectTab(index) {
      // unset selected tab
      this.leftPanel.tabs.forEach(tab => {
        tab.active = false;
      });
      // set selected tab
      this.leftPanel.tabs.forEach((tab, i) => {
        tab.active = (i === index);
      });
    },
    selectPage(page_id) {
      this.selectedPage = page_id;
      this.setSectionShown('page');
    },
    setSectionShown(section) {
      this.showInSection = section;
    },
    goTo(url, blank = false) {
      const baseUrl = window.location.origin;

      if(blank) {
        window.open(baseUrl + url, '_blank');
      } else {
        window.location.href = baseUrl + url;
      }
    }
  },
  computed: {
    currentPage() {
      return this.pages.find(page => page.id === this.selectedPage);
    },
    leftPanelActiveTab() {
      return this.leftPanel.tabs.find(tab => tab.active).title;
    }
  },
  watch: {
    "$store.state.formBuilder.lastSave": {
      handler(newValue) {
        this.lastSave = newValue;
      },
      deep: true
    },
  }
}
</script>

<style lang="scss">
#formBuilder {
  background: white;

  header {
    box-shadow: inset 0px -1px 0px #E3E5E8;

    button {
      margin: 8px 16px;
      height: 32px;
    }

    #saved-at {
      white-space: nowrap;
    }
  }

  .body {
    height: calc(100% - 48px);

    aside, section {
      justify-content: flex-start;
    }

    section {
      overflow-y: auto;
      background: #f8f8f8;
    }

    .right-panel {
      min-width: 366px;
      width: 366px;
      border-left: solid 1px #E3E5E8;

      > div {
        width: 100%;
      }
    }

    .left-panel {
      padding: 0;
      border-right: solid 1px #E3E5E8;
      align-self: flex-start;

      .tabs {
        align-self: flex-start;
        align-items: flex-start;
        border-right: solid 1px #E3E5E8;

        .tab {
          cursor: pointer;

          &.active {
            background-color: #f8f8f8;
          }

          .material-icons {
            font-size: 22px !important;
          }
        }
      }

      .tab-content {
        align-items: flex-start;
        padding: 0 16px;
        height: 100%;
      }
    }

    .form-builder-title {
      font-weight: 700;
      font-size: 16px;
      line-height: 19px;
      letter-spacing: 0.0015em;
      color: #080C12;
    }
  }

  .editable-data {
    padding: 4px;
    border-radius: 4px;

    &:focus {
      background-color: #DFF5E9;
    }
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
