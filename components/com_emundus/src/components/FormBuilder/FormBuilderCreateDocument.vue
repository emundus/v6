<template>
  <div id="form-builder-create-document">
    <div class="em-flex-row em-flex-space-between em-p-16" v-if="context !== 'element'">
      <p class="em-font-weight-500">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PROPERTIES") }}</p>
      <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
    </div>
    <ul id="properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in activeTabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active, 'em-w-50': activeTabs.length == 2, 'em-w-100': activeTabs.length == 1}"
          class="em-p-16 em-pointer"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>

    <div id="properties">
      <div id="general-properties" class="em-p-16" v-show="tabs[0].active">
        <div class="em-mb-16 em-flex-row em-flex-space-between">
          <label class="em-font-weight-500">{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_REQUIRED") }}</label>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="isMandatory" @click="toggleDocumentMandatory">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

        <div class="em-mb-16" id="selectDoc" >
          <label for="title" class="em-font-weight-500">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NAME") }}</label>
          <incremental-select
              v-if="models.length > 0"
              :options="documentList"
              :defaultValue="this.current_document.id "
              :locked="mode != 'create'"
              @update-value="updateDocumentSelectedValue"
          >
          </incremental-select>
        </div>

        <div class="em-mb-16">
          <label class="em-font-weight-500">{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DESCRIPTION') }}</label>
          <textarea id="descDoc" name="" rows="5" v-model="document.description[shortDefaultLang]">{{ document.description[shortDefaultLang] }}</textarea>
        </div>

        <div class="em-mb-16" >
          <label class="em-font-weight-500">{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_TYPES') }}</label>
          <div v-for="(filetype, index) in fileTypes" :key="filetype.value" class="em-flex-row em-mb-4 em-flex-align-start" name="formatDocCheckbox">
            <input
              type="checkbox"
              name="filetypes"
              style="height: auto;"
              :id="filetype.value"
              :value="filetype.value"
              v-model="document.selectedTypes[filetype.value]"
              @change="checkFileType"
            >
            <label :for="filetype.value" class="em-font-weight-400 em-mb-0-important em-ml-8"> {{ translate(filetype.title) }} ({{ filetype.value }})</label>
          </div>
        </div>

        <div class="em-mb-16">
          <label for="nbmax" class="em-font-weight-500">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NBMAX") }}</label>
          <input type="number" id="nbmax" class="em-w-100" v-model="document.nbmax">
        </div>
      </div>

      <div id="advanced-properties" class="em-p-16" v-show="tabs[1].active">
        <div v-show="hasImg" id="resolution" class="em-mb-16">
          <label class="em-font-weight-500">{{ translate('COM_EMUNDUS_ONBOARD_IMAGE_WIDTH') }}</label>
          <div class="em-w-100 em-flex-row em-flex-space-between">
            <div class="em-w-50 em-mr-4">
              <label for="minResolutionW" class="em-font-weight-400">{{ translate("COM_EMUNDUS_ONBOARD_MIN_RESOLUTION_PLACEHOLDER") }}</label>
              <input type="number" id="minResolutionW" class="em-w-100" v-model="document.minResolution.width" :max="document.maxResolution.width">
            </div>
            <div class="em-w-50 em-ml-4">
              <label for="maxResolutionW" class="em-font-weight-400">{{ translate("COM_EMUNDUS_ONBOARD_MAX_RESOLUTION_PLACEHOLDER") }}</label>
              <input type="number" id="maxResolutionW" class="em-w-100" v-model="document.maxResolution.width" :min="document.minResolution.width">
            </div>
          </div>

          <label class="em-font-weight-500">{{ translate('COM_EMUNDUS_ONBOARD_IMAGE_HEIGHT') }}</label>
          <div class="em-w-100 em-flex-row em-flex-space-between">
            <div class="em-w-50 em-mr-4">
              <label for="minResolutionH" class="em-font-weight-400">{{ translate("COM_EMUNDUS_ONBOARD_MIN_RESOLUTION_PLACEHOLDER") }}</label>
              <input type="number" id="minResolutionH" class="em-w-100" v-model="document.minResolution.height" :max="document.maxResolution.height">
            </div>
            <div class="em-w-50 em-ml-4">
              <label for="maxResolutionH" class="em-font-weight-400">{{ translate("COM_EMUNDUS_ONBOARD_MAX_RESOLUTION_PLACEHOLDER") }}</label>
              <input type="number" id="maxResolutionH" class="em-w-100" v-model="document.maxResolution.height" :min="document.minResolution.height">
            </div>
          </div>
        </div>

        <div id="document-sample">
          <label class="em-font-weight-500">{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_MODEL_TITLE') }}</label>
          <div class="em-mb-16 em-flex-row em-flex-space-between">
            <label for="has-model" class="em-font-weight-500">{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_GIVE_MODEL') }}</label>
            <div class="em-toggle">
              <input type="checkbox" id="has-model" name="has-model" class="em-toggle-check" v-model="hasSample" @change="onHasSampleChange">
              <strong class="b em-toggle-switch"></strong>
              <strong class="b em-toggle-track"></strong>
            </div>
          </div>
          <div v-if="hasSample && currentSample" id="current-sample" class="em-mb-16">
            <p>{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_CURRENT_MODEL') }}</p>
            <a :href="currentSample" target="_blank">{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DOWNLOAD_SAMPLE') }}</a>
          </div>
          <div v-if="hasSample">
            <label for="sample" id="formbuilder_attachments_sample_upload">
              <span v-if="!currentSample">{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_MODEL_ADD') }}</span>
              <span v-else>{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_MODEL_EDIT') }}</span>
              <span class="material-icons-outlined em-ml-4 em-text-neutral-900">backup</span>
            </label>
            <input id="sample" style="display: none" name="sample" type="file" ref="sampleFileInput" @change="onSampleFileInputChange" accept=".pdf,.doc,.docx,.png,.jpg,.xls,.xlsx"/>
          </div>
          <div v-if="newSample !== ''">
            <p class="em-neutral-700-color">{{ translate('COM_EMUNDUS_FORMBUILDER_DOCUMENTS_MODEL_FILE_UPLOADED') }} : {{ this.newSample.name}}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="em-p-16">
      //todo context === 'element'
      <button class="em-primary-button" id="saveFormCreateDoc" @click="saveDocument">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_DOCUMENT_SAVE') }}</button>
    </div>
  </div>
</template>

<script>
import formService from '../../services/form';
import formBuilderService from '../../services/formbuilder';
import campaignService from '../../services/campaign';
import globalMixin from "../../mixins/mixin";
import editor from '../editor.vue';
import IncrementalSelect from "../IncrementalSelect";
import formBuilderMixin from "../../mixins/formbuilder";
import Swal from 'sweetalert2';
import {parseInt} from "lodash";

export default {
  name: 'FormBuilderCreateDocument',
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    current_document: {
      type: [Object,Number],
      default: null
    },
    mandatory: {
      type: Boolean,
      default: true
    },
		mode: {
			type: String,
			default: "create"
		},
    context: {
      type: String,
      required: false,
      default: ''
    }
  },
  components: {
    IncrementalSelect,
    editor
  },
  mixins: [globalMixin, formBuilderMixin],
  data() {
    return {
      models: [],
	    modelsUsage: [],
      document: {
        id: null,
        type: {},
        mandatory: this.$props.mandatory,
        nbmax: 1,
        description: {
          fr: '',
          en: ''
        },
        name: {
          fr: '',
          en: ''
        },
        selectedTypes: {},
        minResolution: {
          width: 0,
          height: 0
        },
        maxResolution: {
          width: 0,
          height: 0
        },
	      max_pages_pdf: 0
      },
      fileTypes: [],
      activeTab: 'general',
      tabs: [
        {
          id: 0,
          label: 'COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL',
          active: true,
          published: true,
        },
        {
          id: 1,
          label: 'COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_ADVANCED',
          active: false,
          published: true,
        }
      ],
	    hasPDF: false,
	    hasImg: false,
	    hasSample: false,
	    currentSample: '',
	    newSample: '',
	    sampleFromDocumentId: null
    };
  },
  created() {
    this.getDocumentModels();
    this.getFileTypes();
  },
  methods: {
    parseInt,
    selectTab(tab) {
      this.tabs.forEach(t => {
        t.active = false;
      });
      tab.active = true;
    },
    getDocumentModels() {
      formService.getDocumentModels().then(response => {
        if (response.status) {
          this.models = response.data;
          if(this.current_document !== null && typeof this.current_document !== 'object') {
            this.models.find((model) => {
              if (model.id == this.current_document) {
                this.current_document = model;
              }
            });
          }

          if (this.current_document != null && (this.current_document.docid || this.current_document.id)) {
            this.selectModel({
              target: {
                value: this.current_document.docid ? this.current_document.docid : this.current_document.id
              }
            }, this.current_document.mandatory !== null && this.current_document.mandatory != 'undefined' ? this.current_document.mandatory : null);
          }

					formService.getDocumentModelsUsage(this.models.map((model) => {
						return model.id;
					})).then(response => {
						if (response.status) {
							this.modelsUsage = response.data;
						}
					});
        }
      });
    },
    toggleDocumentMandatory() {
      this.document.mandatory = this.document.mandatory == "1" ? "0" : "1";
    },
    getFileTypes() {
      this.fileTypes = require('../../../data/form-builder-filetypes.json');
      this.fileTypes.forEach(filetype => {
        this.document.selectedTypes[filetype.value] = false;
      });
    },
    checkFileType(event) {
      this.document.selectedTypes[event.target.value] = event.target.checked;
			this.hasImgFormat();
			this.hasPDFFormat();
    },
	  selectModel(event, mandatory = null) {
      if (event.target.value !== 'none') {
        const model = this.models.find(model => model.id == event.target.value);
        this.document.id = model.id;
        this.document.type = model.type;
        this.document.mandatory = mandatory == null ? model.mandatory : mandatory;
        this.document.nbmax = model.nbmax;
        this.document.description = model.description;
        this.document.name = model.name;
        this.document.minResolution = {
	        width: model.min_width,
	        height: model.min_height
        };
        this.document.maxResolution = {
	        width: model.max_width,
	        height: model.max_height
        };
	      this.document.max_pages_pdf = model.max_pages_pdf;

        this.fileTypes.forEach(filetype => {
          this.document.selectedTypes[filetype.value] = false;
        });

        let types = model.allowed_types.split(';');
        types.forEach((type) => {
          if(['pdf'].includes(type)) {
            this.document.selectedTypes['pdf'] = true;
          }
          if(['jpeg','jpg','png','gif'].includes(type)) {
            this.document.selectedTypes['jpeg;jpg;png;gif'] = true;
          }
          if(['doc','docx','odt','ppt','pptx'].includes(type)) {
            this.document.selectedTypes['doc;docx;odt;ppt;pptx'] = true;
          }
          if(['xls','xlsx','odf'].includes(type)) {
            this.document.selectedTypes['xls;xlsx;odf'] = true;
          }
          if(['mp3'].includes(type)) {
            this.document.selectedTypes['mp3'] = true;
          }
          if(['mp4'].includes(type)) {
            this.document.selectedTypes['mp4'] = true;
          }

					if (['zip'].includes(type)) {
						this.document.selectedTypes['zip'] = true;
					}
				});

				this.hasImgFormat();
				this.hasPDFFormat();
			}
    },
	  saveDocument() {
	    let empty_names = true;
			Object.values(this.document.name).forEach((name) => {
				if (name != "") {
					empty_names = false;
				}
			});

			if (empty_names === true) {
				Swal.fire({
					type: 'warning',
					title: this.translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PLEASE_FILL_TYPE"),
					reverseButtons: true,
					customClass: {
						title: 'em-swal-title',
						confirmButton: 'em-swal-confirm-button',
						actions: "em-swal-single-action",
					},
				});
				return false;
			}

      const isModel = this.models.find((model) => {
        return model.id == this.document.id;
      });

      let types = [];
      Object.entries(this.document.selectedTypes).forEach((entry, type) => {
        if (entry[1]) {
          types.push(entry[0]);
        }
      });

			if (types.length < 1) {
				Swal.fire({
					type: 'warning',
					title: this.translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PLEASE_FILL_FORMAT"),
					reverseButtons: true,
					customClass: {
						title: 'em-swal-title',
						confirmButton: 'em-swal-confirm-button',
						actions: "em-swal-single-action",
					},
				});
				return false;
			}

      if (!isModel) {
        this.document.id = null;

        const data = {
          pid:  this.profile_id,
          types: JSON.stringify(types),
          document: JSON.stringify(this.document),
	        has_sample: this.hasSample,
        };

	      if (this.hasSample && this.newSample !== null) {
		      const sampleFileInput = this.$refs.sampleFileInput;
		      const file = sampleFileInput.files[0];
		      data.sample = file;
	      }

        campaignService.updateDocument(data, true).then(response => {
          this.document.id = response.data.status;
          this.$emit('documents-updated', this.document);
        });
      } else {
	      const data = {
		      profile_id:  this.profile_id,
		      document_id: this.document.id,
		      types: JSON.stringify(types),
		      document: JSON.stringify(this.document),
		      has_sample: this.hasSample,
	      };

	      if (this.hasSample && this.newSample !== null) {
		      const sampleFileInput = this.$refs.sampleFileInput;
		      const file = sampleFileInput.files[0];
		      data.sample = file;
	      }

	      if (Object.keys(this.modelsUsage).includes(this.document.id) && this.modelsUsage[this.document.id].usage > 1) {
					this.swalConfirm(
							this.translate('COM_EMUNDUS_FORM_BUILDER_MULTIPLE_FORMS_IMPACTED'),
							this.translate('COM_EMUNDUS_FORM_BUILDER_MULTIPLE_FORMS_IMPACTED_TEXT') + ' : ' + this.modelsUsage[this.document.id].profiles.map((profile) => {
								return profile.label;
							}).join(', '),
							this.translate('COM_EMUNDUS_ONBOARD_OK'),
							this.translate('COM_EMUNDUS_ONBOARD_CANCEL'),
					).then((response) => {
						if (response) {
							formBuilderService.updateDocument(data).then(response => {
								this.$emit('documents-updated');
							});
						}
					});
	      } else {
		      formBuilderService.updateDocument(data).then(response => {
			      this.$emit('documents-updated');
		      });
				}
      }

    },
    updateDocumentSelectedValue(document)
    {
      if (document.id) {
        this.document.name[this.shortDefaultLang] = document.label;
        this.selectModel({target: {value: document.id}}, this.current_document && this.current_document.id && this.current_document.id == document.id ? this.current_document.mandatory : this.mandatory);
      } else {
        this.document.id = null;
        this.document.name[this.shortDefaultLang] = document.label;
      }
    },
	  hasImgFormat() {
			let hasImg = false

		  const imgExtensions = ['jpeg', 'jpg', 'png', 'gif'];
		  Object.keys(this.document.selectedTypes).forEach((extensions) => {
			  if (this.document.selectedTypes[extensions] && imgExtensions.some(imgExt => extensions.includes(imgExt))) {
				  hasImg = true;
			  }
		  });

		  this.hasImg = hasImg;
	  },
	  hasPDFFormat() {
		  let hasPDF = false;

		  Object.keys(this.document.selectedTypes).forEach((extensions) => {
			  if (this.document.selectedTypes[extensions] && extensions.includes('pdf')) {
				  hasPDF = true;
			  }
		  });

		  this.hasPDF = hasPDF;
	  },
	  onHasSampleChange() {
			if (!this.hasSample) {
				this.newSample = '';
			}
	  },
	  onSampleFileInputChange(event) {
		  const files = event.target.files || [];
		  if (files.length > 0) {
			  const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
				const fileExtension = files[0].name.split('.').pop().toLowerCase();
				if (!allowedExtensions.includes(fileExtension)) {
					Swal.fire({
						type: 'warning',
						title: this.translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_SAMPLE_WRONG_FORMAT'),
						reverseButtons: true,
						customClass: {
							title: 'em-swal-title',
							confirmButton: 'em-swal-confirm-button',
							actions: 'em-swal-single-action',
						},
					});
					this.newSample = null;
					return false;
				}
			  this.newSample = files[0];
		  } else {
			  this.newSample = null;
		  }
	  },
	  getCurrentSample() {
		  this.sampleFromDocumentId = this.document.id;

		  if (this.document.id === null) {
				this.hasSample = false;
				this.currentSample = '';
		  }	else {
				formBuilderService.getDocumentSample(Number(this.document.id), Number(this.profile_id)).then((response) => {

					if (response.status && response.data) {
						this.hasSample = response.data.has_sample === '1';
						this.currentSample = this.hasSample ? response.data.sample_filepath : '';
					} else {
						this.hasSample = false;
						this.currentSample = '';
					}
				});
			}
	  },
  },
  computed: {
    activeTabs() {
      return this.tabs.filter((tab) =>  {
        return tab.published;
      });
    },
    documentList() {
      return this.models.map((document) => {
        return {
          id: document.id,
          label: document.name[this.shortDefaultLang]
        };
      });
    },
    isMandatory() {
      return this.document.mandatory == '1';
    },
    incSelectDefaultValue() {
			let defaultValue = null;
	    if (this.current_document && (this.current_document.docid || this.current_document.id)) {
				defaultValue = this.current_document.docid ? this.current_document.docid : this.current_document.id;
	    }
			return defaultValue;
    },
  },
  watch: {
    current_document(newValue) {
      if (newValue && (newValue.docid || newValue.id)) {
        if (this.models.length < 1) {
          this.getDocumentModels().then(() => {
            this.selectModel({
              target: {
                value: newValue.docid ? newValue.docid : newValue.id
              }
            },  newValue.mandatory ? newValue.mandatory : null);
          });
        } else {
          this.selectModel({
            target: {
              value: newValue.docid ? newValue.docid : newValue.id
            }
          }, newValue.mandatory ? newValue.mandatory : null);
        }
      }
    },
	  document: {
		  handler(newValue) {
				if (newValue.id !== this.sampleFromDocumentId) {
					this.getCurrentSample();
				}
		  },
		  deep: true
	  },
  }
}
</script>
<style>
#formbuilder_attachments_sample_upload{
  display: flex;
  align-items: center;
  color: black;
  padding: 6px;
  border: dashed 1px #E3E3E3;
  border-radius: 8px;
}
</style>
