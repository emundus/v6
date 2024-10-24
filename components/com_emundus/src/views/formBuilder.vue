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
      <div v-if="$store.state.global.currentLanguage !== $store.state.global.defaultLang" class="justify-center bg-[#FEF6EE] flex items-center gap-3 p-2">
        <span class="material-icons-outlined text-[#EF681F]">warning_amber</span>
        <span>{{ translate('COM_EMUNDUS_ONBOARD_FORMBUILDER_EDIT_DEFAULT_LANG') }}{{ defaultLangLabel }}</span>
      </div>
      <header class="em-flex-row em-flex-space-between">
        <div class="right-actions em-p-12-16 em-flex-row em-pointer"
             @click="clickGoBack">
          <span id="go-back"
                class="material-icons-outlined">
            navigate_before
          </span>
          <span class="em-ml-8 em-text-neutral-900" >{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
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
      <div v-if="principalContainer === 'default'" class="body em-flex-row em-flex-space-between">
        <aside class="left-panel em-flex-row em-flex-start em-h-100">
          <div class="tabs em-flex-column em-flex-start em-h-100">
            <div class="tab" v-for="(tab,i) in displayedLeftPanels" :key="title + '_' + i" :class="{ active: tab.active }">
              <span
                  class="material-icons-outlined em-p-16"
                  @click="tab.url ? goTo(tab.url, 'blank') : selectTab(tab.title)"
              >
                {{ tab.icon }}
              </span>
            </div>
          </div>
          <div class="tab-content em-flex-start">
            <form-builder-elements v-if="leftPanelActiveTab === 'Elements'" @element-created="onElementCreated" :form="currentPage">
            </form-builder-elements>
            <form-builder-document-formats
                v-else-if="leftPanelActiveTab === 'Documents'"
                :profile_id="profile_id"
                @open-create-document="onEditDocument"
            >
            </form-builder-document-formats>
          </div>
        </aside>
        <section class="em-flex-column em-w-100 em-h-100">
          <transition name="fade" mode="out-in">
            <form-builder-page
              ref="formBuilderPage"
              v-if="currentPage && showInSection === 'page'"
              :key="currentPage.id"
              :profile_id="parseInt(profile_id)"
              :page="currentPage"
              :mode="mode"
              @open-element-properties="onOpenElementProperties"
              @open-section-properties="onOpenSectionProperties"
              @open-create-model="onOpenCreateModel"
              @update-page-title="getPages(currentPage.id)"
            ></form-builder-page>
            <form-builder-document-list
              ref="formBuilderDocumentList"
              v-else-if="showInSection === 'documents'"
              :profile_id="parseInt(profile_id)"
              :campaign_id="parseInt(campaign_id)"
              @add-document="onOpenCreateDocument"
              @edit-document="onEditDocument"
              @delete-document="onDeleteDocument"
            ></form-builder-document-list>
          </transition>
        </section>
	      <transition name="slide-fade" mode="out-in">
		      <aside v-if="rightPanel.tabs.includes(showInRightPanel)" class="right-panel em-flex-column em-h-100">
	          <transition name="fade" mode="out-in">
	            <div id="form-hierarchy" v-if="showInRightPanel === 'hierarchy' && rightPanel.tabs.includes('hierarchy')" class="em-w-100">
	              <form-builder-pages
	                  :pages="pages"
	                  :selected="parseInt(selectedPage)"
	                  :profile_id="parseInt(profile_id)"
	                  @select-page="selectPage($event)"
	                  @add-page="getPages(currentPage.id)"
	                  @delete-page="selectedPage = pages[0].id;"
	                  @open-page-create="principalContainer = 'create-page';"
	                  @reorder-pages="onReorderedPages"
	                  @open-create-model="onOpenCreateModel"
	              ></form-builder-pages>
	              <hr>
	              <form-builder-documents
	                  ref="formBuilderDocuments"
	                  :profile_id="parseInt(profile_id)"
	                  :campaign_id="parseInt(campaign_id)"
	                  @show-documents="setSectionShown('documents')"
	                  @open-create-document="onOpenCreateDocument"
	              ></form-builder-documents>
	            </div>
	            <form-builder-element-properties
	                v-if="showInRightPanel === 'element-properties'"
	                @close="onCloseElementProperties"
	                :element="selectedElement"
	                :profile_id="parseInt(profile_id)"
	            ></form-builder-element-properties>
	            <form-builder-section-properties
	                v-if="showInRightPanel === 'section-properties'"
	                @close="onCloseSectionProperties"
	                :section_id="selectedSection.group_id"
	                :profile_id="parseInt(profile_id)"
	            ></form-builder-section-properties>
		          <form-builder-create-model
				          v-if="showInRightPanel === 'create-model'"
				          :page="selectedPage"
				          @close="showInRightPanel = 'hierarchy';"
		          ></form-builder-create-model>
	            <form-builder-create-document
	                v-if="showInRightPanel === 'create-document' && rightPanel.tabs.includes('create-document')"
	                ref="formBuilderCreateDocument"
	                :key="formBuilderCreateDocumentKey"
	                :profile_id="parseInt(profile_id)"
	                :current_document="selectedDocument ? selectedDocument : null"
	                :mandatory="createDocumentMandatory"
	                :mode="createDocumentMode"
	                @close="showInRightPanel = 'hierarchy'"
	                @documents-updated="onUpdateDocument"
	            ></form-builder-create-document>
	          </transition>
	        </aside>
	      </transition>
      </div>
	    <div v-else-if="principalContainer === 'create-page'">
		    <form-builder-create-page :profile_id="parseInt(profile_id)" @close="onCloseCreatePage"></form-builder-create-page>
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
import FormBuilderCreatePage from "../components/FormBuilder/FormBuilderCreatePage";
import FormBuilderPages     from "../components/FormBuilder/FormBuilderPages";
import FormBuilderDocuments from "../components/FormBuilder/FormBuilderDocuments";
import FormBuilderDocumentList from "../components/FormBuilder/FormBuilderDocumentList";
import FormBuilderCreateDocument from "../components/FormBuilder/FormBuilderCreateDocument";
import FormBuilderDocumentFormats from "../components/FormBuilder/FormBuilderDocumentFormats";

// services
import formService from '../services/form.js';
import FormBuilderCreateModel from "../components/FormBuilder/FormBuilderCreateModel";

export default {
  name: 'FormBuilder',
  components: {
	  FormBuilderCreateModel,
    FormBuilderSectionProperties,
	  FormBuilderCreatePage,
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
			mode: 'forms',
      profile_id: 0,
	    form_id: 0,
      campaign_id: 0,
      title: '',
      pages: [],
	    principalContainer: 'default',
      showInSection: 'page',
      selectedPage: 0,
      selectedSection: null,
      selectedElement: null,
      optionsSelectedElement: false,
      selectedDocument: null,
	    rightPanel: {
				tabs: [
						'hierarchy',
						'element-properties',
						'section-properties',
						'create-model',
						'create-document',
				]
	    },
      showInRightPanel: 'hierarchy',
      createDocumentMandatory: '1',
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
            url: '/parametres-globaux?layout=translation&default_menu=2&object=emundus_setup_profiles'
          },
        ],
      },
	    formBuilderCreateDocumentKey: 0,
	    createDocumentMode: 'create'
    }
  },
  created() {
	  const data = this.$store.getters['global/datas'];
	  if(parseInt(this.$store.state.global.manyLanguages) === 0){
		  this.leftPanel.tabs[2].displayed = false;
	  }
	  this.profile_id = data.prid.value;
	  this.campaign_id = data.cid.value;

	  if (data && data.settingsmenualias && data.settingsmenualias.value) {
		  this.leftPanel.tabs[2].url = '/' + data.settingsmenualias.value + '?layout=translation&default_menu=2&object=emundus_setup_profiles';
	  }

	  if (data && data.mode && data.mode.value) {
			this.mode = data.mode.value;

			if (this.mode === 'eval' || this.mode == 'models') {
				this.rightPanel.tabs = this.rightPanel.tabs.filter(tab => tab !== 'hierarchy' && tab !== 'create-document');
				this.leftPanel.tabs = this.leftPanel.tabs.filter(tab => tab.title != 'Documents');
				this.form_id = this.profile_id;
				this.profile_id = 0;
			}
		}

	  if (this.profile_id > 0) {
		  this.leftPanel.tabs[2].url += '&data=' + this.profile_id;
	  }

	  this.getFormTitle();
    this.getPages();
  },
  mounted() {
    this.$modal.show('formBuilder');
  },
  methods: {
    getFormTitle() {
			if (this.profile_id) {
				formService.getProfileLabelByProfileId(this.profile_id).then(response => {
					if (response.status !== false) {
						this.title = response.data.data.label;
					}
				});
			}
    },
    updateFormTitle()
    {
      this.title = this.$refs.formTitle.innerText.trim().replace(/[\r\n]/gm, " ");
      this.$refs.formTitle.innerText = this.$refs.formTitle.innerText.trim().replace(/[\r\n]/gm, " ");
      formService.updateFormLabel({label: this.title, prid: this.profile_id, form_id: this.form_id});
    },
    updateFormTitleKeyup() {
      document.activeElement.blur();
    },
    getPages(page_id = 0) {
			if (this.profile_id) {
				formService.getFormsByProfileId(this.profile_id).then(response => {
					this.pages = response.data.data;

					if (page_id === 0) {
						this.selectPage(this.pages[0].id);
					} else {
						this.selectPage(String(page_id));
					}
					this.principalContainer = 'default';

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
			} else if (this.form_id) {
				formService.getFormByFabrikId(this.form_id).then(response => {
					this.title = response.data.data.label;
					this.pages = [response.data.data];
					this.selectPage(this.pages[0].id);
					this.principalContainer = 'default';
				});
			}
    },
	  onReorderedPages(reorderedPages) {
		  this.pages = reorderedPages;
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
      if (this.selectedElement.plugin === 'dropdown') {
        this.optionsSelectedElement = true;
      } else {
        if (this.optionsSelectedElement === true){
          this.$refs.formBuilderPage.getSections();
        }
        this.optionsSelectedElement = false;
      }
      this.showInRightPanel = 'element-properties';
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
	  onCloseCreatePage(response)
	  {
			if (response.reload) {
				this.getPages(response.newSelected);
			} else {
				this.principalContainer = 'default';
			}
	  },
	  onOpenCreateModel(pageId)
	  {
			if (pageId > 0) {
				this.selectedPage = pageId;
				this.showInRightPanel = 'create-model';
			} else {
				console.error('No page id provided');
			}
	  },
    onOpenCreateDocument(mandatory = '1')
    {
      this.selectedDocument = null;
	    this.createDocumentMandatory = mandatory;
	    this.createDocumentMode = 'create';
	    this.formBuilderCreateDocumentKey++;
	    this.showInRightPanel = 'create-document';
	    this.setSectionShown('documents');
    },
    onEditDocument(document)
    {
	    this.selectedDocument = document;
	    this.createDocumentMode = 'update';
	    this.createDocumentMandatory = document.mandatory;
	    this.formBuilderCreateDocumentKey++;
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
        tab.active = tab.title === title;
      });
    },
    selectPage(page_id) {
      this.selectedPage = page_id;
      this.setSectionShown('page');
    },
    setSectionShown(section) {
      if (section === 'documents') {
        this.leftPanel.tabs.forEach((tab, i) => {
          this.leftPanel.tabs[i].displayed = tab.title !== 'Elements';
        });
        this.selectTab('Documents');
        this.selectedPage = null;
      } else {
        this.leftPanel.tabs.forEach((tab, i) => {
          this.leftPanel.tabs[i].displayed = tab.title !== 'Documents';
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
    },
    clickGoBack() {
      if (this.principalContainer === 'create-page') {
        this.onCloseCreatePage({reload: false});
      } else {
        window.history.go(-1);
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
      return this.leftPanel.tabs.filter((tab) => {
	      return tab.displayed;
      });
    },
    defaultLangLabel() {
      let label = 'Français';

      switch (this.$store.state.global.defaultLang) {
        case 'en-GB':
          label = 'English';
          break;
        case 'pt-PT':
          label = 'Português';
      }

      return label;
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

  ul {
    list-style-position: inside;
  }

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

	  aside {
			transition: all .3s;
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
	      overflow: auto;
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
	      overflow: auto;
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

	input.editable-data {
		border: none !important;

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
