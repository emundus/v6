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
      <notifications
          group="foo-velocity"
          animation-type="velocity"
          :speed="500"
          position="bottom left"
          :classes="'vue-notification-custom'"
      />
      <header class="em-flex-row em-flex-space-between">
        <div class="right-actions">
          <span id="go-back" class="material-icons-outlined em-p-12-16 em-pointer" @click="goTo('/formulaires')">
            navigate_before
          </span>
        </div>
          <span
            class="em-font-size-14  em-font-weight-600 editable-data"
            contenteditable="true"
            ref="formTitle"
            @focusout="updateFormTitle"
            @keyup.enter="updateFormTitleKeyup"
          >
            {{ title }}
          </span>
        <div class="left-actions em-flex-row em-flex-space-between em-p-12-16">
          <p v-if="lastSave" id="saved-at" class="em-font-size-14 em-main-500-color">
            {{ translate("COM_EMUNDUS_FORM_BUILDER_SAVED_AT") }} {{ lastSave }}
          </p>
        </div>
      </header>
      <div class="body em-flex-row em-flex-space-between">
        <aside class="left-panel em-flex-row em-flex-start em-h-100">
          <div class="tabs em-flex-column em-flex-start em-h-100">
            <div class="tab" v-for="tab in displayedLeftPanels" :key="title" :class="{ active: tab.active }">
              <span
                  class="material-icons-outlined em-p-16"
                  @click="tab.url ? goTo(tab.url, 'blank') : selectTab(tab.title)"
              >
                {{ tab.icon }}
              </span>
            </div>
          </div>
          <div class="tab-content em-flex-start">
            <form-builder-elements v-if="leftPanelActiveTab === 'Elements'" @element-created="onElementCreated">
            </form-builder-elements>
            <form-builder-document-formats
                v-else-if="leftPanelActiveTab === 'Documents'"
                :profile_id="profile_id"
                @document-created="onDocumentCreated"
                @dragging-element="onCloseCreateDocument"
            >
            </form-builder-document-formats>
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
              @open-section-properties="onOpenSectionProperties"
              @update-page-title="getPages(currentPage.id)"
            ></form-builder-page>
            <form-builder-document-list
              ref="formBuilderDocumentList"
              v-else-if="showInSection == 'documents'"
              :profile_id="profile_id"
              :campaign_id="campaign_id"
              @add-document="onOpenCreateDocument"
              @edit-document="onEditDocument"
              @delete-document="onDeleteDocument"
            ></form-builder-document-list>
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
                  ref="formBuilderDocuments"
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
            ></form-builder-page-properties>
            <form-builder-element-properties
                v-if="showInRightPanel == 'element-properties'"
                @close="onCloseElementProperties"
                :element="selectedElement"
                :profile_id="profile_id"
            ></form-builder-element-properties>
            <form-builder-section-properties
                v-if="showInRightPanel == 'section-properties'"
                @close="onCloseSectionProperties"
                :section_id="selectedSection.group_id"
                :profile_id="profile_id"
              ></form-builder-section-properties>
            <form-builder-create-document
                v-if="showInRightPanel == 'create-document'"
                ref="formBuilderCreateDocument"
                :profile_id="profile_id"
                :current_document="selectedDocument ? selectedDocument : null"
                :mandatory="createDocumentMandatory"
                @close="onCloseCreateDocument"
                @documents-updated="onUpdateDocument"
            ></form-builder-create-document>
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
import FormBuilderSectionProperties  from "../components/FormBuilder/FormBuilderSectionProperties";
import FormBuilderPage      from "../components/FormBuilder/FormBuilderPage";
import FormBuilderPageProperties from "../components/FormBuilder/FormBuilderPageProperties";
import FormBuilderPages     from "../components/FormBuilder/FormBuilderPages";
import FormBuilderDocuments from "../components/FormBuilder/FormBuilderDocuments";
import FormBuilderDocumentList from "../components/FormBuilder/FormBuilderDocumentList";
import FormBuilderCreateDocument from "../components/FormBuilder/FormBuilderCreateDocument";
import FormBuilderDocumentFormats from "../components/FormBuilder/FormBuilderDocumentFormats";

// services
import formService from '../services/form.js';

export default {
  name: 'FormBuilder',
  components: {
    FormBuilderSectionProperties,
    FormBuilderPageProperties,
    FormBuilderElements,
    FormBuilderElementProperties,
    FormBuilderPage,
    FormBuilderPages,
    FormBuilderDocuments,
    FormBuilderDocumentList,
    FormBuilderCreateDocument,
    FormBuilderDocumentFormats
  },
  data() {
    return {
      profile_id: 0,
      campaign_id: 0,
      title: '',
      pages: [],
      showInSection: 'page',
      selectedPage: 0,
      selectedSection: null,
      selectedElement: null,
      optionsSelectedElement: false,
      selectedDocument: null,
      showInRightPanel: 'hierarchy',
      createDocumentMandatory: true,
      lastSave: null,
      leftPanel: {
        tabs: [
          {
            title: 'Elements',
            icon: 'edit_note',
            active: true,
            displayed: true
          },
          {
            title: 'Documents',
            icon: 'edit_note',
            active: false,
            displayed: false
          },
          {
            title: 'Translations',
            icon: 'translate',
            active: false,
            displayed: true,
            url: '/parametres-globaux'
          },
        ],
      },
    }
  },
  created() {
    if(parseInt(this.$store.state.global.manyLanguages) === 0){
      this.leftPanel.tabs[2].displayed = false;
    }
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
      this.title = this.$refs.formTitle.innerText.trim().replace(/[\r\n]/gm, " ");
      this.$refs.formTitle.innerText = this.$refs.formTitle.innerText.trim().replace(/[\r\n]/gm, " ");
      formService.updateFormLabel({
        label: this.title,
        prid: this.profile_id,
      });
    },
    updateFormTitleKeyup() {
      document.activeElement.blur();
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
    onDocumentCreated() {
      this.$refs.formBuilderDocuments.getDocuments();
      this.$refs.formBuilderDocumentList.getDocuments();
    },
    onOpenSectionProperties(event)
    {
      this.selectedSection = event;
      this.showInRightPanel = 'section-properties';
    },
    onOpenElementProperties(event)
    {
      this.selectedElement = event;
      if(this.selectedElement.plugin === 'dropdown'){
        this.optionsSelectedElement = true;
      } else {
        if(this.optionsSelectedElement === true){
          this.$refs.formBuilderPage.getSections();
        }
        this.optionsSelectedElement = false;
      }
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
	  onUpdateDocument()
	  {
		  this.$refs.formBuilderDocumentList.getDocuments();
		  this.showInRightPanel = 'hierarchy';
	  },
    onCloseElementProperties()
    {
      this.selectedElement = null;
      this.showInRightPanel = 'hierarchy';
      this.$refs.formBuilderPage.getSections();
    },
    onCloseSectionProperties()
    {
      this.selectedSection = null;
      this.showInRightPanel = 'hierarchy';
      this.$refs.formBuilderPage.getSections();
    },
    onClosePageProperties(page = null)
    {
      if (page) {
        this.pages.splice(this.pages.length-1,0, page)
        this.selectedPage = page.id;
      }
      this.showInRightPanel = 'hierarchy';
    },
    onOpenCreateDocument(mandatory = "1")
    {
      this.selectedDocument = null;
	    if (this.$refs.formBuilderCreateDocument) {
		    this.$refs.formBuilderCreateDocument.document.mandatory = mandatory;
	    } else {
		    this.createDocumentMandatory = mandatory;
	    }
	    this.showInRightPanel = 'create-document';
	    this.setSectionShown('documents');
    },
    onEditDocument(document)
    {
      this.selectedDocument = document;
      this.showInRightPanel = 'create-document';
      this.setSectionShown('documents');
    },
    onDeleteDocument(){
      this.selectedDocument = null;
      this.showInRightPanel = 'hierarchy';
      this.setSectionShown('documents');
    },
    selectTab(title) {
      this.leftPanel.tabs.forEach((tab) => {
        tab.active = tab.title == title;
      });
    },
    selectPage(page_id) {
      this.selectedPage = page_id;
      this.setSectionShown('page');
    },
    setSectionShown(section) {
      if (section === 'documents') {
        this.leftPanel.tabs.forEach((tab, i) => {
          this.leftPanel.tabs[i].displayed = tab.title != 'Elements';
        });
        this.selectTab('Documents');
        this.selectedPage = null;
      } else {
        this.leftPanel.tabs.forEach((tab, i) => {
          this.leftPanel.tabs[i].displayed = tab.title != 'Documents';
        });
        this.selectTab('Elements');
      }
      this.showInSection = section;
    },
    goTo(url, blank = false) {
      const baseUrl = window.location.origin;

      if (blank) {
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
    },
    displayedLeftPanels() {
      const displayedPanels = this.leftPanel.tabs.filter((tab) => {
        return tab.displayed;
      });

      return displayedPanels;
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
	      height: 100%;
	      overflow: scroll;
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

          .material-icons, .material-icons-outlined {
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
    padding: 4px 8px !important;
    border-radius: 4px;
    margin-bottom: 0;

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
